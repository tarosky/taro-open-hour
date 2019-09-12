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
	 * Get site location
	 *
	 * @return \WP_Post|null
	 */
	public function get_site_location() {
		foreach ( get_posts( [
			'post_type'      => $this->post_types,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_query'     => [
				[
					'key'   => '_tsoh_site_location',
					'value' => '1',
				],
			],
		] ) as $post ) {
			return $post;
		}
		return null;
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
	 * Render location block
	 *
	 * @param null|int|\WP_Post $post
	 * @param string            $type
	 * @param array             $settings
	 *
	 * @return string
	 */
	public function display_location( $post = null, $type = 'card', $settings = [] ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return '';
		}
		$file = '';
		switch ( $type ) {
			case 'block':
				$file = tsoh_template( 'location-' . $type . '.php' );
				break;
		}
		if ( ! $file ) {
			$file = tsoh_template( 'location.php' );
		}
		ob_start();
		include $file;
		$output = ob_get_contents();
		ob_end_clean();
		return apply_filters( 'tsoh_location_display', $output, $post, $type );
	}
	
	
	/**
	 * Display location.
	 *
	 * @param array  $attributes
	 * @param string $content
	 *
	 * @return string
	 */
	public function short_code_display( $attributes, $content = '' ) {
		$attributes = shortcode_atts( [
			'post_id' => get_the_ID(),
			'class'   => '',
			'type'    => 'block',
		], $attributes, 'business-place' );
		
		$location = get_post( $attributes['post_id'] );
		if ( ! $location || ! $this->is_supported( $location->post_type ) ) {
			return '';
		}
		return $this->display_location( $location, $attributes['type'], $attributes );
	}
	
	/**
	 * Get formatted address.
	 *
	 * @param null|int|\WP_Post $post
	 * @param string[]          $excludes Key name to be excluded.
	 *
	 * @return string
	 */
	public function format_address( $post = null, $excludes = [] ) {
		$address_parts = [];
		$post = get_post( $post );
		if ( ! $post ) {
			return '';
		}
		foreach ( $this->get_address_parts() as $key => $label ) {
			if ( in_array( $key, $excludes ) ) {
				continue;
			}
			$value = get_post_meta( $post->ID, '_tsoh_' . $key, true );
			if ( ! $value ) {
				continue;
			}
			switch ( $key ) {
				case 'zip':
					$address_parts[ $key ] = _x( '', 'zip_prefix', 'tsoh' ) . $value;
					break;
				case 'country':
					if ( 'no' !== _x( 'yes', 'display_country', 'tsoh' ) ) {
						$address_parts[ $key ] = $value;
					}
					break;
				default:
					$address_parts[ $key ] = $value;
					break;
			}
		}
		$address_parts = apply_filters( 'tsoh_formatted_address', $address_parts, $post );
		return implode( ' ', $address_parts );
	}
	
	/**
	 * Get Google map iframe.
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return string
	 */
	public function get_map_src( $post = null ) {
		$post = get_post( $post );
		$api_key = get_option( 'tsoh_google_api_key' );
		if ( ! $api_key || ! $post ) {
			return '';
		}
		$q = $this->format_address( $post, [ 'zip', 'address2' ] );
		return (string) apply_filters( 'tsoh_gmap_src', sprintf( 'https://www.google.com/maps/embed/v1/place?q=%s&key=%s', rawurlencode( $q ), $api_key ), $post );
	}
	
	/**
	 * Get location contacts.
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return array
	 */
	public function location_contacts( $post = null ) {
		$contacts = [];
		$post = get_post( $post );
		if ( ! $post ) {
			return $contacts;
		}
		foreach ( [ 'tel', 'email', 'url' ] as $key ) {
			$value = get_post_meta( $post->ID, '_tsoh_' . $key, true );
			if ( ! $value ) {
				continue;
			}
			$icon  = $key;
			$label = $value;
			switch ( $key ) {
				case 'tel':
					$url   = 'tel:' . $value;
					$icon = 'phone';
					break;
				case 'email':
					$url = 'mailto:' . $value;
					break;
				case 'url':
					$url = $value;
					$label = __( 'Web Site', 'tsoh' );
					$icon = 'admin-links';
					break;
				default:
					continue;
			}
			$contacts[ $key ] = [
				'label' => $label,
				'url'   => $url,
				'icon'  => $icon,
				'value' => $value,
			];
		}
		if ( get_post_type_object( $post->post_type )->public && ! is_single( $post ) ) {
			$contacts[ 'detail' ] = [
				'label' => __( 'See Detail', 'tsoh' ),
				'url'   => get_permalink( $post ),
				'icon'  => 'info',
				'value' => get_permalink( $post ),
			];
		}
		$contacts = apply_filters( 'tsoh_contacts', $contacts, $post );
		return $contacts;
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
