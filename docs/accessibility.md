# CommandBar — Accessibility

CommandBar is built with accessibility as a first principle, not an afterthought. This document covers the full WCAG 2.1 AA compliance implementation, keyboard navigation reference, screen reader behaviour, ARIA architecture, and focus management details.

---

## Table of Contents

- [Compliance Target](#compliance-target)
- [Keyboard Navigation Reference](#keyboard-navigation-reference)
- [ARIA Implementation](#aria-implementation)
- [Focus Management](#focus-management)
- [Screen Reader Support](#screen-reader-support)
- [Color and Contrast](#color-and-contrast)
- [Motion and Animation](#motion-and-animation)
- [RTL Support](#rtl-support)
- [Testing Notes](#testing-notes)
- [Known Limitations](#known-limitations)

---

## Compliance Target

CommandBar targets **WCAG 2.1 Level AA** compliance across all supported browsers and screen reader / browser combinations.

Applicable success criteria:

| Criterion | Level | How CommandBar addresses it |
|---|---|---|
| 1.1.1 Non-text Content | A | All Dashicons have visually hidden text labels; decorative icons have `aria-hidden` |
| 1.3.1 Info and Relationships | A | Semantic roles (dialog, listbox, option, group) communicate structure |
| 1.3.2 Meaningful Sequence | A | DOM order matches visual order |
| 1.3.3 Sensory Characteristics | A | Active result indicated by both background color and a distinct border — not color alone |
| 1.4.1 Use of Color | A | Active state uses background + left border, not color alone |
| 1.4.3 Contrast (Minimum) | AA | All text passes 4.5:1 against its background |
| 1.4.4 Resize Text | AA | Palette scales correctly when browser text size is increased to 200% |
| 1.4.10 Reflow | AA | Palette reflows to single-column layout at 320px width |
| 1.4.11 Non-text Contrast | AA | Focus ring and icon colors meet 3:1 ratio |
| 1.4.12 Text Spacing | AA | Palette layout tolerates doubled line height and letter spacing |
| 1.4.13 Content on Hover/Focus | AA | Not applicable — no hover-only content |
| 2.1.1 Keyboard | A | All functionality reachable and operable via keyboard |
| 2.1.2 No Keyboard Trap | A | Focus trap is intentional while palette is open; Escape always exits |
| 2.1.3 Keyboard (No Exception) | AAA | All content reachable via keyboard |
| 2.4.3 Focus Order | A | Focus order is logical (input → results) |
| 2.4.7 Focus Visible | AA | Focus ring always visible; high-contrast mode respected |
| 3.1.1 Language of Page | A | Inherits page language; no language override |
| 3.2.1 On Focus | A | No context changes on focus |
| 3.2.2 On Input | A | Typing filters results; does not navigate away |
| 3.3.1 Error Identification | A | Action errors announced via `aria-live` region |
| 4.1.2 Name, Role, Value | A | All interactive elements have accessible names, roles, and states |
| 4.1.3 Status Messages | AA | Loading and result count messages use `aria-live` |

---

## Keyboard Navigation Reference

### Opening the Palette

| Key | Platform | Behaviour |
|---|---|---|
| `CMD+K` | macOS | Opens CommandBar from anywhere in wp-admin |
| `CTRL+K` | Windows / Linux | Opens CommandBar from anywhere in wp-admin |
| `CMD+K` / `CTRL+K` again | All | Closes CommandBar if already open |

**Scope exception:** The global shortcut does not fire when the user's cursor is inside:
- `<input>` elements
- `<textarea>` elements
- `<select>` elements
- Elements with `[contenteditable]`

This prevents conflicts with block editor shortcuts, TinyMCE shortcuts, and custom admin JavaScript. The exception does **not** apply when the palette itself is already open.

### Palette Navigation

| Key | Behaviour |
|---|---|
| `↓` (Arrow Down) | Move to next result. Wraps from last to first. |
| `↑` (Arrow Up) | Move to previous result. Wraps from first to last. |
| `Tab` | Same as Arrow Down (navigates within results list, does not leave palette) |
| `Shift+Tab` | Same as Arrow Up |
| `Home` | Jump to first result in list |
| `End` | Jump to last result in list |
| `Enter` | Execute the currently highlighted result |
| `Escape` | Close CommandBar and return focus to the element that was focused before opening |

### Text Input

| Key | Behaviour |
|---|---|
| Any printable character | Appended to search query; results update immediately |
| `Backspace` | Removes last character from query |
| `CMD+A` / `CTRL+A` | Selects all text in the search input |
| `CMD+Z` / `CTRL+Z` | Undoes last text change (native browser undo) |

### Confirmation Step (for destructive actions)

When a command requires confirmation (e.g., Logout), an inline confirmation appears within the palette:

| Key | Behaviour |
|---|---|
| `Enter` | Confirm and execute |
| `Escape` | Cancel confirmation, return to results |
| `↑` / `↓` | Navigate between "Confirm" and "Cancel" options |

---

## ARIA Implementation

### Palette Container

```html
<div
  id="commandbar-palette"
  role="dialog"
  aria-modal="true"
  aria-label="Command palette"
  aria-describedby="commandbar-instructions"
>
```

- `role="dialog"` — Identifies the element as a dialog to assistive technologies.
- `aria-modal="true"` — Tells screen readers that content outside the dialog is inert while the dialog is open. Note: this is supplemented by actual DOM inertness on older screen readers that ignore `aria-modal`.
- `aria-label="Command palette"` — Provides an accessible name without requiring a visible heading.
- `aria-describedby` — Points to a visually hidden description: *"Type to search commands. Use arrow keys to navigate, Enter to execute, Escape to close."*

### Overlay

```html
<div
  id="commandbar-overlay"
  aria-hidden="true"
>
```

The overlay scrim is `aria-hidden="true"` — it is purely decorative and carries no information.

### Search Input

```html
<input
  id="commandbar-input"
  type="text"
  role="combobox"
  aria-expanded="true"
  aria-haspopup="listbox"
  aria-autocomplete="list"
  aria-controls="commandbar-results"
  aria-activedescendant="commandbar-result-0"
  autocomplete="off"
  spellcheck="false"
  placeholder="Type a command or search..."
/>
```

- `role="combobox"` — Identifies this as a combobox widget (input that controls a listbox).
- `aria-expanded="true"` — Indicates the associated listbox is visible.
- `aria-autocomplete="list"` — Indicates that results are filtered by the current value.
- `aria-controls="commandbar-results"` — Associates the input with the results list by ID.
- `aria-activedescendant` — Updated dynamically to the ID of the currently highlighted result as the user navigates. This tells screen readers which result is "active" without moving actual DOM focus away from the input.

### Results List

```html
<ul
  id="commandbar-results"
  role="listbox"
  aria-label="Command results"
>
```

- `role="listbox"` — Identifies as a listbox that the combobox controls.

### Group Labels

```html
<li role="presentation">
  <span
    id="commandbar-group-content"
    role="group"
    aria-label="Content"
  >
    Content
  </span>
</li>
```

Group labels use `role="group"` with `aria-label` so screen readers can announce "Content, group" when entering a group of results.

### Individual Results

```html
<li
  id="commandbar-result-0"
  role="option"
  aria-selected="false"
  data-command-id="new-post"
>
  <span class="commandbar-result-icon" aria-hidden="true">
    <span class="dashicons dashicons-edit"></span>
  </span>
  <span class="commandbar-result-title">New Post</span>
  <span class="commandbar-result-description">Create a new blog post</span>
</li>
```

- `role="option"` — Each result is a selectable option within the listbox.
- `aria-selected="true/false"` — Updated when the user highlights a result. The highlighted result has `aria-selected="true"`; all others have `aria-selected="false"`.
- Icons have `aria-hidden="true"` — they are decorative; the result title provides the accessible name.

When `aria-activedescendant` on the input points to a result ID, screen readers announce that result's accessible name (title + description) automatically.

### Live Regions

```html
<!-- Status announcements: result count, loading state -->
<div
  id="commandbar-status"
  role="status"
  aria-live="polite"
  aria-atomic="true"
  class="commandbar-sr-only"
></div>

<!-- Action completion and error announcements -->
<div
  id="commandbar-assertive"
  role="alert"
  aria-live="assertive"
  aria-atomic="true"
  class="commandbar-sr-only"
></div>
```

**Polite region (`aria-live="polite"`):**
- Announces result count when the query changes: *"5 results found"*
- Announces loading state: *"Searching…"*
- Announces empty state: *"No results found"*
- Waits for the user to finish their current screen reader utterance before announcing

**Assertive region (`aria-live="assertive"`):**
- Announces action completion: *"Rewrite rules flushed successfully."*
- Announces action errors: *"Action failed. Please try again."*
- Interrupts the screen reader immediately — used only for time-sensitive confirmations

### Toast Notifications

```html
<div
  role="alert"
  aria-live="assertive"
  class="commandbar-toast commandbar-toast--success"
>
  Rewrite rules flushed successfully.
</div>
```

Toast notifications are inserted into a persistent container in `document.body`. The container has `aria-live="assertive"` so the message is announced immediately when inserted.

---

## Focus Management

### On Open

1. The palette is inserted into (or made visible in) the DOM.
2. `aria-hidden="true"` is removed from the palette.
3. Focus moves to the search input (`commandbar-input`).
4. The previously focused element is stored in a variable.
5. A focus trap is activated (see below).

The focus move happens synchronously on the same frame as the open animation to ensure no delay between the shortcut key and the input being ready to receive typing.

### While Open (Focus Trap)

CommandBar implements a focus trap following the [ARIA dialog pattern](https://www.w3.org/WAI/ARIA/apg/patterns/dialog-modal/).

**Implementation:**

```js
// On Tab keydown while palette is open
if ( event.key === 'Tab' ) {
    event.preventDefault();
    if ( event.shiftKey ) {
        selectPrevious(); // Navigate up in results
    } else {
        selectNext(); // Navigate down in results
    }
}
```

Tab and Shift+Tab are intercepted and converted to result navigation. They do not cycle through other focusable elements within the palette because the palette is designed to be operated entirely from the search input — result navigation happens via `aria-activedescendant` without physically moving DOM focus.

The only exception is the close button (×) on the floating trigger button, which is a real focusable element. When reached via Tab, pressing Enter or Space closes the button.

### On Close

1. The focus trap is deactivated.
2. `aria-hidden="true"` is added back to the palette.
3. Focus returns to the previously focused element (stored at open time).

This ensures that after dismissing the palette, the user's position in the page is preserved exactly. For screen reader users, this means they do not lose their place in the admin UI.

### Inert Background

When the palette is open, the rest of the admin UI is marked as inert:

```js
document.querySelectorAll( '#wpcontent, #adminmenu, #wpadminbar' ).forEach( el => {
    el.setAttribute( 'inert', '' );
    el.setAttribute( 'aria-hidden', 'true' );
} );
```

This prevents screen readers from reading background content while the dialog is open. On browsers that do not support the `inert` attribute, the `aria-hidden="true"` approach is the fallback.

---

## Screen Reader Support

### Tested Combinations

| Screen Reader | Browser | Platform | Status |
|---|---|---|---|
| VoiceOver | Safari | macOS 14+ | Supported |
| VoiceOver | Safari | iOS 17+ | Supported |
| NVDA | Firefox | Windows 11 | Supported |
| NVDA | Chrome | Windows 11 | Supported |
| JAWS | Chrome | Windows 11 | Supported |
| JAWS | Edge | Windows 11 | Supported |
| Narrator | Edge | Windows 11 | Supported |
| TalkBack | Chrome | Android 14+ | Supported |

### Announcements

| Event | Polite/Assertive | Example announcement |
|---|---|---|
| Palette opens | — | VoiceOver: *"Command palette. Type a command or search. Use arrow keys to navigate, Enter to execute, Escape to close."* |
| User types a query | Polite | *"5 results found"* |
| No results | Polite | *"No results found"* |
| Loading dynamic results | Polite | *"Searching…"* |
| Arrow key navigation | — | ARIA activedescendant change: *"New Post, Create a new blog post, option 1 of 5"* |
| Action completes | Assertive | *"Rewrite rules flushed successfully."* |
| Action fails | Assertive | *"Action failed. Please try again."* |
| Palette closes | — | Focus returns to previous element; screen reader reads that element |

### VoiceOver-Specific Notes

VoiceOver on macOS with Safari reads the `aria-activedescendant` change reliably when the combobox pattern is used correctly. The input maintains focus while VoiceOver announces the highlighted result.

**Quick Nav:** VoiceOver Quick Nav (`←→` arrow keys) is not affected by the focus trap because Quick Nav does not use Tab for navigation. The focus trap only intercepts Tab.

### NVDA-Specific Notes

NVDA with Firefox uses Browse Mode (virtual cursor) by default. When a `role="dialog"` element with `aria-modal="true"` is present, NVDA should automatically switch to Forms Mode, which is required for the combobox pattern to work correctly.

If a user reports NVDA not entering Forms Mode automatically: pressing `Enter` or `Insert+Space` manually switches modes.

---

## Color and Contrast

### Text Contrast Ratios

| Text element | Light mode | Dark mode | Ratio |
|---|---|---|---|
| Input text | `#0A0A0A` on `#FFFFFF` | `#F5F5F5` on `#1E1E1E` | 21:1 / 17:1 |
| Result title | `#0A0A0A` on `#FFFFFF` | `#F5F5F5` on `#1E1E1E` | 21:1 / 17:1 |
| Result description | `#6B7280` on `#FFFFFF` | `#9CA3AF` on `#1E1E1E` | 5.74:1 / 4.88:1 |
| Group label | `#6B7280` on `#FFFFFF` | `#9CA3AF` on `#1E1E1E` | 5.74:1 / 4.88:1 |
| Placeholder text | `#9CA3AF` on `#FFFFFF` | `#6B7280` on `#1E1E1E` | 3.0:1 (decorative) |

All informational text passes the 4.5:1 minimum ratio. Placeholder text is intentionally below 4.5:1 because it is decorative (the accessible name is provided by `aria-label` on the input) — this matches common practice in major design systems.

### Active State

The active (highlighted) result uses two visual indicators:
1. A subtle background color change (`rgba(0,0,0,0.05)` light, `rgba(255,255,255,0.08)` dark)
2. A 3px left border in the WordPress admin accent color

This satisfies **1.4.1 Use of Color** — the active state is never conveyed by color alone.

### Focus Ring

The search input and floating trigger button have a focus ring that is:
- 2px solid outline
- Uses the WordPress admin accent color with full opacity
- Offset by 2px from the element edge
- Visible in all WordPress admin color schemes
- Visible in Windows High Contrast Mode (uses `currentColor` in High Contrast)

---

## Motion and Animation

### `prefers-reduced-motion`

When the user's operating system is set to reduce motion, all CommandBar animations are disabled:

```css
@media (prefers-reduced-motion: reduce) {
    .commandbar-palette,
    .commandbar-overlay,
    .commandbar-toast {
        animation: none;
        transition: none;
    }
}
```

The palette appears and disappears instantly. All functionality remains identical — only the animations are removed.

### Animation durations

| Animation | Duration | Easing |
|---|---|---|
| Palette open (fade + scale) | 150ms | ease-out |
| Palette close (fade + scale) | 100ms | ease-in |
| Result highlight | 80ms | linear |
| Toast in | 150ms | ease-out |
| Toast out | 100ms | ease-in |

Durations are deliberately short. Long animations interfere with keyboard-first workflows — users who press Enter immediately after CMD+K expect to be on a new page within 200ms total, not waiting for a 300ms animation.

---

## RTL Support

CommandBar is fully compatible with right-to-left languages (Arabic, Hebrew, Persian, etc.).

**Implementation:**

- The palette uses `direction: inherit` so it follows the document's text direction
- Flexbox layouts use `flex-start` / `flex-end` (logical properties) rather than `left` / `right`
- The floating trigger button position is mirrored when `<html dir="rtl">` is detected:
  - Bottom-right becomes bottom-left
  - Bottom-left becomes bottom-right
- Icons are flipped horizontally in RTL where direction is semantically meaningful (e.g., forward/back arrows). Dashicons that represent objects (edit pencil, settings gear) are not flipped.
- CSS `margin-inline-start` / `margin-inline-end` are used instead of `margin-left` / `margin-right` throughout

WordPress sets `<html dir="rtl">` automatically for RTL languages. No configuration is required.

---

## Testing Notes

### Manual Testing Checklist

Before each release, the following accessibility checks are performed manually:

**Keyboard:**
- [ ] CMD+K / CTRL+K opens palette from various locations in wp-admin
- [ ] Shortcut does not fire inside block editor, TinyMCE, or other inputs
- [ ] Arrow keys navigate results correctly with wrapping
- [ ] Enter executes the highlighted result
- [ ] Escape closes the palette
- [ ] Focus returns to the correct element after close
- [ ] Tab/Shift+Tab navigates within palette (does not leave)
- [ ] Home/End jump to first/last result

**Screen Reader (NVDA + Firefox):**
- [ ] Palette opens and announces its name and description
- [ ] Typing announces result count via polite live region
- [ ] Arrow key navigation announces result title and description
- [ ] Executing an action announces the success/failure message
- [ ] Palette close returns focus and screen reader reads the returned element

**Color and Contrast:**
- [ ] All result text passes 4.5:1 in both light and dark themes
- [ ] Active result is distinguishable without color
- [ ] Focus ring visible on input and trigger button

**Motion:**
- [ ] Animations disabled when `prefers-reduced-motion: reduce` is active
- [ ] Palette still opens and closes correctly without animation

**High Contrast Mode (Windows):**
- [ ] Palette is usable in Windows High Contrast Black and White themes
- [ ] Focus ring visible in High Contrast mode
- [ ] Active result visible in High Contrast mode

**Zoom:**
- [ ] Palette usable at 200% browser zoom
- [ ] No content overflow or overlap at 200% zoom

---

## Known Limitations

**`aria-modal` browser support:** Some older screen reader / browser combinations do not fully respect `aria-modal="true"`. CommandBar supplements this with explicit `aria-hidden` and `inert` on background content.

**Dark Mode toggle:** The dark mode toggle applies a CSS class to `document.body`. It does not interact with the WordPress admin color scheme system. Some screen reader users who rely on OS-level dark mode may find the toggle redundant — their OS dark mode is already detected via `prefers-color-scheme`.

**Virtual cursor (NVDA / JAWS Browse Mode):** In Browse Mode, virtual cursor navigation can move outside the palette even with `aria-modal`. Users should switch to Forms Mode (`Insert+Space` in NVDA, `Insert+Z` in JAWS) for the intended keyboard experience. Most experienced screen reader users are aware of this distinction.

---

*For setup instructions, see [setup.md](setup.md).*
*For keyboard shortcuts, see [commands.md](commands.md).*
*For contribution guidelines, see [contribution-guide.md](contribution-guide.md).*
