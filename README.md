Business Places
==================================

Contributors: tarosky,Takahashi_Fumiki
Tags: business,place,open-hour,widget  
Requires at least: 4.7.0  
Requires PHP: 5.6  
Tested up to: 5.2.3  
Stable tag: 2.1.0  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

A WordPress plugin for business place and open hour.

## Description

This plugin add places and open hours to your WordPress site.
Formerly known as "**Taro Open Hour**".

* Google Map supprted.
* JSON-LD supported.

### Case Study

#### Case 1

If your site is for your book store, add site location as your business place.

These location and open hour are available via widget.

#### Case 2

If your site is bouldering shop database, choose post type to be treated as location.

Each single page have place and open hour information.

### How to display

#### Widgets

You can use widget for open our and business location.

#### Shortcodes

You can use shortcode `[open-hour]` for time table. If you are a theme developer,
just use `tsoh_the_timetable()` function.

For business places, you can use `[business-place post_id='10']`.
The attribute `post_id` can be omitted and it's default value is current post.

### Acknowledgements

* Banner images is a deliverative of the work of the Geospatial Information Authority of Japan.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/taro-open-hour` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Go to `Setting > Business Places` and set it up.

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
        'url'     => get_stylesheet_directory_uri() . '/assets/css/table.css',
        'version' => wp_get_theme()->get('Version'),
    ];
    return $style;
});
```

If you returns `false` on filter hook, no style will be loaded.

### Change table markup

Table's template is located at `taro-open-hour/templates/time-table.php`.
Copy it to `your-theme/template-part/tsoh/time-table.php ` and change markups.

Of course, you can change template path with filter hook.

```
// e.g. If post type is event, change template from default.
add_filter( 'tsoh_timetable_template_path', function( $path, $post ) {
    if ( 'event' == $post->post_type ) {
        $path = get_template_directory() . '/templates/yours/event.php';
    }
    return $path;
}, 10, 2 );
```

## Frequently Asked Questions

### How can I contribute to?

Please make issue at [Github](https://github.com/tarosky/taro-open-hour/issues).

## Screenshots

1. Time table displayed on single page with short code.
2. You can enter time shift with metabox.
3. You can choose post types, default time shift and default open day. Good for business with several branches.
4. Widgets available. Open hour widget and location widget.

## Changelog

### 2.1.0

* Add shortcode `business-place`.
* Add filter and action hooks.

### 2.0.1

* Bugfix: version number changed.

### 2.0.0

* Change plugin name.
* Add location feature.
* Add widgets.

### 1.0.0

* Initial release. 
