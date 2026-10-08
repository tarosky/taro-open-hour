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
 * Searching by time condition is not supported since 2.0.0.
 * This function always returns empty condition.
 *
 * @deprecated 2.0.0
 * @param bool     $undefined_as_now Treat undefined as now.
 * @param bool     $echo Default true. If false, output nothing.
 * @param WP_Query $query Not used.
 *
 * @return array{time:string, days:int[]}
 */
function tsoh_current_time_condition( $undefined_as_now = false, $echo = true, $query = null ) {
	_deprecated_function( __FUNCTION__, '2.0.0' );
	if ( $echo ) {
		echo $undefined_as_now ? esc_html__( 'Now', 'taro-open-hour' ) : esc_html__( 'Undefined', 'taro-open-hour' );
	}
	return array(
		'time' => '',
		'days' => array(),
	);
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
 * Detect if the post is open at the time.
 *
 * @param null|int|WP_Post $post      Post object.
 * @param null             $query     Deprecated. Not used.
 * @param bool|int         $timestamp Unix timestamp. Default now.
 *
 * @return bool
 */
function tsoh_is_open( $post = null, $query = null, $timestamp = false ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}
	list( $day, $hour ) = tsoh_day_and_hour( $timestamp );
	return \Tarosky\OpenHour\Model::instance()->is_open( $post, array( $day ), $hour );
}

/**
 * Get day index and hour in site timezone.
 *
 * @param bool|int|string $timestamp Unix timestamp. Default now.
 *
 * @return array{0:int, 1:string} Day index starts from Monday(0) and time in H:i format.
 */
function tsoh_day_and_hour( $timestamp = false ) {
	$timestamp = $timestamp ? (int) $timestamp : time();
	return array( (int) wp_date( 'N', $timestamp ) - 1, wp_date( 'H:i', $timestamp ) );
}

/**
 * Locate time table template.
 *
 * Child theme overrides parent theme.
 *
 * @return string
 */
function tsoh_locate_timetable_template() {
	$path = tsoh_template( 'time-table.php' );
	foreach ( array( get_template_directory(), get_stylesheet_directory() ) as $dir ) {
		// "templat-part" is a typo kept for backward compatibility.
		foreach ( array( 'templat-part', 'template-part' ) as $part ) {
			$style = "{$dir}/{$part}/tsoh/time-table.php";
			if ( file_exists( $style ) ) {
				$path = $style;
			}
		}
	}
	return $path;
}

/**
 * Get time table
 *
 * @param bool|int $timestamp Unix timestamp. Default now.
 * @param array $additional_class
 * @param int|null|WP_Post $post
 *
 * @return string
 */
function tsoh_get_timetable( $timestamp = false, array $additional_class = array(), $post = null ) {
	$post               = get_post( $post );
	list( $day, $hour ) = tsoh_day_and_hour( $timestamp );
	$time_table         = array_filter(
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
	$path    = tsoh_locate_timetable_template();
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
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template output from tsoh_get_timetable().
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
	// If theme overrides the stylesheet, deregister and re-register with the theme URL.
	$default_url = tsoh_asset( '/css/tsoh-style.css' );
	if ( $style['url'] !== $default_url ) {
		wp_deregister_style( 'tsoh-style' );
		wp_register_style( 'tsoh-style', $style['url'], array( 'dashicons' ), $style['version'] );
	}
	wp_enqueue_style( 'tsoh-style' );
}

/**
 * Default mark for the time table.
 *
 * @param string $type 'open' or 'close'.
 *
 * @return string
 */
function tsoh_default_mark( $type ) {
	return 'close' === $type ? '-' : '✓';
}

/**
 * Preset marks selectable on the setting screen.
 *
 * @param string $type 'open' or 'close'.
 *
 * @return string[]
 */
function tsoh_mark_presets( $type ) {
	if ( 'close' === $type ) {
		return array( '-', '×', '✕', '' );
	}
	return array( '✓', '○', '●', '◎', '✔' );
}

/**
 * Sanitize a mark submitted from the setting screen.
 *
 * @param string $type   'open' or 'close'.
 * @param string $preset Selected preset value or 'custom'.
 * @param string $custom Custom text, used when $preset is 'custom'.
 *
 * @return string
 */
function tsoh_sanitize_mark( $type, $preset, $custom = '' ) {
	$preset = (string) $preset;
	if ( 'custom' === $preset ) {
		$custom = trim( sanitize_text_field( (string) $custom ) );
		return mb_substr( $custom, 0, 10, 'UTF-8' );
	}
	if ( in_array( $preset, tsoh_mark_presets( $type ), true ) ) {
		return $preset;
	}
	return tsoh_default_mark( $type );
}

/**
 * Mark for open cells in the time table.
 *
 * Falls back to the default when the option is empty.
 *
 * @return string
 */
function tsoh_open_mark() {
	$mark = (string) get_option( 'tsoh_open_mark', '' );
	if ( '' === $mark ) {
		$mark = tsoh_default_mark( 'open' );
	}
	/**
	 * tsoh_open_mark
	 *
	 * @param string $mark Mark for open cells.
	 */
	return (string) apply_filters( 'tsoh_open_mark', $mark );
}

/**
 * Mark for closed cells in the time table.
 *
 * Falls back to the default when the option is not saved.
 * An empty string is a valid value (blank cell).
 *
 * @return string
 */
function tsoh_close_mark() {
	$mark = get_option( 'tsoh_close_mark', false );
	if ( false === $mark ) {
		$mark = tsoh_default_mark( 'close' );
	}
	/**
	 * tsoh_close_mark
	 *
	 * @param string $mark Mark for closed cells.
	 */
	return (string) apply_filters( 'tsoh_close_mark', (string) $mark );
}
