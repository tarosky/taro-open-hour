Taro Open Hour
==================================

Contributors: tarosky,Takahashi_Fumiki
Tags: custom field  
Requires at least: 4.7.0  
Tested up to: 4.7.5  
Stable tag: 1.0.0  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A WordPress plugin for Open Hour.

## Description

This plugin displays open hour table on single page.

### How to display

You can use shortcode `[open-hour]` for it. If you are a theme developer,
just use `tsoh_load_style` function.

This plugin is hosted on [Github](https://github.com/tarosky/taro-open-hour/), any Pull Requests are welcomed!

## Installation

1. Upload the plugin files to the `/wp-content/plugins/taro-open-hour` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Go to `Setting > Open Hour` and set it up.

## Customization

Here is a list of customizations.

### Change Style

If you have `tsoh-style.css` in your theme folder, it will be used.
Child theme supported.

We also have filter hook `tsoh_stylesheet`. Below is the example to change css url.

```
<?php
// Change css path.
add_filter('tsoh_stylesheet', function($style){
    $style = [
        'url'     => get_stylesheed_directory_uri() . '/assets/css/table.css',
        'version' => wp_get_theme()->get('Version'),
    ];
    return $style;
});
```

If you returns `false` on filter hook, no style will be loaded.

### Change table markup

Table's template is located at `taro-open-hour/templates/time-table.php`.
Copy it to `your-theme/template-part/tsoh/time-table.php ` and change markups.

## Frequently Asked Questions

### How to make reqeust?

Please make issue at [github](https://github.com/tarosky/taro-open-hour/issues).


## Screenshots

1. Time table displayed on single page with short code.
2. You can enter time shift with metabox.
3. You can choose post types, default time shift and default open day. Good for business with several branches.

## Changelog

### 1.0.0

* Initial release. 
