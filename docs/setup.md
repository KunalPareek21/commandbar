# CommandBar – Smart Admin Navigation — Setup & Installation Guide

> **Your WordPress admin. At the speed of thought.**

CommandBar – Smart Admin Navigation is a zero-configuration keyboard-first command palette for the WordPress admin. This guide covers every way to install it and get running in under two minutes.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation from WordPress.org](#installation-from-wordpressorg)
- [Manual Installation](#manual-installation)
- [First Use Walkthrough](#first-use-walkthrough)
- [Keyboard Shortcut Reference](#keyboard-shortcut-reference)
- [Settings Reference](#settings-reference)
- [Multisite Setup](#multisite-setup)
- [Uninstalling](#uninstalling)

---

## Requirements

| Requirement | Minimum |
|---|---|
| WordPress | 6.3 |
| PHP | 8.0 |
| Browser | Chrome 90+, Firefox 90+, Safari 15+, Edge 90+ |

CommandBar – Smart Admin Navigation has **zero runtime dependencies**. No external services, no CDN, no npm packages — just plain vanilla JavaScript and WordPress APIs.

---

## Installation from WordPress.org

This is the recommended installation method for most users.

### Via the WordPress Admin

1. Log in to your WordPress admin dashboard.
2. Navigate to **Plugins → Add New Plugin**.
3. In the search box, type `CommandBar – Smart Admin Navigation`.
4. Find the plugin by **Kunal Pareek** and click **Install Now**.
5. Once installed, click **Activate**.
6. Press **CMD+K** (Mac) or **CTRL+K** (Windows/Linux) anywhere in wp-admin.

That's it. No configuration required.

### Via WP-CLI

```bash
wp plugin install commandbar --activate
```

---

## Manual Installation

Use this method when installing on a server without internet access, or when installing a specific version from GitHub.

### From a ZIP File

1. Download the latest release ZIP from:
   - [WordPress.org plugin page](https://wordpress.org/plugins/commandbar/)
   - [GitHub Releases](https://github.com/KunalPareek21/commandbar/releases)
2. Log in to your WordPress admin dashboard.
3. Navigate to **Plugins → Add New Plugin → Upload Plugin**.
4. Click **Choose File**, select the downloaded ZIP, and click **Install Now**.
5. Click **Activate Plugin**.

### Via FTP / SFTP

1. Download and extract the plugin ZIP.
2. Upload the extracted `commandbar/` folder to `/wp-content/plugins/` on your server.
3. Log in to WordPress admin and navigate to **Plugins**.
4. Find **CommandBar – Smart Admin Navigation** in the list and click **Activate**.

### Via WP-CLI (from ZIP)

```bash
wp plugin install /path/to/commandbar.zip --activate
```

---

## First Use Walkthrough

### Step 1: Open the Command Palette

Press **CMD+K** on a Mac or **CTRL+K** on Windows/Linux from anywhere inside the WordPress admin. The command palette will appear instantly in the center of your screen.

If you prefer using a mouse, a small floating trigger button appears in the bottom-right corner of every admin page. Click it to open the palette.

### Step 2: Explore Default Commands

When you open the palette without typing anything, you will see your **Recently Used** commands (empty on first open) and a set of default commands grouped by category:

- **Content** — New Post, New Page, All Posts, All Pages, Media Library
- **Appearance** — Themes, Customize, Widgets, Menus
- **Plugins** — All Plugins, Add Plugin
- **Settings** — General, Writing, Reading, Discussion, Permalinks
- **Users** — All Users, Add User, Your Profile
- **Tools** — Import, Export, Site Health

### Step 3: Search for Something

Start typing. Results filter instantly as you type. No network request is needed for static commands — results appear within milliseconds.

Try typing:
- `new post` → jumps to the new post editor
- `permalinks` → opens Permalink Settings
- `theme` → opens the Themes screen

### Step 4: Use Dynamic Search

CommandBar – Smart Admin Navigation also searches your actual content via the REST API:

- Type any word → searches posts and pages by title
- Type `@username` → searches users (requires `list_users` capability)
- Type `>setting` → searches WordPress settings pages
- Type `+plugin` → searches installed plugins (requires `activate_plugins` capability)

### Step 5: Navigate Results

| Key | Action |
|---|---|
| `↓` Arrow Down | Move to next result |
| `↑` Arrow Up | Move to previous result |
| `Enter` | Execute highlighted result |
| `Escape` | Close palette |

### Step 6: Check the Settings (Optional)

Navigate to **Settings → CommandBar** if you want to adjust:
- Whether the floating trigger button is shown
- Its position (bottom-right or bottom-left)
- How many recent commands to display
- Which user roles have access to the palette

The plugin works perfectly well with all defaults. Settings are entirely optional.

---

## Keyboard Shortcut Reference

### Global Shortcuts

| Shortcut | Platform | Action |
|---|---|---|
| `CMD+K` | macOS | Open / Close CommandBar |
| `CTRL+K` | Windows / Linux | Open / Close CommandBar |
| `Escape` | All | Close CommandBar |

**Note:** The shortcut is intentionally not triggered when your cursor is inside a text input, textarea, or `contenteditable` element — unless the palette is already open. This prevents conflicts with WordPress editor shortcuts.

### Palette Navigation

| Key | Action |
|---|---|
| `↓` or `Tab` | Move to next result |
| `↑` or `Shift+Tab` | Move to previous result |
| `Home` | Jump to first result |
| `End` | Jump to last result |
| `Enter` | Execute highlighted result |
| `Escape` | Close palette |
| `CMD+A` / `CTRL+A` | Select all text in search input |
| `Backspace` | Delete last character in search input |

### Search Prefixes

| Prefix | What it searches |
|---|---|
| *(no prefix)* | Static commands + posts + pages |
| `@` | Users (requires `list_users` capability) |
| `>` | Settings pages and options |
| `+` | Installed plugins (requires `activate_plugins` capability) |

---

## Settings Reference

Settings are located at **Settings → CommandBar**.

### General

| Setting | Default | Description |
|---|---|---|
| Enable CommandBar | On | Master toggle for the entire plugin |
| Show floating trigger button | On | Show the floating CMD+K button in the corner |
| Floating button position | Bottom Right | Position of the floating trigger button |
| Show recent commands | On | Show recently used commands when palette opens |
| Number of recent commands | 5 | How many recent commands to display (3–10) |

### Appearance

| Setting | Default | Description |
|---|---|---|
| Palette theme | Auto | Auto follows the WordPress admin color scheme; Light or Dark forces a specific theme |
| Show command icons | On | Show Dashicons next to each result |
| Show keyboard shortcut hints | On | Show shortcut badges on applicable results |

### Advanced

| Setting | Default | Description |
|---|---|---|
| Enable for these roles | Administrator, Editor, Author | Which user roles can use CommandBar |
| Reset recent commands | Button | Clears the current user's recent commands from localStorage |

---

## Multisite Setup

CommandBar – Smart Admin Navigation works on WordPress Multisite without any special configuration.

- On a **network-activated** installation, the plugin is active on all sites in the network.
- Each site has its own settings under **Settings → CommandBar**.
- REST API searches are scoped to the current site — CommandBar – Smart Admin Navigation does not search across the network.
- Network admins see CommandBar – Smart Admin Navigation in the **Network Admin** dashboard as well.

To network-activate:
1. Navigate to **Network Admin → Plugins**.
2. Find **CommandBar – Smart Admin Navigation** and click **Network Activate**.

---

## Uninstalling

### Deactivation (keeps data)

Deactivating the plugin via **Plugins → Deactivate** preserves all settings. Reactivating restores the plugin exactly as it was.

### Full Uninstall (removes all data)

1. Navigate to **Plugins**.
2. Deactivate CommandBar – Smart Admin Navigation.
3. Click **Delete**.

WordPress will run `uninstall.php`, which:
- Deletes all `commandbar_*` options from `wp_options`
- Removes any CommandBar – Smart Admin Navigation transients from the database

**Note:** `localStorage` data (recent commands, dark mode preference) is stored in the browser and cannot be cleared server-side. It will be cleaned up automatically when the user's browser storage is cleared or when the browser's localStorage for that domain expires.

---

## Troubleshooting

### The keyboard shortcut doesn't open the palette

- Make sure the plugin is **activated**.
- Ensure your user role has access (check **Settings → CommandBar → Advanced → Enable for these roles**).
- Check whether another plugin is capturing `CMD+K` / `CTRL+K` before CommandBar – Smart Admin Navigation can.
- Try opening from the floating trigger button to confirm the plugin is working.

### Search results are not appearing

- The dynamic search uses the WordPress REST API. Ensure the REST API is not disabled on your site.
- Check browser console for any `401 Unauthorized` or `403 Forbidden` errors.
- Ensure your user has the required capabilities (`edit_posts`, `list_users`, etc.).

### The floating button is not visible

- Check **Settings → CommandBar → Show floating trigger button** — it may be turned off.
- Check whether you dismissed it via the × button (it is hidden for the current browser session via `localStorage`).

### Plugin Check or security scanner flags an issue

- CommandBar – Smart Admin Navigation passes WordPress Plugin Check with zero blocking issues.
- If a security scanner flags a false positive, refer to [docs/wordpress-org-submission.md](wordpress-org-submission.md) for details.

---

*For architecture details, see [architecture.md](architecture.md).*
*For extending the plugin, see [extending.md](extending.md).*
*For accessibility details, see [accessibility.md](accessibility.md).*
