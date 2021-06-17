<?php

namespace Tarosky\OpenHour\Pattern;

/**
 * Singleton Pattern.
 *
 * @package tsoh
 */
abstract class Singleton {

	/**
	 * @var array Instance holders
	 */
	private static $instances = array();

	/**
	 * Singleton constructor.
	 *
	 */
	final private function __construct() {
		$this->init();
	}

	/**
	 * Override this function if do something in constructor.
	 *
	 */
	protected function init() {}

	/**
	 * Get instance
	 *
	 * @param array $settings
	 *
	 * @return static
	 */
	final public static function instance() {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name();
		}
		return self::$instances[ $class_name ];
	}
}
