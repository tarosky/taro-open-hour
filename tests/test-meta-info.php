<?php
/**
 * JSON-LD meta info test.
 *
 * @package tsoh
 */

use Tarosky\OpenHour\Services\MetaInfo;

/**
 * Test postal address in JSON-LD.
 */
class Tsoh_Meta_Info_Test extends WP_UnitTestCase {

	/**
	 * PHP warnings/notices caught during a test.
	 *
	 * @var string[]
	 */
	private $errors = array();

	/**
	 * Build address with error handler and assert no warnings.
	 *
	 * @param array $meta Meta values keyed without prefix.
	 * @return array
	 */
	private function get_address( $meta ) {
		$post_id = self::factory()->post->create();
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, '_tsoh_' . $key, $value );
		}
		$this->errors = array();
		set_error_handler( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
			function ( $errno, $errstr ) {
				$this->errors[] = $errstr;
				return true;
			}
		);
		try {
			$address = MetaInfo::instance()->get_postal_address( get_post( $post_id ) );
		} finally {
			restore_error_handler();
		}
		$this->assertSame( array(), $this->errors, 'PHP warnings occurred.' );
		return $address;
	}

	/**
	 * Data provider for street address combinations.
	 *
	 * @return array
	 */
	public function street_address_provider() {
		return array(
			'line1 only'      => array( array( 'address' => '1-2-3 Shibuya' ), '1-2-3 Shibuya' ),
			'line2 only'      => array( array( 'address2' => 'Room 101' ), 'Room 101' ),
			'both'            => array(
				array(
					'address'  => '1-2-3 Shibuya',
					'address2' => 'Room 101',
				),
				'1-2-3 Shibuya Room 101',
			),
			'whitespace only' => array(
				array(
					'address'  => '  ',
					'address2' => 'Room 101',
				),
				'Room 101',
			),
		);
	}

	/**
	 * streetAddress is built correctly without warnings.
	 *
	 * @dataProvider street_address_provider
	 * @param array  $meta     Meta values.
	 * @param string $expected Expected streetAddress.
	 */
	public function test_street_address( $meta, $expected ) {
		$address = $this->get_address( array_merge( $meta, array( 'city' => 'Tokyo' ) ) );
		$this->assertSame( $expected, $address['streetAddress'] );
		$this->assertSame( 'Tokyo', $address['addressLocality'] );
		$this->assertSame( 'PostalAddress', $address['@type'] );
	}

	/**
	 * No streetAddress key if both lines are empty.
	 */
	public function test_no_street_address() {
		$address = $this->get_address(
			array(
				'city' => 'Tokyo',
				'zip'  => '150-0001',
			)
		);
		$this->assertArrayNotHasKey( 'streetAddress', $address );
		$this->assertSame( '150-0001', $address['postalCode'] );
	}

	/**
	 * Empty address returns empty array.
	 */
	public function test_empty_address() {
		$this->assertSame( array(), $this->get_address( array() ) );
	}
}
