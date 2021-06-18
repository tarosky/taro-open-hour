<?php

namespace Tarosky\OpenHour\Rest;


use Tarosky\OpenHour\Pattern\AbstractRest;

/**
 * Endpoint for places.
 *
 * @package tsoh
 */
class PlacesApi extends AbstractRest {

	protected $route = 'places';

	/**
	 * Get arguments.
	 *
	 * @param string $method
	 *
	 * @return array
	 */
	protected function get_args( $method ) {
		return array(
			'post_type'      => array(
				'type'        => 'string',
				'description' => 'Post type to search. Default is all places. CSV(location,store,clinic) allowed.',
				'default'     => '',

			),
			's'              => array(
				'type'        => 'string',
				'description' => 'Search query for places',
				'default'     => '',
			),
			'page'           => array(
				'type'              => 'integer',
				'description'       => 'Page number.',
				'default'           => 1,
				'validate_callback' => function ( $var ) {
					return is_numeric( $var );
				},
			),
			'posts_per_page' => array(
				'type'              => 'integer',
				'description'       => 'Posts number.',
				'default'           => 10,
				'validate_callback' => function ( $var ) {
					return is_numeric( $var );
				},
			),
		);
	}

	/**
	 * Get places.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	protected function handle_get( $request ) {
		$post_type = $request->get_param( 'post_type' );
		if ( $post_type ) {
			$post_types = array_filter(
				explode( ',', $post_type ),
				function( $type ) {
					return $type && $this->places->is_supported( $type );
				}
			);
		} else {
			$post_types = $this->places->post_types;
		}
		$args = array(
			'post_type'        => $post_types,
			'posts_per_page'   => max( 10, $request->get_param( 'posts_per_page' ) ),
			'paged'            => max( 1, $request->get_param( 'paged' ) ),
			'post_status'      => current_user_can( 'edit_others_posts' ) ? 'any' : 'publish',
			'suppress_filters' => false,
		);
		$s    = $request->get_param( 's' );
		if ( ! empty( $s ) ) {
			$args['s'] = $s;
		}
		$query    = new \WP_Query( $args );
		$response = new \WP_REST_Response( array_map( array( $this, 'place_to_array' ), $query->posts ) );
		$response->set_headers(
			array(
				'X-WP-Total' => $query->found_posts,
				'X-WP-Page'  => (int) $args['paged'],
			)
		);
		if ( ! $query->have_posts() ) {
			$response->set_status( 404 );
		}
		return $response;
	}

}
