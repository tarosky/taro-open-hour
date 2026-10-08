<?php
/**
 * Widget and location display test.
 *
 * @package tsoh
 */

/**
 * Widget test case.
 */
class Tsoh_Widgets_Test extends WP_UnitTestCase {

	/**
	 * Create a location with map and access info.
	 *
	 * @return int
	 */
	protected function create_location() {
		update_option( 'tsoh_google_api_key', 'dummy-key' );
		$post_id = self::factory()->post->create(
			[
				'post_type'   => 'location',
				'post_title'  => 'Test Shop',
				'post_status' => 'publish',
			]
		);
		update_post_meta( $post_id, '_tsoh_address', '1-1-1 Shibuya' );
		update_post_meta( $post_id, '_tsoh_city', 'Tokyo' );
		update_post_meta( $post_id, '_tsoh_access', 'Walk 5 minutes from the station.' );
		return $post_id;
	}

	/**
	 * Widget names are prefixed with plugin name.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/25
	 */
	public function test_widget_names() {
		$location  = new \Tarosky\OpenHour\Widgets\SiteLocation();
		$open_hour = new \Tarosky\OpenHour\Widgets\SiteOpenHour();
		$this->assertEquals( 'Business Places: Location', $location->name );
		$this->assertEquals( 'Business Places: Open Hour', $open_hour->name );
	}

	/**
	 * display_location() honours no_map and no_access.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/26
	 */
	public function test_display_location_settings() {
		$post_id = $this->create_location();
		$places  = \Tarosky\OpenHour\Places::instance();

		$html = $places->display_location( $post_id );
		$this->assertStringContainsString( 'tsoh-location-map', $html );
		$this->assertStringContainsString( 'tsoh-location-address-access', $html );

		$html = $places->display_location( $post_id, 'card', [ 'no_map' => true ] );
		$this->assertStringNotContainsString( 'tsoh-location-map', $html );
		$this->assertStringContainsString( 'tsoh-location-address-access', $html );

		$html = $places->display_location( $post_id, 'card', [ 'no_access' => true ] );
		$this->assertStringContainsString( 'tsoh-location-map', $html );
		$this->assertStringNotContainsString( 'tsoh-location-address-access', $html );
		$this->assertStringContainsString( 'Test Shop', $html );
	}

	/**
	 * Widget saves checkbox values.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/26
	 */
	public function test_widget_update() {
		$widget   = new \Tarosky\OpenHour\Widgets\SiteLocation();
		$instance = $widget->update(
			[
				'title'       => 'Shop',
				'location_id' => '12',
				'no_map'      => '1',
			],
			[]
		);
		$this->assertTrue( $instance['no_map'] );
		$this->assertFalse( $instance['no_access'] );
	}
}
