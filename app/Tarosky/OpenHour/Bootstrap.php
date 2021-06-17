<?php
namespace Tarosky\OpenHour;

use Tarosky\OpenHour\MetaBoxes\LocationMetaBox;
use Tarosky\OpenHour\MetaBoxes\OpenHourMetaBox;
use Tarosky\OpenHour\Pattern\Singleton;
use Tarosky\OpenHour\Services\MetaInfo;


/**
 * Bootstrap class
 *
 * @package tsoh
 */
class Bootstrap extends Singleton {

	/**
	 * Initializer
	 *
	 */
	public function init() {
		// Create DB if required.
		$model = Model::instance();
		if ( $model->needs_update() ) {
			$model->activate();
		}
		// Register admin screen
		Admin::instance();
		// Register meta box
		OpenHourMetaBox::instance();
		LocationMetaBox::instance();
		// Places instance.
		Places::instance();
		// Enable JSON-LD
		MetaInfo::instance();
		// Register widgets.
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		// Load style
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
		// Load all REST api.
		foreach ( scandir( __DIR__ . '/Rest' ) as $file ) {
			if ( ! preg_match( '/^([^._].*)\.php$/u', $file, $matches ) ) {
				continue;
			}
			$class_name = "Tarosky\\OpenHour\\Rest\\{$matches[1]}";
			if ( class_exists( $class_name ) ) {
				call_user_func( "{$class_name}::instance" );
			}
		}
	}

	/**
	 * Register all widgets.
	 */
	public function register_widgets() {
		$dir = __DIR__ . '/Widgets';
		if ( ! is_dir( $dir ) ) {
			return;
		}
		foreach ( scandir( $dir ) as $file ) {
			if ( ! preg_match( '#(.*)\.php$#u', $file, $matches ) ) {
				continue;
			}
			$class_name = 'Tarosky\\OpenHour\\Widgets\\' . $matches[1];
			if ( ! class_exists( $class_name ) ) {
				continue;
			}
			try {
				$reflection = new \ReflectionClass( $class_name );
				if ( ! $reflection->isSubclassOf( 'WP_Widget' ) ) {
					continue;
				}
				if ( $reflection->hasConstant( 'DUPLICATED' ) ) {
					// If class has duplicated flag, skip loading.
					continue;
				}
				register_widget( $class_name );
			} catch ( \Exception $e ) {
				continue;
			}
		}
	}

	/**
	 * Load styles.
	 */
	public function enqueue_scripts() {
		tsoh_load_style();
	}
}
