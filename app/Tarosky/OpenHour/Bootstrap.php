<?php
namespace Tarosky\OpenHour;

use Tarosky\OpenHour\Pattern\Singleton;


/**
 * Bootstrap class
 *
 * @package tsoh
 */
class Bootstrap extends Singleton {

	/**
	 * Initializer
	 *
	 * @param array $settings
	 */
	public function init( array $settings = [] ) {
		// Create DB if required.
		$model = Model::instance();
		if ( $model->needs_update() ) {
			$model->activate();
		}
		// Register admin screen
		Admin::instance();
		// Register meta box
		MetaBox::instance();
		// Places instance.
		Places::instance();
	}
}
