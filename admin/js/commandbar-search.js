/**
 * CommandBar — Search module
 *
 * Provides fuzzy matching for static commands and orchestrates REST API calls
 * for dynamic results (posts, pages, users, plugins). All network calls use
 * the WordPress REST API with a proper nonce header.
 *
 * Search prefix conventions (inspired by VS Code / Linear):
 *   @query  — search users only
 *   >query  — search settings pages
 *   +query  — search installed plugins
 *   (none)  — fuzzy-match static commands + search posts/pages
 *
 * @package CommandBar
 * @since   1.0.0
 */

'use strict';

/* global CommandBarData */

/**
 * In-memory cache for REST API results within the current page session.
 * Keyed by the full request URL string.
 *
 * @type {Map<string, Array>}
 */
const _apiCache = new Map();

/**
 * Pending debounce timer ID for REST API calls.
 *
 * @type {number|null}
 */
let _debounceTimer = null;

/** Debounce delay in ms before firing a REST API request. */
const API_DEBOUNCE_MS = 200;

// ---------------------------------------------------------------------------
// Fuzzy matching
// ---------------------------------------------------------------------------

/**
 * Compute a relevance score for a command against a search query.
 *
 * Scoring strategy (higher = more relevant):
 *  - Exact title match:           100
 *  - Title starts with query:      80
 *  - Title contains query:         60
 *  - Keyword exact match:          50
 *  - Keyword starts with query:    40
 *  - Keyword contains query:       30
 *  - Every character of the query appears in the title (fuzzy): 10
 *
 * @since 1.0.0
 *
 * @param {import('./commandbar-data').CBCommand} command - Command object.
 * @param {string} query - Lowercase search query string.
 * @returns {number} Relevance score (0 = no match).
 */
function _scoreCommand( command, query ) {
	const title   = ( command.title || '' ).toLowerCase();
	const desc    = ( command.description || '' ).toLowerCase();
	const keywords = Array.isArray( command.keywords )
		? command.keywords.map( ( k ) => k.toLowerCase() )
		: [];

	if ( title === query )           return 100;
	if ( title.startsWith( query ) ) return 80;
	if ( title.includes( query ) )   return 60;

	if ( desc.includes( query ) )    return 55;

	for ( const keyword of keywords ) {
		if ( keyword === query )           return 50;
		if ( keyword.startsWith( query ) ) return 40;
		if ( keyword.includes( query ) )   return 30;
	}

	// Character-by-character fuzzy check on title.
	let charIndex = 0;
	for ( let i = 0; i < title.length && charIndex < query.length; i++ ) {
		if ( title[ i ] === query[ charIndex ] ) {
			charIndex++;
		}
	}
	if ( charIndex === query.length ) return 10;

	return 0;
}

/**
 * Filter and rank static commands against a query string.
 *
 * @since 1.0.0
 *
 * @param {string} query - Raw search query (may include prefix characters).
 * @returns {import('./commandbar-data').CBCommand[]} Sorted, filtered commands.
 */
function searchStaticCommands( query ) {
	if ( ! query || query.trim() === '' ) {
		return [];
	}

	const q        = query.toLowerCase().trim();
	const commands = CommandBarData.getStaticCommands();
	const scored   = [];

	for ( const command of commands ) {
		const score = _scoreCommand( command, q );
		if ( score > 0 ) {
			scored.push( { command, score } );
		}
	}

	// Sort by descending score, then alphabetically by title as a tie-breaker.
	scored.sort( ( a, b ) => {
		if ( b.score !== a.score ) return b.score - a.score;
		return ( a.command.title || '' ).localeCompare( b.command.title || '' );
	} );

	return scored.map( ( entry ) => entry.command );
}

// ---------------------------------------------------------------------------
// REST API search
// ---------------------------------------------------------------------------

/**
 * Determine the search type and clean query string from the raw input value.
 *
 * @since 1.0.0
 *
 * @param {string} raw - Raw input value.
 * @returns {{ type: string, query: string }}
 */
function parseSearchInput( raw ) {
	const trimmed = raw.trim();

	if ( trimmed.startsWith( '@' ) ) {
		return { type: 'users', query: trimmed.slice( 1 ).trim() };
	}
	if ( trimmed.startsWith( '>' ) ) {
		return { type: 'settings', query: trimmed.slice( 1 ).trim() };
	}
	if ( trimmed.startsWith( '+' ) ) {
		return { type: 'plugins', query: trimmed.slice( 1 ).trim() };
	}

	return { type: 'all', query: trimmed };
}

/**
 * Perform a REST API search request with in-memory caching and nonce auth.
 *
 * @since 1.0.0
 *
 * @param {string} query  - Sanitised search term (minimum 2 chars).
 * @param {string} type   - Search type: 'all' | 'posts' | 'pages' | 'users' | 'plugins'.
 * @returns {Promise<Array>} Array of result objects from the server.
 */
async function fetchApiResults( query, type ) {
	const restBase = CommandBarData.getRestBase();
	const nonce    = CommandBarData.getNonce();

	if ( ! restBase || ! nonce || query.length < 2 ) {
		return [];
	}

	const url = `${restBase}/search?q=${encodeURIComponent( query )}&type=${encodeURIComponent( type )}`;

	// Return cached result if available.
	if ( _apiCache.has( url ) ) {
		return _apiCache.get( url );
	}

	try {
		const response = await fetch( url, {
			method:  'GET',
			headers: {
				'X-WP-Nonce': nonce,
				'Accept':     'application/json',
			},
			credentials: 'same-origin',
		} );

		if ( ! response.ok ) {
			return [];
		}

		const json = await response.json();
		const data = ( json && json.success && Array.isArray( json.data ) ) ? json.data : [];

		_apiCache.set( url, data );
		return data;

	} catch {
		// Network error — return empty gracefully.
		return [];
	}
}

/**
 * Search settings pages from the built-in static index.
 *
 * This avoids a network round-trip because all settings destinations
 * are already available in the static command list.
 *
 * @since 1.0.0
 *
 * @param {string} query - Search term.
 * @returns {import('./commandbar-data').CBCommand[]}
 */
function searchSettingsCommands( query ) {
	if ( ! query ) return [];

	const q        = query.toLowerCase().trim();
	const commands = CommandBarData.getStaticCommands();

	return commands.filter( ( cmd ) => {
		return (
			cmd.group === 'Settings' ||
			( Array.isArray( cmd.keywords ) &&
				cmd.keywords.some( ( k ) => k.toLowerCase().includes( 'settings' ) ) )
		) && _scoreCommand( cmd, q ) > 0;
	} );
}

/**
 * Run a debounced search and call the provided callback with results.
 *
 * For queries 0–1 characters long or starting with > (settings prefix),
 * only static results are returned (no network request).
 * For all other queries ≥ 2 chars, dynamic API results are fetched after
 * the debounce delay.
 *
 * @since 1.0.0
 *
 * @param {string}   rawQuery  - Raw value from the search input.
 * @param {Function} onResults - Callback invoked with (staticResults, dynamicResults, isPending).
 */
function search( rawQuery, onResults ) {
	const { type, query } = parseSearchInput( rawQuery );

	// Always cancel any pending API debounce.
	if ( _debounceTimer !== null ) {
		clearTimeout( _debounceTimer );
		_debounceTimer = null;
	}

	// ── Settings prefix: static only ──
	if ( type === 'settings' ) {
		const results = searchSettingsCommands( query );
		onResults( results, [], false );
		return;
	}

	// ── Short query: static match only ──
	if ( query.length < 2 ) {
		const results = searchStaticCommands( query );
		onResults( results, [], false );
		return;
	}

	// ── Static results are instant ──
	let staticResults;
	if ( type === 'users' || type === 'plugins' ) {
		// For @ and + prefixes, skip static commands — go straight to API.
		staticResults = [];
	} else {
		staticResults = searchStaticCommands( query );
	}

	// Signal that dynamic results are loading.
	onResults( staticResults, [], true );

	// ── Dynamic results: debounced API call ──
	_debounceTimer = setTimeout( async () => {
		_debounceTimer = null;
		const apiType    = type === 'all' ? 'all' : type;
		const dynamic    = await fetchApiResults( query, apiType );
		onResults( staticResults, dynamic, false );
	}, API_DEBOUNCE_MS );
}

/**
 * Clear the in-memory API result cache (e.g., after a relevant action).
 *
 * @since 1.0.0
 */
function clearApiCache() {
	_apiCache.clear();
}

// Expose module API.
window.CommandBarSearch = {
	search,
	searchStaticCommands,
	parseSearchInput,
	clearApiCache,
};
