<?php
/**
 * SubMenu Pages Registration Helper
 *
 * @package     ArrayPress/WP/Register/SubMenuPages
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 * @author      David Sherlock
 */

declare( strict_types=1 );

namespace ArrayPress\WP\Register;

// Exit if accessed directly
use WP_Error;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( __NAMESPACE__ . '\\submenu_pages' ) ):
	/**
	 * Helper function to get SubMenuPages instance
	 *
	 * @return SubMenuPages
	 * @since 1.0.0
	 */
	function submenu_pages(): SubMenuPages {
		return SubMenuPages::instance();
	}
endif;

if ( ! function_exists( __NAMESPACE__ . '\\register_submenu_pages' ) ):
	/**
	 * Helper function to register multiple submenu pages
	 *
	 * Example usage:
	 * ```php
	 * $pages = [
	 *     [
	 *         'menu_title' => 'Downloads',
	 *         'page_title' => 'Downloads',
	 *         'menu_slug'  => 'edit.php?post_type=download',
	 *         'callback'   => function() { }
	 *     ],
	 *     [
	 *         'menu_title'    => 'Orders',
	 *         'page_title'    => 'Orders',
	 *         'menu_slug'     => 'edd-orders',
	 *         'callback'      => function() { },
	 *         'add_separator' => true
	 *     ]
	 * ];
	 *
	 * register_submenu_pages('edit.php?post_type=download', $pages);
	 * ```
	 *
	 * @param string $parent_slug Parent menu slug
	 * @param array  $items       Array of menu items
	 *
	 * @return SubMenuPages|WP_Error SubMenuPages instance or WP_Error on failure
	 * @since 1.0.0
	 */
	function register_submenu_pages( string $parent_slug, array $items = [] ) {
		return SubMenuPages::register( $parent_slug, $items );
	}
endif;