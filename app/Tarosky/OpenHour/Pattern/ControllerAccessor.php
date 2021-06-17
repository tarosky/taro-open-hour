<?php

namespace Tarosky\OpenHour\Pattern;


use Tarosky\OpenHour\Formatter;
use Tarosky\OpenHour\Model;
use Tarosky\OpenHour\Places;

/**
 * Trait ControllerAccessor
 *
 * @package tsoh
 * @property Model     $model
 * @property Formatter $formatter
 * @property Places    $places
 * @property \wpdb     $db
 */
trait ControllerAccessor {

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'places':
				return Places::instance();
			case 'model':
				return Model::instance();
			case 'formatter':
				return Formatter::instance();
			case 'db':
				global $wpdb;
				return $wpdb;
			default:
				return null;
				break;
		}
	}

}
