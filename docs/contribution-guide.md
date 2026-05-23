# CommandBar — Contribution Guide

Thank you for contributing to CommandBar. This guide covers everything you need to set up a development environment, understand the codebase, and submit a pull request.

---

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Development Environment Setup](#development-environment-setup)
- [Project Structure](#project-structure)
- [Coding Standards](#coding-standards)
- [How to Add a New Command](#how-to-add-a-new-command)
- [How to Add a New REST Endpoint](#how-to-add-a-new-rest-endpoint)
- [How to Add a New Setting](#how-to-add-a-new-setting)
- [Commit Message Format](#commit-message-format)
- [Pull Request Process](#pull-request-process)
- [Testing Checklist](#testing-checklist)
- [Release Process](#release-process)

---

## Code of Conduct

Contributors are expected to:
- Be respectful in all communication (issues, pull requests, discussions).
- Focus feedback on code, not on people.
- Follow the WordPress community [Code of Conduct](https://make.wordpress.org/handbook/community-code-of-conduct/).

---

## Development Environment Setup

### Prerequisites

| Tool | Version | Notes |
|---|---|---|
| PHP | 8.0+ | Match plugin minimum requirement |
| WordPress | 6.3+ | Match plugin minimum requirement |
| Composer | 2.x | For PHP coding standards checks |
| Node.js | 18+ | Optional — only needed if you add npm tooling |
| WP-CLI | Any recent | Recommended for development workflows |

### Option 1: Local by Flywheel / LocalWP

1. Create a new WordPress site in Local.
2. Clone the repository into `wp-content/plugins/`:
   ```bash
   cd /path/to/local/site/app/public/wp-content/plugins/
   git clone https://github.com/KunalPareek21/commandbar.git
   ```
3. Activate the plugin:
   ```bash
   wp plugin activate commandbar
   ```
4. Navigate to your local site's wp-admin and press CMD+K.

### Option 2: Varying Vagrants Vagrant (VVV)

1. Add CommandBar to a VVV provisioned site's plugins:
   ```bash
   cd /srv/www/wordpress-default/public_html/wp-content/plugins/
   git clone https://github.com/KunalPareek21/commandbar.git
   ```
2. Activate via WP-CLI:
   ```bash
   wp --path=/srv/www/wordpress-default/public_html plugin activate commandbar
   ```

### Option 3: wp-env (Official WordPress Environment)

If you have Node.js and Docker installed:

```bash
# From the plugin root
npm install -g @wordpress/env
wp-env start
```

This creates a Docker-based WordPress environment with the plugin automatically activated.

### Option 4: Any XAMPP / MAMP / Laragon / Lando Setup

Copy or symlink the plugin folder into the `wp-content/plugins/` directory of any local WordPress installation and activate via the Plugins screen or WP-CLI.

### PHP Coding Standards Setup

```bash
# From the plugin root directory
composer install
# or install PHPCS + WordPress standards manually:
composer require --dev squizlabs/php_codesniffer
composer require --dev wp-coding-standards/wpcs
./vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs
```

Run standards check:
```bash
./vendor/bin/phpcs --standard=WordPress commandbar.php includes/
```

Run auto-fix (where possible):
```bash
./vendor/bin/phpcbf --standard=WordPress commandbar.php includes/
```

---

## Project Structure

See [architecture.md](architecture.md) for the full architecture explanation. Quick reference:

```
includes/           PHP classes — one responsibility per file
admin/css/          Single stylesheet for the palette UI
admin/js/           Five JavaScript modules
languages/          Translation template (.pot)
docs/               This documentation
uninstall.php       Data cleanup on plugin delete
commandbar.php      Plugin bootstrap (header + init)
```

---

## Coding Standards

### PHP

CommandBar follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

Key rules:
- Tabs for indentation (not spaces).
- Opening braces on the same line for control structures, on their own line for class and function declarations.
- Spaces inside parentheses: `if ( $condition )` not `if($condition)`.
- Yoda conditions: `if ( 'value' === $variable )`.
- DocBlocks on all classes and public/protected methods.
- Prefix all function names, class names, hook names, and option keys with `commandbar_` or `CommandBar_`.
- `if ( ! defined( 'ABSPATH' ) ) exit;` at the top of every PHP file.

**Required on all output:**
- `esc_html()` for text
- `esc_url()` for URLs
- `esc_attr()` for HTML attributes
- `wp_json_encode()` for JSON

**Required on all input:**
- `sanitize_text_field()` for text strings
- `sanitize_key()` for keys and action names
- `absint()` for integers
- `wp_unslash()` before sanitizing `$_POST` and `$_GET` values

### JavaScript

CommandBar JavaScript follows these rules:

- `'use strict';` at the top of every file.
- `const` and `let` — never `var`.
- Arrow functions for callbacks where `this` is not needed.
- Template literals instead of string concatenation.
- `async/await` instead of `.then()` chains.
- JSDoc on all exported functions and non-obvious helpers.
- `textContent` instead of `innerHTML` for any content derived from user input or server data.
- `window.commandbarData` for accessing localised data (not `commandbar_data` or global variables).
- No `console.log`, `console.warn`, or `console.error` in committed code.
- No `alert()`, `confirm()`, or `prompt()`.
- No jQuery — vanilla JavaScript only.
- No external library imports.

### CSS

- BEM-adjacent naming: `.commandbar-palette`, `.commandbar-palette__input`, `.commandbar-result--active`.
- All selectors prefixed with `.commandbar-` — no generic selectors that might conflict with WordPress admin or other plugins.
- CSS custom properties for all colors and dimensions — no hardcoded values.
- `rem` units for font sizes; `px` for borders, box-shadow, and fixed dimensions.
- No `!important` except where overriding WordPress admin styles that cannot be avoided.

---

## How to Add a New Command

### Step 1: Add to `commandbar-data.js`

Open `admin/js/commandbar-data.js` and add a new object to the `COMMANDBAR_COMMANDS` array:

```js
{
    id:          'export-users',          // unique, kebab-case, stable
    title:       'Export Users',
    description: 'Download a CSV of all users',
    keywords:    [ 'export users', 'download users', 'csv', 'users export' ],
    icon:        'admin-users',           // Dashicon name, no 'dashicons-' prefix
    category:    'Users',
    action:      {
        type: 'navigate',
        url:  '/wp-admin/users.php?action=export', // example URL
    },
    capability:  'list_users',
    shortcut:    null,
},
```

### Step 2: Add to `class-commandbar-commands.php`

Open `includes/class-commandbar-commands.php` and add the same command to the PHP `get_commands()` method. This is the server-side source of truth for capability filtering:

```php
[
    'id'         => 'export-users',
    'title'      => __( 'Export Users', 'commandbar' ),
    'capability' => 'list_users',
],
```

### Step 3: Update the POT file

If you added any new translatable strings, regenerate the `.pot` file:

```bash
wp i18n make-pot . languages/commandbar.pot
```

### Step 4: Add tests

Add a test case verifying the command appears for a user with the required capability and does not appear for a user without it.

### Step 5: Update `docs/commands.md`

Add the new command to the appropriate section in [commands.md](commands.md) with all fields documented.

---

## How to Add a New REST Endpoint

### Step 1: Register the endpoint in `class-commandbar-rest-api.php`

Inside the `register_routes()` method:

```php
register_rest_route(
    'commandbar/v1',
    '/my-endpoint',
    [
        [
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [ $this, 'handle_my_endpoint' ],
            'permission_callback' => [ $this, 'check_my_endpoint_permission' ],
            'args'                => [
                'q' => [
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => function( $param ) {
                        return is_string( $param ) && strlen( $param ) > 0;
                    },
                ],
            ],
        ],
    ]
);
```

### Step 2: Add the permission callback

```php
/**
 * Permission callback for the my-endpoint route.
 *
 * @return bool|\WP_Error
 */
public function check_my_endpoint_permission(): bool|\WP_Error {
    if ( ! is_user_logged_in() ) {
        return new \WP_Error(
            'rest_forbidden',
            __( 'You must be logged in.', 'commandbar' ),
            [ 'status' => 401 ]
        );
    }

    if ( ! current_user_can( 'required_capability' ) ) {
        return new \WP_Error(
            'rest_forbidden',
            __( 'You do not have permission.', 'commandbar' ),
            [ 'status' => 403 ]
        );
    }

    return true;
}
```

### Step 3: Add the handler

```php
/**
 * Handles GET /commandbar/v1/my-endpoint requests.
 *
 * @param \WP_REST_Request $request Full details about the request.
 * @return \WP_REST_Response|\WP_Error Response object.
 */
public function handle_my_endpoint( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
    $query = $request->get_param( 'q' ); // Already sanitized by sanitize_callback

    // ... process ...

    return rest_ensure_response( [
        'success' => true,
        'data'    => $results,
        'message' => '',
    ] );
}
```

### Rules for all REST endpoints

- `permission_callback` must never return `true` without a capability check.
- Always use `sanitize_callback` on all `args` — never call `sanitize_*` inside the handler.
- Always return the standard envelope: `{ success, data, message }`.
- Always return proper HTTP status codes via `WP_Error` with a `status` data key.
- Cache results via `set_transient()` if the result is expensive to compute.

---

## How to Add a New Setting

### Step 1: Add to `class-commandbar-settings.php`

In the `register_settings()` method:

```php
register_setting(
    'commandbar_settings',
    'commandbar_my_new_setting',
    [
        'type'              => 'boolean',
        'default'           => true,
        'sanitize_callback' => [ $this, 'sanitize_boolean' ],
    ]
);
```

Add the default value to `get_defaults()`:

```php
'commandbar_my_new_setting' => true,
```

### Step 2: Add to `class-commandbar-activator.php`

Ensure the default is set on activation:

```php
add_option( 'commandbar_my_new_setting', true );
```

### Step 3: Add to `uninstall.php`

Add a `delete_option( 'commandbar_my_new_setting' );` call.

### Step 4: Render in `class-commandbar-admin.php`

Add the field HTML to the settings page render method.

### Step 5: Pass to JavaScript

If the setting affects JavaScript behaviour, add it to the `$settings` array in `get_localized_settings()` inside `class-commandbar-admin.php`.

---

## Commit Message Format

CommandBar uses [Conventional Commits](https://www.conventionalcommits.org/).

### Format

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

| Type | When to use |
|---|---|
| `feat` | A new feature or new command |
| `fix` | A bug fix |
| `perf` | A performance improvement |
| `refactor` | Code change that neither fixes a bug nor adds a feature |
| `style` | Formatting, whitespace, missing semicolons — no logic change |
| `docs` | Documentation changes only |
| `test` | Adding or modifying tests |
| `chore` | Build process, dependency updates, tooling |
| `a11y` | Accessibility improvement |
| `i18n` | Internationalisation changes |
| `security` | Security fix |

### Scopes

| Scope | What it covers |
|---|---|
| `palette` | Palette UI (HTML structure, opening/closing) |
| `search` | Search logic (fuzzy match, REST fetch) |
| `keyboard` | Keyboard handling |
| `actions` | Action execution, toast notifications |
| `data` | Static command definitions |
| `rest` | REST API endpoints |
| `settings` | Settings page and options |
| `css` | Stylesheet |
| `php` | PHP classes (use more specific scope when possible) |
| `docs` | Documentation |
| `deps` | Dependency changes |

### Examples

```
feat(data): add Export Users command

fix(keyboard): prevent shortcut firing inside block editor inputs

perf(search): cache in-memory results by query key

a11y(palette): add aria-activedescendant updates on result navigation

docs(extending): add complete working example for WooCommerce integration

security(rest): add rate limiting to search endpoint
```

### Breaking changes

If a commit introduces a breaking change (e.g., changes the `commandbar_commands` filter signature):

```
feat(php)!: change commandbar_commands filter to receive WP_User as second argument

BREAKING CHANGE: The commandbar_commands filter now receives the current WP_User
object as a second argument. Update any callbacks that rely on the single-argument
signature.
```

---

## Pull Request Process

### Before opening a PR

- [ ] Run PHPCS: `./vendor/bin/phpcs --standard=WordPress commandbar.php includes/`
- [ ] Test in Chrome, Firefox, and Safari
- [ ] Test keyboard navigation end-to-end
- [ ] Test with a screen reader (NVDA + Firefox, or VoiceOver + Safari)
- [ ] Test with at least two different WordPress admin color schemes
- [ ] Test with a non-Administrator user role (Editor or Author)
- [ ] Verify no JavaScript errors in the browser console
- [ ] Verify no PHP errors or warnings with `WP_DEBUG=true`
- [ ] Update `docs/commands.md` if you added or changed a command
- [ ] Update `CHANGELOG.md` with your change under `Unreleased`

### PR title

Use the same Conventional Commits format as your commit message:

```
fix(keyboard): prevent shortcut firing inside block editor inputs
```

### PR description template

```markdown
## What does this PR do?

[Clear description of the change]

## Why is this change needed?

[Context — what problem does it solve?]

## Testing instructions

1. Step one
2. Step two
3. Expected result

## Screenshots (if UI change)

[Before / After screenshots]

## Checklist

- [ ] PHPCS passes
- [ ] Tested in Chrome, Firefox, Safari
- [ ] Keyboard navigation tested
- [ ] No JS errors in console
- [ ] No PHP errors/warnings with WP_DEBUG=true
- [ ] CHANGELOG.md updated
- [ ] Docs updated (if applicable)
```

### Review process

1. A maintainer reviews the PR within a reasonable time.
2. Feedback is given as inline comments on the diff.
3. You address feedback and push new commits (do not squash until ready to merge).
4. Once approved, the maintainer merges using **Squash and Merge** to keep the commit history clean.

---

## Testing Checklist

Use this checklist before every release and before merging any significant PR.

### Functional

- [ ] CMD+K opens palette on macOS
- [ ] CTRL+K opens palette on Windows/Linux
- [ ] Floating trigger button opens palette on click
- [ ] Typing filters results immediately
- [ ] Arrow keys navigate results
- [ ] Enter executes selected result
- [ ] Escape closes palette
- [ ] Click outside closes palette
- [ ] Recent commands shown when palette opens with empty input
- [ ] Dynamic search returns results for posts
- [ ] Dynamic search returns results for pages
- [ ] `@` prefix searches users
- [ ] `>` prefix searches settings
- [ ] `+` prefix searches plugins
- [ ] Flush Rewrite Rules action shows success toast
- [ ] Logout command shows confirmation before executing
- [ ] Dark Mode toggle works and persists on page reload
- [ ] Settings page saves correctly
- [ ] Settings page shows success notice on save

### Security

- [ ] REST endpoints return 401 for unauthenticated requests
- [ ] REST endpoints return 403 for users without required capability
- [ ] Settings page inaccessible to non-administrators
- [ ] No PHP errors or warnings with `WP_DEBUG=true` and `WP_DEBUG_LOG=true`

### Accessibility

- [ ] All functionality reachable via keyboard only
- [ ] Screen reader announces results on search
- [ ] Screen reader announces action completion
- [ ] Focus returns to correct element on close
- [ ] Animations disabled with `prefers-reduced-motion`
- [ ] Focus ring visible on all interactive elements

### Compatibility

- [ ] WordPress 6.3 (minimum version)
- [ ] WordPress 6.8 (tested up to)
- [ ] PHP 8.0
- [ ] PHP 8.3
- [ ] Chrome latest
- [ ] Firefox latest
- [ ] Safari latest
- [ ] Edge latest
- [ ] All WordPress admin color schemes (Default, Light, Blue, Coffee, Ectoplasm, Midnight, Ocean, Sunrise)
- [ ] RTL layout (`<html dir="rtl">`)
- [ ] Multisite (network-activated)
- [ ] Non-Administrator role (Editor)

---

## Release Process

1. Update `CHANGELOG.md` — move `Unreleased` items to a new version section with the date.
2. Update `Version` in the plugin header (`commandbar.php`).
3. Update `COMMANDBAR_VERSION` constant in `commandbar.php`.
4. Update `Stable tag` in `readme.txt`.
5. Update `Tested up to` in `readme.txt` and plugin header if needed.
6. Commit: `chore(release): bump version to 1.x.x`.
7. Tag: `git tag -a v1.x.x -m "Version 1.x.x"`.
8. Push tag: `git push origin v1.x.x`.
9. Deploy to WordPress.org SVN (`trunk` and `tags/1.x.x`).

---

*For the plugin architecture, see [architecture.md](architecture.md).*
*For the command reference, see [commands.md](commands.md).*
*For the WordPress.org submission checklist, see [wordpress-org-submission.md](wordpress-org-submission.md).*
