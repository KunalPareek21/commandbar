/**
 * CommandBar — Actions module
 *
 * Handles the execution of commands: navigation, REST API actions, and
 * confirmation flows for potentially destructive commands. Also manages
 * recent command history (stored in localStorage) and toast notifications.
 *
 * @package CommandBar
 * @since   1.0.0
 */

'use strict';

/* global CommandBarData, CommandBarSearch */

/** localStorage key for recent command IDs. */
const RECENT_COMMANDS_KEY = 'commandbar_recent';

/** localStorage key for the dark mode preference. */
const DARK_MODE_KEY = 'commandbar_dark_mode';

/** localStorage key recording whether the trigger button has been dismissed. */
const TRIGGER_DISMISSED_KEY = 'commandbar_trigger_dismissed';

// ---------------------------------------------------------------------------
// Toast notifications
// ---------------------------------------------------------------------------

/**
 * Ensure the toast region element exists in the DOM.
 *
 * @since 1.0.0
 *
 * @returns {HTMLElement}
 */
function _getToastRegion() {
	let region = document.getElementById( 'commandbar-toast-region' );
	if ( ! region ) {
		region = document.createElement( 'div' );
		region.id                    = 'commandbar-toast-region';
		region.setAttribute( 'aria-live', 'assertive' );
		region.setAttribute( 'aria-atomic', 'true' );
		region.setAttribute( 'role', 'status' );
		document.body.appendChild( region );
	}
	return region;
}

/**
 * Display a toast notification at the bottom-centre of the screen.
 *
 * @since 1.0.0
 *
 * @param {string}  message   - Text to display.
 * @param {'success'|'error'|'info'} [variant='info'] - Toast colour variant.
 * @param {number}  [duration=3000] - Auto-dismiss duration in milliseconds.
 */
function showToast( message, variant, duration ) {
	const variantClass = variant === 'error'
		? 'cb-toast-error'
		: variant === 'success'
			? 'cb-toast-success'
			: '';

	const region = _getToastRegion();
	const toast  = document.createElement( 'div' );
	toast.className    = `cb-toast${variantClass ? ' ' + variantClass : ''}`;
	toast.textContent  = message;
	toast.setAttribute( 'role', 'alert' );

	region.appendChild( toast );

	// Trigger entrance animation on next frame.
	requestAnimationFrame( () => {
		requestAnimationFrame( () => {
			toast.classList.add( 'cb-toast-visible' );
		} );
	} );

	setTimeout( () => {
		toast.classList.remove( 'cb-toast-visible' );
		toast.addEventListener( 'transitionend', () => toast.remove(), { once: true } );
		// Fallback removal in case transitionend doesn't fire.
		setTimeout( () => toast.remove(), 500 );
	}, duration || 3000 );
}

// ---------------------------------------------------------------------------
// Recent commands
// ---------------------------------------------------------------------------

/**
 * Return the array of recently executed command IDs from localStorage.
 *
 * @since 1.0.0
 *
 * @returns {string[]}
 */
function getRecentCommandIds() {
	try {
		const raw = localStorage.getItem( RECENT_COMMANDS_KEY );
		return raw ? JSON.parse( raw ) : [];
	} catch {
		return [];
	}
}

/**
 * Record a command ID as recently used.
 *
 * Stores only the IDs (never user data) and respects the configured limit.
 *
 * @since 1.0.0
 *
 * @param {string} commandId - The command id to record.
 */
function recordRecentCommand( commandId ) {
	if ( ! CommandBarData.getSetting( 'show_recent_commands', true ) ) {
		return;
	}

	const limit  = Number( CommandBarData.getSetting( 'recent_commands_count', 5 ) ) || 5;
	const recent = getRecentCommandIds().filter( ( id ) => id !== commandId );

	recent.unshift( commandId );
	recent.splice( limit );

	try {
		localStorage.setItem( RECENT_COMMANDS_KEY, JSON.stringify( recent ) );
	} catch {
		// localStorage may be full or disabled — fail silently.
	}
}

/**
 * Return the recent CBCommand objects in order (most recent first).
 *
 * @since 1.0.0
 *
 * @returns {import('./commandbar-data').CBCommand[]}
 */
function getRecentCommands() {
	if ( ! CommandBarData.getSetting( 'show_recent_commands', true ) ) {
		return [];
	}

	const ids      = getRecentCommandIds();
	const commands = CommandBarData.getStaticCommands();
	const result   = [];

	for ( const id of ids ) {
		const match = commands.find( ( c ) => c.id === id );
		if ( match ) result.push( match );
	}

	return result;
}

/**
 * Clear all stored recent commands (called from the settings page Reset button).
 *
 * @since 1.0.0
 */
function clearRecentCommands() {
	try {
		localStorage.removeItem( RECENT_COMMANDS_KEY );
	} catch {
		// Fail silently.
	}
}

// ---------------------------------------------------------------------------
// Dark mode
// ---------------------------------------------------------------------------

/**
 * Return the current dark mode preference from localStorage.
 *
 * @since 1.0.0
 *
 * @returns {boolean}
 */
function isDarkMode() {
	try {
		return localStorage.getItem( DARK_MODE_KEY ) === 'true';
	} catch {
		return false;
	}
}

/**
 * Toggle the admin dark mode class on the document body and persist the pref.
 *
 * @since 1.0.0
 */
function toggleDarkMode() {
	const enabled = ! isDarkMode();

	try {
		localStorage.setItem( DARK_MODE_KEY, String( enabled ) );
	} catch {
		// Fail silently.
	}

	document.body.setAttribute( 'data-commandbar-dark', String( enabled ) );

	const i18n = CommandBarData.getI18n();
	showToast(
		enabled ? ( i18n.darkModeOn || 'Dark mode enabled' ) : ( i18n.darkModeOff || 'Dark mode disabled' ),
		'info'
	);
}

/**
 * Apply the persisted dark mode preference on page load.
 *
 * @since 1.0.0
 */
function applyDarkModePreference() {
	if ( isDarkMode() ) {
		document.body.setAttribute( 'data-commandbar-dark', 'true' );
	}
}

// ---------------------------------------------------------------------------
// Trigger button dismiss
// ---------------------------------------------------------------------------

/**
 * Return whether the floating trigger button has been dismissed this session.
 *
 * @since 1.0.0
 *
 * @returns {boolean}
 */
function isTriggerDismissed() {
	try {
		return sessionStorage.getItem( TRIGGER_DISMISSED_KEY ) === 'true';
	} catch {
		return false;
	}
}

/**
 * Mark the floating trigger button as dismissed for this session.
 *
 * @since 1.0.0
 */
function dismissTrigger() {
	try {
		sessionStorage.setItem( TRIGGER_DISMISSED_KEY, 'true' );
	} catch {
		// Fail silently.
	}
}

// ---------------------------------------------------------------------------
// REST API action executor
// ---------------------------------------------------------------------------

/**
 * Execute a server-side action via the CommandBar REST API.
 *
 * @since 1.0.0
 *
 * @param {string} action - Action identifier (e.g. 'flush_rewrite_rules').
 * @returns {Promise<{success: boolean, message: string}>}
 */
async function executeRestAction( action ) {
	const restBase = CommandBarData.getRestBase();
	const nonce    = CommandBarData.getNonce();
	const i18n     = CommandBarData.getI18n();

	if ( ! restBase || ! nonce ) {
		return { success: false, message: i18n.actionError || 'Action failed.' };
	}

	try {
		const response = await fetch( `${restBase}/actions`, {
			method:  'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   nonce,
				'Accept':       'application/json',
			},
			credentials: 'same-origin',
			body: JSON.stringify( { action } ),
		} );

		const json = await response.json();

		return {
			success: json && json.success === true,
			message: ( json && json.message ) || ( i18n.actionError || 'Action failed.' ),
		};
	} catch {
		return {
			success: false,
			message: i18n.actionError || 'Action failed. Please try again.',
		};
	}
}

// ---------------------------------------------------------------------------
// Command execution
// ---------------------------------------------------------------------------

/**
 * Execute a command object.
 *
 * For 'navigate' commands: navigates immediately.
 * For 'action' commands: calls the appropriate action handler.
 *
 * @since 1.0.0
 *
 * @param {import('./commandbar-data').CBCommand} command - Command to execute.
 * @param {Function} [onClose] - Callback to close the palette after execution.
 * @returns {Promise<void>}
 */
async function executeCommand( command, onClose ) {
	if ( ! command ) return;

	// Record for recent commands (store ID only — never user content).
	recordRecentCommand( command.id );

	if ( command.type === 'navigate' && command.url ) {
		if ( typeof onClose === 'function' ) onClose();
		window.location.href = command.url;
		return;
	}

	if ( command.type === 'action' ) {
		await _executeAction( command, onClose );
		return;
	}

	// Dynamic results from the API — they always have a url.
	if ( command.url ) {
		if ( typeof onClose === 'function' ) onClose();
		window.location.href = command.url;
	}
}

/**
 * Execute a named action command.
 *
 * @since  1.0.0
 * @access private
 *
 * @param {import('./commandbar-data').CBCommand} command - Action command.
 * @param {Function} [onClose] - Palette close callback.
 * @returns {Promise<void>}
 */
async function _executeAction( command, onClose ) {
	const i18n = CommandBarData.getI18n();

	switch ( command.action ) {
		case 'flush_rewrite_rules': {
			if ( typeof onClose === 'function' ) onClose();
			const result = await executeRestAction( 'flush_rewrite_rules' );
			CommandBarSearch.clearApiCache();
			showToast(
				result.success
					? ( i18n.flushSuccess || 'Rewrite rules flushed successfully.' )
					: result.message,
				result.success ? 'success' : 'error'
			);
			break;
		}

		case 'toggle_dark_mode': {
			if ( typeof onClose === 'function' ) onClose();
			toggleDarkMode();
			break;
		}

		default:
			if ( typeof onClose === 'function' ) onClose();
			showToast( i18n.actionError || 'Unknown action.', 'error' );
	}
}

// Expose module API.
window.CommandBarActions = {
	executeCommand,
	executeRestAction,
	showToast,
	getRecentCommands,
	getRecentCommandIds,
	recordRecentCommand,
	clearRecentCommands,
	isDarkMode,
	toggleDarkMode,
	applyDarkModePreference,
	isTriggerDismissed,
	dismissTrigger,
	RECENT_COMMANDS_KEY,
	DARK_MODE_KEY,
	TRIGGER_DISMISSED_KEY,
};
