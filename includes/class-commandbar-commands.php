<?php
/**
 * Provides the server-side command registry for CommandBar.
 *
 * This class builds the canonical list of all static commands and filters them
 * based on the current user's capabilities before passing the list to JavaScript
 * via wp_localize_script(). Third-party code can extend the command list using
 * the 'commandbar_commands' filter.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Commands
 *
 * @since 1.0.0
 */
class CommandBar_Commands {

	/**
	 * Return all registered commands filtered by the current user's capabilities.
	 *
	 * Each command object contains the following keys:
	 *  - id          (string)   Unique identifier.
	 *  - title       (string)   Display title shown in the palette.
	 *  - description (string)   Secondary text shown in the result row.
	 *  - keywords    (string[]) Alias terms used for fuzzy matching.
	 *  - icon        (string)   Dashicon class name (without 'dashicons-' prefix).
	 *  - type        (string)   'navigate' | 'action' | 'dynamic'.
	 *  - url         (string)   Absolute admin URL (for 'navigate' type).
	 *  - action      (string)   Action identifier (for 'action' type).
	 *  - capability  (string)   WordPress capability required to see this command.
	 *  - group       (string)   Category group label shown in palette.
	 *  - shortcut    (string)   Optional keyboard shortcut label.
	 *  - confirm     (bool)     Whether to show a confirmation prompt before executing.
	 *
	 * @since 1.0.0
	 *
	 * @return array Capability-filtered array of command objects.
	 */
	public function get_commands(): array {
		$all_commands = $this->build_commands();

		/**
		 * Filter the full list of CommandBar commands.
		 *
		 * Third-party plugins can add, remove, or modify commands using this filter.
		 * Each command must follow the schema described in class-commandbar-commands.php.
		 *
		 * @since 1.0.0
		 *
		 * @param array $commands Array of command objects.
		 */
		$all_commands = apply_filters( 'commandbar_commands', $all_commands );

		// Filter to only commands the current user can execute.
		return array_values(
			array_filter(
				$all_commands,
				static function ( array $command ): bool {
					$capability = $command['capability'] ?? 'read';
					return current_user_can( $capability );
				}
			)
		);
	}

	/**
	 * Build the canonical array of all built-in commands.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return array
	 */
	private function build_commands(): array {
		return array(

			// ── CONTENT ────────────────────────────────────────────────────

			array(
				'id'          => 'new-post',
				'title'       => __( 'New Post', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Create a new blog post', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'new post', 'create post', 'write', 'add post', 'blog' ),
				'icon'        => 'edit',
				'type'        => 'navigate',
				'url'         => admin_url( 'post-new.php' ),
				'capability'  => 'edit_posts',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'new-page',
				'title'       => __( 'New Page', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Create a new page', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'new page', 'create page', 'add page' ),
				'icon'        => 'media-default',
				'type'        => 'navigate',
				'url'         => admin_url( 'post-new.php?post_type=page' ),
				'capability'  => 'edit_pages',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'new-media',
				'title'       => __( 'Upload Media', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Add images, videos or files to the media library', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'upload media', 'new media', 'add image', 'add file', 'upload image' ),
				'icon'        => 'format-image',
				'type'        => 'navigate',
				'url'         => admin_url( 'media-new.php' ),
				'capability'  => 'upload_files',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'all-posts',
				'title'       => __( 'All Posts', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View and manage all posts', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'all posts', 'posts list', 'manage posts', 'view posts' ),
				'icon'        => 'list-view',
				'type'        => 'navigate',
				'url'         => admin_url( 'edit.php' ),
				'capability'  => 'edit_posts',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'all-pages',
				'title'       => __( 'All Pages', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View and manage all pages', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'all pages', 'pages list', 'manage pages', 'view pages' ),
				'icon'        => 'admin-page',
				'type'        => 'navigate',
				'url'         => admin_url( 'edit.php?post_type=page' ),
				'capability'  => 'edit_pages',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'media-library',
				'title'       => __( 'Media Library', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Browse all uploaded media files', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'media library', 'all media', 'images', 'files', 'gallery' ),
				'icon'        => 'admin-media',
				'type'        => 'navigate',
				'url'         => admin_url( 'upload.php' ),
				'capability'  => 'upload_files',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'draft-posts',
				'title'       => __( 'Draft Posts', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View all draft posts', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'drafts', 'draft posts', 'unpublished' ),
				'icon'        => 'edit-page',
				'type'        => 'navigate',
				'url'         => admin_url( 'edit.php?post_status=draft' ),
				'capability'  => 'edit_posts',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'scheduled-posts',
				'title'       => __( 'Scheduled Posts', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View posts scheduled for future publication', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'scheduled', 'scheduled posts', 'future posts', 'pending posts' ),
				'icon'        => 'clock',
				'type'        => 'navigate',
				'url'         => admin_url( 'edit.php?post_status=future' ),
				'capability'  => 'edit_posts',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'comments',
				'title'       => __( 'Comments', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Manage and moderate comments', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'comments', 'manage comments', 'moderate', 'approve comments' ),
				'icon'        => 'admin-comments',
				'type'        => 'navigate',
				'url'         => admin_url( 'edit-comments.php' ),
				'capability'  => 'moderate_comments',
				'group'       => __( 'Content', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),

			// ── APPEARANCE ─────────────────────────────────────────────────

			array(
				'id'          => 'themes',
				'title'       => __( 'Themes', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Browse and activate themes', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'themes', 'change theme', 'appearance', 'switch theme' ),
				'icon'        => 'admin-appearance',
				'type'        => 'navigate',
				'url'         => admin_url( 'themes.php' ),
				'capability'  => 'switch_themes',
				'group'       => __( 'Appearance', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'customize',
				'title'       => __( 'Customize', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Open the WordPress Customizer', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'customize', 'customizer', 'site settings', 'theme options' ),
				'icon'        => 'admin-customizer',
				'type'        => 'navigate',
				'url'         => admin_url( 'customize.php' ),
				'capability'  => 'customize',
				'group'       => __( 'Appearance', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'widgets',
				'title'       => __( 'Widgets', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Manage sidebar and footer widgets', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'widgets', 'sidebar widgets', 'footer widgets' ),
				'icon'        => 'welcome-widgets-menus',
				'type'        => 'navigate',
				'url'         => admin_url( 'widgets.php' ),
				'capability'  => 'edit_theme_options',
				'group'       => __( 'Appearance', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'menus',
				'title'       => __( 'Menus', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Manage navigation menus', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'menus', 'navigation menus', 'nav menu', 'navigation' ),
				'icon'        => 'menu',
				'type'        => 'navigate',
				'url'         => admin_url( 'nav-menus.php' ),
				'capability'  => 'edit_theme_options',
				'group'       => __( 'Appearance', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'site-editor',
				'title'       => __( 'Site Editor', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Open the Full Site Editor (FSE)', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'site editor', 'full site editing', 'fse', 'block editor', 'gutenberg' ),
				'icon'        => 'layout',
				'type'        => 'navigate',
				'url'         => admin_url( 'site-editor.php' ),
				'capability'  => 'edit_theme_options',
				'group'       => __( 'Appearance', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),

			// ── PLUGINS ────────────────────────────────────────────────────

			array(
				'id'          => 'all-plugins',
				'title'       => __( 'All Plugins', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View all installed plugins', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'plugins', 'manage plugins', 'installed plugins' ),
				'icon'        => 'admin-plugins',
				'type'        => 'navigate',
				'url'         => admin_url( 'plugins.php' ),
				'capability'  => 'activate_plugins',
				'group'       => __( 'Plugins', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'add-plugin',
				'title'       => __( 'Add Plugin', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Search and install new plugins from WordPress.org', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'add plugin', 'install plugin', 'new plugin', 'plugin install' ),
				'icon'        => 'plus-alt2',
				'type'        => 'navigate',
				'url'         => admin_url( 'plugin-install.php' ),
				'capability'  => 'install_plugins',
				'group'       => __( 'Plugins', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'plugin-updates',
				'title'       => __( 'Plugin Updates', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View plugins that have available updates', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'plugin updates', 'update plugins', 'outdated plugins' ),
				'icon'        => 'update',
				'type'        => 'navigate',
				'url'         => admin_url( 'plugins.php?plugin_status=upgrade' ),
				'capability'  => 'update_plugins',
				'group'       => __( 'Plugins', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),

			// ── SETTINGS ───────────────────────────────────────────────────

			array(
				'id'          => 'general-settings',
				'title'       => __( 'General Settings', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Site title, tagline, timezone and more', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'general settings', 'site title', 'tagline', 'admin email', 'timezone' ),
				'icon'        => 'admin-settings',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-general.php' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Settings', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'writing-settings',
				'title'       => __( 'Writing Settings', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Default post category, post format and editor settings', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'writing settings', 'default category', 'post format' ),
				'icon'        => 'edit',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-writing.php' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Settings', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'reading-settings',
				'title'       => __( 'Reading Settings', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Homepage display, blog pages and feed settings', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'reading settings', 'homepage settings', 'front page', 'blog page', 'rss' ),
				'icon'        => 'book',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-reading.php' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Settings', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'discussion-settings',
				'title'       => __( 'Discussion Settings', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Comment settings, moderation and notifications', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'discussion settings', 'comment settings', 'moderation', 'notifications' ),
				'icon'        => 'admin-comments',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-discussion.php' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Settings', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'permalinks',
				'title'       => __( 'Permalinks', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'URL structure and slug settings', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'permalinks', 'url structure', 'slugs', 'pretty urls' ),
				'icon'        => 'admin-links',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-permalink.php' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Settings', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'privacy-settings',
				'title'       => __( 'Privacy Settings', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Privacy policy page and GDPR settings', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'privacy', 'privacy policy', 'gdpr', 'data protection' ),
				'icon'        => 'shield',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-privacy.php' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Settings', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),

			// ── USERS ──────────────────────────────────────────────────────

			array(
				'id'          => 'all-users',
				'title'       => __( 'All Users', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'View and manage all user accounts', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'users', 'manage users', 'all users', 'user list' ),
				'icon'        => 'admin-users',
				'type'        => 'navigate',
				'url'         => admin_url( 'users.php' ),
				'capability'  => 'list_users',
				'group'       => __( 'Users', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'add-user',
				'title'       => __( 'Add New User', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Create a new user account', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'add user', 'new user', 'create user', 'register user' ),
				'icon'        => 'plus-alt2',
				'type'        => 'navigate',
				'url'         => admin_url( 'user-new.php' ),
				'capability'  => 'create_users',
				'group'       => __( 'Users', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'your-profile',
				'title'       => __( 'Your Profile', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Edit your user profile and account settings', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'profile', 'my profile', 'edit profile', 'account', 'account settings' ),
				'icon'        => 'admin-users',
				'type'        => 'navigate',
				'url'         => admin_url( 'profile.php' ),
				'capability'  => 'read',
				'group'       => __( 'Users', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),

			// ── TOOLS ──────────────────────────────────────────────────────

			array(
				'id'          => 'import',
				'title'       => __( 'Import', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Import content from another platform or file', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'import', 'import content', 'migrate' ),
				'icon'        => 'download',
				'type'        => 'navigate',
				'url'         => admin_url( 'import.php' ),
				'capability'  => 'import',
				'group'       => __( 'Tools', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'export',
				'title'       => __( 'Export', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Export content as an XML file', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'export', 'export content', 'download export', 'xml export' ),
				'icon'        => 'upload',
				'type'        => 'navigate',
				'url'         => admin_url( 'export.php' ),
				'capability'  => 'export',
				'group'       => __( 'Tools', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'site-health',
				'title'       => __( 'Site Health', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Diagnose your site performance and security', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'site health', 'health check', 'diagnostics', 'performance check' ),
				'icon'        => 'heart',
				'type'        => 'navigate',
				'url'         => admin_url( 'site-health.php' ),
				'capability'  => 'view_site_health_checks',
				'group'       => __( 'Tools', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'site-health-info',
				'title'       => __( 'Site Health Info', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Detailed system and server information', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'site health info', 'system info', 'server info', 'debug info', 'php version' ),
				'icon'        => 'info',
				'type'        => 'navigate',
				'url'         => admin_url( 'site-health.php?tab=debug' ),
				'capability'  => 'view_site_health_checks',
				'group'       => __( 'Tools', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'erase-personal-data',
				'title'       => __( 'Erase Personal Data', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Remove personal data for a specific user (GDPR)', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'erase data', 'personal data', 'gdpr', 'remove user data', 'privacy erasure' ),
				'icon'        => 'trash',
				'type'        => 'navigate',
				'url'         => admin_url( 'tools.php?page=remove_personal_data' ),
				'capability'  => 'erase_others_personal_data',
				'group'       => __( 'Tools', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),

			// ── ACTIONS ────────────────────────────────────────────────────

			array(
				'id'          => 'flush-rewrite-rules',
				'title'       => __( 'Flush Rewrite Rules', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Clear and regenerate all permalink rewrite rules', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'flush rewrite', 'clear rewrite', 'refresh permalinks', 'rewrite rules', '404 fix' ),
				'icon'        => 'update',
				'type'        => 'action',
				'url'         => '',
				'action'      => 'flush_rewrite_rules',
				'capability'  => 'manage_options',
				'group'       => __( 'Actions', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'check-updates',
				'title'       => __( 'Check for Updates', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Visit the WordPress updates screen', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'check updates', 'wordpress updates', 'update core', 'updates available' ),
				'icon'        => 'update',
				'type'        => 'navigate',
				'url'         => admin_url( 'update-core.php' ),
				'capability'  => 'update_core',
				'group'       => __( 'Actions', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'logout',
				'title'       => __( 'Log Out', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Sign out of your WordPress account', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'logout', 'sign out', 'log out', 'logoff' ),
				'icon'        => 'exit',
				'type'        => 'navigate',
				'url'         => wp_logout_url( home_url() ),
				'capability'  => 'read',
				'group'       => __( 'Actions', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => true,
			),
			array(
				'id'          => 'dark-mode',
				'title'       => __( 'Toggle Dark Mode', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Switch between light and dark admin appearance', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'dark mode', 'toggle dark mode', 'light mode', 'night mode', 'theme toggle' ),
				'icon'        => 'moon',
				'type'        => 'action',
				'url'         => '',
				'action'      => 'toggle_dark_mode',
				'capability'  => 'read',
				'group'       => __( 'Actions', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
			array(
				'id'          => 'commandbar-settings',
				'title'       => __( 'CommandBar Settings', 'commandbar-smart-admin-navigation' ),
				'description' => __( 'Configure the CommandBar command palette', 'commandbar-smart-admin-navigation' ),
				'keywords'    => array( 'commandbar settings', 'palette settings', 'configure commandbar' ),
				'icon'        => 'admin-settings',
				'type'        => 'navigate',
				'url'         => admin_url( 'options-general.php?page=commandbar' ),
				'capability'  => 'manage_options',
				'group'       => __( 'Actions', 'commandbar-smart-admin-navigation' ),
				'shortcut'    => '',
				'confirm'     => false,
			),
		);
	}
}
