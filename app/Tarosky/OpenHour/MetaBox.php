<?php

namespace Tarosky\OpenHour;


use Tarosky\OpenHour\Pattern\Singleton;

/**
 * MetaBox handler
 *
 * @package tsoh
 * @property Model $model
 * @property Formatter $formatter
 */
class MetaBox extends Singleton {

	/**
	 * Initializer
	 *
	 * @param array $settings
	 */
	protected function init( array $settings = [] ) {
		// Register meta box is allowed.
		add_action( 'add_meta_boxes', function ( $post_type ) {
			if ( tsoh_supported( $post_type ) ) {
				add_meta_box( 'tsoh-meta', __( 'Open Hour', 'tsoh' ), [
					$this,
					'do_meta_box',
				], $post_type, 'normal', 'low' );
			}
		} );
		// Register scripts
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		// Save post
		add_action( 'save_post', [ $this, 'edit_post' ], 10, 2 );
	}

	/**
	 * Register scripts
	 */
	public function enqueue_scripts() {
		$screen = get_current_screen();
		if ( ( 'post' === $screen->base ) && tsoh_supported( $screen->id ) ) {
			// Load style
			wp_enqueue_style( 'tsoh-admin', tsoh_asset( 'css/admin.css' ), [], tsoh_version() );
			// Load script
			wp_enqueue_script( 'tsoh-edit-helper', tsoh_asset( '/js/edit-helper.js' ), [ 'jquery-effects-highlight' ], tsoh_version(), true );
			$default_time = tsoh_default();
			/**
			 * tsoh_default_days
			 *
			 * @param array $days Array of int. Monday(0) to Sunday(6).
			 *
			 * @return array
			 */
			$default_days = array_map( 'intval', apply_filters( 'tsoh_default_days', (array) get_option( 'tsoh_default_days', range( 0, 4 ) ) ) );

			wp_localize_script( 'tsoh-edit-helper', 'TsOpenHour', [
				'startError'     => _x( 'Start time is invalid. Please input in "hh:mm" format.', 'metabox-js', 'tsoh' ),
				'endError'       => _x( 'End time is invalid. Please input in "hh:mm" format.', 'metabox-js', 'tsoh' ),
				'pastStartError' => _x( 'Start time must be earlier than end time.', 'metabox-js', 'tsoh' ),
				'notEmpty'       => _x( 'Time shift is not empty. Clear them all before insert defaults.', 'metabox-js', 'tsoh' ),
				'deleteBtn'      => _x( 'Delete', 'metabox-js', 'tsoh' ),
				'deleteConfirm'  => _x( 'Are you sure to delete this time shift?', 'metabox-js', 'tsoh' ),
				'defaultTime'    => $default_time,
				'defaultDays'    => $default_days,
			] );
		}
	}

	/**
	 * Save post hook
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	public function edit_post( $post_id, $post ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! tsoh_supported( $post->post_type ) ) {
			return;
		}
		if ( ! isset( $_POST['_tsohnonce'] ) || ! wp_verify_nonce( $_POST['_tsohnonce'], 'tsoh_meta_box' ) ) {
			return;
		}
		// Save note
		update_post_meta( $post_id, '_tsoh_holiday_note', sanitize_text_field( $_POST['tsoh_note'] ) );
		// Clear all time shift.
		$this->model->clear( $post_id );
		// Save new time shift
		if ( isset( $_POST['tsoh_open_hour'] ) ) {
			foreach ( $_POST['tsoh_open_hour'] as $index => $time ) {
				$time  = explode( '-', $time );
				$open  = $this->formatter->time2my( $time[0] );
				$close = $this->formatter->time2my( $time[1] );
				for ( $i = 0; $i < 7; $i ++ ) {
					if ( isset( $_POST[ 'tsoh_date_' . $i ][ $index ] ) && '' !== $_POST[ 'tsoh_date_' . $i ][ $index ] ) {
						$this->model->add( $post_id, $i, $open, $close, 1 );
					}
				}
			}
		}
	}


	/**
	 * Render meta box
	 *
	 * @param \WP_Post $post
	 */
	public function do_meta_box( $post ) {
		$file = tsoh_template( 'metabox.php' );
		if ( $file ) {
			include $file;
		}
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'model':
				return Model::instance();
				break;
			case 'formatter':
				return Formatter::instance();
				break;
			default:
				return null;
				break;
		}
	}

}
