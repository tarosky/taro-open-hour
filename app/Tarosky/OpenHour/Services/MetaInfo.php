<?php

namespace Tarosky\OpenHour\Services;


use Tarosky\OpenHour\Pattern\ControllerAccessor;
use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Meta ifno
 *
 * @package Tarosky\OpenHour\Services
 */
class MetaInfo extends Singleton {

	use ControllerAccessor;

	protected function init() {
		add_action( 'wp_head', array( $this, 'display_json_ld' ) );
	}

	/**
	 * Display JSON LD on every page.
	 */
	public function display_json_ld() {
		$location = $this->get_location_for_current_page();
		if ( ! $location ) {
			return;
		}
		$json = $this->get_json( $location );
		if ( ! $json ) {
			return;
		}
		$json = json_encode( $json );
		echo <<<HTML
<script type="application/ld+json">
{$json}
</script>
HTML;

	}

	/**
	 * Get business location.
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	public function get_json( $post ) {
		// Basic information.
		$type = apply_filters( 'tsoh_local_business_type', get_post_meta( $post->ID, '_tsoh_local_business_type', true ), $post );
		if ( ! $type ) {
			$type = tsoh_get_default_local_business( $post->post_type );
		}
		$json = array(
			'@context' => 'http://schema.org',
			'@type'    => $type,
			'@id'      => get_the_guid( $post ),
			'name'     => get_the_title( $post ),
		);
		// Contacts.
		foreach ( array(
			'tel'   => 'telephone',
			'url'   => 'url',
			'email' => 'email',
		) as $key => $prop ) {
			$value = get_post_meta( $post->ID, '_tsoh_' . $key, true );
			if ( $value ) {
				$json[ $prop ] = $value;
			}
		}
		// Address.
		$address = $this->get_postal_address( $post );
		if ( $address ) {
			$json['address'] = $address;
		}
		// Open hours
		$opening_hours = $this->get_opening_hours( $post );
		if ( $opening_hours ) {
			$json['openingHoursSpecification'] = $opening_hours;
		}
		// Image
		if ( has_post_thumbnail( $post ) ) {
			$json['image'] = get_the_post_thumbnail_url( $post, 'full' );
		}
		// Done.
		return apply_filters( 'tsoh_json_ld', $json, $post );
	}

	/**
	 * Get postal address for JSON-LD
	 *
	 * @param \WP_Post $post
	 * @return array
	 */
	public function get_postal_address( $post ) {
		$address = array();
		$keys    = array(
			'address'  => 'streetAddress',
			'address2' => '',
			'city'     => 'addressLocality',
			'state'    => 'addressRegion',
			'country'  => 'addressCountry',
			'zip'      => 'postalCode',
		);
		foreach ( $this->places->get_address_parts() as $key => $label ) {
			$value = get_post_meta( $post->ID, '_tsoh_' . $key, true );
			if ( ! $value || ! isset( $keys[ $key ] ) ) {
				continue;
			}
			if ( 'address2' === $key ) {
				$address['streetAddress'] .= ' ' . $value;
			} else {
				$address[ $keys[ $key ] ] = $value;
			}
		}
		if ( ! $address ) {
			return array();
		}
		$address['@type'] = 'PostalAddress';
		return $address;
	}

	/**
	 * Get opening hours specifications.
	 *
	 * @param \WP_Post $post
	 * @return array[]
	 */
	public function get_opening_hours( $post ) {
		$opening_horus = array();
		foreach ( $this->model->get_timetable( $post->ID ) as $row ) {
			for ( $i = 0; $i < 7; $i++ ) {
				if ( ! isset( $row[ $i ] ) ) {
					continue;
				}
				$opening_horus[] = array(
					'@type'     => 'OpeningHoursSpecification',
					'opens'     => $row['open'] . ':00',
					'closes'    => $row['close'] . ':00',
					'dayOfWeek' => 'http://schema.org/' . array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' )[ $i ],
				);
			}
		}
		return $opening_horus;
	}

	/**
	 * Get location for current page.
	 *
	 * @return \WP_Post|null
	 */
	protected function get_location_for_current_page() {
		if ( is_front_page() ) {
			return $this->places->get_site_location();
		} elseif ( is_singular() ) {
			return $this->places->is_supported( get_queried_object()->post_type ) ? get_queried_object() : null;
		}
	}

	/**
	 * Get open days for OGP
	 *
	 * @param null|int|\WP_Post $post
	 *
	 * @return array
	 */
	public function get_open_days( $post = null ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return array();
		}
		$date_arr  = $this->model->get_open_date( $post->ID );
		$dates     = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
		$open_days = array();
		for ( $i = 0; $i < 7; $i ++ ) {
			if ( false !== $date_arr[ $i ] ) {
				$open_days[] = $dates[ $i ];
			}
		}

		return $open_days;
	}

}
