<?php
/**
 * Registers all actions and filters for the plugin.
 *
 * Maintains a list of all hooks that are registered throughout the plugin,
 * and registers them with the WordPress API. Call the run() function to
 * execute the list of actions and filters.
 *
 * @package    CommandBar
 * @subpackage CommandBar/includes
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CommandBar_Loader
 *
 * Handles registration and execution of all WordPress hooks.
 *
 * @since 1.0.0
 */
class CommandBar_Loader {

	/**
	 * Array of actions registered with WordPress.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $actions Collection of actions to register.
	 */
	protected $actions = array();

	/**
	 * Array of filters registered with WordPress.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $filters Collection of filters to register.
	 */
	protected $filters = array();

	/**
	 * Add a new action to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress action.
	 * @param object $component     A reference to the instance of the object.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. Default 10.
	 * @param int    $accepted_args Optional. Default 1.
	 */
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a new filter to the collection to be registered with WordPress.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook          The name of the WordPress filter.
	 * @param object $component     A reference to the instance of the object.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      Optional. Default 10.
	 * @param int    $accepted_args Optional. Default 1.
	 */
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * A utility function for registering actions and hooks into a single collection.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param array  $hooks         The collection of hooks being registered (actions or filters).
	 * @param string $hook          The name of the WordPress filter or action.
	 * @param object $component     A reference to the instance of the object.
	 * @param string $callback      The name of the function definition on the $component.
	 * @param int    $priority      The priority at which the function should be fired.
	 * @param int    $accepted_args The number of arguments that should be passed to the $callback.
	 * @return array The collection of hooks with the new hook added.
	 */
	private function add( array $hooks, string $hook, object $component, string $callback, int $priority, int $accepted_args ): array {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register the filters and actions with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run(): void {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
