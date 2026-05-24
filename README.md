# CommandBar – Smart Admin Navigation

Your WordPress admin. At the speed of thought.

CommandBar – Smart Admin Navigation is a lightweight, keyboard-first command palette for the WordPress admin area. Press `CMD + K` on macOS or `CTRL + K` on Windows/Linux from anywhere inside `wp-admin` to instantly search posts, pages, users, plugins, settings, and execute actions without leaving the keyboard.

Inspired by modern developer tooling and command palettes, CommandBar – Smart Admin Navigation brings fast navigation and workflow acceleration to WordPress while remaining fully native to the WordPress ecosystem.

---

# Why CommandBar – Smart Admin Navigation Exists

Large WordPress admin workflows often involve:
- deeply nested menus
- repetitive navigation
- excessive clicking
- fragmented interfaces
- slow context switching

CommandBar – Smart Admin Navigation was built to reduce interaction cost inside `wp-admin` by introducing a fast, keyboard-first workflow while still respecting:
- WordPress admin conventions
- accessibility standards
- extensibility patterns
- long-term maintainability
- low-overhead architecture

The plugin intentionally avoids:
- frontend asset loading
- heavy frameworks
- unnecessary abstractions
- visual clutter

in favor of a clean, WordPress-native engineering approach.

---

# Features

- 30+ built-in commands across WordPress admin
- Keyboard-first navigation
- Fuzzy search across commands and aliases
- Live REST API search for:
  - posts
  - pages
  - users
  - plugins
- Search prefixes:
  - `@` → users
  - `>` → settings
  - `+` → plugins
- Recent commands persistence using localStorage
- Server-side actions:
  - Flush Rewrite Rules
  - Toggle Dark Mode
  - Log Out
- Floating trigger button
- Configurable settings page
- Extensible command system via filters
- Zero frontend footprint
- Full WCAG 2.1 AA accessibility support
- RTL support
- Multisite compatible
- Works with all WordPress admin colour schemes

---

# Installation

## WordPress Plugin Directory

1. Open:
   `Plugins → Add New`
2. Search:
   `CommandBar`
3. Install and activate

---

## Manual Installation

```bash
cd wp-content/plugins
git clone https://github.com/KunalPareek21/commandbar commandbar
```

Then activate the plugin from:

```txt
Plugins → Installed Plugins
```

---

# Usage

| Action | Shortcut |
|---|---|
| Open / Close palette | CMD + K / CTRL + K |
| Navigate results | ↑ ↓ or Tab / Shift + Tab |
| Execute command | Enter |
| Close palette | Esc |
| Jump to first result | Home |
| Jump to last result | End |
| Search users | @ + query |
| Search settings | > + query |
| Search plugins | + + query |

---

# Extending CommandBar – Smart Admin Navigation

CommandBar – Smart Admin Navigation is intentionally designed to be extensible using native WordPress hooks.

Third-party plugins and themes can register custom commands using the `commandbar_commands` filter.

Example:

```php
add_filter( 'commandbar_commands', function( array $commands ): array {

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
```

See:

```txt
docs/extending.md
```

for complete developer documentation.

---

# Engineering Decisions

## Vanilla JavaScript Over Frameworks

CommandBar – Smart Admin Navigation intentionally uses lightweight vanilla JavaScript instead of large frontend frameworks in order to:
- reduce admin overhead
- simplify maintenance
- minimize dependencies
- improve long-term stability
- avoid unnecessary bundle complexity

---

## Separation of Concerns

Search logic, keyboard interactions, actions, and rendering responsibilities are isolated into separate modules to improve:
- maintainability
- extensibility
- debugging
- testing
- long-term scalability

---

## WordPress-Native Extensibility

Commands are extensible through native WordPress hooks rather than requiring direct plugin modification.

This allows:
- safer customization
- plugin interoperability
- ecosystem compatibility

---

## Zero Frontend Footprint

The plugin loads:
- no JavaScript
- no CSS
- no assets

on the public-facing frontend.

All functionality remains isolated to `wp-admin`.

---

# Architecture

```txt
commandbar/
├── admin/
│   ├── css/
│   │   └── commandbar.css
│   └── js/
│       ├── commandbar-data.js
│       ├── commandbar-search.js
│       ├── commandbar-actions.js
│       ├── commandbar-keyboard.js
│       └── commandbar.js
│
├── includes/
│   ├── class-commandbar.php
│   ├── class-commandbar-loader.php
│   ├── class-commandbar-i18n.php
│   ├── class-commandbar-activator.php
│   ├── class-commandbar-deactivator.php
│   ├── class-commandbar-admin.php
│   ├── class-commandbar-commands.php
│   ├── class-commandbar-rest-api.php
│   └── class-commandbar-settings.php
│
├── languages/
│   └── commandbar.pot
│
├── docs/
├── uninstall.php
├── commandbar.php
├── readme.txt
└── CHANGELOG.md
```

Detailed architecture documentation:

```txt
docs/architecture.md
```

---

# Accessibility

CommandBar – Smart Admin Navigation is designed with accessibility as a first-class engineering requirement.

Supported accessibility features include:
- WCAG 2.1 AA compliance
- Full keyboard navigation
- Focus trap management
- ARIA labels and semantics
- Screen reader compatibility
- Reduced motion support
- High contrast compatibility
- RTL support

---

# Security

Security considerations include:

- Authentication required for all REST endpoints
- Nonce validation using `X-WP-Nonce`
- Capability checks before all privileged actions
- Input sanitization:
  - `sanitize_text_field`
  - `sanitize_key`
  - `absint`
- Output escaping:
  - `esc_html`
  - `esc_attr`
  - `esc_url`
  - `wp_json_encode`
- No unsafe HTML rendering
- No external HTTP requests
- DOM manipulation uses `textContent` instead of `innerHTML`

---

# Performance

Performance goals:
- Palette opens in under 100ms
- Minimal runtime overhead
- Zero frontend impact
- Fast dynamic search responses
- Lightweight admin-only asset loading

Implementation details:
- Static commands require no network requests
- REST API search responses cached with transients
- Vanilla ES6+ JavaScript
- No jQuery dependency
- No build system required

---

# Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.3 |
| PHP | 8.0 |
| Browser Support | Chrome, Firefox, Safari, Edge |

---

# Documentation

Additional documentation available in:

```txt
docs/
```

Including:
- architecture
- extensibility
- performance
- accessibility
- customization
- development workflow

---

# Development Philosophy

CommandBar – Smart Admin Navigation is built around a few core principles:

- reduce admin friction
- prioritize keyboard workflows
- respect WordPress conventions
- maintain low overhead
- build extensible systems
- optimize long-term maintainability
- favor clarity over abstraction

---

# Roadmap

Planned future improvements may include:
- custom keyboard shortcuts
- command usage analytics
- global search providers
- WooCommerce integrations
- developer SDK helpers
- command grouping APIs
- Gutenberg-specific commands

---

# License

GPL v2 or later

---

# Author

Kunal Pareek

Website:
https://kunalpareek.in

GitHub:
https://github.com/KunalPareek21

Plugin URI:
https://kunalpareek.in/commandbar
