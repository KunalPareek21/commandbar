=== CommandBar – Smart Admin Navigation ===
Contributors:      kunalpareek
Tags:              keyboard, admin, command-palette, productivity, developer-tools
Requires at least: 6.3
Tested up to:      6.8
Stable tag:        1.0.2
Requires PHP:      8.0
License:           GPLv2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Your WordPress admin. At the speed of thought.

== Description ==

**CommandBar** is a lightweight, keyboard-first command palette for the WordPress admin. Press **CMD+K** on Mac or **CTRL+K** on Windows and Linux from *anywhere* in wp-admin to instantly search posts, pages, users, plugins, settings, and run actions — without touching the mouse.

Inspired by VS Code, Linear, GitHub, Raycast, and Vercel — CommandBar brings that same instant-search, keyboard-driven workflow to WordPress.

= Who is this for? =

* WordPress developers who live in wp-admin every day
* Power users managing large WordPress sites
* Content editors who constantly switch between posts and settings
* Agency developers managing multiple client sites
* Site administrators who want keyboard-first workflows

= What can you do with CommandBar? =

**Navigate instantly** — jump to any admin screen in two keystrokes.

**Content commands:**
* New Post / New Page / Upload Media
* All Posts / All Pages / Media Library
* Draft Posts / Scheduled Posts / Comments

**Appearance commands:**
* Themes / Customize / Widgets / Menus / Site Editor (FSE)

**Plugin commands:**
* All Plugins / Add Plugin / Plugin Updates

**Settings commands:**
* General / Writing / Reading / Discussion / Permalinks / Privacy

**User commands:**
* All Users / Add New User / Your Profile

**Tools commands:**
* Import / Export / Site Health / Site Health Info / Erase Personal Data

**Action commands:**
* Flush Rewrite Rules (server-side, no page reload)
* Check for Updates
* Log Out (with confirmation)
* Toggle Dark Mode

**Dynamic search (live REST API results):**
* Type any word → searches your posts and pages
* Type **@** → searches users
* Type **>** → searches settings pages
* Type **+** → searches installed plugins

= Design philosophy =

* Keyboard first, always
* Speed is the feature — the palette opens in under 100ms
* Zero friction between thought and action
* Appears instantly, disappears cleanly
* Zero impact on your site's frontend performance
* Accessibility is not optional (WCAG 2.1 AA)
* Works immediately with zero configuration

= Zero configuration required =

Install and activate — CommandBar works immediately. No setup wizard, no API keys, no required configuration. Every feature is available from the moment activation completes.

= Accessibility =

CommandBar is built to WCAG 2.1 AA standards:
* Full keyboard navigation with focus trap
* ARIA roles: dialog, combobox, listbox, option
* Screen reader announcements for results and actions
* Prefers-reduced-motion respected (no animations)
* Minimum 4.5:1 contrast ratio throughout
* RTL layout support

= Performance =

* Zero JavaScript or CSS on the frontend — your site is unaffected
* Palette DOM is mounted once and reused — no layout thrashing
* Static commands require zero network requests
* Dynamic REST API results cached for 60 seconds per user
* JavaScript under 30KB unminified across all modules
* CSS under 10KB unminified

= Multisite =

CommandBar is fully multisite compatible. Settings are stored per-site.

= Extending CommandBar =

Developers can add custom commands using the `commandbar_commands` filter:

`
add_filter( 'commandbar_commands', function( $commands ) {
    $commands[] = [
        'id'          => 'my-custom-command',
        'title'       => 'Open My Plugin',
        'description' => 'Navigate to My Plugin settings',
        'keywords'    => [ 'my plugin', 'custom', 'settings' ],
        'icon'        => 'admin-settings',
        'type'        => 'navigate',
        'url'         => admin_url( 'admin.php?page=my-plugin' ),
        'capability'  => 'manage_options',
        'group'       => 'My Plugin',
        'shortcut'    => '',
        'confirm'     => false,
    ];
    return $commands;
} );
`

See the Settings → CommandBar page for built-in options, or use the `commandbar_commands` filter to add fully custom commands with any navigation URL or action.

== Installation ==

= From WordPress.org (Recommended) =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **CommandBar**
3. Click **Install Now** then **Activate**
4. Press **CMD+K** (Mac) or **CTRL+K** (Windows/Linux) — you're done

= Manual installation =

1. Download the plugin zip file
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the downloaded zip file and click **Install Now**
4. Click **Activate Plugin**

= From GitHub =

1. Clone or download from [https://github.com/KunalPareek21/commandbar](https://github.com/KunalPareek21/commandbar)
2. Upload the `commandbar` folder to `/wp-content/plugins/`
3. Activate through the Plugins screen

== Frequently Asked Questions ==

= Does this affect my site's frontend performance? =

Absolutely not. CommandBar adds zero JavaScript or CSS to your site's frontend. All assets are loaded exclusively in wp-admin. Your visitors will never know this plugin exists.

= What is the keyboard shortcut? =

Press **CMD+K** on Mac or **CTRL+K** on Windows and Linux from anywhere in wp-admin. The shortcut will not trigger while you are typing inside a text field, so it never interferes with post editing.

= Does it work with all WordPress admin color schemes? =

Yes. CommandBar reads WordPress admin colour scheme variables and adapts automatically. It also supports a configurable Light / Dark / Auto theme mode. The Auto mode (default) follows your WordPress admin colour scheme.

= Can I add custom commands? =

Yes. Use the `commandbar_commands` filter to add, remove, or modify commands. Each command is a PHP array following the documented schema. Use the `commandbar_commands` filter in your theme's `functions.php` or a custom plugin.

= Is it accessible? =

Yes, CommandBar is built to WCAG 2.1 AA. It uses proper ARIA roles (`dialog`, `combobox`, `listbox`, `option`), manages focus correctly, announces result counts and action completions to screen readers, respects `prefers-reduced-motion`, supports full keyboard navigation, and maintains a minimum 4.5:1 contrast ratio throughout.

= Does it work on WordPress Multisite? =

Yes. CommandBar is fully multisite compatible. Each sub-site has its own settings stored independently in `wp_options`.

= What capabilities does a user need to use CommandBar? =

CommandBar respects WordPress capabilities. Which commands appear in the palette depends entirely on what each user can do in WordPress. An Author will see post commands; only Administrators will see settings and plugin commands. You can also restrict which user roles can access CommandBar at all in **Settings → CommandBar → Advanced**.

= Does it make external HTTP requests? =

Never. CommandBar makes zero external HTTP requests. All dynamic searches use the WordPress REST API on your own server. No data leaves your site.

= Is it compatible with the WordPress Block Editor (Gutenberg)? =

Yes. CommandBar loads on all wp-admin pages including post editor screens. The CMD+K shortcut is carefully scoped to not fire when focus is inside a text editor or input field, so it will not interfere with block editor keyboard shortcuts.

= What happens to my settings if I deactivate the plugin? =

Settings are preserved on deactivation. They are only removed when you delete (uninstall) the plugin. This means you can deactivate and reactivate without losing your configuration.

== Screenshots ==

1. The CommandBar command palette open with default commands grouped by category
2. Live search results for posts and pages as you type
3. Dynamic user search using the @ prefix
4. Plugin search using the + prefix
5. The floating trigger button in the bottom-right corner
6. Settings page under Settings > CommandBar

== Changelog ==

= 1.0.2 =
* Fix: Floating trigger button now correctly shows WordPress blue background (moved --cb-accent CSS variable to :root scope)
* Fix: Palette theme (Dark/Light) now correctly applies to the floating trigger button on page load
* Bump: Version number updated for asset cache busting

= 1.0.1 =
* Fix: Dark palette theme now applies to floating trigger button (dark background + white text)
* Fix: Light palette theme button uses accent background with white text
* Fix: _applyPaletteTheme() now runs on initial page load, not only when palette opens

= 1.0.0 =
* Initial release
* Command palette with CMD+K / CTRL+K shortcut
* 30+ built-in commands across content, appearance, plugins, settings, users, tools, and actions
* Dynamic search for posts, pages, users, and plugins via REST API
* Fuzzy matching for static commands with relevance scoring
* Search prefixes: @ for users, > for settings, + for plugins
* Recently used commands (stored in localStorage)
* Flush Rewrite Rules action via REST API
* Toggle Dark Mode action with localStorage persistence
* Floating trigger button (dismissible per session)
* Settings page under Settings > CommandBar
* Full keyboard navigation with focus trap
* WCAG 2.1 AA accessibility compliance
* RTL layout support
* Multisite compatibility
* Zero frontend footprint
* `commandbar_commands` filter for developer extensibility
* Toast notifications for action feedback
* REST API results cached 60 seconds per user

== Upgrade Notice ==

= 1.0.2 =
Bug fix: floating trigger button now displays correct background colour in all themes. Recommended update for all users.

= 1.0.0 =
Initial release.
