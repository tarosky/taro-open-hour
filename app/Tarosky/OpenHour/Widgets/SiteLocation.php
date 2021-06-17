<?php

namespace Tarosky\OpenHour\Widgets;

use phpDocumentor\Reflection\Location;
use Tarosky\OpenHour\Pattern\AbstractWidget;

/**
 * Display Site location
 *
 * @package tsoh
 */
class SiteLocation extends AbstractWidget {

	/**
	 * @var \WP_Post
	 */
	private $location = null;

	protected function get_id_base() {
		return 'tsoh-site-location';
	}

	protected function get_name() {
		return __( 'Business Place', 'tsoh' );
	}

	protected function get_description() {
		return __( 'Display place information.', 'tsoh' );
	}

	protected function form_elements( $instance ) {
		$instance = wp_parse_args(
			$instance,
			array(
				'location_id' => '',
			)
		);
		$this->location_selector( $this->get_field_id( 'location_id' ), $this->get_field_name( 'location_id' ), $instance['location_id'] );
		foreach ( array(
			'show_map'    => __( 'Display Google Map', 'tsoh' ),
			'show_access' => __( 'Display Google Map', 'tsoh' ),
		) as $key => $label ) {

		}
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
			if ( $post && $this->places->is_supported( $post->post_type ) && 'publish' === $post->post_status ) {
				$this->location = $post;
			}
		}
		return ! $this->location;
	}


	/**
	 * Get instance.
	 *
	 * @param array $args
	 * @param array $instance
	 */
	protected function render_widget( $args, $instance ) {
		echo $this->places->display_location( $this->location, 'card', $instance );
	}


}
