<?php
/**
 * Class SubMenuPages
 *
 * Manages the registration and display of WordPress admin submenu pages with support for
 * visual separators. This class provides a fluent interface for adding submenu pages
 * and separators, with automatic handling of menu positioning and styling.
 *
 * @package     ArrayPress\WP\Register
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 * @version     1.0.0
 */

declare( strict_types=1 );

namespace ArrayPress\WP\Register;

defined( 'ABSPATH' ) || exit;

use WP_Error;

class SubMenuPages {

	/**
	 * Collection of class instances
	 *
	 * @var self[] Array of SubMenuPages instances
	 */
	private static array $instances = [];

	/**
	 * Plugin prefix for this instance
	 *
	 * @var string
	 */
	private string $prefix = '';

	/**
	 * Parent menu slug.
	 *
	 * @var string
	 */
	private string $parent_slug = '';

	/**
	 * Registered menu items.
	 *
	 * @var array Array of menu item configurations.
	 */
	private array $items = [];

	/**
	 * Menu slugs that should have separators.
	 *
	 * @var array Array of menu slugs after which to add separators.
	 */
	private array $separators = [];

	/**
	 * Debug mode status
	 *
	 * @var bool
	 */
	private bool $debug = false;

	/**
	 * Core WordPress menu slugs and their base identifiers.
	 *
	 * Maps WordPress core menu slugs to their base identifier strings used in
	 * CSS selectors and menu construction.
	 *
	 * @var array<string, string>
	 */
	protected const CORE_MENU_SLUGS = [
		'index.php'   => 'dashboard',
		'upload.php'  => 'media',
		'edit.php'    => 'posts',
		'options.php' => 'settings',
		'tools.php'   => 'tools',
		'users.php'   => 'users',
		'plugins.php' => 'plugins',
		'themes.php'  => 'appearance',
	];

	/**
	 * Get instance of this class.
	 *
	 * @param string $prefix      Unique prefix for the plugin
	 * @param string $parent_slug Parent menu slug
	 *
	 * @return self Instance of this class.
	 */
	public static function instance( string $prefix, string $parent_slug ): self {
		$key = $prefix . '|' . $parent_slug;

		if ( ! isset( self::$instances[ $key ] ) ) {
			self::$instances[ $key ] = new self();
		}

		return self::$instances[ $key ];
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->debug = defined( 'WP_DEBUG' ) && WP_DEBUG;
	}

	/**
	 * Initialize submenu pages.
	 *
	 * @param string $prefix      Unique prefix for the plugin
	 * @param string $parent_slug Parent menu slug
	 * @param array  $items       Optional array of menu items
	 *
	 * @return WP_Error|true
	 */
	public function init( string $prefix, string $parent_slug, array $items = [] ) {
		if ( empty( $parent_slug ) ) {
			return new WP_Error( 'invalid_parent_slug', __( 'Parent slug cannot be empty.', 'arraypress' ) );
		}

		if ( empty( $prefix ) ) {
			return new WP_Error( 'invalid_prefix', __( 'Plugin prefix cannot be empty.', 'arraypress' ) );
		}

		$this->prefix      = $prefix;
		$this->parent_slug = $parent_slug;

		if ( ! empty( $items ) ) {
			$this->add_pages( $items );
		}

		$this->initialize_hooks();

		$this->log( sprintf( 'Initialized submenu pages for parent: %s with prefix: %s', $parent_slug, $prefix ) );

		return true;
	}

	/**
	 * Add multiple submenu pages.
	 *
	 * @param array $items Submenu page configurations
	 *
	 * @return self
	 */
	public function add_pages( array $items ): self {
		foreach ( $items as $item ) {
			if ( isset( $item['separator'] ) && $item['separator'] === true ) {
				$this->add_separator();
				continue;
			}
			$this->add_page( $item );
		}

		$this->log( sprintf( 'Added %d pages to %s', count( $items ), $this->parent_slug ) );

		return $this;
	}

	/**
	 * Add a submenu page.
	 *
	 * @param array   $args          {
	 *                               Submenu page configuration.
	 *
	 * @type string   $menu_title    Menu text
	 * @type string   $page_title    Browser title
	 * @type string   $capability    Required capability
	 * @type string   $menu_slug     Unique identifier
	 * @type callable $callback      Content output function
	 * @type bool     $add_separator Add separator after item
	 * @type int      $position      Menu order position
	 *                               }
	 *
	 * @return self
	 */
	public function add_page( array $args ): self {
		$defaults = [
			'menu_title'    => '',
			'page_title'    => '',
			'capability'    => 'manage_options',
			'menu_slug'     => '',
			'callback'      => '',
			'add_separator' => false,
			'position'      => null,
		];

		$args = wp_parse_args( $args, $defaults );

		// Ensure menu slug is unique per plugin
		if ( ! empty( $args['menu_slug'] ) ) {
			$args['menu_slug'] = $this->prefix . '-' . $args['menu_slug'];
		}

		$this->items[] = $args;

		if ( $args['add_separator'] ) {
			$this->separators[] = $args['menu_slug'];
		}

		$this->log( sprintf( 'Added page: %s', $args['menu_slug'] ) );

		return $this;
	}

	/**
	 * Add separator after last item.
	 *
	 * @return self
	 */
	public function add_separator(): self {
		if ( ! empty( $this->items ) ) {
			$lastItem = end( $this->items );
			if ( isset( $lastItem['menu_slug'] ) ) {
				$this->separators[] = $lastItem['menu_slug'];
				$this->log( sprintf( 'Added separator after: %s', $lastItem['menu_slug'] ) );
			}
		}

		return $this;
	}

	/**
	 * Register all submenu pages.
	 */
	public function register_pages(): void {
		foreach ( $this->items as $item ) {
			add_submenu_page(
				$this->parent_slug,
				$item['page_title'],
				$item['menu_title'],
				$item['capability'],
				$item['menu_slug'],
				$item['callback'],
				$item['position'] ?? null
			);
		}

		$this->log( sprintf( 'Registered %d pages with WordPress', count( $this->items ) ) );
	}

	/**
	 * Add separator styles.
	 */
	public function add_separator_styles(): void {
		if ( empty( $this->separators ) ) {
			return;
		}

		$menu_base = $this->get_menu_base();
		if ( empty( $menu_base ) ) {
			return;
		}

		$selectors = [];
		foreach ( $this->separators as $slug ) {
			$selector = strpos( $this->parent_slug, 'edit.php?post_type=' ) === 0
				? "#menu-posts-{$menu_base}"
				: "#" . sanitize_html_class( $menu_base );

			$selectors[] = "{$selector} li:not(:last-child) a[href$='page={$slug}']:after";
		}

		if ( ! empty( $selectors ) ) {
			$css = implode( ', ', $selectors ) . " {
                border-bottom: 1px solid hsla(0, 0%, 100%, .2);
                display: block;
                float: left;
                margin: 13px -15px 8px;
                content: '';
                width: calc(100% + 26px);
            }";

			printf( '<style>%s</style>', $css );
		}
	}

	/**
	 * Register hooks for menu pages.
	 */
	private function initialize_hooks(): void {
		add_action( 'admin_menu', [ $this, 'register_pages' ] );
		add_action( 'admin_head', [ $this, 'add_separator_styles' ] );
	}

	/**
	 * Get menu base identifier.
	 *
	 * @return string
	 */
	protected function get_menu_base(): string {
		if ( strpos( $this->parent_slug, 'edit.php?post_type=' ) === 0 ) {
			return str_replace( 'edit.php?post_type=', '', $this->parent_slug );
		}

		return self::CORE_MENU_SLUGS[ $this->parent_slug ] ?? sanitize_title( $this->parent_slug );
	}

	/**
	 * Create and initialize submenu pages.
	 *
	 * @param string $prefix      Unique prefix for the plugin
	 * @param string $parent_slug Parent menu slug
	 * @param array  $items       Optional array of menu items
	 *
	 * @return WP_Error|self Instance on success, WP_Error on failure
	 */
	public static function register( string $prefix, string $parent_slug, array $items = [] ) {
		$instance = self::instance( $prefix, $parent_slug );
		$result   = $instance->init( $prefix, $parent_slug, $items );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $instance;
	}

	/**
	 * Log debug message
	 *
	 * @param string $message Message to log
	 * @param array  $context Optional context
	 */
	protected function log( string $message, array $context = [] ): void {
		if ( $this->debug ) {
			error_log( sprintf(
				'[SubMenu Pages] [%s] %s %s',
				$this->prefix,
				$message,
				! empty( $context ) ? json_encode( $context ) : ''
			) );
		}
	}

}