# CommandBar – Smart Admin Navigation — WordPress.org Submission Guide

This document covers everything needed to submit CommandBar – Smart Admin Navigation to the WordPress.org plugin repository: Plugin Check results, GPL compliance, the submission checklist, and notes for the Plugin Review Team.

---

## Table of Contents

- [Plugin Check Results](#plugin-check-results)
- [GPL Compliance](#gpl-compliance)
- [Submission Checklist](#submission-checklist)
- [SVN Deployment](#svn-deployment)
- [Plugin Review Team Notes](#plugin-review-team-notes)
- [Common Rejection Reasons and How CommandBar – Smart Admin Navigation Avoids Them](#common-rejection-reasons-and-how-commandbar--smart-admin-navigation-avoids-them)
- [Post-Approval Maintenance](#post-approval-maintenance)

---

## Plugin Check Results

[Plugin Check](https://wordpress.org/plugins/plugin-check/) is the official automated tool for validating WordPress.org plugin submissions.

### How to run Plugin Check

**Via WP-CLI:**
```bash
wp plugin check commandbar
```

**Via the admin screen:**
1. Install and activate the Plugin Check plugin.
2. Go to **Tools → Plugin Check**.
3. Select **CommandBar – Smart Admin Navigation** from the dropdown.
4. Click **Check it!**.

### Expected results

CommandBar – Smart Admin Navigation is engineered to pass Plugin Check with **zero blocking issues** and **zero errors**.

| Check category | Expected result |
|---|---|
| Plugin header validity | PASS — all required headers present |
| Text domain | PASS — `commandbar` matches header and all `__()` calls |
| Internationalization | PASS — all user-facing strings use i18n functions |
| Direct database access | PASS — uses WordPress APIs only (`get_option`, `WP_Query`, etc.) |
| Filesystem API | PASS — no direct filesystem operations |
| File inclusion | PASS — no dynamic file includes |
| ABSPATH check | PASS — every PHP file starts with `if ( ! defined( 'ABSPATH' ) ) exit;` |
| Escaping | PASS — all output escaped with appropriate functions |
| Sanitization | PASS — all input sanitized at entry points |
| Nonces | PASS — nonce verification on all state-changing operations |
| Capability checks | PASS — `current_user_can()` checked before all privileged operations |
| Plugin URI | PASS — valid URI in header |
| Author URI | PASS — valid URI in header |
| Stable tag | PASS — matches plugin header version |
| Readme validity | PASS — all required sections present |
| GPL license | PASS — GPL v2 or later declared |
| External requests | PASS — no external HTTP requests |
| Enqueue scripts | PASS — all scripts/styles enqueued via proper hooks |
| Deprecated functions | PASS — no deprecated WordPress functions used |
| Short tags | PASS — no PHP short tags |
| Closing PHP tags | PASS — no closing `?>` tags in PHP files |

### If Plugin Check reports a warning

Plugin Check sometimes reports **informational warnings** that are not blocking. These are expected and acceptable:

- **"Consider using wp_safe_redirect() instead of wp_redirect()"** — Not applicable to CommandBar – Smart Admin Navigation; no redirects used.
- **"Translation functions should not have dynamic strings"** — Not applicable; all strings are static literals.

If a new version of Plugin Check introduces a new check that CommandBar – Smart Admin Navigation does not yet pass, file an issue immediately and address it before the next release.

---

## GPL Compliance

### Plugin license

CommandBar – Smart Admin Navigation is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

The license is declared in:
- The plugin header: `License: GPL v2 or later` and `License URI: https://www.gnu.org/licenses/gpl-2.0.html`
- `readme.txt`: `License: GPLv2 or later`
- Every PHP file's docblock

### Dependency licenses

CommandBar – Smart Admin Navigation has **zero runtime dependencies**. This is intentional and simplifies GPL compliance entirely:

| Component | Source | License |
|---|---|---|
| PHP code | Written by author | GPL v2 or later |
| JavaScript | Written by author | GPL v2 or later |
| CSS | Written by author | GPL v2 or later |
| Dashicons | WordPress core | GPL v2 or later |

No third-party libraries, no npm packages, no CDN resources. Every line of code in the plugin is either original or derived from WordPress core (which is GPL v2 or later).

### Confirming GPL compatibility

The WordPress.org Plugin Review Team will verify:
1. The license header is present in the main plugin file. ✓
2. The license file or URI is declared. ✓
3. No code with incompatible licenses is included. ✓ (no dependencies)
4. The `readme.txt` declares the license. ✓

---

## Submission Checklist

Work through this checklist completely before submitting to WordPress.org.

### Plugin files

- [ ] `commandbar.php` exists at the plugin root with a valid plugin header
- [ ] `readme.txt` exists and is formatted correctly
- [ ] `uninstall.php` exists and checks `WP_UNINSTALL_PLUGIN`
- [ ] `languages/commandbar.pot` exists
- [ ] No build artifacts committed (no `node_modules/`, no `vendor/` unless needed at runtime)
- [ ] No development-only files committed (no `phpunit.xml`, no `.env`, no test fixtures)

### Plugin header (`commandbar.php`)

- [ ] `Plugin Name` present
- [ ] `Plugin URI` is a valid URL
- [ ] `Description` is under 150 characters
- [ ] `Version` matches `Stable tag` in `readme.txt`
- [ ] `Author` present
- [ ] `Author URI` is a valid URL
- [ ] `License` is `GPL v2 or later`
- [ ] `License URI` is the GPL URL
- [ ] `Text Domain` matches the plugin slug (`commandbar`)
- [ ] `Domain Path` is `/languages`
- [ ] `Requires at least` is set (`6.3`)
- [ ] `Tested up to` is the current WordPress version
- [ ] `Requires PHP` is set (`8.0`)

### `readme.txt`

- [ ] `=== Plugin Name ===` matches plugin header name
- [ ] `Contributors` lists the WordPress.org username(s)
- [ ] `Tags` (maximum 5 relevant tags)
- [ ] `Requires at least` matches plugin header
- [ ] `Tested up to` is the current WordPress version
- [ ] `Stable tag` matches plugin header `Version`
- [ ] `License` is `GPLv2 or later`
- [ ] `License URI` is the GPL URL
- [ ] Short description (one sentence, under 150 characters)
- [ ] `== Description ==` section with full feature list
- [ ] `== Installation ==` section with step-by-step instructions
- [ ] `== Frequently Asked Questions ==` section with at least 5 Q&As
- [ ] `== Screenshots ==` section (even if no screenshots yet, list intended captions)
- [ ] `== Changelog ==` section starting with current version
- [ ] `== Upgrade Notice ==` section

### Security

- [ ] Every PHP file starts with `if ( ! defined( 'ABSPATH' ) ) exit;`
- [ ] All input sanitized: `sanitize_text_field()`, `sanitize_key()`, `absint()`, etc.
- [ ] All output escaped: `esc_html()`, `esc_url()`, `esc_attr()`, `wp_json_encode()`
- [ ] Nonce verified on all AJAX and REST state-changing operations
- [ ] `current_user_can()` checked before all privileged data access
- [ ] No `eval()` usage
- [ ] No `base64_decode()` on dynamic input
- [ ] No external HTTP requests
- [ ] No hardcoded credentials or API keys

### Code quality

- [ ] WordPress Coding Standards compliant (PHPCS passes)
- [ ] No PHP syntax errors (run `php -l *.php includes/*.php`)
- [ ] No deprecated WordPress functions
- [ ] No closing PHP tags (`?>`) in PHP files
- [ ] No PHP short tags (`<?`)
- [ ] Docblocks on all classes and public methods
- [ ] Text domain consistent throughout: `commandbar`
- [ ] Plugin constants prefixed: `COMMANDBAR_*`
- [ ] Functions prefixed: `commandbar_*`
- [ ] Classes prefixed: `CommandBar_*`
- [ ] No conflicts with WordPress core functions (no function name collisions)

### Assets

- [ ] No external CDN resources
- [ ] All scripts enqueued via `wp_enqueue_script()`
- [ ] All styles enqueued via `wp_enqueue_style()`
- [ ] Assets load only in wp-admin (not on frontend)
- [ ] Version string used in `wp_enqueue_*` calls (for cache busting)

### Data handling

- [ ] `uninstall.php` deletes all plugin data from `wp_options`
- [ ] Deactivation does not delete data
- [ ] No custom database tables (none needed)
- [ ] No data stored beyond what is documented

### Internationalization

- [ ] All user-facing strings wrapped in i18n functions
- [ ] `__()`, `_e()`, `esc_html__()`, `esc_html_e()` used appropriately
- [ ] Text domain loaded on `plugins_loaded` hook
- [ ] `.pot` file up to date
- [ ] No hardcoded English strings outside of i18n function calls

---

## SVN Deployment

WordPress.org uses Subversion (SVN) for plugin hosting. Git is used for development; SVN is for distribution.

### Initial setup

```bash
# Check out your plugin's SVN repository (after approval)
svn co https://plugins.svn.wordpress.org/commandbar /path/to/svn/commandbar
```

### Repository structure

```
commandbar/
├── trunk/          # Current development/latest stable
├── tags/           # One folder per released version
│   └── 1.0.0/
└── assets/         # WordPress.org listing assets (banner, icon, screenshots)
    ├── banner-772x250.png
    ├── banner-1544x500.png
    ├── icon-128x128.png
    ├── icon-256x256.png
    ├── screenshot-1.png
    └── screenshot-2.png
```

### Deploying a new version

```bash
# Copy new plugin files to trunk
cp -r /path/to/git/commandbar/* /path/to/svn/commandbar/trunk/

# Create a tag for the version
svn cp trunk tags/1.0.0

# Add any new files
svn add trunk/* --force

# Commit
svn ci -m "Release CommandBar 1.0.0"
```

### Assets directory

The `assets/` directory in SVN (not in the plugin ZIP) contains the WordPress.org listing graphics:

| File | Dimensions | Format |
|---|---|---|
| `banner-772x250.png` | 772×250px | PNG |
| `banner-1544x500.png` | 1544×500px | PNG (retina) |
| `icon-128x128.png` | 128×128px | PNG |
| `icon-256x256.png` | 256×256px | PNG (retina) |
| `screenshot-1.png` | Any (max 1280px wide) | PNG |
| `screenshot-2.png` | Any (max 1280px wide) | PNG |

Screenshots in `readme.txt` reference these files:

```
== Screenshots ==

1. The CommandBar – Smart Admin Navigation palette open with a search query for "post".
2. The CommandBar – Smart Admin Navigation settings page at Settings → CommandBar.
```

The number in `readme.txt` corresponds to `screenshot-{n}.png` in the `assets/` directory.

---

## Plugin Review Team Notes

This section contains notes specifically for members of the WordPress.org Plugin Review Team.

### About this plugin

CommandBar – Smart Admin Navigation is a keyboard-first command palette for the WordPress admin. It adds no frontend assets, requires no configuration, and has no external dependencies.

### Security model

**REST API authentication:** All REST endpoints use `permission_callback` to verify user authentication and capability before any data is returned. The `X-WP-Nonce` header with a `wp_rest` nonce is required on all POST requests.

**Capability checks:** Every search type has a minimum capability requirement:
- Post search: `edit_posts`
- Page search: `edit_pages`
- User search: `list_users`
- Plugin search: `activate_plugins`
- Server-side actions: `manage_options`

These checks happen in `permission_callback` — before the route handler runs — so WordPress returns a `403` before any data processing occurs.

**Input sanitization:** All `WP_REST_Request` parameters use `sanitize_callback` in the route registration `args` array. The handler functions receive pre-sanitized values.

**Output escaping:** All PHP output uses the appropriate escaping function. REST API responses use `wp_json_encode()` via `rest_ensure_response()`.

### Why `wp_localize_script` instead of `wp_set_script_translations`

`wp_set_script_translations` requires a separate HTTP request to load a `.json` translation file on every admin page load. For a plugin this size (under 30KB of JavaScript), this adds unnecessary HTTP overhead. All translatable strings used in JavaScript are inlined via `wp_localize_script` as part of the `commandbarData` object. The strings are defined in PHP and included in the `.pot` file, so they are fully translatable.

### Why inline the palette HTML instead of creating it with JavaScript

The palette HTML is created via JavaScript on `DOMContentLoaded` (not inlined into PHP). This keeps the PHP layer clean and ensures the palette HTML is only present in the DOM when the plugin's JavaScript has loaded successfully.

### About the floating trigger button

The floating button is dismissible per session via `localStorage`. It does not use a WordPress option or make any server request when dismissed — it simply sets a `localStorage` flag that prevents it from appearing for the rest of the browser session.

### Data stored

**Server-side (`wp_options`):**
- Plugin settings (9 options with `commandbar_` prefix)
- Plugin version (`commandbar_version`)
- Search result transients (auto-expire, 60-second TTL)

**Client-side (`localStorage`):**
- Recent command IDs (array of strings, maximum 10 entries)
- Dark mode preference (boolean)
- Floating button dismissed state (boolean, session only)

No personally identifiable information is stored anywhere. Recent commands are stored as command IDs (e.g., `new-post`, `edit.php`) not as user-entered text.

---

## Common Rejection Reasons and How CommandBar – Smart Admin Navigation Avoids Them

The Plugin Review Team publishes common reasons for plugin rejection. Here is how CommandBar – Smart Admin Navigation addresses each one.

### "Plugin is using a generic function/class/define/namespace/option name"

All functions, classes, constants, and options use the `commandbar_` / `CommandBar_` / `COMMANDBAR_` prefix. There are no generic names.

### "Plugin is not compatible with the WordPress.org directory guidelines"

CommandBar – Smart Admin Navigation follows all directory guidelines:
- No upsells or freemium features
- No tracking or analytics
- No external service calls
- No hardcoded links to external sites
- License is GPL v2 or later

### "Plugin is using an outdated or insecure function"

No deprecated WordPress functions are used. All PHP is 8.0+ compatible. The plugin uses modern WordPress APIs (REST API, Settings API, Transients API).

### "Plugin is making external HTTP requests"

CommandBar – Smart Admin Navigation makes **zero** external HTTP requests. All data comes from the local WordPress installation.

### "Plugin is not properly sanitizing/escaping"

Every input is sanitized at the point of entry. Every output is escaped with the appropriate function. This is verified with PHPCS on every commit.

### "Plugin does not include a license"

License is declared in:
- The plugin header (`License: GPL v2 or later`)
- `readme.txt` (`License: GPLv2 or later`)

### "Plugin has JavaScript errors"

The JavaScript is tested in Chrome, Firefox, and Safari before every release with the browser console open. No `console.error`, `console.warn`, or uncaught exceptions are present in production.

### "Plugin is not i18n compatible"

All user-facing strings use i18n functions. The text domain is loaded on `plugins_loaded`. A `.pot` file is included in the `languages/` directory.

### "Stable tag is not set correctly"

`Stable tag` in `readme.txt` is always updated to match the `Version` in the plugin header before every release. They are never out of sync.

---

## Post-Approval Maintenance

After the plugin is approved and live on WordPress.org:

### Keeping "Tested up to" current

When a new WordPress version releases:
1. Test CommandBar – Smart Admin Navigation against the new version in a local environment.
2. If all tests pass, update `Tested up to` in both `readme.txt` and the plugin header.
3. Commit to SVN `trunk` (no version bump needed for `Tested up to` updates).

### Responding to support forum questions

Monitor the [WordPress.org support forum](https://wordpress.org/support/plugin/commandbar/) for questions and issues. Respond within a reasonable time. Mark resolved topics as resolved.

### Security vulnerability process

If a security vulnerability is discovered:
1. Do not disclose publicly until a fix is ready.
2. Prepare a fix and test thoroughly.
3. Release a new version with the fix.
4. Update `CHANGELOG.md` with a `Security` section entry.
5. Notify any reporter privately.
6. After the fix is live, post a clear security advisory in the support forum.

---

*For the full architecture, see [architecture.md](architecture.md).*
*For contributing, see [contribution-guide.md](contribution-guide.md).*
*For setup instructions, see [setup.md](setup.md).*
