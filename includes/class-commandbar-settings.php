<?php
/**
 * Plugin settings management via the WordPress Settings API.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Settings
 *
 * Handles registration, sanitization, and retrieval of all plugin settings
 * using the native WordPress Settings API.
 *
 * @since 1.0.0
 */
class CommandBar_Settings {

	/**
	 * The option key used to store all plugin settings in wp_options.
	 *
	 * @since  1.0.0
	 * @access public
	 * @var    string
	 */
	const OPTION_KEY = 'commandbar_settings';

	/**
	 * Cached settings array to avoid repeated database reads.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    array|null
	 */
	private ?array $settings = null;

	/**
	 * Register plugin settings, sections, and fields with the WordPress Settings API.
	 *
	 * @since 1.0.0
	 */
	public function register_settings(): void {
		register_setting(
			'commandbar_settings_group',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => $this->get_defaults(),
			)
		);

		// ── General Section ─────────────────────────────────────────────────
		add_settings_section(
			'commandbar_general',
			__( 'General', 'commandbar' ),
			'__return_false',
			'commandbar'
		);

		add_settings_field(
			'enabled',
			__( 'Enable CommandBar', 'commandbar' ),
			array( $this, 'field_checkbox' ),
			'commandbar',
			'commandbar_general',
			array(
				'key'         => 'enabled',
				'description' => __( 'Activate the command palette across all wp-admin pages.', 'commandbar' ),
			)
		);

		add_settings_field(
			'show_trigger_button',
			__( 'Show floating trigger button', 'commandbar' ),
			array( $this, 'field_checkbox' ),
			'commandbar',
			'commandbar_general',
			array(
				'key'         => 'show_trigger_button',
				'description' => __( 'Display a floating button in the corner of wp-admin as an alternative way to open the command palette.', 'commandbar' ),
			)
		);

		add_settings_field(
			'trigger_button_position',
			__( 'Floating button position', 'commandbar' ),
			array( $this, 'field_select' ),
			'commandbar',
			'commandbar_general',
			array(
				'key'     => 'trigger_button_position',
				'options' => array(
					'bottom-right' => __( 'Bottom Right', 'commandbar' ),
					'bottom-left'  => __( 'Bottom Left', 'commandbar' ),
				),
			)
		);

		add_settings_field(
			'show_recent_commands',
			__( 'Show recent commands', 'commandbar' ),
			array( $this, 'field_checkbox' ),
			'commandbar',
			'commandbar_general',
			array(
				'key'         => 'show_recent_commands',
				'description' => __( 'Show recently executed commands when the palette opens with an empty input.', 'commandbar' ),
			)
		);

		add_settings_field(
			'recent_commands_count',
			__( 'Number of recent commands', 'commandbar' ),
			array( $this, 'field_number' ),
			'commandbar',
			'commandbar_general',
			array(
				'key' => 'recent_commands_count',
				'min' => 3,
				'max' => 10,
			)
		);

		// ── Appearance Section ───────────────────────────────────────────────
		add_settings_section(
			'commandbar_appearance',
			__( 'Appearance', 'commandbar' ),
			'__return_false',
			'commandbar'
		);

		add_settings_field(
			'palette_theme',
			__( 'Palette theme', 'commandbar' ),
			array( $this, 'field_select' ),
			'commandbar',
			'commandbar_appearance',
			array(
				'key'     => 'palette_theme',
				'options' => array(
					'auto'  => __( 'Auto (follows WordPress admin scheme)', 'commandbar' ),
					'light' => __( 'Light', 'commandbar' ),
					'dark'  => __( 'Dark', 'commandbar' ),
				),
			)
		);

		add_settings_field(
			'show_command_icons',
			__( 'Show command icons', 'commandbar' ),
			array( $this, 'field_checkbox' ),
			'commandbar',
			'commandbar_appearance',
			array(
				'key'         => 'show_command_icons',
				'description' => __( 'Display Dashicons next to each command in the results list.', 'commandbar' ),
			)
		);

		add_settings_field(
			'show_shortcut_hints',
			__( 'Show keyboard shortcut hints', 'commandbar' ),
			array( $this, 'field_checkbox' ),
			'commandbar',
			'commandbar_appearance',
			array(
				'key'         => 'show_shortcut_hints',
				'description' => __( 'Show keyboard shortcut badges inside command results.', 'commandbar' ),
			)
		);

		// ── Advanced Section ─────────────────────────────────────────────────
		add_settings_section(
			'commandbar_advanced',
			__( 'Advanced', 'commandbar' ),
			'__return_false',
			'commandbar'
		);

		add_settings_field(
			'enabled_roles',
			__( 'Enable for these roles', 'commandbar' ),
			array( $this, 'field_roles' ),
			'commandbar',
			'commandbar_advanced',
			array(
				'key'         => 'enabled_roles',
				'description' => __( 'CommandBar will only be loaded for users with one of the selected roles.', 'commandbar' ),
			)
		);
	}

	/**
	 * Sanitize the settings array before saving.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input Raw input from the settings form.
	 * @return array Sanitized settings array.
	 */
	public function sanitize_settings( mixed $input ): array {
		$defaults  = $this->get_defaults();
		$sanitized = array();

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		$sanitized['enabled']                 = ! empty( $input['enabled'] );
		$sanitized['show_trigger_button']     = ! empty( $input['show_trigger_button'] );
		$sanitized['show_recent_commands']    = ! empty( $input['show_recent_commands'] );
		$sanitized['show_command_icons']      = ! empty( $input['show_command_icons'] );
		$sanitized['show_shortcut_hints']     = ! empty( $input['show_shortcut_hints'] );

		$allowed_positions                    = array( 'bottom-right', 'bottom-left' );
		$position_raw                         = sanitize_key( $input['trigger_button_position'] ?? '' );
		$sanitized['trigger_button_position'] = in_array( $position_raw, $allowed_positions, true )
			? $position_raw
			: $defaults['trigger_button_position'];

		$count_raw                            = absint( $input['recent_commands_count'] ?? $defaults['recent_commands_count'] );
		$sanitized['recent_commands_count']   = max( 3, min( 10, $count_raw ) );

		$allowed_themes                       = array( 'auto', 'light', 'dark' );
		$theme_raw                            = sanitize_key( $input['palette_theme'] ?? '' );
		$sanitized['palette_theme']           = in_array( $theme_raw, $allowed_themes, true )
			? $theme_raw
			: $defaults['palette_theme'];

		$all_roles                            = array_keys( wp_roles()->roles );
		$roles_raw                            = isset( $input['enabled_roles'] ) && is_array( $input['enabled_roles'] )
			? $input['enabled_roles']
			: array();
		$sanitized['enabled_roles']           = array_values(
			array_intersect( array_map( 'sanitize_key', $roles_raw ), $all_roles )
		);

		// Reset cached value.
		$this->settings = null;

		return $sanitized;
	}

	/**
	 * Retrieve a single setting value by key.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key     The setting key.
	 * @param mixed  $default Fallback value if the key is not found.
	 * @return mixed The setting value.
	 */
	public function get_setting( string $key, mixed $default = null ): mixed {
		$settings = $this->get_all_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Retrieve the complete settings array, merging stored values with defaults.
	 *
	 * @since 1.0.0
	 *
	 * @return array Settings array.
	 */
	public function get_all_settings(): array {
		if ( null === $this->settings ) {
			$stored         = get_option( self::OPTION_KEY, array() );
			$this->settings = wp_parse_args( is_array( $stored ) ? $stored : array(), $this->get_defaults() );
		}
		return $this->settings;
	}

	/**
	 * Return the default settings values.
	 *
	 * @since 1.0.0
	 *
	 * @return array Default settings.
	 */
	public function get_defaults(): array {
		return array(
			'enabled'                 => true,
			'show_trigger_button'     => true,
			'trigger_button_position' => 'bottom-right',
			'show_recent_commands'    => true,
			'recent_commands_count'   => 5,
			'palette_theme'           => 'auto',
			'show_command_icons'      => true,
			'show_shortcut_hints'     => true,
			'enabled_roles'           => array( 'administrator', 'editor', 'author' ),
		);
	}

	// ── Field renderers ─────────────────────────────────────────────────────

	/**
	 * Render a checkbox field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function field_checkbox( array $args ): void {
		$key   = sanitize_key( $args['key'] );
		$value = $this->get_setting( $key, false );
		$desc  = isset( $args['description'] ) ? $args['description'] : '';
		printf(
			'<label><input type="checkbox" name="%1$s[%2$s]" value="1" %3$s /> %4$s</label>',
			esc_attr( self::OPTION_KEY ),
			esc_attr( $key ),
			checked( true, (bool) $value, false ),
			esc_html( $desc )
		);
	}

	/**
	 * Render a select (dropdown) field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments including 'options' key => label pairs.
	 */
	public function field_select( array $args ): void {
		$key     = sanitize_key( $args['key'] );
		$value   = $this->get_setting( $key, '' );
		$options = is_array( $args['options'] ) ? $args['options'] : array();
		printf(
			'<select name="%1$s[%2$s]">',
			esc_attr( self::OPTION_KEY ),
			esc_attr( $key )
		);
		foreach ( $options as $opt_value => $opt_label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $opt_value ),
				selected( $value, $opt_value, false ),
				esc_html( $opt_label )
			);
		}
		echo '</select>';
	}

	/**
	 * Render a number input field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function field_number( array $args ): void {
		$key   = sanitize_key( $args['key'] );
		$value = absint( $this->get_setting( $key, 5 ) );
		$min   = isset( $args['min'] ) ? absint( $args['min'] ) : 1;
		$max   = isset( $args['max'] ) ? absint( $args['max'] ) : 100;
		printf(
			'<input type="number" name="%1$s[%2$s]" value="%3$s" min="%4$s" max="%5$s" class="small-text" />',
			esc_attr( self::OPTION_KEY ),
			esc_attr( $key ),
			esc_attr( (string) $value ),
			esc_attr( (string) $min ),
			esc_attr( (string) $max )
		);
	}

	/**
	 * Render a multi-select list of WordPress roles.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Field arguments.
	 */
	public function field_roles( array $args ): void {
		$key           = sanitize_key( $args['key'] );
		$selected_roles = (array) $this->get_setting( $key, array() );
		$all_roles     = wp_roles()->roles;
		$desc          = isset( $args['description'] ) ? $args['description'] : '';

		echo '<fieldset>';
		foreach ( $all_roles as $role_slug => $role_data ) {
			$checked = in_array( $role_slug, $selected_roles, true );
			printf(
				'<label style="display:block;margin-bottom:4px;"><input type="checkbox" name="%1$s[%2$s][]" value="%3$s" %4$s /> %5$s</label>',
				esc_attr( self::OPTION_KEY ),
				esc_attr( $key ),
				esc_attr( $role_slug ),
				checked( $checked, true, false ),
				esc_html( translate_user_role( $role_data['name'] ) )
			);
		}
		if ( $desc ) {
			printf( '<p class="description">%s</p>', esc_html( $desc ) );
		}
		echo '</fieldset>';
	}
}
