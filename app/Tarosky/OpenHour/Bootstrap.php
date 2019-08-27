<?php
namespace Tarosky\OpenHour;

use Tarosky\OpenHour\MetaBoxes\LocationMetaBox;
use Tarosky\OpenHour\MetaBoxes\OpenHourMetaBox;
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
	}
}
