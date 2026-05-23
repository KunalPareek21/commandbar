/**
 * CommandBar — Keyboard module
 *
 * Registers and manages all keyboard interactions:
 *  - CMD+K / CTRL+K global shortcut (open/close toggle)
 *  - Arrow Up / Down / Home / End result navigation
 *  - Tab / Shift+Tab navigation
 *  - Enter to execute the highlighted result
 *  - Escape to close
 *  - Focus trap within the open palette
 *
 * This module exports event-handling functions that are wired up by
 * commandbar.js (the main entry point).
 *
 * @package CommandBar
 * @since   1.0.0
 */

'use strict';

/* global CommandBarData */

// ---------------------------------------------------------------------------
// Global shortcut listener
// ---------------------------------------------------------------------------

/**
 * Determine whether the CMD+K / CTRL+K shortcut was pressed.
 *
 * Ignores the event when a native input element is focused (unless the
 * palette is already open), so we never hijack text editing shortcuts.
 *
 * @since 1.0.0
 *
 * @param {KeyboardEvent} event          - The keyboard event.
 * @param {boolean}       isPaletteOpen  - Whether the palette is currently visible.
 * @returns {boolean}
 */
function isTriggerShortcut( event, isPaletteOpen ) {
	const k = event.key === 'k' || event.key === 'K';
	const modifier = event.ctrlKey || event.metaKey;

	if ( ! modifier || ! k ) return false;

	// If the palette is already open, always allow CMD+K to close it.
	if ( isPaletteOpen ) return true;

	// Do not intercept while user is typing in a native input.
	const tag = ( document.activeElement && document.activeElement.tagName ) || '';
	const isEditable =
		tag === 'INPUT' ||
		tag === 'TEXTAREA' ||
		tag === 'SELECT' ||
		( document.activeElement &&
			document.activeElement.getAttribute( 'contenteditable' ) === 'true' );

	return ! isEditable;
}

/**
 * Register the global CMD+K / CTRL+K keyboard listener on the document.
 *
 * @since 1.0.0
 *
 * @param {Function} onToggle - Callback invoked when the shortcut fires.
 *                              Receives no arguments; toggling is handled by the caller.
 * @returns {Function} Cleanup function that removes the listener.
 */
function registerGlobalShortcut( onToggle ) {
	let _isOpen = false;

	/**
	 * Update the cached open-state (kept in sync by commandbar.js).
	 *
	 * @param {boolean} open
	 */
	function setOpen( open ) {
		_isOpen = open;
	}

	/**
	 * Handle keydown on the document.
	 *
	 * @param {KeyboardEvent} event
	 */
	function handler( event ) {
		if ( isTriggerShortcut( event, _isOpen ) ) {
			event.preventDefault();
			onToggle();
		}
	}

	document.addEventListener( 'keydown', handler );

	return {
		setOpen,
		remove: () => document.removeEventListener( 'keydown', handler ),
	};
}

// ---------------------------------------------------------------------------
// Palette keyboard navigation
// ---------------------------------------------------------------------------

/**
 * Handle all keyboard events while the palette is open.
 *
 * Call this from the palette's keydown listener. Returns true if the event
 * was handled (so the caller can call preventDefault()), false otherwise.
 *
 * @since 1.0.0
 *
 * @param {KeyboardEvent} event            - The keyboard event.
 * @param {Object}        context          - Navigation context object.
 * @param {HTMLElement[]} context.items    - Currently visible result item elements.
 * @param {number}        context.activeIndex - Index of the currently highlighted item (-1 = none).
 * @param {Function}      context.setActiveIndex - Setter for activeIndex.
 * @param {Function}      context.onEnter  - Callback when Enter is pressed on an item.
 * @param {Function}      context.onEscape - Callback when Escape is pressed.
 * @param {HTMLElement}   context.input    - The search input element.
 * @returns {boolean} Whether the event was consumed.
 */
function handlePaletteKeydown( event, context ) {
	const { items, activeIndex, setActiveIndex, onEnter, onEscape, input } = context;
	const total = items.length;

	switch ( event.key ) {
		case 'Escape': {
			event.preventDefault();
			onEscape();
			return true;
		}

		case 'ArrowDown':
		case 'Tab': {
			if ( event.key === 'Tab' && event.shiftKey ) break; // handled below
			event.preventDefault();
			if ( total === 0 ) return true;

			const next = activeIndex < total - 1 ? activeIndex + 1 : 0; // wrap
			setActiveIndex( next );
			_scrollItemIntoView( items[ next ] );
			return true;
		}

		case 'ArrowUp': {
			event.preventDefault();
			if ( total === 0 ) return true;

			const prev = activeIndex > 0 ? activeIndex - 1 : total - 1; // wrap
			setActiveIndex( prev );
			_scrollItemIntoView( items[ prev ] );
			return true;
		}

		case 'Home': {
			event.preventDefault();
			if ( total === 0 ) return true;
			setActiveIndex( 0 );
			_scrollItemIntoView( items[ 0 ] );
			return true;
		}

		case 'End': {
			event.preventDefault();
			if ( total === 0 ) return true;
			setActiveIndex( total - 1 );
			_scrollItemIntoView( items[ total - 1 ] );
			return true;
		}

		case 'Enter': {
			event.preventDefault();
			if ( activeIndex >= 0 && items[ activeIndex ] ) {
				onEnter( activeIndex );
			}
			return true;
		}

		default:
			return false;
	}

	// Shift+Tab (navigate backwards).
	if ( event.key === 'Tab' && event.shiftKey ) {
		event.preventDefault();
		if ( total === 0 ) return true;

		const prev = activeIndex > 0 ? activeIndex - 1 : total - 1;
		setActiveIndex( prev );
		_scrollItemIntoView( items[ prev ] );
		return true;
	}

	return false;
}

// ---------------------------------------------------------------------------
// Focus trap
// ---------------------------------------------------------------------------

/**
 * Trap keyboard focus inside the palette element while it is open.
 *
 * Returns a cleanup function that removes the trap listener.
 *
 * @since 1.0.0
 *
 * @param {HTMLElement} palette   - The palette wrapper element.
 * @param {HTMLElement} input     - The search input to refocus if needed.
 * @returns {Function} Cleanup function.
 */
function trapFocus( palette, input ) {
	/**
	 * Handle Tab keydown to keep focus inside the palette.
	 *
	 * @param {KeyboardEvent} event
	 */
	function handler( event ) {
		if ( event.key !== 'Tab' ) return;

		const focusable = Array.from(
			palette.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
			)
		).filter( ( el ) => el.offsetParent !== null );

		if ( focusable.length === 0 ) {
			event.preventDefault();
			return;
		}

		const first = focusable[ 0 ];
		const last  = focusable[ focusable.length - 1 ];

		if ( event.shiftKey ) {
			if ( document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			}
		} else {
			if ( document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	}

	palette.addEventListener( 'keydown', handler );
	return () => palette.removeEventListener( 'keydown', handler );
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Scroll a result item into the visible area of the results list.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {HTMLElement} item - Result item element.
 */
function _scrollItemIntoView( item ) {
	if ( ! item ) return;
	item.scrollIntoView( { block: 'nearest', behavior: 'auto' } );
}

// Expose module API.
window.CommandBarKeyboard = {
	isTriggerShortcut,
	registerGlobalShortcut,
	handlePaletteKeydown,
	trapFocus,
};
