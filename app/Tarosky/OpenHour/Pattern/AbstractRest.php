<?php

namespace Tarosky\OpenHour\Pattern;


use Tarosky\OpenHour\Model;
use Tarosky\OpenHour\Places;

/**
 * Abstract REST pattern.
 *
 * @package tsoh
 * @property-read Model  $open_hour
 * @property-read Places $places
 */
abstract class AbstractRest extends Singleton {

	protected $route = '';

	protected $namespace = 'business-places/v1';

	protected $is_duplicated = false;

	const ENDPOINTS = array( 'GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'HEAD', 'PATCH' );

	/**
	 * Constructor
	 */
	protected function init() {
		if ( $this->is_duplicated ) {
			return;
		}
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Arguments for REST route.
	 *
	 * @param string $method
	 *
	 * @return array
	 */
	abstract protected function get_args( $method );

	/**
	 * Register REST endpoints.
	 */
	public function register_rest_routes() {
		$args = array();
		foreach ( self::ENDPOINTS as $method ) {
			$method_name = 'handle_' . strtolower( $method );
			if ( method_exists( $this, $method_name ) ) {
				$args[] = array(
					'methods'             => $method,
					'args'                => $this->get_args( $method ),
					'callback'            => array( $this, 'call' ),
					'permission_callback' => array( $this, 'permission_callback' ),
				);
			}
		}
		if ( ! $args ) {
			return;
		}
		register_rest_route( $this->namespace, $this->route, $args );
	}

	/**
	 * Handle rest request.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function call( $request ) {
		$method_name = strtolower( 'handle_' . $request->get_method() );
		try {
			if ( ! method_exists( $this, $method_name ) ) {
				throw new \Exception( __( 'Specified endpoint is not available.', 'tsoh' ), 400 );
			}
			$result = call_user_func_array( array( $this, $method_name ), array( $request ) );
			if ( is_wp_error( $result ) || is_a( $result, 'WP_REST_Response' ) ) {
				return $result;
			} else {
				return new \WP_REST_Response( $result );
			}
		} catch ( \Exception $e ) {
			return new \WP_Error(
				'invalid_request',
				$e->getMessage(),
				array(
					'status' => $e->getCode(),
				)
			);
		}
	}

	/**
	 * Permission callback.
	 *
	 * @param \WP_REST_Request $request
	 * @return bool
	 */
	public function permission_callback( $request ) {
		return true;
	}

	/**
	 * Convert a place to array.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	protected function place_to_array( $post ) {
		$result          = (array) $post;
		$result['label'] = $this->get_location_label( $post );
		return $result;
	}

	/**
	 * Get site location label.
	 *
	 * @param \WP_Post $post
	 * @return string
	 */
	public function get_location_label( $post ) {
		$obj   = get_post_type_object( $post->post_type );
		$label = sprintf( '%s(%s)', get_the_title( $post ), $obj->label );
		if ( 'publish' !== $post->post_status ) {
			$statuses = get_post_statuses();
			$label   .= ' - ' . ( isset( $statuses[ $post->post_status ] ) ? $statuses[ $post->post_status ] : $post->post_status );
		}
		return $label;
	}

	/**
	 * Getter.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'places':
				return Places::instance();
			case 'open_hours':
				return Model::instance();
			default:
				return null;
		}
	}


}
