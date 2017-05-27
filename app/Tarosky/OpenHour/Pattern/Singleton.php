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
	protected static $instances = [];

	/**
	 * Singleton constructor.
	 *
	 * @param array $settings
	 */
	final private function __construct( array $settings = [] ) {
		$this->init( $settings );
	}

	/**
	 * Override this function if do something in constructor.
	 *
	 * @param array $settings
	 */
	protected function init( array $settings = [] ) {}

	/**
	 * Get instance
	 *
	 * @param array $settings
	 *
	 * @return static
	 */
	final public static function instance( array $settings = [] ) {
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name( $settings );
		}
		return self::$instances[ $class_name ];
	}
}
