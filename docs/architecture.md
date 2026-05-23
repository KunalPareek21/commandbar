# CommandBar — Architecture

This document explains the technical architecture of CommandBar: why it is built the way it is, what every file does, and the principles behind each major decision.

---

## Table of Contents

- [Folder Structure](#folder-structure)
- [PHP Class Responsibilities](#php-class-responsibilities)
- [JavaScript Module Architecture](#javascript-module-architecture)
- [REST API Design](#rest-api-design)
- [Why Vanilla JavaScript, Not React](#why-vanilla-javascript-not-react)
- [Performance Decisions](#performance-decisions)
- [Security Decisions](#security-decisions)
- [Hooks and Filters Architecture](#hooks-and-filters-architecture)
- [Data Flow](#data-flow)

---

## Folder Structure

```
commandbar/
├── admin/
│   ├── css/
│   │   └── commandbar.css          # All palette UI styles
│   └── js/
│       ├── commandbar.js           # Main entry point — initialises everything
│       ├── commandbar-data.js      # Static command definitions (no network)
│       ├── commandbar-actions.js   # Action execution + toast notifications
│       ├── commandbar-search.js    # Search logic: fuzzy + REST API
│       └── commandbar-keyboard.js  # Keyboard listeners + focus trap
├── includes/
│   ├── class-commandbar.php            # Core plugin class, constants, bootstrap
│   ├── class-commandbar-loader.php     # Hook registration registry
│   ├── class-commandbar-i18n.php       # Text domain loader
│   ├── class-commandbar-activator.php  # Activation hook logic
│   ├── class-commandbar-deactivator.php # Deactivation hook logic
│   ├── class-commandbar-admin.php      # Asset enqueue + settings page render
│   ├── class-commandbar-rest-api.php   # REST endpoint registration + handlers
│   ├── class-commandbar-commands.php   # Server-side capability-filtered commands
│   └── class-commandbar-settings.php   # Settings API registration + helpers
├── languages/
│   └── commandbar.pot              # POT file for translators
├── docs/                           # Documentation (this folder)
├── uninstall.php                   # Clean uninstall — removes all plugin data
├── commandbar.php                  # Plugin bootstrap file (header + init)
├── readme.txt                      # WordPress.org listing file
├── README.md                       # GitHub README
└── CHANGELOG.md                    # Version history
```

The structure follows the [WordPress Plugin Boilerplate](https://wppb.io/) conventions so that any WordPress developer familiar with that pattern can navigate the code immediately.

---

## PHP Class Responsibilities

### `commandbar.php` (root file)

The entry point. Contains the plugin header comment, defines the three plugin constants, and calls `run()` on the main class. Nothing else.

```php
define( 'COMMANDBAR_VERSION', '1.0.0' );
define( 'COMMANDBAR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COMMANDBAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
```

### `class-commandbar.php`

The orchestrator. Instantiates every other class and wires them together via the Loader. Uses a static singleton via `get_instance()` to ensure only one instance exists per request. Does not contain business logic — only wiring.

**Responsibilities:**
- Instantiate `CommandBar_Loader`
- Instantiate `CommandBar_I18n` and add its hooks
- Instantiate `CommandBar_Admin` and add its hooks
- Instantiate `CommandBar_REST_API` and add its hooks
- Call `$this->loader->run()`

### `class-commandbar-loader.php`

A hook registry. Inspired by the WordPress Plugin Boilerplate pattern. All `add_action` and `add_filter` calls go through this class so that:
- Hooks are never registered directly inside business logic classes
- The full list of hooks the plugin uses is visible in one place
- Testing is simpler because hooks can be inspected without running them

**Key methods:**
- `add_action( $hook, $component, $callback, $priority, $accepted_args )`
- `add_filter( $hook, $component, $callback, $priority, $accepted_args )`
- `run()` — loops through collected hooks and registers them with WordPress

### `class-commandbar-i18n.php`

Loads the plugin text domain on the `plugins_loaded` hook. The text domain is `commandbar`, matching the `Text Domain` header in `commandbar.php`. All translatable strings in PHP use `__( 'string', 'commandbar' )` or `esc_html__( 'string', 'commandbar' )`.

### `class-commandbar-activator.php`

Called once when the plugin is activated via `register_activation_hook()`. Responsibilities:
- Save default plugin options to `wp_options` if they do not already exist
- Store `commandbar_version` in `wp_options`
- Flush rewrite rules to register any new endpoints

**Important:** This class does NOT create database tables. CommandBar uses no custom database tables.

### `class-commandbar-deactivator.php`

Called once when the plugin is deactivated via `register_deactivation_hook()`. Responsibilities:
- Flush rewrite rules to deregister endpoints
- Nothing else — no data deletion on deactivation

Data deletion only happens in `uninstall.php`, which runs on **delete**, not deactivation. This respects the WordPress convention that deactivation is reversible.

### `class-commandbar-admin.php`

The admin-facing layer. Hooked into `admin_enqueue_scripts` to load assets, and into `admin_menu` to register the settings page.

**Asset enqueuing:**
- Loads CSS via `wp_enqueue_style()`
- Loads all five JS modules via `wp_enqueue_script()` with correct dependencies
- Calls `wp_localize_script()` to pass server-side data to JavaScript
- Uses `COMMANDBAR_VERSION` as the version string for automatic cache busting

**Localized data (`commandbarData`):**
```js
{
  nonce:       '...',          // wp_create_nonce( 'wp_rest' )
  restUrl:     '...',          // rest_url( 'commandbar/v1/' )
  ajaxUrl:     '...',          // admin_url( 'admin-ajax.php' )
  capabilities: { ... },       // current user capability flags
  settings:    { ... },        // plugin settings for current user
  currentUser: { id, name },   // minimal current user info
  i18n:        { ... }         // all translatable UI strings
}
```

**Why localize all i18n strings?**
WordPress's JavaScript i18n system (`wp_set_script_translations`) requires a separate HTTP request to load a `.json` translation file. For a plugin this size, inlining strings via `wp_localize_script` eliminates that request entirely while still being fully translatable by translators (strings live in PHP and are extracted into the `.pot` file).

### `class-commandbar-commands.php`

Builds the server-side command list filtered by the current user's capabilities. This list is passed to JavaScript via `wp_localize_script`. The JavaScript then uses this as its starting dataset for fuzzy search.

Having capability filtering happen on the PHP side means:
- The JavaScript never receives commands the current user cannot execute
- No capability check is needed inside the JavaScript search logic
- The list is correct for the specific user, not a union of all possible commands

### `class-commandbar-rest-api.php`

Registers and handles all REST API endpoints under the `commandbar/v1` namespace.

**Endpoints:**

| Method | Route | Handler |
|---|---|---|
| GET | `/commandbar/v1/search` | `handle_search()` |
| POST | `/commandbar/v1/actions` | `handle_action()` |

All endpoints:
- Define `permission_callback` to check user authentication and capabilities
- Sanitize all input parameters before use
- Return a consistent JSON envelope: `{ success, data, message }`
- Return proper HTTP status codes (200, 400, 403, 500)
- Cache search results via WordPress Transients (60-second TTL)

**Why REST API, not admin-ajax.php?**
The REST API provides:
- Automatic nonce handling via `X-WP-Nonce` header
- Proper HTTP methods (GET for search, POST for actions)
- Schema validation via `args` registration
- Better error response structure
- `admin-ajax.php` is a legacy pattern deprecated in spirit if not in code

### `class-commandbar-settings.php`

Registers all plugin settings with the WordPress Settings API via `register_setting()`. Each setting has a sanitize callback. Provides a `get_setting( $key )` static helper that returns the setting value or its default.

**Settings stored in `wp_options`:**

| Option key | Type | Default |
|---|---|---|
| `commandbar_enabled` | bool | true |
| `commandbar_show_trigger_button` | bool | true |
| `commandbar_trigger_button_position` | string | `'bottom-right'` |
| `commandbar_show_recent` | bool | true |
| `commandbar_recent_count` | int | 5 |
| `commandbar_theme` | string | `'auto'` |
| `commandbar_show_icons` | bool | true |
| `commandbar_show_shortcuts` | bool | true |
| `commandbar_enabled_roles` | array | `['administrator','editor','author']` |

---

## JavaScript Module Architecture

The JavaScript is split into five files that are loaded as separate `<script>` tags with dependencies. There is no bundler. This is intentional — see [Why Vanilla JavaScript](#why-vanilla-javascript-not-react).

### Load order and dependencies

```
commandbar-data.js      (no dependencies)
commandbar-keyboard.js  (no dependencies)
commandbar-search.js    (depends on: commandbar-data.js)
commandbar-actions.js   (depends on: commandbar-data.js)
commandbar.js           (depends on: all four above)
```

### `commandbar-data.js`

A pure data file. Exports a single `COMMANDBAR_COMMANDS` array. Each object in the array is a command definition:

```js
{
  id:          'new-post',
  title:       'New Post',
  description: 'Create a new blog post',
  keywords:    ['new', 'post', 'create', 'write', 'draft'],
  icon:        'dashicons-edit',
  category:    'Content',
  action:      { type: 'navigate', url: '/wp-admin/post-new.php' },
  capability:  'edit_posts',
  shortcut:    null
}
```

No network requests. No DOM access. Just data.

### `commandbar-search.js`

Handles all search logic:

1. **Static fuzzy search** — Scores each command in `COMMANDBAR_COMMANDS` against the query string. Scoring weights:
   - Exact title match: highest score
   - Title starts with query: high score
   - Keyword exact match: medium score
   - Keyword starts with query: lower score
   - Substring match anywhere: lowest score

2. **Prefix routing** — Checks the first character of the query:
   - `@` → user search via REST
   - `>` → settings search (static index)
   - `+` → plugin search via REST
   - *(anything else)* → static commands + post/page search via REST

3. **REST API search** — Debounced at 200ms to avoid firing on every keystroke. Uses `fetch()` with `X-WP-Nonce` header. Results cached in a `Map` keyed by query string for the duration of the browser session (clears on page reload).

### `commandbar-actions.js`

Executes commands when the user presses Enter or clicks a result.

**Action types:**
- `navigate` — `window.location.href = url`
- `navigate-new-tab` — `window.open( url, '_blank' )`
- `rest-action` — POST to `/commandbar/v1/actions`, then show toast
- `confirm-then-navigate` — show inline confirmation step, then navigate (used for Logout)
- `toggle-class` — toggle a class on `document.body` (used for Dark Mode toggle)

**Toast system:**
A lightweight in-DOM notification system. Creates a `<div role="alert" aria-live="assertive">` element, appends it to `document.body`, removes it after 3 seconds. No third-party library.

**Recent commands:**
After every successful execution, saves the command ID to `localStorage` under the key `commandbar_recent`. Stores maximum 10 IDs. The UI shows the most recent 5 (configurable via settings).

### `commandbar-keyboard.js`

Owns all keyboard event handling. Has no side effects outside of keyboard events — it calls into other modules to take actions.

**Global listener** (`document.addEventListener('keydown')`):
- Checks for `CMD+K` or `CTRL+K`
- Ignores if the active element is an `<input>`, `<textarea>`, `<select>`, or `[contenteditable]` — unless the palette is already open
- Calls `CommandBar.open()` or `CommandBar.close()` accordingly

**Palette listener** (added when palette opens, removed when it closes):
- `ArrowDown` / `Tab` → `selectNext()`
- `ArrowUp` / `Shift+Tab` → `selectPrevious()`
- `Home` → `selectFirst()`
- `End` → `selectLast()`
- `Enter` → `executeSelected()`
- `Escape` → `CommandBar.close()`

**Focus trap:**
When the palette is open, `Tab` and `Shift+Tab` do not leave the palette — they cycle through results instead. This is implemented by intercepting the Tab key in the palette listener before the browser processes it.

### `commandbar.js`

The main entry point. Runs on `DOMContentLoaded`. Responsibilities:
- Mount the palette HTML into `document.body`
- Mount the floating trigger button into `document.body`
- Initialise all modules
- Expose the `CommandBar.open()` and `CommandBar.close()` public API
- Handle open/close animation via CSS class toggling

---

## REST API Design

### Namespace

All endpoints live under `commandbar/v1`. The version suffix exists for future compatibility — a `v2` namespace can be added without breaking existing clients.

### Search endpoint: `GET /commandbar/v1/search`

**Parameters:**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `q` | string | Yes | Search query (sanitized with `sanitize_text_field`) |
| `type` | string | No | Filter type: `posts`, `pages`, `users`, `plugins` |

**Capability mapping:**

| Type | Required capability |
|---|---|
| posts | `edit_posts` |
| pages | `edit_pages` |
| users | `list_users` |
| plugins | `activate_plugins` |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "post-42",
      "title": "Hello World",
      "description": "Published · Post",
      "url": "/wp-admin/post.php?post=42&action=edit",
      "type": "post",
      "icon": "dashicons-admin-post"
    }
  ],
  "message": ""
}
```

**Caching:** Results for identical `q+type` combinations are stored in a WordPress Transient with a 60-second TTL. Cache key: `commandbar_search_` + `md5( $query . '_' . $type )`.

### Actions endpoint: `POST /commandbar/v1/actions`

**Parameters:**

| Parameter | Type | Required | Description |
|---|---|---|---|
| `action` | string | Yes | Action to execute (sanitized with `sanitize_key`) |

**Supported actions:**

| Action | Required capability |
|---|---|
| `flush_rewrite_rules` | `manage_options` |

**Response:**
```json
{
  "success": true,
  "data": null,
  "message": "Rewrite rules flushed successfully."
}
```

---

## Why Vanilla JavaScript, Not React

This was a deliberate decision, not a constraint.

**Reasons:**

1. **Zero build step.** The plugin ships as-is, readable by any developer. No `npm install`, no `webpack`, no compiled bundle to debug.

2. **Size.** The entire JavaScript footprint is under 30KB unminified. A minimal React setup adds ~45KB of runtime before a single line of app code.

3. **WordPress compatibility.** WordPress ships with React as `wp-element`, but using it as a dependency creates coupling to WordPress's React version. Breaking changes in WP core could break the plugin.

4. **Longevity.** Vanilla JavaScript written to the ES6+ standard will run without modification in any browser for the next 20 years. Framework-dependent code requires ongoing migration.

5. **Appropriate complexity.** CommandBar's UI is a single modal with a list of results. This does not require a virtual DOM, a component tree, or a state management system.

6. **Teachability.** Any WordPress developer can read, understand, and modify the JavaScript without knowing React, Vue, or any framework.

---

## Performance Decisions

See [performance.md](performance.md) for detailed numbers. Architecture-level decisions:

**No frontend assets.** `wp_enqueue_scripts` is never called. CSS and JS are loaded only via `admin_enqueue_scripts`, scoped to `wp-admin`. Site visitors never load any CommandBar assets.

**Static commands need no network.** The most common operations (New Post, Settings, Users) are defined entirely in `commandbar-data.js` and served inline with the page. The palette is usable before any network request.

**REST API only for dynamic content.** Only post/page/user searches require a network request, and only when the user has typed at least one character. Debounced at 200ms to avoid unnecessary requests.

**Transient caching.** REST API results are cached for 60 seconds. Typing the same query twice in a minute uses the cached result.

**No jQuery.** Dropping jQuery removes ~30KB of dependency and forces cleaner, more modern code.

---

## Security Decisions

**ABSPATH check on every PHP file.** Every `.php` file in the plugin starts with:
```php
if ( ! defined( 'ABSPATH' ) ) exit;
```
This prevents direct file access and the information disclosure that can result from PHP error output when files are accessed directly.

**Nonce verification on all REST endpoints.** The `wp_rest` nonce is generated server-side, passed to JavaScript via `wp_localize_script`, and sent with every REST request in the `X-WP-Nonce` header. WordPress REST API authentication automatically verifies this nonce.

**Capability check before every data access.** No data is returned unless the current user has the required capability. This is checked in `permission_callback` on every endpoint — not in the handler body — so WordPress returns a 403 before the handler even runs.

**Input sanitization at the boundary.** Every query parameter and POST body value is sanitized as soon as it enters PHP:
- `sanitize_text_field()` for search queries
- `sanitize_key()` for action names
- `absint()` for integer settings
- `sanitize_text_field()` for string settings

**Output escaping on every output.** Every PHP output is escaped:
- `esc_html()` for text content
- `esc_url()` for URLs
- `esc_attr()` for HTML attributes
- `wp_json_encode()` for JSON

**No `innerHTML` with untrusted data.** All DOM manipulation in JavaScript uses `textContent` for user-derived or server-derived strings. `innerHTML` is only used for template fragments that contain no user data.

---

## Hooks and Filters Architecture

CommandBar exposes a developer API via WordPress filters. See [extending.md](extending.md) for full documentation.

| Hook | Type | Description |
|---|---|---|
| `commandbar_commands` | filter | Modify the full command list before it is sent to JavaScript |
| `commandbar_search_results` | filter | Modify REST API search results before they are returned |
| `commandbar_actions` | filter | Register additional server-side actions |
| `commandbar_enabled_for_user` | filter | Override whether CommandBar is enabled for the current user |

---

## Data Flow

### Static command search (no network)

```
User types query
  → commandbar-search.js scores COMMANDBAR_COMMANDS array
  → Filters by commandbarData.capabilities
  → Returns top 8 results sorted by score
  → commandbar.js renders result list
  → Total time: < 5ms
```

### Dynamic content search (REST API)

```
User types query (after 200ms debounce)
  → commandbar-search.js checks in-memory cache
  → Cache miss: fetch( restUrl + 'search?q=...' )
      with X-WP-Nonce header
  → WordPress REST API authenticates request
  → class-commandbar-rest-api.php checks capability
  → Runs WP_Query / WP_User_Query / get_plugins()
  → Caches result in Transient (60s)
  → Returns JSON response
  → commandbar-search.js stores in memory cache
  → commandbar.js merges with static results and renders
  → Total time: < 500ms on typical hosting
```

### Action execution (REST API POST)

```
User executes action (e.g., Flush Rewrite Rules)
  → commandbar-actions.js POSTs to /commandbar/v1/actions
      with action: 'flush_rewrite_rules'
      with X-WP-Nonce header
  → WordPress REST API authenticates request
  → class-commandbar-rest-api.php checks manage_options capability
  → Calls flush_rewrite_rules()
  → Returns { success: true, message: '...' }
  → commandbar-actions.js shows success toast
  → Total time: < 300ms
```

---

*For setup instructions, see [setup.md](setup.md).*
*For extending CommandBar, see [extending.md](extending.md).*
*For accessibility implementation, see [accessibility.md](accessibility.md).*
