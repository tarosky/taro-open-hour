<?php
/**
 * Utility functions of Taro Open Hour
 *
 * @package tsoh
 */

use Tarosky\OpenHour\Places;
use Tarosky\OpenHour\Services\MetaInfo;


/**
 * Detect
 *
 * @param string $post_type
 *
 * @return bool
 */
function tsoh_supported( $post_type ) {
	$post_types = (array) get_option( 'tsoh_post_types', array() );
	$post_types = array_merge( $post_types, Places::instance()->post_types );
	return false !== array_search( $post_type, $post_types, true );
}

/**
 * Get default value
 *
 * @param bool $raw If true, returns default value.
 *
 * @return string|array
 */
function tsoh_default( $raw = false ) {
	$default = get_option( 'tsoh_default_time' );
	if ( $raw ) {
		return $default;
	}
	if ( ! $default ) {
		$default = '10:00,20:00';
	}
	$rows = array();
	foreach ( preg_split( '#(\r|\n)#u', $default ) as $line ) {
		$line = array_filter(
			array_map( 'trim', explode( ',', trim( $line ) ) ),
			function ( $time ) {
				return preg_match( '#\\d{2}:\\d{2}#u', $time );
			}
		);
		if ( count( $line ) < 2 ) {
			continue;
		}
		$rows[] = $line;
	}

	/**
	 * tsoh_default_time
	 *
	 * @package tsoh
	 *
	 * @param array $times
	 */
	return apply_filters( 'tsoh_default_time', $rows );
}

/**
 * Detect if post has time table
 *
 *
 * @param int|WP_Post|null $post
 *
 * @return boolean
 */
function tsoh_has_timetable( $post = null ) {
	$post = get_post( $post );

	return $post ? \Tarosky\OpenHour\Model::instance()->has_time_table( $post->ID ) : false;
}

/**
 * Get current time condition
 *
 * @param bool $undefined_as_now Treat undefined as now.
 * @param bool $echo Default true. If false, output nothing.
 * @param WP_Query $query
 *
 * @return array
 */
function tsoh_current_time_condition( $undefined_as_now = false, $echo = true, WP_Query $query = null ) {
	global $haoh;
	if ( is_null( $query ) ) {
		global $wp_query;
		$query = $wp_query;
	}
	$day_string   = array( __( 'Mon' ), __( 'Tue' ), __( 'Wed' ), __( 'Thu' ), __( 'Fri' ), __( 'Sat' ), __( 'Sun' ) );
	$time_setting = $haoh->retrieve_specified_time( $query );
	if ( $echo ) {
		if ( ! $time_setting['time'] && ! $time_setting['days'] ) {
			echo $undefined_as_now ? __( 'Now', 'tsoh' ) : __( 'Undefined', 'tsoh' );
		} else {
			$str = array();
			if ( $time_setting['time'] ) {
				$str[] = \Tarosky\OpenHour\Formatter::instance()->my2time( $time_setting['time'] );
			}
			if ( $time_setting['days'] ) {
				$str[] = implode(
					', ',
					array_map(
						function ( $d ) use ( $day_string ) {
							return isset( $day_string[ $d ] ) ? $day_string[ $d ] : '';
						},
						$time_setting['days']
					)
				);
			}
			echo implode( ' ', $str );
		}
	}

	return $time_setting;
}

/**
 * Open days for OGP
 *
 * @deprecated 2.0.0
 * @param null|int|WP_post $post
 *
 * @return array
 */
function tsoh_get_open_days_for_ogp( $post = null ) {
	return MetaInfo::instance()->get_open_days( $post );
}

/**
 * 現在のポストがオープンしているかどうか
 *
 * @param null $post
 * @param WP_Query $query
 *
 * @return bool
 */
function tsoh_is_open( $post = null, WP_Query $query = null ) {
	/** @var Bootstrap $haoh */
	/** @var wpdb $wpdb */
	global $haoh;
	$post = get_post( $post );
	if ( is_null( $query ) ) {
		global $wp_query;
		$query = $wp_query;
	}

	return $haoh->is_open( $post, $query );
}

/**
 * Get time table
 *
 * @param bool|int $timestamp
 * @param array $additional_class
 * @param int|null|WP_Post $post
 *
 * @return string
 */
function tsoh_get_timetable( $timestamp = false, array $additional_class = array(), $post = null ) {
	$post = get_post( $post );
	if ( ! $timestamp ) {
		$timestamp = current_time( 'timestamp' );
	}
	$day        = date_i18n( 'N', $timestamp ) - 1;
	$hour       = date_i18n( 'H:i', $timestamp );
	$time_table = array_filter(
		\Tarosky\OpenHour\Model::instance()->get_timetable( $post->ID ),
		function ( $row ) {
			return count( $row ) > 2;
		}
	);

	foreach ( $time_table as $index => $row ) {
		if ( \Tarosky\OpenHour\Model::instance()->between( $hour, $row['open'], $row['close'] ) ) {
			$time_table[ $index ]['now'] = true;
		} else {
			$time_table[ $index ]['now'] = false;
		}
	}

	if ( empty( $time_table ) ) {
		return '';
	}
	$classes = implode( ' ', array_merge( array( 'tsoh-time-table' ), $additional_class ) );
	$path    = tsoh_template( 'time-table.php' );
	foreach ( array( get_template_directory(), get_stylesheet_directory() ) as $dir ) {
		$style = "{$dir}/templat-part/tsoh/time-table.php";
		if ( file_exists( $style ) ) {
			$path = $style;
		}
	}
	/**
	 * tsoh_timetable_template_path
	 *
	 * @package tsoh
	 * @since 1.0.0
	 *
	 * @param string $path File path.
	 * @param WP_Post $post Post object.
	 *
	 * @return string
	 */
	$path = apply_filters( 'tsoh_timetable_template_path', $path, $post );
	if ( file_exists( $path ) ) {
		ob_start();
		include $path;
		$table = ob_get_contents();
		ob_end_clean();

		return $table;
	}
}


/**
 * Display time table
 *
 * @see tsoh_get_timetable()
 *
 * @param bool $timestamp
 * @param array $additional_class
 * @param null|int|WP_Post $post
 */
function tsoh_the_timetable( $timestamp = false, array $additional_class = array(), $post = null ) {
	$table = tsoh_get_timetable( $timestamp, $additional_class, $post );
	if ( $table ) {
		echo $table;
	}
}

/**
 * Show holiday note
 *
 * @param int|null|WP_Post $post
 *
 * @return string
 */
function tsoh_holiday_note( $post = null ) {
	$post = get_post( $post );

	return $post ? get_post_meta( $post->ID, '_tsoh_holiday_note', true ) : '';
}

/**
 * Get placeholder
 *
 * @param string $placeholder
 * @param null|int|WP_post $post
 */
function tsoh_the_holiday_note( $placeholder = '', $post = null ) {
	$note = tsoh_holiday_note( $post );
	echo $note ? wp_kses_post( nl2br( trim( $note ) ) ) : $placeholder;
}

/**
 * Get default local business.
 *
 * @param string $post_type
 * @return string
 */
function tsoh_get_default_local_business( $post_type ) {
	return (string) apply_filters( 'tsoh_default_local_business_type', 'LocalBusiness', $post_type );
}

/**
 * Get stylesheet information.
 *
 * @param string $context
 *
 * @return array|bool
 */
function tsoh_style_url( $context = '' ) {
	static $style = array();
	if ( $style || false === $style ) {
		return $style;
	}
	$style = array(
		'url'     => tsoh_asset( '/css/tsoh-style.css' ),
		'version' => tsoh_version(),
	);
	if ( file_exists( get_template_directory() . '/tsoh-style.css' ) ) {
		$style = array(
			'url'     => get_template_directory_uri() . '/tsoh-style.css',
			'version' => filemtime( get_template_directory() . '/tsoh-style.css' ),
		);
	}
	if ( get_template_directory() !== get_stylesheet_directory() && file_exists( get_stylesheet_directory() . '/tsoh-style.css' ) ) {
		$style = array(
			'url'     => get_stylesheet_directory_uri() . '/tsoh-style.css',
			'version' => filemtime( get_stylesheet_directory() . '/tsoh-style.css' ),
		);
	}
	/**
	 * tsoh_stylesheet
	 *
	 * @package tsoh
	 * @since 1.0.0
	 *
	 * @param array $style Array with 'url' and 'version'.
	 * @param string $context Context string. Default empty.
	 *
	 * @return array|false If return is false, no style will be enqueued.
	 */
	$style = apply_filters( 'tsoh_stylesheet', $style );

	return $style;
}

/**
 * Enqueue style
 */
function tsoh_load_style() {
	$style = tsoh_style_url();
	if ( false === $style ) {
		// Do nothing.
		return;
	}
	wp_enqueue_style( 'tsoh-style', $style['url'], array( 'dashicons' ), $style['version'] );
}
