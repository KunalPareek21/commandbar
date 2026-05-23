# Changelog

All notable changes to CommandBar are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2026-05-20

### Added

#### Core
- Command palette modal triggered by `CMD+K` (Mac) or `Ctrl+K` (Windows/Linux)
- Toggle shortcut closes the palette when it is already open
- Escape key closes the palette
- Click outside the palette card closes it
- Focus trap within the open palette
- Focus returns to the previously focused element on close
- Smooth open/close animations (150ms open, 100ms close) — disabled when `prefers-reduced-motion` is set
- Floating trigger button in the bottom-right corner (configurable, dismissible per session)

#### Commands (30+ built-in)
- **Content:** New Post, New Page, Upload Media, All Posts, All Pages, Media Library, Draft Posts, Scheduled Posts, Comments
- **Appearance:** Themes, Customize, Widgets, Menus, Site Editor (FSE)
- **Plugins:** All Plugins, Add Plugin, Plugin Updates
- **Settings:** General, Writing, Reading, Discussion, Permalinks, Privacy
- **Users:** All Users, Add New User, Your Profile
- **Tools:** Import, Export, Site Health, Site Health Info, Erase Personal Data
- **Actions:** Flush Rewrite Rules, Check for Updates, Log Out (with confirmation), Toggle Dark Mode, CommandBar Settings

#### Search
- Fuzzy relevance-scored matching against command titles, descriptions, and keyword aliases
- Live REST API search for posts, pages, users, and plugins (debounced 200ms)
- Search prefix `@` — search users only
- Search prefix `>` — search settings pages (static, zero network)
- Search prefix `+` — search installed plugins
- Results deduplicated when static and dynamic results overlap
- Results grouped by category (Content, Appearance, Plugins, Settings, Users, Tools, Actions, Posts, Pages, Users, Plugins)

#### REST API
- `GET /commandbar/v1/search` — authenticated, capability-checked, 60s transient cache per user
- `POST /commandbar/v1/actions` — authenticated, nonce-verified, capability-checked
- Standard JSON envelope: `{ success, data, message }`

#### Settings (`Settings → CommandBar`)
- Enable / disable CommandBar globally
- Show / hide floating trigger button
- Trigger button position (bottom-right / bottom-left)
- Show / hide recent commands
- Number of recent commands (3–10)
- Palette theme (Auto / Light / Dark)
- Show / hide command icons
- Show / hide keyboard shortcut hints
- Enable for specific user roles
- Keyboard shortcut reference table

#### Accessibility (WCAG 2.1 AA)
- `role="dialog"` + `aria-modal="true"` on the overlay
- `role="combobox"` on the input with `aria-expanded`, `aria-autocomplete`, `aria-controls`
- `role="listbox"` on the results container
- `role="option"` + `aria-selected` on each result
- Screen reader announcements for result count, loading state, and empty state
- Action toasts use `role="alert"` + `aria-live="assertive"`
- All animations disabled when `prefers-reduced-motion: reduce`
- RTL layout support via CSS logical properties

#### Developer experience
- `commandbar_commands` filter for adding/modifying/removing commands
- Command schema fully documented in `docs/extending.md`
- `window.CommandBar.open()`, `.close()`, `.toggle()` public JS API
- Each JS module exposes a clean namespace: `CommandBarData`, `CommandBarSearch`, `CommandBarActions`, `CommandBarKeyboard`

#### Documentation
- `docs/setup.md` — installation and first use
- `docs/architecture.md` — technical decisions and class responsibilities
- `docs/commands.md` — complete command reference with capabilities
- `docs/extending.md` — filter documentation with code examples
- `docs/accessibility.md` — WCAG 2.1 AA compliance details
- `docs/performance.md` — performance targets and caching strategy
- `docs/contribution-guide.md` — development setup and standards
- `docs/wordpress-org-submission.md` — Plugin Check notes and submission checklist

---

[1.0.0]: https://github.com/KunalPareek21/commandbar/releases/tag/v1.0.0
