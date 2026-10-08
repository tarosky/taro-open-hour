<?php

namespace Tarosky\OpenHour\Widgets;

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
		return __( 'Business Places: Location', 'taro-open-hour' );
	}

	protected function get_description() {
		return __( 'Display place information.', 'taro-open-hour' );
	}

	/**
	 * Display toggle options.
	 *
	 * @return array<string, string>
	 */
	protected function get_toggles() {
		return array(
			'no_map'    => __( 'Hide Google Map', 'taro-open-hour' ),
			'no_access' => __( 'Hide access information', 'taro-open-hour' ),
		);
	}

	protected function form_elements( $instance ) {
		$instance = wp_parse_args(
			$instance,
			array(
				'location_id' => '',
				'no_map'      => false,
				'no_access'   => false,
			)
		);
		$this->location_selector( $this->get_field_id( 'location_id' ), $this->get_field_name( 'location_id' ), $instance['location_id'] );
		foreach ( $this->get_toggles() as $key => $label ) {
			?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>">
					<input type="checkbox" value="1" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" <?php checked( ! empty( $instance[ $key ] ) ); ?> />
					<?php echo esc_html( $label ); ?>
				</label>
			</p>
			<?php
		}
	}

	protected function handle_update( $instance, $new_instance ) {
		$instance['location_id'] = $new_instance['location_id'];
		foreach ( array_keys( $this->get_toggles() ) as $key ) {
			$instance[ $key ] = ! empty( $new_instance[ $key ] );
		}
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
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML template output from display_location().
		echo $this->places->display_location( $this->location, 'card', $instance );
	}
}
