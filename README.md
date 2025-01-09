# WordPress SubMenu Pages Manager

A PHP library for managing WordPress submenu pages with support for separators and hierarchical organization.

## Features

- 🚀 Simple submenu page registration
- 🔄 Support for menu separators
- 📝 Hierarchical menu organization
- 🎯 Position management
- 🔧 Core menu integration
- 🔒 Plugin-specific prefixing
- 🐞 Debug logging support

## Requirements

- PHP 7.4 or higher
- WordPress 6.7.1 or higher

## Installation

Install via composer:

```bash
composer require arraypress/wp-register-submenu-pages
```

## Basic Usage

Using the helper function:

```php
// Register submenu pages
$pages = [
	[
		'menu_title' => 'Settings',
		'page_title' => 'Settings',
		'menu_slug'  => 'settings', // Will become 'my-plugin-settings'
		'callback'   => 'display_settings'
	],
	[
		'separator' => true // Adds a separator
	],
	[
		'menu_title' => 'Reports',
		'page_title' => 'Reports',
		'menu_slug'  => 'reports', // Will become 'my-plugin-reports'
		'callback'   => 'display_reports'
	]
];

// Register pages with unique prefix and parent slug
register_submenu_pages( 'my-plugin', 'edit.php?post_type=download', $pages );
```

Using the class directly:

```php
use ArrayPress\WP\Register\SubMenuPages;

// Initialize with prefix and parent slug
$submenu = SubMenuPages::instance( 'my-plugin', 'edit.php?post_type=download' );

$submenu->init( 'my-plugin', 'edit.php?post_type=download' )
        ->add_page( [
	        'menu_title' => 'Settings',
	        'page_title' => 'Settings',
	        'menu_slug'  => 'settings', // Will become 'my-plugin-settings'
	        'callback'   => 'display_settings'
        ] )
        ->add_separator()
        ->add_page( [
	        'menu_title' => 'Reports',
	        'page_title' => 'Reports',
	        'menu_slug'  => 'reports', // Will become 'my-plugin-reports'
	        'callback'   => 'display_reports'
        ] );
```

## Configuration Options

### Page Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| menu_title | string | '' | The text shown in the menu |
| page_title | string | '' | The text shown in the browser title |
| capability | string | 'manage_options' | Required user capability |
| menu_slug | string | '' | Unique identifier (will be prefixed) |
| callback | callable | '' | Function to output page content |
| add_separator | boolean | false | Add separator after this item |
| position | int/null | null | Menu position |

## Menu Slugs and Prefixing

The library automatically prefixes menu slugs with your plugin's prefix for proper isolation:

```php
// This registration...
register_submenu_pages( 'my-plugin', 'edit.php?post_type=download', [
	[
		'menu_slug' => 'settings',
		// ... other options
	]
] );

// Results in menu slug: 'my-plugin-settings'
```

## Adding Separators

Separators can be added in two ways:

```php
// Method 1: Using add_separator flag
register_submenu_pages( 'my-plugin', 'edit.php?post_type=download', [
	[
		'menu_title'    => 'Settings',
		'page_title'    => 'Settings',
		'menu_slug'     => 'settings',
		'callback'      => 'display_settings',
		'add_separator' => true // Adds separator after this item
	],
	[
		'menu_title' => 'Reports',
		'page_title' => 'Reports',
		'menu_slug'  => 'reports',
		'callback'   => 'display_reports'
	]
] );

// Method 2: Using separator item
register_submenu_pages( 'my-plugin', 'edit.php?post_type=download', [
	[
		'menu_title' => 'Settings',
		'page_title' => 'Settings',
		'menu_slug'  => 'settings',
		'callback'   => 'display_settings'
	],
	[ 'separator' => true ],
	[
		'menu_title' => 'Reports',
		'page_title' => 'Reports',
		'menu_slug'  => 'reports',
		'callback'   => 'display_reports'
	]
] );
```

## Debug Logging

When WP_DEBUG is enabled, the library logs important operations:
- Page registration
- Separator addition
- Initialization status
- Error conditions

## Contributing

Contributions welcome! Please open an issue first to discuss changes.

## License

GPL2+ License. See LICENSE file for details.

## Support

Use the [issue tracker](https://github.com/arraypress/wp-register-submenu-pages/issues)