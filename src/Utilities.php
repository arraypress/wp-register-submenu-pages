<?php
/**
 * SubMenu Pages Registration Helper Functions
 *
 * @package     ArrayPress\WP\Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 */

declare( strict_types=1 );

use ArrayPress\WP\Register\SubMenuPages;

if ( ! function_exists( 'submenu_pages' ) ):
	/**
	 * Helper function to get SubMenuPages instance
	 *
	 * @param string $prefix      Unique prefix for the plugin
	 * @param string $parent_slug Parent menu slug
	 *
	 * @return SubMenuPages
	 */
	function submenu_pages( string $prefix, string $parent_slug ): SubMenuPages {
		return SubMenuPages::instance( $prefix, $parent_slug );
	}
endif;

if ( ! function_exists( 'register_submenu_pages' ) ):
	/**
	 * Helper function to register multiple submenu pages
	 *
	 * Example usage:
	 * ```php
	 * $pages = [
	 *     [
	 *         'menu_title' => 'Downloads',
	 *         'page_title' => 'Downloads',
	 *         'menu_slug'  => 'downloads', // Will become 'my-plugin-downloads'
	 *         'callback'   => function() { }
	 *     ],
	 *     [
	 *         'menu_title'    => 'Orders',
	 *         'page_title'    => 'Orders',
	 *         'menu_slug'     => 'orders', // Will become 'my-plugin-orders'
	 *         'capability'    => 'manage_options',
	 *         'callback'      => function() { },
	 *         'add_separator' => true
	 *     ]
	 * ];
	 *
	 * // Register pages with a unique plugin prefix
	 * register_submenu_pages( 'my-plugin', 'edit.php?post_type=download', $pages );
	 * ```
	 *
	 * @param string $prefix      Unique prefix for the plugin
	 * @param string $parent_slug Parent menu slug
	 * @param array  $items       Array of menu items
	 *
	 * @return SubMenuPages|WP_Error SubMenuPages instance or WP_Error on failure
	 */
	function register_submenu_pages( string $prefix, string $parent_slug, array $items = [] ) {
		return SubMenuPages::register( $prefix, $parent_slug, $items );
	}
endif;