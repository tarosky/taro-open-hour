<?php

namespace Tarosky\OpenHour\MetaBoxes;


use Tarosky\OpenHour\Pattern\AbstractMetaBox;

/**
 * Open hour meta box
 *
 * @package tsoh
 */
class OpenHourMetaBox extends AbstractMetaBox {

	protected $id = 'tsoh-meta';

	protected $position = 'advanced';

	protected function get_title() {
		return __( 'Open Hour', 'tsoh' );
	}

	protected function should_display( $post_type ) {
		return tsoh_supported( $post_type );
	}

	public function save_post( $post_id, $post ) {
		if ( ! tsoh_supported( $post->post_type ) ) {
			return;
		}
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_tsohnonce' ), 'tsoh_meta_box' ) ) {
			return;
		}
		// Save note
		update_post_meta( $post_id, '_tsoh_holiday_note', sanitize_textarea_field( filter_input( INPUT_POST, 'tsoh_note' ) ) );
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


	public function render_meta_box( $post ) {
		wp_enqueue_script( 'tsoh-edit-helper', tsoh_asset( '/js/edit-helper.js' ), array( 'jquery-effects-highlight' ), tsoh_version(), true );
		$default_time = tsoh_default();
		/**
		 * tsoh_default_days
		 *
		 * @param array $days Array of int. Monday(0) to Sunday(6).
		 *
		 * @return array
		 */
		$default_days = array_map( 'intval', apply_filters( 'tsoh_default_days', (array) get_option( 'tsoh_default_days', range( 0, 4 ) ) ) );

		wp_localize_script(
			'tsoh-edit-helper',
			'TsOpenHour',
			array(
				'startError'     => _x( 'Start time is invalid. Please input in "hh:mm" format.', 'metabox-js', 'tsoh' ),
				'endError'       => _x( 'End time is invalid. Please input in "hh:mm" format.', 'metabox-js', 'tsoh' ),
				'pastStartError' => _x( 'Start time must be earlier than end time.', 'metabox-js', 'tsoh' ),
				'notEmpty'       => _x( 'Time shift is not empty. Clear them all before insert defaults.', 'metabox-js', 'tsoh' ),
				'deleteBtn'      => _x( 'Delete', 'metabox-js', 'tsoh' ),
				'deleteConfirm'  => _x( 'Are you sure to delete this time shift?', 'metabox-js', 'tsoh' ),
				'defaultTime'    => $default_time,
				'defaultDays'    => $default_days,
			)
		);

		$file = tsoh_template( 'metabox.php' );
		if ( $file ) {
			include $file;
		}
	}
}
