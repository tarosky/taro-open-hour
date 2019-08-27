<?php

namespace Tarosky\OpenHour\Widgets;


use Tarosky\OpenHour\Pattern\AbstractWidget;

/**
 * Site open hour
 *
 * @package tsoh
 */
class SiteOpenHour extends AbstractWidget {
	
	private $location = null;
	
	protected function get_id_base() {
		return 'tsoh-site-open-hour';
	}
	
	protected function get_name() {
		return __( 'Site Open Hour', 'tsoh' );
	}
	
	protected function get_description() {
		return __( 'Display site open hour or nothing if not set.', 'tsoh' );
	}
	
	protected function skip_widget( $args, $instance ) {
		$this->location = $this->places->get_site_location();
		return ! $this->location || ! tsoh_has_timetable( $this->location );
	}
	
	protected function render_widget( $args, $instance ) {
		$style = 'widget';
		$style = 'narrow';
		$time_table = tsoh_get_timetable( false, [ 'tsoh-time-table-' . $style ], $this->location );
		echo $time_table;
	}
	
	
}
