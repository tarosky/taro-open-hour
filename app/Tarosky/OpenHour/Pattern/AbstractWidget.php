<?php

namespace Tarosky\OpenHour\Pattern;

use Tarosky\OpenHour\Rest\PlacesApi;

/**
 * Widget base
 *
 * @package tsoh
 */
abstract class AbstractWidget extends \WP_Widget {

	use ControllerAccessor;

	protected $has_title = true;

	/**
	 * AbstractWidget constructor.
	 *
	 */
	public function __construct() {
		$options     = $this->get_widget_options();
		$description = $this->get_description();
		if ( $description ) {
			$options['description'] = $description;
		}
		parent::__construct( $this->get_id_base(), $this->get_name(), $options, $this->get_control_options() );
	}

	/**
	 * Update information.
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		if ( $this->has_title ) {
			$instance['title'] = sanitize_text_field( $new_instance['title'] );
		}
		return $this->handle_update( $instance, $new_instance );
	}

	/**
	 * Handle instance to save.
	 *
	 * @param array $instance
	 *
	 * @return array
	 */
	protected function handle_update( $instance, $new_instance ) {
		return $instance;
	}

	/**
	 * Render widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		if ( $this->skip_widget( $args, $instance ) ) {
			return;
		}
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}
		$this->render_widget( $args, $instance );
		echo $args['after_widget'];
	}

	/**
	 * If widget is not proper, skip rendering.
	 *
	 * @param array $args
	 * @param array $instance
	 * @return bool
	 */
	protected function skip_widget( $args, $instance ) {
		return false;
	}

	/**
	 * Display form.
	 *
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {
		if ( $this->has_title ) {
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>" class="widefat"><?php esc_html_e( 'Title', 'tsoh' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
					   name="<?php echo $this->get_field_name( 'title' ); ?>"
					   value="<?php echo isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : ''; ?>" />
			</p>
			<?php
		}
		$this->form_elements( $instance );
	}

	/**
	 * Display location selector field.
	 *
	 * @param string $id
	 * @param string $name
	 * @param string $current
	 */
	protected function location_selector( $id, $name, $current = '' ) {
		if ( is_numeric( $current ) ) {
			$place = get_post( $current );
		} else {
			$place = null;
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Post ID of Place', 'tsoh' ); ?></label><br />
			<select class="location-selector" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>"
					data-placeholder="<?php esc_attr_e( 'Search places...', 'tsoh' ); ?>">
				<?php if ( $place ) : ?>
				<option value="<?php echo esc_attr( $place->ID ); ?>" selected="selected"><?php echo esc_html( PlacesApi::instance()->get_location_label( $place ) ); ?></option>
				<?php endif; ?>
			</select>
		</p>
		<p class="description">
			<?php esc_html_e( 'If left black, site default location will be used.', 'tsoh' ); ?>
			<?php
			$default = $this->places->get_site_location();
			if ( ! $default ) :
				?>
				<span style="color: orange;">
					<strong><span class="dashicons dashicons-info"></span> <?php esc_html_e( 'Notice' ); ?>: </strong>
					<?php esc_html_e( 'This site has no site location. Please register one.', 'tsoh' ); ?>
				</span>
			<?php else : ?>
				<?php
				// translators: %s is location label.
				printf( esc_html__( 'Current site location is %s', 'tsoh' ), PlacesApi::instance()->get_location_label( $default ) );
				?>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Render form elements.
	 *
	 * @param array $instance
	 */
	protected function form_elements( $instance ) {
		// Do something.
	}


	/**
	 * Render widget content
	 *
	 * @param array $args
	 * @param array $instance
	 *
	 * @return void
	 */
	abstract protected function render_widget( $args, $instance );

	/**
	 * Get ID base.
	 *
	 * @return string
	 */
	abstract protected function get_id_base();

	/**
	 * Get name.
	 *
	 * @return string
	 */
	abstract protected function get_name();

	/**
	 * Widget options.
	 *
	 * @return array
	 */
	protected function get_widget_options() {
		return array();
	}

	/**
	 * Control options.
	 *
	 * @return array
	 */
	protected function get_control_options() {
		return array();
	}

	/**
	 * Widget description.
	 *
	 * @return string
	 */
	protected function get_description() {
		return '';
	}
}
