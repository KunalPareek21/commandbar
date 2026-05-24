<?php
/**
 * REST API endpoints for CommandBar.
 *
 * Registers and handles all REST API routes under the 'commandbar/v1' namespace.
 * Every endpoint performs full capability verification, nonce validation, and
 * input sanitization before any data is returned or action is executed.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Rest_API
 *
 * @since 1.0.0
 */
class CommandBar_Rest_API {

	/**
	 * REST API namespace.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string
	 */
	private const NAMESPACE = 'commandbar/v1';

	/**
	 * Transient TTL in seconds for cached search results.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    int
	 */
	private const CACHE_TTL = 60;

	/**
	 * Maximum number of search results returned per type.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    int
	 */
	private const MAX_RESULTS = 8;

	/**
	 * Register all REST API routes.
	 *
	 * @since 1.0.0
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/search',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'handle_search' ),
				'permission_callback' => array( $this, 'is_authenticated' ),
				'args'                => array(
					'q'    => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static function ( $param ): bool {
							return is_string( $param ) && strlen( $param ) >= 2;
						},
					),
					'type' => array(
						'required'          => false,
						'default'           => 'all',
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => static function ( $param ): bool {
							return in_array( $param, array( 'all', 'posts', 'pages', 'users', 'plugins' ), true );
						},
					),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE,
			'/actions',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'handle_action' ),
				'permission_callback' => array( $this, 'is_authenticated' ),
				'args'                => array(
					'action' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_key',
						'validate_callback' => static function ( $param ): bool {
							return in_array( $param, array( 'flush_rewrite_rules' ), true );
						},
					),
				),
			)
		);
	}

	/**
	 * Verify that the current request comes from a logged-in user.
	 *
	 * The nonce is validated automatically by the REST API infrastructure
	 * when the X-WP-Nonce header is present. This callback provides an
	 * additional authentication gate.
	 *
	 * @since 1.0.0
	 *
	 * @return bool|WP_Error
	 */
	public function is_authenticated(): bool|WP_Error {
		if ( ! is_user_logged_in() ) {
			return new WP_Error(
				'rest_forbidden',
				__( 'You must be logged in to use CommandBar.', 'commandbar-smart-admin-navigation' ),
				array( 'status' => 401 )
			);
		}
		return true;
	}

	/**
	 * Handle GET /commandbar/v1/search.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function handle_search( WP_REST_Request $request ): WP_REST_Response {
		$query = $request->get_param( 'q' );
		$type  = $request->get_param( 'type' );

		// Build a cache key scoped to the current user to prevent data leakage.
		$cache_key = 'commandbar_search_' . get_current_user_id() . '_' . md5( $query . '_' . $type );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $this->success_response( $cached );
		}

		$results = array();

		if ( 'all' === $type || 'posts' === $type ) {
			if ( current_user_can( 'edit_posts' ) ) {
				$results = array_merge( $results, $this->search_posts( $query, 'post' ) );
			}
		}

		if ( 'all' === $type || 'pages' === $type ) {
			if ( current_user_can( 'edit_pages' ) ) {
				$results = array_merge( $results, $this->search_posts( $query, 'page' ) );
			}
		}

		if ( 'all' === $type || 'users' === $type ) {
			if ( current_user_can( 'list_users' ) ) {
				$results = array_merge( $results, $this->search_users( $query ) );
			}
		}

		if ( 'all' === $type || 'plugins' === $type ) {
			if ( current_user_can( 'activate_plugins' ) ) {
				$results = array_merge( $results, $this->search_plugins( $query ) );
			}
		}

		set_transient( $cache_key, $results, self::CACHE_TTL );

		return $this->success_response( $results );
	}

	/**
	 * Handle POST /commandbar/v1/actions.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function handle_action( WP_REST_Request $request ): WP_REST_Response {
		$action = $request->get_param( 'action' );

		switch ( $action ) {
			case 'flush_rewrite_rules':
				if ( ! current_user_can( 'manage_options' ) ) {
					return $this->error_response(
						'rest_forbidden',
						__( 'You do not have permission to perform this action.', 'commandbar-smart-admin-navigation' ),
						403
					);
				}
				flush_rewrite_rules();
				return $this->success_response(
					array(),
					__( 'Rewrite rules flushed successfully.', 'commandbar-smart-admin-navigation' )
				);

			default:
				return $this->error_response(
					'invalid_action',
					__( 'Unknown action requested.', 'commandbar-smart-admin-navigation' ),
					400
				);
		}
	}

	/**
	 * Search posts or pages by title.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param string $query     Search term.
	 * @param string $post_type Post type to search ('post' or 'page').
	 * @return array Array of result objects.
	 */
	private function search_posts( string $query, string $post_type ): array {
		$args = array(
			'post_type'      => $post_type,
			'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
			's'              => $query,
			'posts_per_page' => self::MAX_RESULTS,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		);

		$wp_query = new WP_Query( $args );
		$results  = array();

		foreach ( $wp_query->posts as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post instanceof WP_Post ) {
				continue;
			}

			$status_labels = array(
				'publish' => __( 'Published', 'commandbar-smart-admin-navigation' ),
				'draft'   => __( 'Draft', 'commandbar-smart-admin-navigation' ),
				'pending' => __( 'Pending Review', 'commandbar-smart-admin-navigation' ),
				'future'  => __( 'Scheduled', 'commandbar-smart-admin-navigation' ),
			);

			$results[] = array(
				'id'          => $post->ID,
				'title'       => get_the_title( $post ),
				'description' => sprintf(
					/* translators: 1: Post type label, 2: Post status label */
					__( '%1$s · %2$s', 'commandbar-smart-admin-navigation' ),
					get_post_type_object( $post->post_type )->labels->singular_name ?? $post_type,
					$status_labels[ $post->post_status ] ?? $post->post_status
				),
				'url'         => get_edit_post_link( $post->ID, 'raw' ),
				'type'        => $post_type,
				'icon'        => ( 'page' === $post_type ) ? 'admin-page' : 'admin-post',
				'group'       => ( 'page' === $post_type )
					? __( 'Pages', 'commandbar-smart-admin-navigation' )
					: __( 'Posts', 'commandbar-smart-admin-navigation' ),
			);
		}

		wp_reset_postdata();

		return $results;
	}

	/**
	 * Search users by name, login, or email.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param string $query Search term.
	 * @return array Array of result objects.
	 */
	private function search_users( string $query ): array {
		$user_query = new WP_User_Query(
			array(
				'search'         => '*' . $query . '*',
				'search_columns' => array( 'display_name', 'user_login', 'user_email' ),
				'number'         => self::MAX_RESULTS,
				'fields'         => array( 'ID', 'display_name', 'user_email', 'user_login' ),
			)
		);

		$results = array();

		foreach ( $user_query->get_results() as $user ) {
			$user_obj  = get_userdata( $user->ID );
			$roles     = $user_obj ? (array) $user_obj->roles : array();
			$role_name = ! empty( $roles ) ? ucfirst( reset( $roles ) ) : '';

			$results[] = array(
				'id'          => $user->ID,
				'title'       => $user->display_name,
				'description' => sprintf(
					/* translators: 1: Username/login, 2: Role name */
					__( '@%1$s · %2$s', 'commandbar-smart-admin-navigation' ),
					$user->user_login,
					$role_name
				),
				'url'         => get_edit_user_link( $user->ID ),
				'type'        => 'user',
				'icon'        => 'admin-users',
				'group'       => __( 'Users', 'commandbar-smart-admin-navigation' ),
			);
		}

		return $results;
	}

	/**
	 * Search installed plugins by name.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param string $query Search term.
	 * @return array Array of result objects.
	 */
	private function search_plugins( string $query ): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$results        = array();
		$count          = 0;

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( $count >= self::MAX_RESULTS ) {
				break;
			}
			// Case-insensitive substring match on plugin name.
			if ( false === stripos( $plugin_data['Name'], $query ) ) {
				continue;
			}

			$is_active = in_array( $plugin_file, $active_plugins, true );

			$results[] = array(
				'id'          => sanitize_key( $plugin_file ),
				'title'       => $plugin_data['Name'],
				'description' => $is_active
					? __( 'Active', 'commandbar-smart-admin-navigation' )
					: __( 'Inactive', 'commandbar-smart-admin-navigation' ),
				'url'         => admin_url( 'plugins.php' ),
				'type'        => 'plugin',
				'icon'        => 'admin-plugins',
				'group'       => __( 'Plugins', 'commandbar-smart-admin-navigation' ),
			);

			++$count;
		}

		return $results;
	}

	/**
	 * Build a standardised success response envelope.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param array  $data    Response data payload.
	 * @param string $message Optional success message.
	 * @return WP_REST_Response
	 */
	private function success_response( array $data, string $message = '' ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => $data,
				'message' => $message,
			),
			200
		);
	}

	/**
	 * Build a standardised error response envelope.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param string $code    Error code.
	 * @param string $message Human-readable error message.
	 * @param int    $status  HTTP status code.
	 * @return WP_REST_Response
	 */
	private function error_response( string $code, string $message, int $status = 400 ): WP_REST_Response {
		return new WP_REST_Response(
			array(
				'success' => false,
				'data'    => array(),
				'message' => $message,
				'code'    => $code,
			),
			$status
		);
	}
}
