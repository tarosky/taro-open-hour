<?php

namespace Tarosky\OpenHour\Widgets;

use Tarosky\OpenHour\Pattern\AbstractWidget;

/**
 * Display Site location
 *
 * @package tsoh
 */
class SiteLocation extends AbstractWidget{
	
	/**
	 * @var \WP_Post
	 */
	private $location = null;
	
	protected function get_id_base() {
		return 'tsoh-site-location';
	}
	
	protected function get_name() {
		return __( 'Site Location', 'tsoh' );
	}
	
	protected function get_description() {
		return __( 'Display site location or nothing if not set.', 'tsoh' );
	}
	
	protected function form_elements( $instance ) {
		
		foreach ( [
			'show_map'    => __( 'Display Google Map', 'tsoh' ),
			'show_access' => __( 'Display Google Map', 'tsoh' ),
		] as $key => $label) {
		
		}
		if ( ! $this->places->get_site_location() ) {
			?>
			<p class="description" style="color: red;">
				<?php esc_html_e( 'This site has no site location. Please register one.', 'tsoh' ) ?>
			</p>
			<?php
		}
	}
	
	protected function handle_update( $instance, $new_instance ) {
		return $instance;
	}
	
	protected function skip_widget( $args, $instance ) {
		$this->location = $this->places->get_site_location();
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
