<?php

namespace Tarosky\OpenHour;


use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Formatter singleton
 *
 * @package tsoh
 */
class Formatter extends Singleton {


	/**
	 * Add second string for MySQL usage.
	 *
	 * @param string $time
	 *
	 * @return string
	 */
	public function time2my( $time ) {
		return $time . ':00';
	}

	/**
	 * Convert MySQL time format to non-second.
	 *
	 * @param string $mysql_time
	 *
	 * @return string
	 */
	public function my2time( $mysql_time ) {
		return preg_replace( '/:00$/', '', $mysql_time );
	}

	/**
	 * Convert time to comparable format
	 *
	 * @param string $time HH:ii format
	 *
	 * @return string
	 */
	public function make_time_comparable( $time ) {
		$time = strval( $time );

		return str_replace( ':', '.', $time );
	}

	/**
	 * Normalize day format
	 *
	 * @param string $string
	 *
	 * @return bool|int
	 */
	public function normalize_day( $string = '' ) {
		$string = (string) $string;
		if ( '' === $string ) {
			return false;
		}
		switch ( strtolower( $string ) ) {
			case 'monday':
			case 'mon':
			case '0':
				return 0;
				break;
			case 'tuesday':
			case 'tue':
			case '1':
				return 1;
				break;
			case 'wednesday':
			case 'wed':
			case '2':
				return 2;
				break;
			case 'thursday':
			case 'thu':
			case '3':
				return 3;
				break;
			case 'friday':
			case 'fri':
			case '4':
				return 4;
				break;
			case 'saturday':
			case 'sat':
			case '5':
				return 5;
				break;
			case 'sunday':
			case 'sun':
			case '6':
				return 6;
				break;
			default:
				return false;
				break;
		}
	}
}
