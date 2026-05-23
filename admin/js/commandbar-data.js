/**
 * CommandBar — Data module
 *
 * Exports the static command registry and provides the data shape used
 * throughout all other modules. Dynamic commands (REST API results) are
 * assembled in commandbar-search.js and share the same schema.
 *
 * Note: The authoritative command list is built server-side by
 * CommandBar_Commands (PHP) and capability-filtered per user before being
 * passed via wp_localize_script(). This file exposes accessor helpers so
 * other modules never reference the global directly.
 *
 * @package CommandBar
 * @since   1.0.0
 */

'use strict';

/**
 * @typedef {Object} CBCommand
 * @property {string}   id          - Unique identifier.
 * @property {string}   title       - Display title.
 * @property {string}   description - Secondary text / subtitle.
 * @property {string[]} keywords    - Additional match terms for fuzzy search.
 * @property {string}   icon        - Dashicon slug (without 'dashicons-' prefix).
 * @property {string}   type        - 'navigate' | 'action' | 'dynamic'.
 * @property {string}   url         - Destination URL (for 'navigate' type).
 * @property {string}   [action]    - Action identifier (for 'action' type).
 * @property {string}   capability  - WordPress capability required.
 * @property {string}   group       - Category group label.
 * @property {string}   shortcut    - Optional keyboard shortcut display label.
 * @property {boolean}  confirm     - Whether to show a confirmation step.
 */

/**
 * Return the full, server-provided command registry as localised by PHP.
 *
 * This is the single source of truth for static commands. The array is
 * already filtered to the current user's capabilities by the server.
 *
 * @since 1.0.0
 *
 * @returns {CBCommand[]}
 */
function getStaticCommands() {
	return ( window.commandbarData && Array.isArray( window.commandbarData.commands ) )
		? window.commandbarData.commands
		: [];
}

/**
 * Return the full plugin settings object provided by wp_localize_script().
 *
 * @since 1.0.0
 *
 * @returns {Object}
 */
function getSettings() {
	return ( window.commandbarData && window.commandbarData.settings )
		? window.commandbarData.settings
		: {};
}

/**
 * Return a single plugin setting value by key.
 *
 * @since 1.0.0
 *
 * @param {string} key          - Setting key.
 * @param {*}      defaultValue - Fallback value.
 * @returns {*}
 */
function getSetting( key, defaultValue ) {
	const settings = getSettings();
	return ( key in settings ) ? settings[ key ] : defaultValue;
}

/**
 * Return the i18n string map provided by PHP.
 *
 * @since 1.0.0
 *
 * @returns {Object}
 */
function getI18n() {
	return ( window.commandbarData && window.commandbarData.i18n )
		? window.commandbarData.i18n
		: {};
}

/**
 * Return a single translated string by key.
 *
 * @since 1.0.0
 *
 * @param {string} key      - i18n key.
 * @param {string} fallback - Fallback string.
 * @returns {string}
 */
function __( key, fallback ) {
	const i18n = getI18n();
	return i18n[ key ] || fallback || key;
}

/**
 * Return the REST API nonce provided by PHP.
 *
 * @since 1.0.0
 *
 * @returns {string}
 */
function getNonce() {
	return ( window.commandbarData && window.commandbarData.nonce )
		? window.commandbarData.nonce
		: '';
}

/**
 * Return the REST API base URL provided by PHP.
 *
 * @since 1.0.0
 *
 * @returns {string}
 */
function getRestBase() {
	return ( window.commandbarData && window.commandbarData.restBase )
		? window.commandbarData.restBase
		: '';
}

/**
 * Return the current user capabilities object provided by PHP.
 *
 * @since 1.0.0
 *
 * @returns {Object}
 */
function getCapabilities() {
	return ( window.commandbarData && window.commandbarData.capabilities )
		? window.commandbarData.capabilities
		: {};
}

/**
 * Check whether the current user has a specific capability.
 *
 * @since 1.0.0
 *
 * @param {string} capability - Capability key (matching PHP capability names).
 * @returns {boolean}
 */
function currentUserCan( capability ) {
	const caps = getCapabilities();
	return caps[ capability ] === true;
}

// Expose module API on a single namespace object so each module can import
// what it needs without polluting the global scope.
window.CommandBarData = {
	getStaticCommands,
	getSettings,
	getSetting,
	getI18n,
	__,
	getNonce,
	getRestBase,
	getCapabilities,
	currentUserCan,
};
