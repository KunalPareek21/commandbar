/**
 * CommandBar — Main entry point
 *
 * Initialises the CommandBar command palette, mounts the DOM, wires all
 * modules together, and manages the palette lifecycle.
 *
 * Modules loaded before this file (via wp_enqueue_script dependencies):
 *   - commandbar-data.js      → window.CommandBarData
 *   - commandbar-search.js    → window.CommandBarSearch
 *   - commandbar-actions.js   → window.CommandBarActions
 *   - commandbar-keyboard.js  → window.CommandBarKeyboard
 *
 * @package CommandBar
 * @since   1.0.0
 */

'use strict';

/* global CommandBarData, CommandBarSearch, CommandBarActions, CommandBarKeyboard */

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------

/** @type {boolean} Whether the palette is currently visible. */
let _isOpen = false;

/** @type {number} Index of the currently highlighted result item (-1 = none). */
let _activeIndex = -1;

/** @type {Array} Currently rendered result items (commands). */
let _currentResults = [];

/** @type {boolean} Whether a confirmation step is pending for the active item. */
let _confirmPending = false;

/** @type {HTMLElement|null} The element that had focus before the palette opened. */
let _previousFocus = null;

/** @type {Function|null} Focus-trap cleanup function. */
let _removeTrapFocus = null;

/** @type {Object|null} Global shortcut controller returned by registerGlobalShortcut. */
let _shortcutController = null;

// ---------------------------------------------------------------------------
// DOM references (populated in _mount())
// ---------------------------------------------------------------------------

let _overlay  = null; // #commandbar-overlay
let _wrapper  = null; // .commandbar-palette-wrapper
let _input    = null; // #commandbar-input
let _results  = null; // #commandbar-results
let _trigger  = null; // #commandbar-trigger
let _clearBtn = null; // .cb-clear-btn

// ---------------------------------------------------------------------------
// Palette open / close
// ---------------------------------------------------------------------------

/**
 * Open the command palette.
 *
 * @since 1.0.0
 */
function openPalette() {
	if ( _isOpen ) return;
	_isOpen = true;

	// Remember what was focused so we can restore it on close.
	_previousFocus = document.activeElement;

	_overlay.setAttribute( 'aria-hidden', 'false' );
	_overlay.style.display = 'flex';

	// Force a reflow before adding the class to ensure CSS transitions fire.
	// eslint-disable-next-line no-unused-expressions
	_overlay.offsetHeight;
	_overlay.classList.add( 'cb-open' );
	_overlay.classList.remove( 'cb-closing' );

	_input.value = '';
	_input.focus();

	_renderResults( [] );
	_showDefaultResults();

	// Apply palette theme from settings.
	_applyPaletteTheme();

	// Sync keyboard controller open state.
	if ( _shortcutController ) {
		_shortcutController.setOpen( true );
	}

	// Install focus trap.
	if ( _wrapper ) {
		_removeTrapFocus = CommandBarKeyboard.trapFocus( _wrapper, _input );
	}

	// Announce to screen readers.
	_overlay.setAttribute( 'aria-label', CommandBarData.__( 'dialogLabel', 'Command palette' ) );

	// Update body attribute for dark mode scoping.
	document.body.setAttribute( 'data-commandbar-open', 'true' );
}

/**
 * Close the command palette.
 *
 * @since 1.0.0
 */
function closePalette() {
	if ( ! _isOpen ) return;
	_isOpen = false;
	_confirmPending = false;
	_activeIndex = -1;

	_overlay.classList.remove( 'cb-open' );
	_overlay.classList.add( 'cb-closing' );

	// Sync keyboard controller.
	if ( _shortcutController ) {
		_shortcutController.setOpen( false );
	}

	// Remove focus trap.
	if ( typeof _removeTrapFocus === 'function' ) {
		_removeTrapFocus();
		_removeTrapFocus = null;
	}

	// After closing animation completes, hide from the DOM.
	const duration = _prefersReducedMotion() ? 0 : 100;
	setTimeout( () => {
		_overlay.classList.remove( 'cb-closing' );
		_overlay.style.display = 'none';
		_overlay.setAttribute( 'aria-hidden', 'true' );
	}, duration );

	// Restore focus to the previously focused element.
	if ( _previousFocus && typeof _previousFocus.focus === 'function' ) {
		_previousFocus.focus();
	}

	document.body.removeAttribute( 'data-commandbar-open' );
}

/**
 * Toggle the palette open/closed state.
 *
 * @since 1.0.0
 */
function togglePalette() {
	_isOpen ? closePalette() : openPalette();
}

// ---------------------------------------------------------------------------
// Results rendering
// ---------------------------------------------------------------------------

/**
 * Render a list of commands into the results container.
 *
 * Groups commands by their 'group' property and renders accessible list
 * markup with group labels, icons, title, and description.
 *
 * @since 1.0.0
 *
 * @param {Array}   commands    - Array of command objects to render.
 * @param {boolean} [isLoading] - Whether API results are still loading.
 */
function _renderResults( commands, isLoading ) {
	_currentResults = commands;
	_activeIndex    = commands.length > 0 ? 0 : -1;
	_confirmPending = false;

	// Clear existing content.
	while ( _results.firstChild ) {
		_results.removeChild( _results.firstChild );
	}

	const showIcons    = CommandBarData.getSetting( 'show_command_icons', true );
	const showShortcuts = CommandBarData.getSetting( 'show_shortcut_hints', true );

	if ( commands.length === 0 ) {
		if ( isLoading ) {
			_renderLoadingState();
		} else if ( _input.value.trim() !== '' ) {
			_renderEmptyState();
		}
		_updateInputAriaState( 0 );
		return;
	}

	// Group commands.
	const groups = _groupCommands( commands );
	let itemIndex = 0;

	for ( const [ groupName, groupCommands ] of Object.entries( groups ) ) {
		// Group label.
		const groupEl = document.createElement( 'div' );
		groupEl.className = 'cb-group-label';
		groupEl.setAttribute( 'role', 'group' );
		groupEl.setAttribute( 'aria-label', groupName );
		groupEl.textContent = groupName;
		_results.appendChild( groupEl );

		for ( const command of groupCommands ) {
			const item = _buildResultItem( command, itemIndex, showIcons, showShortcuts );
			_results.appendChild( item );

			if ( itemIndex === 0 ) {
				// Highlight the first item by default.
				item.setAttribute( 'aria-selected', 'true' );
				item.classList.add( 'cb-active' );
			}

			itemIndex++;
		}
	}

	_updateInputAriaState( commands.length );

	// Announce result count to screen readers.
	const countTemplate = CommandBarData.__( 'resultsCount', 'Showing %d result(s)' );
	_input.setAttribute(
		'aria-label',
		countTemplate.replace( '%d', String( commands.length ) )
	);
}

/**
 * Group an array of commands by their 'group' property.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {Array} commands - Flat command array.
 * @returns {Object} Commands keyed by group name.
 */
function _groupCommands( commands ) {
	const groups = {};
	for ( const command of commands ) {
		const group = command.group || 'Other';
		if ( ! groups[ group ] ) {
			groups[ group ] = [];
		}
		groups[ group ].push( command );
	}
	return groups;
}

/**
 * Build a single result item element.
 *
 * Uses textContent (never innerHTML) for all user-visible text to prevent XSS.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {Object}  command       - Command object.
 * @param {number}  index         - Position in the flat results array.
 * @param {boolean} showIcons     - Whether to render the icon.
 * @param {boolean} showShortcuts - Whether to render the shortcut badge.
 * @returns {HTMLElement}
 */
function _buildResultItem( command, index, showIcons, showShortcuts ) {
	const item = document.createElement( 'div' );
	item.className = 'cb-result-item';
	item.setAttribute( 'role', 'option' );
	item.setAttribute( 'aria-selected', 'false' );
	item.setAttribute( 'data-index', String( index ) );
	item.setAttribute( 'tabindex', '-1' );

	// Icon.
	if ( showIcons && command.icon ) {
		const iconWrap = document.createElement( 'span' );
		iconWrap.className = 'cb-result-icon';
		iconWrap.setAttribute( 'aria-hidden', 'true' );

		const icon = document.createElement( 'span' );
		icon.className = `dashicons dashicons-${command.icon}`;
		iconWrap.appendChild( icon );
		item.appendChild( iconWrap );
	}

	// Body: title + description.
	const body = document.createElement( 'div' );
	body.className = 'cb-result-body';

	const title = document.createElement( 'span' );
	title.className   = 'cb-result-title';
	title.textContent = command.title || '';

	const desc = document.createElement( 'span' );
	desc.className   = 'cb-result-desc';
	desc.textContent = command.description || '';

	body.appendChild( title );
	if ( command.description ) body.appendChild( desc );
	item.appendChild( body );

	// Shortcut badge.
	if ( showShortcuts && command.shortcut ) {
		const shortcutWrap = document.createElement( 'span' );
		shortcutWrap.className = 'cb-result-shortcut';
		shortcutWrap.setAttribute( 'aria-hidden', 'true' );

		const kbd = document.createElement( 'kbd' );
		kbd.textContent = command.shortcut;
		shortcutWrap.appendChild( kbd );
		item.appendChild( shortcutWrap );
	}

	// Click handler.
	item.addEventListener( 'click', () => {
		_handleResultActivation( index );
	} );

	// Mouse-enter highlights the item.
	item.addEventListener( 'mouseenter', () => {
		_setActiveIndex( index );
	} );

	return item;
}

/**
 * Render a loading indicator.
 *
 * @since  1.0.0
 * @access private
 */
function _renderLoadingState() {
	const state = document.createElement( 'div' );
	state.className = 'cb-empty-state';
	state.setAttribute( 'aria-live', 'polite' );

	const icon = document.createElement( 'span' );
	icon.className = 'dashicons dashicons-update';
	icon.setAttribute( 'aria-hidden', 'true' );

	const text = document.createElement( 'span' );
	text.textContent = CommandBarData.__( 'searching', 'Searching\u2026' );

	state.appendChild( icon );
	state.appendChild( text );
	_results.appendChild( state );
}

/**
 * Render an empty-state message when no results are found.
 *
 * @since  1.0.0
 * @access private
 */
function _renderEmptyState() {
	const state = document.createElement( 'div' );
	state.className = 'cb-empty-state';

	const icon = document.createElement( 'span' );
	icon.className = 'dashicons dashicons-search';
	icon.setAttribute( 'aria-hidden', 'true' );

	const text = document.createElement( 'span' );
	text.textContent = CommandBarData.__( 'noResults', 'No results found.' );

	state.appendChild( icon );
	state.appendChild( text );
	_results.appendChild( state );
}

/**
 * Show recently used commands or the full default command list.
 *
 * @since  1.0.0
 * @access private
 */
function _showDefaultResults() {
	const recent = CommandBarActions.getRecentCommands();
	if ( recent.length > 0 ) {
		// Stamp the group name so they appear under "Recently Used".
		const stamped = recent.map( ( cmd ) => Object.assign( {}, cmd, {
			group: CommandBarData.__( 'recentLabel', 'Recently Used' ),
		} ) );
		_renderResults( stamped );
	} else {
		// Show first batch of commands sorted by group.
		const all = CommandBarData.getStaticCommands().slice( 0, 8 );
		_renderResults( all );
	}
}

// ---------------------------------------------------------------------------
// Active index management
// ---------------------------------------------------------------------------

/**
 * Update the highlighted result item.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {number} index - New active index.
 */
function _setActiveIndex( index ) {
	const items = _getResultItems();

	// Clear previous active.
	for ( const item of items ) {
		item.setAttribute( 'aria-selected', 'false' );
		item.classList.remove( 'cb-active' );
	}

	_activeIndex = index;

	if ( index >= 0 && items[ index ] ) {
		items[ index ].setAttribute( 'aria-selected', 'true' );
		items[ index ].classList.add( 'cb-active' );
		// Update aria-activedescendant on input for screen readers.
		_input.setAttribute( 'aria-activedescendant', items[ index ].id || '' );
	}
}

/**
 * Return the current list of rendered result item elements.
 *
 * @since  1.0.0
 * @access private
 *
 * @returns {HTMLElement[]}
 */
function _getResultItems() {
	return Array.from( _results.querySelectorAll( '.cb-result-item' ) );
}

// ---------------------------------------------------------------------------
// Result activation (click / Enter)
// ---------------------------------------------------------------------------

/**
 * Handle activation of a result at the given index.
 *
 * For commands with confirm:true, the first activation shows the confirmation
 * UI; the second activation executes the command.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {number} index - Index of the result to activate.
 */
function _handleResultActivation( index ) {
	const command = _currentResults[ index ];
	if ( ! command ) return;

	const items = _getResultItems();
	const item  = items[ index ];

	if ( command.confirm ) {
		if ( _confirmPending && _activeIndex === index ) {
			// Second press — execute.
			_confirmPending = false;
			if ( item ) item.classList.remove( 'cb-confirm-pending' );
			CommandBarActions.executeCommand( command, closePalette );
		} else {
			// First press — show confirmation.
			_confirmPending = true;
			_setActiveIndex( index );
			if ( item ) item.classList.add( 'cb-confirm-pending' );
		}
		return;
	}

	_confirmPending = false;
	CommandBarActions.executeCommand( command, closePalette );
}

// ---------------------------------------------------------------------------
// Input handling
// ---------------------------------------------------------------------------

/**
 * Handle changes to the search input value.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {Event} event - The input event.
 */
function _onInput( event ) {
	const value = event.target.value;
	_confirmPending = false;

	// Show/hide the clear button.
	if ( _clearBtn ) {
		_clearBtn.style.display = value.length > 0 ? 'flex' : 'none';
	}

	if ( value.trim() === '' ) {
		_showDefaultResults();
		return;
	}

	CommandBarSearch.search( value, ( staticResults, dynamicResults, isLoading ) => {
		// Merge: static first, then dynamic (deduplicate by id).
		const seen = new Set();
		const merged = [];

		for ( const cmd of [ ...staticResults, ...dynamicResults ] ) {
			const key = cmd.id || cmd.title;
			if ( ! seen.has( key ) ) {
				seen.add( key );
				merged.push( cmd );
			}
		}

		_renderResults( merged, isLoading );
	} );
}

/**
 * Clear the search input and reset the results.
 *
 * @since  1.0.0
 * @access private
 */
function _clearInput() {
	_input.value = '';
	if ( _clearBtn ) _clearBtn.style.display = 'none';
	_showDefaultResults();
	_input.focus();
}

// ---------------------------------------------------------------------------
// ARIA helpers
// ---------------------------------------------------------------------------

/**
 * Sync ARIA attributes on the input based on the current result count.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {number} count - Number of visible results.
 */
function _updateInputAriaState( count ) {
	_input.setAttribute( 'aria-expanded', count > 0 ? 'true' : 'false' );
	if ( count === 0 ) {
		_input.removeAttribute( 'aria-activedescendant' );
	}
}

// ---------------------------------------------------------------------------
// Theme application
// ---------------------------------------------------------------------------

/**
 * Apply the configured palette theme class to the wrapper element.
 *
 * @since  1.0.0
 * @access private
 */
function _applyPaletteTheme() {
	const theme = CommandBarData.getSetting( 'palette_theme', 'auto' );

	if ( _wrapper ) {
		_wrapper.classList.remove( 'cb-theme-light', 'cb-theme-dark', 'cb-theme-auto' );
		_wrapper.classList.add( `cb-theme-${theme}` );
	}

	if ( _trigger ) {
		_trigger.classList.remove( 'cb-theme-light', 'cb-theme-dark', 'cb-theme-auto' );
		_trigger.classList.add( `cb-theme-${theme}` );
	}
}

// ---------------------------------------------------------------------------
// Floating trigger button
// ---------------------------------------------------------------------------

/**
 * Mount and configure the floating trigger button.
 *
 * @since  1.0.0
 * @access private
 */
function _mountTriggerButton() {
	if ( ! CommandBarData.getSetting( 'show_trigger_button', true ) ) {
		return;
	}

	if ( CommandBarActions.isTriggerDismissed() ) {
		return;
	}

	_trigger = document.createElement( 'button' );
	_trigger.id   = 'commandbar-trigger';
	_trigger.type = 'button';

	const position = CommandBarData.getSetting( 'trigger_button_position', 'bottom-right' );
	if ( position === 'bottom-left' ) {
		_trigger.classList.add( 'cb-position-bottom-left' );
	}

	const i18n = CommandBarData.getI18n();

	// Icon.
	const icon = document.createElement( 'span' );
	icon.className = 'dashicons dashicons-search';
	icon.setAttribute( 'aria-hidden', 'true' );

	// Label.
	const label = document.createElement( 'span' );
	label.className   = 'cb-trigger-label';
	label.textContent = i18n.triggerTooltip || 'Open CommandBar';

	// Dismiss button.
	const dismiss = document.createElement( 'button' );
	dismiss.type = 'button';
	dismiss.className = 'cb-trigger-dismiss';
	dismiss.setAttribute( 'aria-label', i18n.dismissButton || 'Dismiss' );
	dismiss.title = i18n.dismissButton || 'Dismiss';

	const dismissIcon = document.createElement( 'span' );
	dismissIcon.className = 'dashicons dashicons-no-alt';
	dismissIcon.setAttribute( 'aria-hidden', 'true' );
	dismiss.appendChild( dismissIcon );

	_trigger.setAttribute( 'aria-label', i18n.triggerTooltip || 'Open CommandBar' );
	_trigger.setAttribute( 'title', `${i18n.triggerTooltip || 'Open CommandBar'} (${_isMac() ? '\u2318K' : 'Ctrl+K'})` );

	_trigger.appendChild( icon );
	_trigger.appendChild( label );
	_trigger.appendChild( dismiss );

	_trigger.addEventListener( 'click', ( event ) => {
		if ( event.target === dismiss || dismiss.contains( event.target ) ) {
			return; // handled below
		}
		openPalette();
	} );

	dismiss.addEventListener( 'click', ( event ) => {
		event.stopPropagation();
		CommandBarActions.dismissTrigger();
		_trigger.remove();
		_trigger = null;
	} );

	document.body.appendChild( _trigger );
}

// ---------------------------------------------------------------------------
// DOM mounting
// ---------------------------------------------------------------------------

/**
 * Build and insert all CommandBar DOM elements into the page.
 *
 * @since  1.0.0
 * @access private
 */
function _mount() {
	const i18n = CommandBarData.getI18n();

	// ── Overlay ──────────────────────────────────────────────────────────────
	_overlay = document.createElement( 'div' );
	_overlay.id = 'commandbar-overlay';
	_overlay.setAttribute( 'aria-hidden', 'true' );
	_overlay.setAttribute( 'role', 'dialog' );
	_overlay.setAttribute( 'aria-modal', 'true' );
	_overlay.setAttribute( 'aria-label', i18n.dialogLabel || 'Command palette' );
	_overlay.style.display = 'none';

	// Click on overlay (but not wrapper) closes the palette.
	_overlay.addEventListener( 'click', ( event ) => {
		if ( event.target === _overlay ) {
			closePalette();
		}
	} );

	// ── Wrapper (palette card) ────────────────────────────────────────────
	_wrapper = document.createElement( 'div' );
	_wrapper.className = 'commandbar-palette-wrapper';

	// ── Input row ─────────────────────────────────────────────────────────
	const inputRow = document.createElement( 'div' );
	inputRow.className = 'cb-input-row';

	// Search icon (decorative).
	const inputIconWrap = document.createElement( 'span' );
	inputIconWrap.className = 'cb-input-icon';
	inputIconWrap.setAttribute( 'aria-hidden', 'true' );

	const inputIcon = document.createElement( 'span' );
	inputIcon.className = 'dashicons dashicons-search';
	inputIconWrap.appendChild( inputIcon );

	// Input.
	_input = document.createElement( 'input' );
	_input.type = 'text';
	_input.id   = 'commandbar-input';
	_input.setAttribute( 'role', 'combobox' );
	_input.setAttribute( 'aria-expanded', 'false' );
	_input.setAttribute( 'aria-autocomplete', 'list' );
	_input.setAttribute( 'aria-controls', 'commandbar-results' );
	_input.setAttribute( 'autocomplete', 'off' );
	_input.setAttribute( 'autocorrect', 'off' );
	_input.setAttribute( 'autocapitalize', 'off' );
	_input.setAttribute( 'spellcheck', 'false' );
	_input.placeholder = i18n.placeholder || 'Type a command or search\u2026';

	_input.addEventListener( 'input', _onInput );

	// Keyboard events on the input are handled globally in the overlay listener below.

	// Actions: clear button + shortcut badge.
	const inputActions = document.createElement( 'div' );
	inputActions.className = 'cb-input-actions';

	_clearBtn = document.createElement( 'button' );
	_clearBtn.type  = 'button';
	_clearBtn.className = 'cb-clear-btn';
	_clearBtn.setAttribute( 'aria-label', 'Clear search' );
	_clearBtn.style.display = 'none';

	const clearIcon = document.createElement( 'span' );
	clearIcon.className = 'dashicons dashicons-no-alt';
	clearIcon.setAttribute( 'aria-hidden', 'true' );
	_clearBtn.appendChild( clearIcon );

	_clearBtn.addEventListener( 'click', _clearInput );

	const shortcutBadge = document.createElement( 'span' );
	shortcutBadge.className = 'cb-shortcut-badge';
	shortcutBadge.setAttribute( 'aria-hidden', 'true' );

	const shortcutKbd = document.createElement( 'kbd' );
	shortcutKbd.textContent = _isMac() ? '\u2318K' : 'Ctrl+K';
	shortcutBadge.appendChild( shortcutKbd );

	inputActions.appendChild( _clearBtn );
	inputActions.appendChild( shortcutBadge );

	inputRow.appendChild( inputIconWrap );
	inputRow.appendChild( _input );
	inputRow.appendChild( inputActions );

	// ── Results list ─────────────────────────────────────────────────────
	_results = document.createElement( 'div' );
	_results.id   = 'commandbar-results';
	_results.setAttribute( 'role', 'listbox' );
	_results.setAttribute( 'aria-label', i18n.dialogLabel || 'Command palette results' );

	// ── Status bar ───────────────────────────────────────────────────────
	const statusBar = document.createElement( 'div' );
	statusBar.className = 'cb-status-bar';
	statusBar.setAttribute( 'aria-hidden', 'true' );

	const navHint = document.createElement( 'span' );
	navHint.className = 'cb-status-hint';

	const arrowHint = document.createTextNode( 'Navigate ' );
	const kbdUp = document.createElement( 'kbd' );
	kbdUp.textContent = '\u2191';
	const kbdDown = document.createElement( 'kbd' );
	kbdDown.textContent = '\u2193';
	const enterHint = document.createTextNode( '  Select ' );
	const kbdEnter = document.createElement( 'kbd' );
	kbdEnter.textContent = 'Enter';
	const escHint = document.createTextNode( '  Close ' );
	const kbdEsc = document.createElement( 'kbd' );
	kbdEsc.textContent = 'Esc';

	navHint.appendChild( arrowHint );
	navHint.appendChild( kbdUp );
	navHint.appendChild( kbdDown );
	navHint.appendChild( enterHint );
	navHint.appendChild( kbdEnter );
	navHint.appendChild( escHint );
	navHint.appendChild( kbdEsc );

	statusBar.appendChild( navHint );

	// ── Assemble ──────────────────────────────────────────────────────────
	_wrapper.appendChild( inputRow );
	_wrapper.appendChild( _results );
	_wrapper.appendChild( statusBar );
	_overlay.appendChild( _wrapper );

	document.body.appendChild( _overlay );

	// ── Keyboard delegation from the overlay ─────────────────────────────
	_overlay.addEventListener( 'keydown', ( event ) => {
		if ( ! _isOpen ) return;

		const items = _getResultItems();

		const handled = CommandBarKeyboard.handlePaletteKeydown( event, {
			items,
			activeIndex:    _activeIndex,
			setActiveIndex: _setActiveIndex,
			onEnter:        _handleResultActivation,
			onEscape:       closePalette,
			input:          _input,
		} );

		// If the keyboard module handled it, don't propagate.
		if ( handled ) {
			event.stopPropagation();
		}
	} );
}

// ---------------------------------------------------------------------------
// Utility helpers
// ---------------------------------------------------------------------------

/**
 * Return true if the user's platform is macOS.
 *
 * @since  1.0.0
 * @access private
 *
 * @returns {boolean}
 */
function _isMac() {
	return ( navigator.platform || '' ).toUpperCase().indexOf( 'MAC' ) >= 0 ||
		( navigator.userAgentData && navigator.userAgentData.platform === 'macOS' );
}

/**
 * Return true if the user prefers reduced motion.
 *
 * @since  1.0.0
 * @access private
 *
 * @returns {boolean}
 */
function _prefersReducedMotion() {
	return window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
}

// ---------------------------------------------------------------------------
// Initialisation
// ---------------------------------------------------------------------------

/**
 * Initialise CommandBar.
 *
 * Called once on DOMContentLoaded. Mounts the DOM, registers keyboard
 * shortcuts, applies persisted preferences, and sets up the trigger button.
 *
 * @since 1.0.0
 */
function init() {
	// Bail if disabled.
	if ( ! CommandBarData.getSetting( 'enabled', true ) ) {
		return;
	}

	// Apply dark mode preference immediately so there's no flicker.
	CommandBarActions.applyDarkModePreference();

	// Build the DOM structure.
	_mount();

	// Mount the floating trigger button.
	_mountTriggerButton();

	// Apply palette theme to both the wrapper and the trigger button.
	_applyPaletteTheme();

	// Register the global CMD+K / CTRL+K shortcut.
	_shortcutController = CommandBarKeyboard.registerGlobalShortcut( togglePalette );
}

// Boot on DOMContentLoaded.
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', init );
} else {
	init();
}

// Expose for debugging / third-party use.
window.CommandBar = {
	open:   openPalette,
	close:  closePalette,
	toggle: togglePalette,
};
