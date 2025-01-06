# WordPress SubMenu Pages Manager

A PHP library for managing WordPress submenu pages with support for separators and hierarchical organization.

## Features

- 🚀 Simple submenu page registration
- 🔄 Support for menu separators
- 📝 Hierarchical menu organization
- 🎯 Position management
- 🔧 Core menu integration

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
		'menu_slug'  => 'my-settings',
		'callback'   => 'display_settings'
	],
	[
		'separator' => true // Adds a separator
	],
	[
		'menu_title' => 'Reports',
		'page_title' => 'Reports',
		'menu_slug'  => 'my-reports',
		'callback'   => 'display_reports'
	]
];

// Register pages with a single function call
register_submenu_pages( 'edit.php?post_type=download', $pages );
```

Using the class directly:

```php
use ArrayPress\WP\Register\SubMenuPages;

// Initialize and register pages
$submenu = new SubMenuPages( 'edit.php?post_type=download' );

$submenu->add_page( [
	'menu_title' => 'Settings',
	'page_title' => 'Settings',
	'menu_slug'  => 'my-settings',
	'callback'   => 'display_settings'
] )
        ->add_separator()
        ->add_page( [
	        'menu_title' => 'Reports',
	        'page_title' => 'Reports',
	        'menu_slug'  => 'my-reports',
	        'callback'   => 'display_reports'
        ] )
        ->register();
```

## Configuration Options

### Page Options

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| menu_title | string | '' | The text shown in the menu |
| page_title | string | '' | The text shown in the browser title |
| capability | string | 'manage_options' | Required user capability |
| menu_slug | string | '' | Unique identifier for the page |
| callback | callable | '' | Function to output page content |
| add_separator | boolean | false | Add separator after this item |
| position | int/null | null | Menu position |

## Adding Separators

```php
// Using page configuration
register_submenu_pages( 'edit.php?post_type=download', [
	[
		'menu_title'    => 'Settings',
		'page_title'    => 'Settings',
		'menu_slug'     => 'my-settings',
		'callback'      => 'display_settings',
		'add_separator' => true // Adds separator after this item
	],
	[
		'menu_title' => 'Reports',
		'page_title' => 'Reports',
		'menu_slug'  => 'my-reports',
		'callback'   => 'display_reports'
	]
] );

// Or using separator item
register_submenu_pages( 'edit.php?post_type=download', [
	[
		'menu_title' => 'Settings',
		'page_title' => 'Settings',
		'menu_slug'  => 'my-settings',
		'callback'   => 'display_settings'
	],
	[ 'separator' => true ],
	[
		'menu_title' => 'Reports',
		'page_title' => 'Reports',
		'menu_slug'  => 'my-reports',
		'callback'   => 'display_reports'
	]
] );
```

## Contributing

Contributions welcome! Please open an issue first to discuss changes.

## License

GPL2+ License. See LICENSE file for details.

## Credits

Developed by ArrayPress Limited.

## Support

Use the [issue tracker](https://github.com/arraypress/wp-register-submenu-pages/issues)