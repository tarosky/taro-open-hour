Business Places
==================================

Contributors: tarosky,Takahashi_Fumiki, tswallie  
Tags: business-hours, opening-hours, local-business, structured-data, google-maps  
Requires at least: 6.6  
Requires PHP: 7.4  
Tested up to: 7.1  
Stable tag: nightly  
License: GPLv3 or later  
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

Manage business places and opening hours, show time tables and Google Maps via widgets or shortcodes, and output LocalBusiness JSON-LD.

## Description

This plugin adds business places and opening hours to your WordPress site.
Formerly known as "**Taro Open Hour**".

* Google Maps embed supported.
* JSON-LD (Schema.org LocalBusiness) structured data supported.

### Case Study

#### Case 1

If your site is for your bookstore, add your store's location as a business place.

The location and opening hours are available via widgets.

#### Case 2

If your site is a database of bouldering gyms, choose a post type to be treated as a location.

Each single page will have its own place and opening hour information.

### How to display

#### Blocks

In the block editor, you can use "Open Hour" and "Business Place" blocks.
Choose a place in the block settings. If empty, the current post or the site location is used.

#### Widgets

You can use widgets for opening hours and business locations.

#### Shortcodes

You can use the shortcode `[open-hour]` for the time table. If you are a theme developer,
just use the `tsoh_the_timetable()` function.

For business places, you can use `[business-place post_id='10']`.
The attribute `post_id` can be omitted and its default value is the current post.

### Acknowledgements

* Banner images are a derivative of the work of the Geospatial Information Authority of Japan.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/taro-open-hour` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Go to `Settings > Business Places` and set it up.

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

If you return `false` from the filter hook, no style will be loaded.

### Change table markup

The table's template is located at `taro-open-hour/templates/time-table.php`.
Copy it to `your-theme/template-part/tsoh/time-table.php` and change the markup.

Of course, you can change the template path with a filter hook.

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

### How can I display opening hours or places with a block theme?

Block themes have no widget areas. Add a Shortcode block and enter `[open-hour]` for the time table or `[business-place]` for the place information. Both accept the `post_id` attribute (default: the current post), e.g. `[business-place post_id='10']`.

### How can I contribute?

Please create an issue on [GitHub](https://github.com/tarosky/taro-open-hour/issues).

## Screenshots

1. Time table displayed on a single page with a shortcode.
2. You can enter time shifts with a meta box.
3. You can choose post types, default time shifts and default open days. Good for businesses with several branches.
4. Widgets available: an opening hours widget and a location widget.

## Changelog

### 2.2.1

* Map iframe is now `loading="lazy"`
* Fix admin link.

### 2.2.0

* Add widgets for place and time table.
* Drop support for WordPress 4.8 and below.

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
