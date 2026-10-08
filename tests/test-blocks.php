<?php
/**
 * Block test.
 *
 * @package tsoh
 */

/**
 * Block test case.
 */
class Tsoh_Blocks_Test extends WP_UnitTestCase {

	/**
	 * Create a location with time table.
	 *
	 * @param array $args Post args.
	 * @return int
	 */
	protected function create_location( $args = [] ) {
		update_option( 'tsoh_google_api_key', 'dummy-key' );
		$post_id = self::factory()->post->create(
			array_merge(
				[
					'post_type'   => 'location',
					'post_title'  => 'Block Shop',
					'post_status' => 'publish',
				],
				$args
			)
		);
		update_post_meta( $post_id, '_tsoh_address', '1-1-1 Shibuya' );
		update_post_meta( $post_id, '_tsoh_access', 'Walk 5 minutes.' );
		\Tarosky\OpenHour\Model::instance()->add( $post_id, 0, '10:00', '18:00' );
		return $post_id;
	}

	/**
	 * Blocks are registered.
	 */
	public function test_registered() {
		$registry = WP_Block_Type_Registry::get_instance();
		$this->assertTrue( $registry->is_registered( 'tsoh/open-hour' ) );
		$this->assertTrue( $registry->is_registered( 'tsoh/business-place' ) );
		$this->assertContains( 'tsoh-block-editor', $registry->get_registered( 'tsoh/open-hour' )->editor_script_handles );
	}

	/**
	 * Open hour block renders time table.
	 */
	public function test_open_hour_block() {
		$post_id = $this->create_location();
		$html    = do_blocks( sprintf( '<!-- wp:tsoh/open-hour {"postId":%d} /-->', $post_id ) );
		$this->assertStringContainsString( 'wp-block-tsoh-open-hour', $html );
		$this->assertStringContainsString( 'tsoh-time-table', $html );
		$this->assertStringContainsString( '10:00', $html );
		// Post without time table renders nothing.
		$empty = self::factory()->post->create();
		$this->assertEquals( '', trim( do_blocks( sprintf( '<!-- wp:tsoh/open-hour {"postId":%d} /-->', $empty ) ) ) );
	}

	/**
	 * Business place block renders location and honours toggles.
	 */
	public function test_business_place_block() {
		$post_id = $this->create_location();
		$html    = do_blocks( sprintf( '<!-- wp:tsoh/business-place {"postId":%d} /-->', $post_id ) );
		$this->assertStringContainsString( 'wp-block-tsoh-business-place', $html );
		$this->assertStringContainsString( 'Block Shop', $html );
		$this->assertStringContainsString( 'tsoh-location-map', $html );
		$this->assertStringContainsString( 'tsoh-location-address-access', $html );

		$html = do_blocks( sprintf( '<!-- wp:tsoh/business-place {"postId":%d,"noMap":true,"noAccess":true} /-->', $post_id ) );
		$this->assertStringContainsString( 'Block Shop', $html );
		$this->assertStringNotContainsString( 'tsoh-location-map', $html );
		$this->assertStringNotContainsString( 'tsoh-location-address-access', $html );

		// Unsupported post type renders nothing.
		$post = self::factory()->post->create();
		$this->assertEquals( '', trim( do_blocks( sprintf( '<!-- wp:tsoh/business-place {"postId":%d} /-->', $post ) ) ) );
	}

	/**
	 * Default post ID falls back to current post, then site location.
	 */
	public function test_default_post() {
		$site = $this->create_location( [ 'post_title' => 'Site HQ' ] );
		\Tarosky\OpenHour\Places::instance()->set_site_location( $site );
		$this->assertStringContainsString( 'Site HQ', render_block( parse_blocks( '<!-- wp:tsoh/business-place /-->' )[0] ) );

		$current = $this->create_location( [ 'post_title' => 'Current Shop' ] );
		$this->go_to( get_permalink( $current ) );
		$GLOBALS['post'] = get_post( $current );
		$this->assertStringContainsString( 'Current Shop', render_block( parse_blocks( '<!-- wp:tsoh/business-place /-->' )[0] ) );
	}

	/**
	 * Private place is not displayed to anonymous users.
	 */
	public function test_private_place() {
		$post_id = $this->create_location( [ 'post_status' => 'private' ] );
		wp_set_current_user( 0 );
		$this->assertEquals( '', trim( do_blocks( sprintf( '<!-- wp:tsoh/business-place {"postId":%d} /-->', $post_id ) ) ) );
	}
}
