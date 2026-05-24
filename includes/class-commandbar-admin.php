<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Handles enqueuing of admin assets, script localisation, settings page
 * registration and rendering. No code in this class touches the site frontend.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Admin
 *
 * @since 1.0.0
 */
class CommandBar_Admin {

	/**
	 * Plugin settings instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    CommandBar_Settings
	 */
	private CommandBar_Settings $settings;

	/**
	 * Plugin commands instance.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    CommandBar_Commands
	 */
	private CommandBar_Commands $commands;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param CommandBar_Settings  $settings Plugin settings instance.
	 * @param CommandBar_Commands  $commands Plugin commands instance.
	 */
	public function __construct( CommandBar_Settings $settings, CommandBar_Commands $commands ) {
		$this->settings = $settings;
		$this->commands = $commands;
	}

	/**
	 * Enqueue styles for the admin area.
	 *
	 * Assets are only enqueued when the plugin is enabled and the current user
	 * has an allowed role. The login screen is explicitly excluded.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_styles( string $hook_suffix ): void {
		if ( ! $this->should_load( $hook_suffix ) ) {
			return;
		}

		wp_enqueue_style(
			'commandbar',
			COMMANDBAR_PLUGIN_URL . 'admin/css/commandbar.css',
			array(),
			COMMANDBAR_VERSION
		);

		// Dashicons are required for command icons.
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Enqueue scripts for the admin area.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		if ( ! $this->should_load( $hook_suffix ) ) {
			return;
		}

		// Module files — loaded in dependency order via the main entry point.
		wp_enqueue_script(
			'commandbar-data',
			COMMANDBAR_PLUGIN_URL . 'admin/js/commandbar-data.js',
			array(),
			COMMANDBAR_VERSION,
			true
		);

		wp_enqueue_script(
			'commandbar-search',
			COMMANDBAR_PLUGIN_URL . 'admin/js/commandbar-search.js',
			array( 'commandbar-data' ),
			COMMANDBAR_VERSION,
			true
		);

		wp_enqueue_script(
			'commandbar-actions',
			COMMANDBAR_PLUGIN_URL . 'admin/js/commandbar-actions.js',
			array( 'commandbar-search' ),
			COMMANDBAR_VERSION,
			true
		);

		wp_enqueue_script(
			'commandbar-keyboard',
			COMMANDBAR_PLUGIN_URL . 'admin/js/commandbar-keyboard.js',
			array( 'commandbar-actions' ),
			COMMANDBAR_VERSION,
			true
		);

		wp_enqueue_script(
			'commandbar',
			COMMANDBAR_PLUGIN_URL . 'admin/js/commandbar.js',
			array( 'commandbar-data', 'commandbar-search', 'commandbar-actions', 'commandbar-keyboard' ),
			COMMANDBAR_VERSION,
			true
		);

		// Localise all data the JavaScript layer needs.
		wp_localize_script(
			'commandbar',
			'commandbarData',
			$this->get_localized_data()
		);
	}

	/**
	 * Build the localised data object passed to JavaScript.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function get_localized_data(): array {
		$current_user = wp_get_current_user();

		return array(
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'restBase'     => esc_url_raw( rest_url( 'commandbar/v1' ) ),
			'settings'     => $this->settings->get_all_settings(),
			'commands'     => $this->commands->get_commands(),
			'capabilities' => array(
				'edit_posts'           => current_user_can( 'edit_posts' ),
				'edit_pages'           => current_user_can( 'edit_pages' ),
				'upload_files'         => current_user_can( 'upload_files' ),
				'list_users'           => current_user_can( 'list_users' ),
				'activate_plugins'     => current_user_can( 'activate_plugins' ),
				'manage_options'       => current_user_can( 'manage_options' ),
				'moderate_comments'    => current_user_can( 'moderate_comments' ),
			),
			'i18n'         => array(
				/* translators: Placeholder text inside the command palette input. */
				'placeholder'          => __( 'Type a command or search\u2026', 'commandbar-smart-admin-navigation' ),
				/* translators: Label for the Recently Used command group. */
				'recentLabel'          => __( 'Recently Used', 'commandbar-smart-admin-navigation' ),
				/* translators: Shown inside the palette when no results match the query. */
				'noResults'            => __( 'No results found.', 'commandbar-smart-admin-navigation' ),
				/* translators: Shown while REST API search is in progress. */
				'searching'            => __( 'Searching\u2026', 'commandbar-smart-admin-navigation' ),
				/* translators: Accessible label for the command palette dialog. */
				'dialogLabel'          => __( 'Command palette', 'commandbar-smart-admin-navigation' ),
				/* translators: Tooltip on the floating trigger button. */
				'triggerTooltip'       => __( 'Open CommandBar', 'commandbar-smart-admin-navigation' ),
				/* translators: Aria label for the close button. */
				'closeLabel'           => __( 'Close CommandBar', 'commandbar-smart-admin-navigation' ),
				/* translators: Label shown inside search input when empty. */
				'shortcutHint'         => __( 'CMD+K', 'commandbar-smart-admin-navigation' ),
				/* translators: Confirmation prompt before executing a destructive command. */
				'confirmLabel'         => __( 'Press Enter again to confirm', 'commandbar-smart-admin-navigation' ),
				/* translators: Success toast for rewrite rules flushed. */
				'flushSuccess'         => __( 'Rewrite rules flushed successfully.', 'commandbar-smart-admin-navigation' ),
				/* translators: Error toast when an action fails. */
				'actionError'          => __( 'Action failed. Please try again.', 'commandbar-smart-admin-navigation' ),
				/* translators: %d is replaced with the number of search results. */
				'resultsCount'         => __( 'Showing %d result(s)', 'commandbar-smart-admin-navigation' ),
				/* translators: Logout confirmation message. */
				'logoutConfirm'        => __( 'Press Enter again to log out', 'commandbar-smart-admin-navigation' ),
				/* translators: Dark mode toggle — switched to dark. */
				'darkModeOn'           => __( 'Dark mode enabled', 'commandbar-smart-admin-navigation' ),
				/* translators: Dark mode toggle — switched to light. */
				'darkModeOff'          => __( 'Dark mode disabled', 'commandbar-smart-admin-navigation' ),
				/* translators: Dismiss the floating trigger button for this session. */
				'dismissButton'        => __( 'Dismiss', 'commandbar-smart-admin-navigation' ),
			),
		);
	}

	/**
	 * Register the CommandBar settings page under Settings > CommandBar.
	 *
	 * @since 1.0.0
	 */
	public function add_settings_page(): void {
		add_options_page(
			__( 'CommandBar', 'commandbar-smart-admin-navigation' ),
			__( 'CommandBar', 'commandbar-smart-admin-navigation' ),
			'manage_options',
			'commandbar',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Render the settings page HTML.
	 *
	 * @since 1.0.0
	 */
	public function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'CommandBar', 'commandbar-smart-admin-navigation' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Your WordPress admin. At the speed of thought. Press CMD+K or CTRL+K anywhere in wp-admin to open the command palette.', 'commandbar-smart-admin-navigation' ); ?>
			</p>

			<?php settings_errors( 'commandbar_settings_group' ); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'commandbar_settings_group' );

				echo '<div class="commandbar-settings-grid">';

				echo '<div class="commandbar-settings-section">';
				echo '<h2>' . esc_html__( 'General', 'commandbar-smart-admin-navigation' ) . '</h2>';
				do_settings_sections( 'commandbar' );
				echo '</div>';

				echo '</div>';

				submit_button( __( 'Save Settings', 'commandbar-smart-admin-navigation' ) );
				?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'Keyboard Shortcut', 'commandbar-smart-admin-navigation' ); ?></h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Open / Close', 'commandbar-smart-admin-navigation' ); ?></th>
					<td>
						<kbd>&#8984;K</kbd> <?php esc_html_e( 'on macOS', 'commandbar-smart-admin-navigation' ); ?> &nbsp;|&nbsp;
						<kbd>Ctrl+K</kbd> <?php esc_html_e( 'on Windows / Linux', 'commandbar-smart-admin-navigation' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Navigate results', 'commandbar-smart-admin-navigation' ); ?></th>
					<td>
						<kbd>&#8593;</kbd> <kbd>&#8595;</kbd>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Execute highlighted command', 'commandbar-smart-admin-navigation' ); ?></th>
					<td><kbd>Enter</kbd></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Close palette', 'commandbar-smart-admin-navigation' ); ?></th>
					<td><kbd>Esc</kbd></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Search users', 'commandbar-smart-admin-navigation' ); ?></th>
					<td><?php esc_html_e( 'Type @ then your search term', 'commandbar-smart-admin-navigation' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Search settings pages', 'commandbar-smart-admin-navigation' ); ?></th>
					<td><?php esc_html_e( 'Type > then your search term', 'commandbar-smart-admin-navigation' ); ?></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Search plugins', 'commandbar-smart-admin-navigation' ); ?></th>
					<td><?php esc_html_e( 'Type + then your search term', 'commandbar-smart-admin-navigation' ); ?></td>
				</tr>
			</table>

			<hr />

			<h2><?php esc_html_e( 'About CommandBar', 'commandbar-smart-admin-navigation' ); ?></h2>
			<p>
				<?php
				printf(
					/* translators: 1: opening anchor tag, 2: closing anchor tag */
					esc_html__( 'Version %1$s &mdash; %2$sView on GitHub%3$s', 'commandbar-smart-admin-navigation' ),
					esc_html( COMMANDBAR_VERSION ),
					'<a href="https://github.com/KunalPareek21/commandbar" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Determine whether CommandBar assets should be loaded on the current admin page.
	 *
	 * Assets must NOT be loaded on the login screen or on the frontend.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return bool
	 */
	private function should_load( string $hook_suffix ): bool {
		// Never load on the frontend.
		if ( ! is_admin() ) {
			return false;
		}

		// Never load on the login screen.
		if ( in_array( $hook_suffix, array( 'login', '' ), true ) ) {
			return false;
		}

		// Plugin must be enabled.
		if ( ! $this->settings->get_setting( 'enabled', true ) ) {
			return false;
		}

		// User must be logged in.
		if ( ! is_user_logged_in() ) {
			return false;
		}

		// User must have an allowed role.
		$current_user  = wp_get_current_user();
		$allowed_roles = (array) $this->settings->get_setting( 'enabled_roles', array() );

		if ( empty( $allowed_roles ) ) {
			return true;
		}

		$user_roles = (array) $current_user->roles;

		return ! empty( array_intersect( $user_roles, $allowed_roles ) );
	}
}
