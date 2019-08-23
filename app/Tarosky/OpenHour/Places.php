<?php

namespace Tarosky\OpenHour;


use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Places class
 *
 * @package tsoh
 * @property-read string   $post_type
 * @property-read string[] $post_types
 */
class Places extends Singleton {
	
	/**
	 * Initialize Constructor
	 *
	 * @param array $settings
	 */
	protected function init( array $settings = [] ) {
		add_action( 'init', [ $this, 'register_post_type' ] );
	}
	
	/**
	 * Register post type for location.
	 */
	public function register_post_type() {
		// If not enabled, skip.
		if ( ! $this->post_type ) {
			return;
		}
		$args = apply_filters( 'tsoh_location_post_type_args', [
			'label'           => __( 'Business Locations', 'tsoh' ),
			'labels'          => [
				'menu_name'     => __( 'Locations', 'tsoh' ),
				'singular_name' => __( 'Business Location', 'tsoh' ),
			],
			'public'          => get_option( 'tsoh_place_post_type_public', false ),
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-location',
			'capability_type' => 'page',
			'hierarchical'    => false,
			'supports'        => [ 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ],
			'taxonomies'      => [ 'location-type' ],
		] );
		register_post_type( 'location', $args );
		// Register taxonomy.
		$taxonomy_args = apply_filters( 'tsoh_location_taxonomy_args', [
			'label'             => __( 'Location Type', 'tsoh' ),
			'public'            => get_option( 'tsoh_place_post_type_public', false ),
			'show_ui'           => true,
			'show_tagcloud'     => false,
			'show_admin_column' => true,
			'hierarchical'      => true,
		] );
		register_taxonomy( 'location-type', [ 'location' ], $taxonomy_args );
	}
	
	/**
	 * Check if post type is supported as places.
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	public function is_supported( $post_type ) {
		return in_array( $post_type, $this->post_types );
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
			case 'post_type':
				return get_option( 'tsoh_place_post_type', 1 );
			case 'post_types':
				$post_types = (array) get_option( 'tsoh_place_post_types', [] );
				if ( $this->post_type ) {
					$post_types[] = 'places';
				}
				return array_unique( array_filter( $post_types ) );
			default:
				return null;
		}
	}
}
