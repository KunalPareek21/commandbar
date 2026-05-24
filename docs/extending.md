# CommandBar – Smart Admin Navigation — Developer Extension Guide

CommandBar – Smart Admin Navigation is designed to be extended. The plugin exposes a clean PHP filter API that lets you add custom commands, register custom server-side actions, and modify search results — all without touching plugin core.

---

## Table of Contents

- [Philosophy](#philosophy)
- [The `commandbar_commands` Filter](#the-commandbar_commands-filter)
- [Command Object Schema](#command-object-schema)
- [Action Types](#action-types)
- [Adding Navigation Commands](#adding-navigation-commands)
- [Adding REST Action Commands](#adding-rest-action-commands)
- [Adding Custom Search Sources](#adding-custom-search-sources)
- [Filtering Search Results](#filtering-search-results)
- [Registering Custom Server-Side Actions](#registering-custom-server-side-actions)
- [Controlling Visibility Per User](#controlling-visibility-per-user)
- [Complete Working Example](#complete-working-example)
- [Best Practices](#best-practices)

---

## Philosophy

CommandBar – Smart Admin Navigation extensions should:
- Add commands that feel native to CommandBar – Smart Admin Navigation — same style, same quality
- Use standard WordPress hooks — no special SDK required
- Never bypass capability checks
- Keep commands scoped to what the current user can actually do

---

## The `commandbar_commands` Filter

This is the primary extension point. It fires after CommandBar – Smart Admin Navigation builds its default command list and before that list is sent to the browser via `wp_localize_script`.

**Hook:** `commandbar_commands`
**Type:** filter
**Parameter:** `array $commands` — the full array of command objects
**Return:** `array` — the modified array of command objects

```php
add_filter( 'commandbar_commands', 'my_plugin_add_commands' );

function my_plugin_add_commands( array $commands ): array {
    $commands[] = [
        'id'          => 'my-plugin-dashboard',
        'title'       => 'My Plugin Dashboard',
        'description' => 'Open My Plugin settings',
        'keywords'    => [ 'my plugin', 'dashboard', 'settings' ],
        'icon'        => 'admin-settings',
        'category'    => 'My Plugin',
        'action'      => [
            'type' => 'navigate',
            'url'  => admin_url( 'admin.php?page=my-plugin' ),
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];
    return $commands;
}
```

**Important:** This filter runs inside `admin_enqueue_scripts`, which only fires in wp-admin. You do not need to scope it yourself.

**Capability filtering:** CommandBar – Smart Admin Navigation automatically removes any command whose `capability` value the current user does not have, before sending the list to JavaScript. Always set a capability — never set it to `null` unless any authenticated user should see the command.

---

## Command Object Schema

Every command must be an associative array with the following keys:

```php
[
    // Required fields

    'id'          => 'unique-id',            // string, kebab-case, unique across all commands
    'title'       => 'Command Title',        // string, 2-4 words, shown in results list
    'description' => 'What it does',        // string, shown as secondary text
    'keywords'    => [ 'keyword1', '...' ], // string[], used for fuzzy matching
    'icon'        => 'dashicons-name',      // string, Dashicon name WITHOUT 'dashicons-' prefix
    'category'    => 'Category Name',       // string, used for result grouping
    'action'      => [ ... ],               // array, see Action Types below
    'capability'  => 'manage_options',      // string|null, WordPress capability

    // Optional fields

    'shortcut'    => 'Ctrl+Shift+X',        // string|null, display-only badge (no shortcut registered)
]
```

### Field details

**`id`** — Must be unique across all commands including built-ins. Use a plugin-specific prefix to avoid collisions: `myplugin-command-name`. IDs are stored in `localStorage` for recent commands — changing an ID will break recent command history for existing users.

**`keywords`** — The more keywords you include, the more ways users can find your command. Include:
- The exact title words
- Synonyms
- Common abbreviations
- Related terms users might think of
- Partial phrases

**`icon`** — Must be a [WordPress Dashicon](https://developer.wordpress.org/resource/dashicons/) name. Do not include the `dashicons-` prefix — CommandBar – Smart Admin Navigation adds it. If you pass an invalid name, the icon space will be empty but the command will still work.

**`category`** — Use your plugin name or a logical grouping. Avoid generic names like "Other" or "Misc". Category names appear as group headers in the results list.

**`capability`** — Set this to the minimum capability required to use the command. This is checked server-side before the command is included in the data sent to the browser. Use WordPress built-in capabilities (`manage_options`, `edit_posts`, `upload_files`, etc.).

---

## Action Types

The `action` array defines what happens when a user selects the command. CommandBar – Smart Admin Navigation supports five action types.

### `navigate` — Navigate to a URL

The most common type. Navigate the current tab to a URL.

```php
'action' => [
    'type' => 'navigate',
    'url'  => admin_url( 'admin.php?page=my-plugin' ),
]
```

Always use `admin_url()`, `get_edit_post_link()`, or other WordPress URL helpers — never hardcode `/wp-admin/`.

### `navigate-new-tab` — Open URL in new tab

```php
'action' => [
    'type' => 'navigate-new-tab',
    'url'  => 'https://example.com/docs',
]
```

Use sparingly. Opening new tabs should be intentional and expected by the user.

### `rest-action` — Execute a server-side action

Sends a POST request to `/commandbar/v1/actions` with the specified `actionName`. Shows a success or error toast.

```php
'action' => [
    'type'       => 'rest-action',
    'actionName' => 'my_custom_action',
]
```

You must also register the server-side handler — see [Registering Custom Server-Side Actions](#registering-custom-server-side-actions).

### `confirm-then-navigate` — Ask for confirmation, then navigate

Shows an inline confirmation prompt inside the palette before executing. Use for actions that cannot easily be undone.

```php
'action' => [
    'type'           => 'confirm-then-navigate',
    'url'            => wp_logout_url(),
    'confirmMessage' => 'Are you sure you want to log out?',
]
```

### `toggle-class` — Toggle a CSS class on `document.body`

For client-side UI toggles. The class toggle and `localStorage` persistence are handled automatically.

```php
'action' => [
    'type'      => 'toggle-class',
    'className' => 'my-plugin-dark-mode',
    'storageKey' => 'my_plugin_dark_mode', // localStorage key for persistence
]
```

---

## Adding Navigation Commands

The most common use case. Point users at your plugin's admin pages.

```php
add_filter( 'commandbar_commands', 'acme_plugin_register_commands' );

function acme_plugin_register_commands( array $commands ): array {
    if ( ! current_user_can( 'manage_options' ) ) {
        return $commands;
    }

    $commands[] = [
        'id'          => 'acme-dashboard',
        'title'       => 'Acme Dashboard',
        'description' => 'View Acme overview and stats',
        'keywords'    => [ 'acme', 'dashboard', 'overview', 'stats' ],
        'icon'        => 'chart-area',
        'category'    => 'Acme',
        'action'      => [
            'type' => 'navigate',
            'url'  => admin_url( 'admin.php?page=acme-dashboard' ),
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];

    $commands[] = [
        'id'          => 'acme-settings',
        'title'       => 'Acme Settings',
        'description' => 'Configure Acme plugin options',
        'keywords'    => [ 'acme', 'settings', 'configure', 'options' ],
        'icon'        => 'admin-settings',
        'category'    => 'Acme',
        'action'      => [
            'type' => 'navigate',
            'url'  => admin_url( 'admin.php?page=acme-settings' ),
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];

    return $commands;
}
```

**Tip:** You can check capabilities in your filter callback with `current_user_can()` before adding commands, as shown above. CommandBar – Smart Admin Navigation also checks capabilities server-side, so this is optional but slightly more efficient.

---

## Adding REST Action Commands

For commands that run server-side operations (cache clearing, queue processing, etc.):

### Step 1: Add the command

```php
add_filter( 'commandbar_commands', 'acme_register_action_commands' );

function acme_register_action_commands( array $commands ): array {
    $commands[] = [
        'id'          => 'acme-clear-cache',
        'title'       => 'Clear Acme Cache',
        'description' => 'Flush all Acme transient caches',
        'keywords'    => [ 'acme', 'clear cache', 'flush cache', 'cache', 'transients' ],
        'icon'        => 'update',
        'category'    => 'Acme',
        'action'      => [
            'type'       => 'rest-action',
            'actionName' => 'acme_clear_cache',
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];
    return $commands;
}
```

### Step 2: Register the server-side handler

```php
add_filter( 'commandbar_actions', 'acme_register_rest_actions' );

function acme_register_rest_actions( array $actions ): array {
    $actions['acme_clear_cache'] = [
        'capability' => 'manage_options',
        'callback'   => 'acme_execute_clear_cache',
    ];
    return $actions;
}

function acme_execute_clear_cache(): array {
    // Delete all Acme transients
    global $wpdb;
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
            $wpdb->esc_like( '_transient_acme_' ) . '%',
            $wpdb->esc_like( '_transient_timeout_acme_' ) . '%'
        )
    );

    if ( false === $deleted ) {
        return [
            'success' => false,
            'message' => __( 'Cache clear failed. Please try again.', 'acme-plugin' ),
        ];
    }

    return [
        'success' => true,
        'message' => __( 'Acme cache cleared successfully.', 'acme-plugin' ),
    ];
}
```

**The `commandbar_actions` filter:**

| Hook | Type | Parameter | Return |
|---|---|---|---|
| `commandbar_actions` | filter | `array $actions` — map of action names to handlers | `array` — modified map |

Each entry in the `$actions` array must be:
```php
'action_name' => [
    'capability' => 'required_capability',  // string, WordPress capability
    'callback'   => 'function_name',        // callable, must return ['success' => bool, 'message' => string]
]
```

The callback receives no parameters. It is only called after capability verification has passed.

---

## Adding Custom Search Sources

For plugins with large datasets (WooCommerce products, custom post types, CRM contacts, etc.), you can add dynamic search sources that plug into CommandBar – Smart Admin Navigation's REST API search.

### Using the `commandbar_search_results` filter

This filter fires after CommandBar – Smart Admin Navigation's built-in search results are assembled, before they are returned from the REST endpoint.

```php
add_filter( 'commandbar_search_results', 'acme_add_search_results', 10, 2 );

/**
 * @param array  $results Existing results.
 * @param string $query   The sanitized search query.
 * @return array
 */
function acme_add_search_results( array $results, string $query ): array {
    if ( ! current_user_can( 'manage_options' ) ) {
        return $results;
    }

    if ( strlen( $query ) < 2 ) {
        return $results;
    }

    // Search your custom data
    $orders = acme_search_orders( $query );

    foreach ( $orders as $order ) {
        $results[] = [
            'id'          => 'order-' . (int) $order->id,
            'title'       => '#' . (int) $order->id . ' — ' . esc_html( $order->customer_name ),
            'description' => esc_html( $order->status ) . ' · ' . esc_html( $order->total ),
            'url'         => admin_url( 'admin.php?page=acme-orders&order=' . (int) $order->id ),
            'type'        => 'order',
            'icon'        => 'dashicons-cart',
        ];
    }

    // Limit to 8 total results from this source
    return array_slice( $results, 0, 8 );
}
```

**Result object schema:**

```php
[
    'id'          => 'unique-result-id',   // string, must be unique in the results array
    'title'       => 'Result Title',        // string, shown as primary text
    'description' => 'Result description', // string, shown as secondary text
    'url'         => 'https://...',        // string, where Enter navigates
    'type'        => 'your-type',          // string, used for icon and badge
    'icon'        => 'dashicons-name',     // string, full dashicons class with prefix
]
```

**Note:** The `commandbar_search_results` filter only fires when a REST search request is made. Static commands do not go through this filter.

---

## Registering Custom Server-Side Actions

If you need to add more actions to the `POST /commandbar/v1/actions` endpoint beyond the built-in `flush_rewrite_rules`, use the `commandbar_actions` filter:

```php
add_filter( 'commandbar_actions', function( array $actions ): array {
    $actions['regenerate_thumbnails'] = [
        'capability' => 'manage_options',
        'callback'   => function(): array {
            // Your logic here
            wp_schedule_single_event( time(), 'acme_regenerate_all_thumbnails' );
            return [
                'success' => true,
                'message' => __( 'Thumbnail regeneration scheduled.', 'acme-plugin' ),
            ];
        },
    ];
    return $actions;
} );
```

The callback must return an array with:
- `success` (bool) — whether the action succeeded
- `message` (string) — user-facing message for the toast notification

---

## Controlling Visibility Per User

Use the `commandbar_enabled_for_user` filter to override whether CommandBar – Smart Admin Navigation is enabled for a specific user — useful for testing, role-specific rollouts, or maintenance modes.

```php
add_filter( 'commandbar_enabled_for_user', 'my_plugin_commandbar_visibility', 10, 2 );

/**
 * @param bool    $enabled     Whether CommandBar – Smart Admin Navigation is currently enabled.
 * @param WP_User $current_user The current user object.
 * @return bool
 */
function my_plugin_commandbar_visibility( bool $enabled, WP_User $current_user ): bool {
    // Only enable for users in the 'developer' group (hypothetical)
    if ( ! in_array( 'administrator', $current_user->roles, true ) ) {
        return false;
    }
    return $enabled;
}
```

---

## Complete Working Example

A complete example plugin that adds CommandBar – Smart Admin Navigation support for a fictional "Acme Invoices" plugin:

```php
<?php
/**
 * Plugin Name: Acme Invoices — CommandBar Integration
 * Description: Adds Acme Invoices commands to CommandBar – Smart Admin Navigation.
 * Version: 1.0.0
 * Requires Plugins: commandbar
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register commands with CommandBar – Smart Admin Navigation.
 * Hooks into commandbar_commands after CommandBar – Smart Admin Navigation initialises.
 */
add_filter( 'commandbar_commands', 'acme_commandbar_register_commands' );

function acme_commandbar_register_commands( array $commands ): array {
    // Add navigation commands
    $commands[] = [
        'id'          => 'acme-invoices-list',
        'title'       => 'All Invoices',
        'description' => 'View and manage all invoices',
        'keywords'    => [ 'invoices', 'acme', 'billing', 'all invoices', 'manage invoices' ],
        'icon'        => 'list-view',
        'category'    => 'Acme Invoices',
        'action'      => [
            'type' => 'navigate',
            'url'  => admin_url( 'admin.php?page=acme-invoices' ),
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];

    $commands[] = [
        'id'          => 'acme-new-invoice',
        'title'       => 'New Invoice',
        'description' => 'Create a new invoice',
        'keywords'    => [ 'new invoice', 'create invoice', 'add invoice', 'acme' ],
        'icon'        => 'plus-alt',
        'category'    => 'Acme Invoices',
        'action'      => [
            'type' => 'navigate',
            'url'  => admin_url( 'admin.php?page=acme-new-invoice' ),
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];

    $commands[] = [
        'id'          => 'acme-clear-invoice-cache',
        'title'       => 'Clear Invoice Cache',
        'description' => 'Flush all cached invoice data',
        'keywords'    => [ 'clear cache', 'flush cache', 'acme', 'invoices' ],
        'icon'        => 'update',
        'category'    => 'Acme Invoices',
        'action'      => [
            'type'       => 'rest-action',
            'actionName' => 'acme_clear_invoice_cache',
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];

    return $commands;
}

/**
 * Register the server-side cache-clear action.
 */
add_filter( 'commandbar_actions', 'acme_commandbar_register_actions' );

function acme_commandbar_register_actions( array $actions ): array {
    $actions['acme_clear_invoice_cache'] = [
        'capability' => 'manage_options',
        'callback'   => 'acme_execute_clear_invoice_cache',
    ];
    return $actions;
}

function acme_execute_clear_invoice_cache(): array {
    delete_transient( 'acme_invoices_summary' );
    delete_transient( 'acme_invoices_stats' );

    return [
        'success' => true,
        'message' => __( 'Invoice cache cleared.', 'acme-invoices' ),
    ];
}
```

---

## Best Practices

**Use a plugin-specific prefix on all IDs.** `'id' => 'myplugin-command-name'` prevents collisions with CommandBar – Smart Admin Navigation built-ins and other extensions.

**Provide rich keywords.** Users think in different words. Include synonyms, abbreviations, and related concepts. A command with 8 well-chosen keywords is found 4× more reliably than one with 2 keywords.

**Always set a capability.** Never set `capability` to `null` unless truly any authenticated user should see and run the command. Setting a capability ensures CommandBar – Smart Admin Navigation's server-side filter removes it for users who cannot execute it.

**Use `admin_url()` for all URLs.** Never hardcode `/wp-admin/` paths. This ensures compatibility with custom admin URLs and WordPress configurations that change the admin path.

**Keep category names consistent.** If you are adding multiple commands from one plugin, use a single category name for all of them (your plugin name). This groups them neatly in the results list.

**Return `$commands` unmodified if conditions are not met.** If you add commands conditionally (e.g., only when a certain page exists or a feature is enabled), always return the original `$commands` array when the condition is false.

**Escape all output in action callbacks.** Data returned from `rest-action` callbacks is shown in a toast notification. Use `esc_html()` on any dynamic string in the `message` field.

**Test with multiple roles.** After adding commands, log in as an Editor and Author to confirm capability filtering works correctly.

---

*For the complete command schema, see [commands.md](commands.md).*
*For architecture details, see [architecture.md](architecture.md).*
*For contributing to CommandBar – Smart Admin Navigation core, see [contribution-guide.md](contribution-guide.md).*
