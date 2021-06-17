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
		return __( 'Open Hour', 'tsoh' );
	}

	protected function get_description() {
		return __( 'Display time table.' );
	}


	protected function form_elements( $instance ) {
		$instance = wp_parse_args(
			$instance,
			array(
				'location_id' => '',
			)
		);
		$this->location_selector( $this->get_field_id( 'location_id' ), $this->get_field_name( 'location_id' ), $instance['location_id'] );
	}


	protected function handle_update( $instance, $new_instance ) {
		$instance['location_id'] = $new_instance['location_id'];
		return $instance;
	}

	protected function skip_widget( $args, $instance ) {
		$location_id = isset( $instance['location_id'] ) ? $instance['location_id'] : '';
		if ( ! is_numeric( $location_id ) ) {
			$this->location = $this->places->get_site_location();
		} else {
			$post = get_post( $location_id );
			if ( $post ) {
				$this->location = $post;
			}
		}
		return ! $this->location || ! tsoh_has_timetable( $this->location );
	}

	protected function render_widget( $args, $instance ) {
		$style      = 'widget';
		$style      = 'narrow';
		$time_table = tsoh_get_timetable( false, array( 'tsoh-time-table-' . $style ), $this->location );
		echo $time_table;
	}


}
