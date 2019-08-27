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
	 */
	protected function init() {
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
	 * Set site location
	 *
	 * @param null|int|\WP_Post $post
	 * @param bool              $delete If set to true,
	 */
	public function set_site_location( $post = null, $delete = false ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}
		// Remove exiting.
		global $wpdb;
		$wpdb->delete( $wpdb->postmeta, [
			'meta_key'   => '_tsoh_site_location',
			'meta_value' => 1,
		], [ '%d' ] );
		// Save if exists.
		if ( ! $delete ) {
			update_post_meta( $post->ID, '_tsoh_site_location', 1 );
		}
	}
	
	/**
	 * Get site location.
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return bool
	 */
	public function is_site_location( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return false;
		}
		return (bool) get_post_meta( $post->ID, '_tsoh_site_location', true );
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
	 * Get address parts in i18n order.
	 *
	 * @return string[]
	 */
	public function get_address_parts() {
		$parts = [
			'address'  => _x( 'Address line 1', 'address', 'tsoh' ),
			'address2' => _x( 'Address line 2', 'address', 'tsoh' ),
			'city'     => _x( 'City', 'address', 'tsoh' ),
			'state'    => _x( 'State / Province', 'address', 'tsoh' ),
			'country'  => _x( 'Country', 'address', 'tsoh' ),
			'zip'      => _x( 'Postal Code / Zip', 'address', 'tsoh' ),
		];
		$address_order = _x( 'address,address2,city,state,country,zip', 'address-order', 'tsoh' );
		$filtered      = [];
		foreach ( array_map( 'trim', explode( ',', $address_order ) ) as $key ) {
			if ( isset( $parts[ $key ] ) ) {
				$filtered[ $key ] = $parts[ $key ];
			}
		}
		return $filtered;
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
					$post_types[] = 'location';
				}
				return array_unique( array_filter( $post_types ) );
			default:
				return null;
		}
	}
}
