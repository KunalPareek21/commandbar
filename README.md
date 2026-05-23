# CommandBar

**Your WordPress admin. At the speed of thought.**

A lightweight, keyboard-first command palette for the WordPress admin. Press **CMD+K** on Mac or **CTRL+K** on Windows/Linux from *anywhere* in `wp-admin` to instantly search posts, pages, users, plugins, settings, and run actions — without touching the mouse.

---

## Features

- **30+ built-in commands** across every area of wp-admin
- **Fuzzy search** across command titles, descriptions, and keyword aliases
- **Live REST API search** for posts, pages, users, and plugins
- **Search prefixes** — `@` for users, `>` for settings, `+` for plugins
- **Recent commands** — last N used commands shown on open (localStorage)
- **Server-side actions** — Flush Rewrite Rules, Toggle Dark Mode, Log Out
- **Floating trigger button** — dismissible per session, configurable position
- **Settings page** — minimal options, zero required configuration
- **`commandbar_commands` filter** — add custom commands from any plugin or theme
- **Zero frontend footprint** — no JS or CSS on the site's public pages
- **WCAG 2.1 AA** — full keyboard navigation, ARIA, focus trap, screen reader support
- **RTL support** — full bidirectional layout
- **Multisite compatible**
- **All WordPress admin colour schemes** supported

---

## Installation

```bash
# From the WordPress plugin directory
# Search "CommandBar" in Plugins → Add New

# Or manually:
cd wp-content/plugins
git clone https://github.com/KunalPareek21/commandbar commandbar
```

Then activate from **Plugins → Installed Plugins**.

---

## Usage

| Action | Shortcut |
|--------|----------|
| Open / Close palette | `CMD+K` / `Ctrl+K` |
| Navigate results | `↑` `↓` or `Tab` / `Shift+Tab` |
| Execute highlighted result | `Enter` |
| Close palette | `Esc` or click outside |
| Jump to first result | `Home` |
| Jump to last result | `End` |
| Search users | `@` + query |
| Search settings | `>` + query |
| Search plugins | `+` + query |

---

## Extending — Adding Custom Commands

```php
add_filter( 'commandbar_commands', function( array $commands ): array {
    $commands[] = [
        'id'          => 'my-custom-command',
        'title'       => 'Open My Plugin',
        'description' => 'Navigate to My Plugin settings',
        'keywords'    => [ 'my plugin', 'custom', 'settings' ],
        'icon'        => 'admin-settings',   // Dashicon slug
        'type'        => 'navigate',          // 'navigate' | 'action'
        'url'         => admin_url( 'admin.php?page=my-plugin' ),
        'capability'  => 'manage_options',    // WP capability gate
        'group'       => 'My Plugin',         // Group label in palette
        'shortcut'    => '',
        'confirm'     => false,
    ];
    return $commands;
} );
```

See [`docs/extending.md`](docs/extending.md) for full documentation.

---

## Architecture

```
commandbar/
├── admin/
│   ├── css/commandbar.css              # All palette and settings styles
│   └── js/
│       ├── commandbar-data.js          # Data accessors — no logic
│       ├── commandbar-search.js        # Fuzzy + REST API search
│       ├── commandbar-actions.js       # Command execution, toasts, localStorage
│       ├── commandbar-keyboard.js      # Keyboard shortcuts and focus trap
│       └── commandbar.js               # Main entry point — mounts and orchestrates
├── includes/
│   ├── class-commandbar.php            # Main plugin class
│   ├── class-commandbar-loader.php     # Hook registration
│   ├── class-commandbar-i18n.php       # Text domain loading
│   ├── class-commandbar-activator.php  # Activation defaults
│   ├── class-commandbar-deactivator.php
│   ├── class-commandbar-admin.php      # Asset enqueue, settings page
│   ├── class-commandbar-commands.php   # Built-in command registry
│   ├── class-commandbar-rest-api.php   # REST endpoints
│   └── class-commandbar-settings.php  # Settings API registration
├── languages/commandbar.pot
├── docs/                               # Developer documentation
├── uninstall.php
├── commandbar.php                      # Plugin header + bootstrap
├── readme.txt                          # WordPress.org readme
└── CHANGELOG.md
```

See [`docs/architecture.md`](docs/architecture.md) for in-depth explanation of every decision.

---

## Security

- All REST endpoints require authentication + nonce (`X-WP-Nonce`)
- Every endpoint performs capability checks before returning data
- All inputs sanitised (`sanitize_text_field`, `sanitize_key`, `absint`)
- All outputs escaped (`esc_html`, `esc_url`, `esc_attr`, `wp_json_encode`)
- Zero external HTTP requests
- DOM manipulation uses `textContent` — never `innerHTML` for user content

---

## Performance

- Palette opens in **< 100ms** (no network required for static commands)
- Dynamic search results in **< 500ms** (REST API + 60s transient cache)
- Plugin adds **zero assets to the frontend**
- All JS is vanilla ES6+ — no jQuery, no frameworks, no bundler required

---

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress | 6.3 |
| PHP | 8.0 |
| Browsers | Chrome, Firefox, Safari, Edge (all modern) |

---

## License

GPL v2 or later — see [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html)

---

## Author

**Kunal Pareek**
- Website: [kunalpareek.in](https://kunalpareek.in)
- Plugin URI: [kunalpareek.in/commandbar](https://kunalpareek.in/commandbar)
- GitHub: [@KunalPareek21](https://github.com/KunalPareek21)
