<?php

// Time table short code
use Tarosky\OpenHour\Places;

add_shortcode(
	'open-hour',
	function ( $args, $content = '' ) {
		$args             = shortcode_atts(
			array(
				'post_id'   => get_the_ID(),
				'class'     => '',
				'timestamp' => false,
			),
			$args,
			'open-hour'
		);
		$additional_class = array_filter( explode( ' ', $args['class'] ) );
		tsoh_load_style();

		return tsoh_get_timetable( $args['timestamp'], $additional_class, $args['post_id'] );
	}
);


add_shortcode(
	'business-place',
	function( $args, $content = '' ) {
		return Places::instance()->short_code_display( $args, $content );
	}
);
