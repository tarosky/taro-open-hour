<?php
/*
Plugin Name: Business Places
Plugin URI: https://wordpress.org/plugin/taro-open-hour
Description: Add place and open hour to any post type.
Author: Tarosky INC
Version: nightly
Requires at least: 5.9
Requires PHP: 7.4
Author URI: https://tarosky.co.jp
Text Domain: taro-open-hour
Domain Path: /language
*/

// Avoid direct loading.
defined( 'ABSPATH' ) or die();

// Register assets.
add_action( 'init', 'tsoh_register_assets' );

// Register bootstrap.
add_action( 'plugins_loaded', 'tsoh_plugins_loaded' );

/**
 * Plugin entry point
 *
 * @internal
 * @package tsoh
 */
function tsoh_plugins_loaded() {
	// Check PHP version
	if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
		add_action( 'admin_notices', 'tsoh_php_low' );
	} else {
		// Version check O.K.
		// Load function file
		$dir = __DIR__ . '/includes';
		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( preg_match( '#^[^._].*\.php$#u', $file ) ) {
					require $dir . '/' . $file;
				}
			}
		}
		// Load bootstrap
		$auto_loader = __DIR__ . '/vendor/autoload.php';
		if ( file_exists( $auto_loader ) ) {
			require $auto_loader;
			call_user_func( array( 'Tarosky\\OpenHour\\Bootstrap', 'instance' ) );
		} else {
			trigger_error( __( 'Auto loader file is missing. You should run composer install.', 'taro-open-hour' ), E_USER_WARNING );
		}
	}
}

/**
 * PHP version warning
 *
 * @internal
 * @package tsoh
 */
function tsoh_php_low() {
	$message = sprintf(
		// translators: %1$s is current PHP version, %2$s is required version.
		__( '[ERROR] Business Places doesn\'t work because your PHP version %1$s is too low. PHP %2$s and over is required.', 'taro-open-hour' ),
		phpversion(),
		'5.6.0'
	);
	printf( '<div class="error"><p>%s</p></div>', $message );
}

/**
 * Register all assets from wp-dependencies.json.
 *
 * @return void
 */
function tsoh_register_assets() {
	$json = __DIR__ . '/wp-dependencies.json';
	if ( ! file_exists( $json ) ) {
		return;
	}
	$dependencies = json_decode( file_get_contents( $json ), true );
	if ( empty( $dependencies ) ) {
		return;
	}
	$base = trailingslashit( plugin_dir_url( __FILE__ ) );
	foreach ( $dependencies as $dep ) {
		if ( empty( $dep['path'] ) ) {
			continue;
		}
		$url = $base . $dep['path'];
		switch ( $dep['ext'] ) {
			case 'css':
				wp_register_style( $dep['handle'], $url, $dep['deps'], $dep['hash'], $dep['media'] );
				break;
			case 'js':
				$footer = array( 'in_footer' => $dep['footer'] );
				if ( in_array( $dep['strategy'], array( 'defer', 'async' ), true ) ) {
					$footer['strategy'] = $dep['strategy'];
				}
				wp_register_script( $dep['handle'], $url, $dep['deps'], $dep['hash'], $footer );
				if ( in_array( 'wp-i18n', $dep['deps'], true ) ) {
					wp_set_script_translations( $dep['handle'], 'taro-open-hour' );
				}
				break;
		}
	}
}

/**
 * Get plugin version
 *
 * @package tsoh
 * @return string
 */
function tsoh_version() {
	static $data = null;
	if ( is_null( $data ) ) {
		$data = get_file_data(
			__FILE__,
			array(
				'version' => 'Version',
			)
		);
	}
	return $data['version'];
}

/**
 * Get template dir
 *
 * @package tsoh
 * @param string $file
 *
 * @return string
 */
function tsoh_template( $file ) {
	$template_path = trailingslashit( __DIR__ . '/templates' ) . ltrim( $file, '/' );
	if ( file_exists( $template_path ) ) {
		return $template_path;
	} else {
		return '';
	}
}

/**
 * Get plugin asset dir URL
 *
 * @package tsoh
 * @param string $path
 *
 * @return string
 */
function tsoh_asset( $path ) {
	$base = trailingslashit( plugin_dir_url( __FILE__ ) . 'dist' );
	return $base . ltrim( $path, '/' );
}
