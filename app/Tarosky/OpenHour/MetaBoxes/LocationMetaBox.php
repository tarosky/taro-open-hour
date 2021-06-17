<?php

namespace Tarosky\OpenHour\MetaBoxes;


use Tarosky\OpenHour\Pattern\AbstractMetaBox;

/**
 * Location meta box.
 *
 * @package tsoh
 */
class LocationMetaBox extends AbstractMetaBox {

	protected $id = 'tsoh-place';

	protected $position = 'advanced';

	protected function should_display( $post_type ) {
		return $this->places->is_supported( $post_type );
	}

	protected function get_title() {
		return __( 'Location Setting', 'tsoh' );
	}

	public function save_post( $post_id, $post ) {
		if ( ! $this->places->is_supported( $post->post_type ) ) {
			return;
		}
		if ( ! wp_verify_nonce( filter_input( INPUT_POST, '_tsohplacenonce' ), 'tsoh_place_meta_box' ) ) {
			return;
		}
		$keys = array_merge( array_keys( $this->places->get_address_parts() ), array( 'access', 'tel', 'url', 'email', 'local_business_type' ) );
		foreach ( $keys as $key ) {
			$id = '_tsoh_' . $key;
			update_post_meta( $post_id, $id, filter_input( INPUT_POST, $id ) );
		}
		$is_location = filter_input( INPUT_POST, '_tsoh_site_location' );
		if ( $is_location ) {
			$this->places->set_site_location( $post );
		} else {
			delete_post_meta( $post->ID, '_tsoh_site_location' );
		}
	}

	public function render_meta_box( $post ) {
		$file = tsoh_template( 'metabox-place.php' );
		if ( $file ) {
			include $file;
		}
	}
}
