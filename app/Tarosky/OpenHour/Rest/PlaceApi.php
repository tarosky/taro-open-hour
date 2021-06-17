<?php

namespace Tarosky\OpenHour\Rest;


use Tarosky\OpenHour\Pattern\AbstractRest;

class PlaceApi extends AbstractRest {

	protected $route = 'place/(?P<post_id>\d+)';

	public function get_args( $method ) {
		return array(
			'post_id' => array(
				'type'              => 'integer',
				'description'       => 'ID of place.',
				'validate_callback' => function( $var ) {
					return $var && ( $post = get_post( $var ) ) && $this->places->is_supported( $post->post_type );
				},
			),
		);
	}

	/**
	 * Handle single post request.
	 *
	 * @param \WP_REST_Request $request
	 * @throws \Exception
	 * @return array|\WP_Error
	 */
	public function handle_get( $request ) {
		$post = get_post( $request->get_param( 'post_id' ) );
		if ( ! current_user_can( 'edit_others_posts' ) && 'publish' !== $post->post_status ) {
			throw new \Exception( __( 'You have no permission to access this place.', 'tsoh' ), 403 );
		}
		return $this->place_to_array( $post );
	}
}
