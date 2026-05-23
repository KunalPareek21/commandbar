# CommandBar — Complete Command Reference

This document lists every built-in command in CommandBar, the search keywords that trigger each one, the WordPress capability required, and any keyboard shortcut or special behaviour.

---

## Table of Contents

- [How Commands Work](#how-commands-work)
- [Search Prefixes](#search-prefixes)
- [Content Commands](#content-commands)
- [Appearance Commands](#appearance-commands)
- [Plugin Commands](#plugin-commands)
- [Settings Commands](#settings-commands)
- [User Commands](#user-commands)
- [Tools Commands](#tools-commands)
- [Action Commands](#action-commands)
- [Dynamic Search Commands](#dynamic-search-commands)
- [Command Object Schema](#command-object-schema)

---

## How Commands Work

When you open CommandBar and type, two things happen simultaneously:

1. **Static fuzzy match** — Every built-in command is scored against your query using a weighted keyword matching algorithm. No network request required.
2. **Dynamic REST search** — After a 200ms debounce, a REST API request fetches live results for posts, pages, users, or plugins depending on your query prefix.

Results from both sources are merged and ranked by relevance. The top 8 results are shown.

**Capability filtering** happens on the server side. Commands your current user role cannot execute are never included in the list delivered to the browser.

---

## Search Prefixes

These special first characters route your query to a specific search source:

| Prefix | Searches | Example |
|---|---|---|
| *(no prefix)* | Static commands + posts + pages | `about us` |
| `@` | Users | `@john` |
| `>` | Settings pages | `>permalink` |
| `+` | Installed plugins | `+woocommerce` |

---

## Content Commands

### New Post

| Field | Value |
|---|---|
| **Keywords** | new post, create post, write, add post, draft |
| **Action** | Navigate to `/wp-admin/post-new.php` |
| **Icon** | `dashicons-edit` |
| **Capability** | `edit_posts` |
| **Category** | Content |

### New Page

| Field | Value |
|---|---|
| **Keywords** | new page, create page, add page |
| **Action** | Navigate to `/wp-admin/post-new.php?post_type=page` |
| **Icon** | `dashicons-media-document` |
| **Capability** | `edit_pages` |
| **Category** | Content |

### New Media

| Field | Value |
|---|---|
| **Keywords** | upload media, new media, add image, add file, upload file |
| **Action** | Navigate to `/wp-admin/media-new.php` |
| **Icon** | `dashicons-format-image` |
| **Capability** | `upload_files` |
| **Category** | Content |

### All Posts

| Field | Value |
|---|---|
| **Keywords** | all posts, posts list, manage posts, edit posts, view posts |
| **Action** | Navigate to `/wp-admin/edit.php` |
| **Icon** | `dashicons-admin-post` |
| **Capability** | `edit_posts` |
| **Category** | Content |

### All Pages

| Field | Value |
|---|---|
| **Keywords** | all pages, pages list, manage pages, edit pages, view pages |
| **Action** | Navigate to `/wp-admin/edit.php?post_type=page` |
| **Icon** | `dashicons-admin-page` |
| **Capability** | `edit_pages` |
| **Category** | Content |

### All Media

| Field | Value |
|---|---|
| **Keywords** | media library, all media, images, files, gallery, uploads |
| **Action** | Navigate to `/wp-admin/upload.php` |
| **Icon** | `dashicons-admin-media` |
| **Capability** | `upload_files` |
| **Category** | Content |

### Draft Posts

| Field | Value |
|---|---|
| **Keywords** | drafts, draft posts, unpublished, pending |
| **Action** | Navigate to `/wp-admin/edit.php?post_status=draft` |
| **Icon** | `dashicons-welcome-write-blog` |
| **Capability** | `edit_posts` |
| **Category** | Content |

### Scheduled Posts

| Field | Value |
|---|---|
| **Keywords** | scheduled, scheduled posts, future posts, queued |
| **Action** | Navigate to `/wp-admin/edit.php?post_status=future` |
| **Icon** | `dashicons-clock` |
| **Capability** | `edit_posts` |
| **Category** | Content |

### Comments

| Field | Value |
|---|---|
| **Keywords** | comments, manage comments, moderate, discussion |
| **Action** | Navigate to `/wp-admin/edit-comments.php` |
| **Icon** | `dashicons-admin-comments` |
| **Capability** | `moderate_comments` |
| **Category** | Content |

---

## Appearance Commands

### Themes

| Field | Value |
|---|---|
| **Keywords** | themes, change theme, appearance, switch theme, install theme |
| **Action** | Navigate to `/wp-admin/themes.php` |
| **Icon** | `dashicons-admin-appearance` |
| **Capability** | `switch_themes` |
| **Category** | Appearance |

### Customize

| Field | Value |
|---|---|
| **Keywords** | customize, customizer, site settings, site identity, theme options |
| **Action** | Navigate to `/wp-admin/customize.php` |
| **Icon** | `dashicons-admin-customizer` |
| **Capability** | `customize` |
| **Category** | Appearance |

### Widgets

| Field | Value |
|---|---|
| **Keywords** | widgets, sidebar widgets, footer widgets, widget areas |
| **Action** | Navigate to `/wp-admin/widgets.php` |
| **Icon** | `dashicons-screenoptions` |
| **Capability** | `edit_theme_options` |
| **Category** | Appearance |

### Menus

| Field | Value |
|---|---|
| **Keywords** | menus, navigation menus, nav menus, header menu, footer menu |
| **Action** | Navigate to `/wp-admin/nav-menus.php` |
| **Icon** | `dashicons-menu` |
| **Capability** | `edit_theme_options` |
| **Category** | Appearance |

### Site Editor (FSE)

| Field | Value |
|---|---|
| **Keywords** | site editor, full site editing, fse, block editor, site, editor |
| **Action** | Navigate to `/wp-admin/site-editor.php` |
| **Icon** | `dashicons-layout` |
| **Capability** | `edit_theme_options` |
| **Category** | Appearance |
| **Note** | Only visible when active theme supports full-site editing |

---

## Plugin Commands

### All Plugins

| Field | Value |
|---|---|
| **Keywords** | plugins, manage plugins, installed plugins, active plugins |
| **Action** | Navigate to `/wp-admin/plugins.php` |
| **Icon** | `dashicons-admin-plugins` |
| **Capability** | `activate_plugins` |
| **Category** | Plugins |

### Add Plugin

| Field | Value |
|---|---|
| **Keywords** | add plugin, install plugin, new plugin, search plugins |
| **Action** | Navigate to `/wp-admin/plugin-install.php` |
| **Icon** | `dashicons-plus-alt` |
| **Capability** | `install_plugins` |
| **Category** | Plugins |

### Plugin Updates

| Field | Value |
|---|---|
| **Keywords** | plugin updates, update plugins, outdated plugins |
| **Action** | Navigate to `/wp-admin/plugins.php?plugin_status=upgrade` |
| **Icon** | `dashicons-update` |
| **Capability** | `update_plugins` |
| **Category** | Plugins |

---

## Settings Commands

### General Settings

| Field | Value |
|---|---|
| **Keywords** | general settings, site title, tagline, admin email, timezone |
| **Action** | Navigate to `/wp-admin/options-general.php` |
| **Icon** | `dashicons-admin-settings` |
| **Capability** | `manage_options` |
| **Category** | Settings |

### Writing Settings

| Field | Value |
|---|---|
| **Keywords** | writing settings, default category, post format |
| **Action** | Navigate to `/wp-admin/options-writing.php` |
| **Icon** | `dashicons-edit` |
| **Capability** | `manage_options` |
| **Category** | Settings |

### Reading Settings

| Field | Value |
|---|---|
| **Keywords** | reading settings, homepage settings, front page, blog page, posts per page |
| **Action** | Navigate to `/wp-admin/options-reading.php` |
| **Icon** | `dashicons-book` |
| **Capability** | `manage_options` |
| **Category** | Settings |

### Discussion Settings

| Field | Value |
|---|---|
| **Keywords** | discussion settings, comment settings, moderation, comment notifications |
| **Action** | Navigate to `/wp-admin/options-discussion.php` |
| **Icon** | `dashicons-admin-comments` |
| **Capability** | `manage_options` |
| **Category** | Settings |

### Permalinks

| Field | Value |
|---|---|
| **Keywords** | permalinks, url structure, slugs, pretty urls, rewrite |
| **Action** | Navigate to `/wp-admin/options-permalink.php` |
| **Icon** | `dashicons-admin-links` |
| **Capability** | `manage_options` |
| **Category** | Settings |

### Privacy Settings

| Field | Value |
|---|---|
| **Keywords** | privacy, privacy policy, privacy page, gdpr |
| **Action** | Navigate to `/wp-admin/options-privacy.php` |
| **Icon** | `dashicons-shield` |
| **Capability** | `manage_options` |
| **Category** | Settings |

---

## User Commands

### All Users

| Field | Value |
|---|---|
| **Keywords** | users, manage users, all users, user list |
| **Action** | Navigate to `/wp-admin/users.php` |
| **Icon** | `dashicons-admin-users` |
| **Capability** | `list_users` |
| **Category** | Users |

### Add User

| Field | Value |
|---|---|
| **Keywords** | add user, new user, create user, invite user |
| **Action** | Navigate to `/wp-admin/user-new.php` |
| **Icon** | `dashicons-plus-alt` |
| **Capability** | `create_users` |
| **Category** | Users |

### Your Profile

| Field | Value |
|---|---|
| **Keywords** | profile, my profile, edit profile, account settings |
| **Action** | Navigate to `/wp-admin/profile.php` |
| **Icon** | `dashicons-admin-users` |
| **Capability** | *(any logged-in user)* |
| **Category** | Users |

---

## Tools Commands

### Import

| Field | Value |
|---|---|
| **Keywords** | import, import content, migrate content |
| **Action** | Navigate to `/wp-admin/import.php` |
| **Icon** | `dashicons-download` |
| **Capability** | `import` |
| **Category** | Tools |

### Export

| Field | Value |
|---|---|
| **Keywords** | export, export content, backup content, download content |
| **Action** | Navigate to `/wp-admin/export.php` |
| **Icon** | `dashicons-upload` |
| **Capability** | `export` |
| **Category** | Tools |

### Site Health

| Field | Value |
|---|---|
| **Keywords** | site health, health check, diagnostics, status |
| **Action** | Navigate to `/wp-admin/site-health.php` |
| **Icon** | `dashicons-heart` |
| **Capability** | `view_site_health_checks` |
| **Category** | Tools |

### Site Health Info

| Field | Value |
|---|---|
| **Keywords** | site health info, system info, server info, debug info, environment |
| **Action** | Navigate to `/wp-admin/site-health.php?tab=debug` |
| **Icon** | `dashicons-info` |
| **Capability** | `view_site_health_checks` |
| **Category** | Tools |

### Erase Personal Data

| Field | Value |
|---|---|
| **Keywords** | erase data, personal data, gdpr, remove user data, data erasure |
| **Action** | Navigate to `/wp-admin/tools.php?page=remove_personal_data` |
| **Icon** | `dashicons-trash` |
| **Capability** | `erase_others_personal_data` |
| **Category** | Tools |

---

## Action Commands

These commands execute server-side or client-side actions rather than navigating to a page.

### Check for Updates

| Field | Value |
|---|---|
| **Keywords** | check updates, wordpress updates, update wordpress, core updates |
| **Action** | Navigate to `/wp-admin/update-core.php` |
| **Icon** | `dashicons-update` |
| **Capability** | `update_core` |
| **Category** | Actions |

### Clear Rewrite Rules

| Field | Value |
|---|---|
| **Keywords** | flush rewrite, clear rewrite, refresh permalinks, reset permalinks |
| **Action** | POST to `/commandbar/v1/actions` with `action: flush_rewrite_rules` |
| **Icon** | `dashicons-update` |
| **Capability** | `manage_options` |
| **Category** | Actions |
| **Note** | Executes server-side via REST API; shows success/error toast |

### Logout

| Field | Value |
|---|---|
| **Keywords** | logout, sign out, log out, exit |
| **Action** | Navigate to `wp-login.php?action=logout` with `_wpnonce` |
| **Icon** | `dashicons-exit` |
| **Capability** | *(any logged-in user)* |
| **Category** | Actions |
| **Note** | Shows an inline confirmation step before executing. The logout URL includes a valid `_wpnonce` generated server-side. |

### Dark Mode Toggle

| Field | Value |
|---|---|
| **Keywords** | dark mode, toggle dark mode, light mode, night mode, theme |
| **Action** | Toggles `commandbar-dark-mode` class on `document.body`; saves preference to `localStorage` |
| **Icon** | `dashicons-visibility` (sun) / `dashicons-hidden` (moon) |
| **Capability** | *(any logged-in user)* |
| **Category** | Actions |
| **Note** | Client-side only. Preference persists across page loads via `localStorage`. Does not affect other users or the WordPress admin theme setting. |

---

## Dynamic Search Commands

These results are fetched live from the WordPress database via REST API as the user types.

### Post Search

| Field | Value |
|---|---|
| **Trigger** | Any query with no prefix that does not match a static command |
| **Endpoint** | `GET /commandbar/v1/search?q={query}&type=posts` |
| **Results show** | Post title, post type badge, post status, Edit link |
| **Action** | Navigate to post edit screen |
| **Capability** | `edit_posts` |
| **Max results** | 8 |

### Page Search

| Field | Value |
|---|---|
| **Trigger** | Any query with no prefix |
| **Endpoint** | `GET /commandbar/v1/search?q={query}&type=pages` |
| **Results show** | Page title, status, hierarchy hint, Edit link |
| **Action** | Navigate to page edit screen |
| **Capability** | `edit_pages` |
| **Max results** | 8 |

### User Search

| Field | Value |
|---|---|
| **Trigger** | Query beginning with `@` |
| **Example** | `@john` |
| **Endpoint** | `GET /commandbar/v1/search?q={query}&type=users` |
| **Results show** | Gravatar, display name, email address, user role |
| **Action** | Navigate to user edit screen |
| **Capability** | `list_users` |
| **Max results** | 8 |

### Settings Search

| Field | Value |
|---|---|
| **Trigger** | Query beginning with `>` |
| **Example** | `>reading` |
| **Source** | Static index of WordPress settings pages (no network request) |
| **Results show** | Settings page name, description of what the option controls |
| **Action** | Navigate to settings page |
| **Capability** | `manage_options` |

### Plugin Search

| Field | Value |
|---|---|
| **Trigger** | Query beginning with `+` |
| **Example** | `+woo` |
| **Endpoint** | `GET /commandbar/v1/search?q={query}&type=plugins` |
| **Results show** | Plugin name, active/inactive status, Activate or Deactivate action |
| **Action** | Navigate to Plugins page filtered to that plugin |
| **Capability** | `activate_plugins` |
| **Max results** | 8 |

---

## Command Object Schema

Every static command in `commandbar-data.js` follows this schema:

```js
{
  /**
   * Unique string identifier. Used for recent command tracking in localStorage.
   * Format: kebab-case. Must be stable across plugin versions.
   * @type {string}
   */
  id: 'new-post',

  /**
   * Display title shown in the results list.
   * Should be short (2-4 words).
   * @type {string}
   */
  title: 'New Post',

  /**
   * Secondary text shown to the right of the title.
   * Describes the action or destination.
   * @type {string}
   */
  description: 'Create a new blog post',

  /**
   * Array of search keywords. The fuzzy search algorithm scores the query
   * against every keyword in this array. Include synonyms, common typos,
   * and related terms.
   * @type {string[]}
   */
  keywords: ['new', 'post', 'create', 'write', 'draft', 'add post'],

  /**
   * WordPress Dashicon class name (without the 'dashicons-' prefix).
   * Full list: https://developer.wordpress.org/resource/dashicons/
   * @type {string}
   */
  icon: 'edit',

  /**
   * Grouping category for display in the results list.
   * One of: 'Content', 'Appearance', 'Plugins', 'Settings', 'Users', 'Tools', 'Actions'
   * @type {string}
   */
  category: 'Content',

  /**
   * The action to execute when this command is selected.
   * @type {Object}
   */
  action: {
    /**
     * Action type determines how the command is executed.
     * One of: 'navigate', 'navigate-new-tab', 'rest-action', 'confirm-then-navigate', 'toggle-class'
     * @type {string}
     */
    type: 'navigate',

    /**
     * For 'navigate' and 'navigate-new-tab' and 'confirm-then-navigate':
     * The URL to navigate to. Can be relative to wp-admin.
     * @type {string}
     */
    url: '/wp-admin/post-new.php',

    /**
     * For 'rest-action':
     * The action parameter to send to /commandbar/v1/actions
     * @type {string}
     */
    // actionName: 'flush_rewrite_rules',

    /**
     * For 'confirm-then-navigate':
     * The confirmation message shown before executing.
     * @type {string}
     */
    // confirmMessage: 'Are you sure you want to log out?',

    /**
     * For 'toggle-class':
     * The CSS class to toggle on document.body
     * @type {string}
     */
    // className: 'commandbar-dark-mode',
  },

  /**
   * WordPress capability required to see and execute this command.
   * If null, any authenticated user can use it.
   * @type {string|null}
   */
  capability: 'edit_posts',

  /**
   * Optional keyboard shortcut hint displayed as a badge on the result.
   * Display-only. Does not register an actual keyboard shortcut.
   * @type {string|null}
   */
  shortcut: null,
}
```

---

## Adding Custom Commands

Developers can extend this command list without modifying plugin core. See [extending.md](extending.md) for the complete guide with code examples.

Quick reference:

```php
add_filter( 'commandbar_commands', function( $commands ) {
    $commands[] = [
        'id'          => 'my-custom-command',
        'title'       => 'My Custom Command',
        'description' => 'Does something useful',
        'keywords'    => [ 'custom', 'my', 'command' ],
        'icon'        => 'star-filled',
        'category'    => 'Custom',
        'action'      => [
            'type' => 'navigate',
            'url'  => admin_url( 'admin.php?page=my-plugin' ),
        ],
        'capability'  => 'manage_options',
        'shortcut'    => null,
    ];
    return $commands;
} );
```

---

*For extending CommandBar with custom commands, see [extending.md](extending.md).*
*For architecture details, see [architecture.md](architecture.md).*
