<?php
/**
 * Class SubMenuPages
 *
 * Manages the registration and display of WordPress admin submenu pages with support for
 * visual separators. This class provides a fluent interface for adding submenu pages
 * and separators, with automatic handling of menu positioning and styling.
 *
 * @package     ArrayPress\WP\Register
 * @since       1.0.0
 * @author      ArrayPress
 * @copyright   Copyright (c) 2024, ArrayPress Limited
 * @license     GPL2+
 */

declare( strict_types=1 );

namespace ArrayPress\WP\Register;

defined( 'ABSPATH' ) || exit;

use WP_Error;

class SubMenuPages {

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 * @var self|null
	 */
	private static ?self $instance = null;

	/**
	 * Parent menu slug.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private string $parent_slug;

	/**
	 * Registered menu items.
	 *
	 * @since 1.0.0
	 * @var array Array of menu item configurations.
	 */
	private array $items = [];

	/**
	 * Menu slugs that should have separators.
	 *
	 * @since 1.0.0
	 * @var array Array of menu slugs after which to add separators.
	 */
	private array $separators = [];

	/**
	 * Core WordPress menu slugs and their base identifiers.
	 *
	 * Maps WordPress core menu slugs to their base identifier strings used in
	 * CSS selectors and menu construction.
	 *
	 * @since 1.0.0
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
	 * @return self Instance of this class.
	 * @since 1.0.0
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
	}

	/**
	 * Initialize submenu pages.
	 *
	 * @param string $parent_slug Parent menu slug
	 * @param array  $items       Optional array of menu items
	 *
	 * @return WP_Error|true
	 * @since 1.0.0
	 */
	public function init( string $parent_slug, array $items = [] ) {
		if ( empty( $parent_slug ) ) {
			return new WP_Error( 'invalid_parent_slug', __( 'Parent slug cannot be empty.', 'arraypress' ) );
		}

		$this->parent_slug = $parent_slug;

		if ( ! empty( $items ) ) {
			$this->add_pages( $items );
		}

		$this->initialize_hooks();

		return true;
	}

	/**
	 * Add multiple submenu pages.
	 *
	 * @param array $items Submenu page configurations
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function add_pages( array $items ): self {
		foreach ( $items as $item ) {
			if ( isset( $item['separator'] ) && $item['separator'] === true ) {
				$this->add_separator();
				continue;
			}
			$this->add_page( $item );
		}

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
	 * @return self
	 * @since 1.0.0
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

		$args          = wp_parse_args( $args, $defaults );
		$this->items[] = $args;

		if ( $args['add_separator'] ) {
			$this->separators[] = $args['menu_slug'];
		}

		return $this;
	}

	/**
	 * Add separator after last item.
	 *
	 * @return self
	 * @since 1.0.0
	 */
	public function add_separator(): self {
		if ( ! empty( $this->items ) ) {
			$lastItem = end( $this->items );
			if ( isset( $lastItem['menu_slug'] ) ) {
				$this->separators[] = $lastItem['menu_slug'];
			}
		}

		return $this;
	}

	/**
	 * Register all submenu pages.
	 *
	 * @since 1.0.0
	 */
	private function initialize_hooks(): void {
		add_action( 'admin_menu', [ $this, 'register_pages' ] );
		add_action( 'admin_head', [ $this, 'add_separator_styles' ] );
	}

	/**
	 * Register pages with WordPress.
	 *
	 * @since 1.0.0
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
	}

	/**
	 * Add separator styles.
	 *
	 * @since 1.0.0
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
	 * Get menu base identifier.
	 *
	 * @return string
	 * @since 1.0.0
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
	 * @param string $parent_slug Parent menu slug
	 * @param array  $items       Optional array of menu items
	 *
	 * @return WP_Error|self Instance on success, WP_Error on failure
	 * @since 1.0.0
	 */
	public static function register( string $parent_slug, array $items = [] ) {
		$instance = self::instance();
		$result   = $instance->init( $parent_slug, $items );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return $instance;
	}

}