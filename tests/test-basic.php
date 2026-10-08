<?php
/**
 * Function test
 *
 * @package tsoh
 */

/**
 * Sample test case.
 */
class Tsoh_Basic_Test extends WP_UnitTestCase {

	/**
	 * A single example test
	 *
	 */
	function test_auto_loader() {
		// Check class exists
		$this->assertTrue( class_exists( 'Tarosky\\OpenHour\\Bootstrap' ) );
	}

	/**
	 * Test functions
	 */
	function test_functions() {
		$this->assertFalse( tsoh_supported( 'unexisting_post_type' ) );
		$this->assertEquals( [ [ '10:00', '20:00' ] ], tsoh_default() );
		$this->assertFalse( tsoh_has_timetable() );
		$this->assertEquals( '', tsoh_holiday_note() );
		$style = tsoh_style_url();
		$this->assertEquals( 1, preg_match( '#^https?://#u', $style['url'] ) );
		$this->assertEquals( $style['version'], tsoh_version() );
	}

	/**
	 * Theme can override time table template.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/102
	 */
	function test_timetable_template_override() {
		$this->assertEquals( tsoh_template( 'time-table.php' ), tsoh_locate_timetable_template() );
		$theme_dir = get_temp_dir() . 'tsoh-theme-' . wp_generate_password( 6, false );
		$template  = $theme_dir . '/template-part/tsoh/time-table.php';
		wp_mkdir_p( dirname( $template ) );
		file_put_contents( $template, '<?php // override' );
		$filter = function () use ( $theme_dir ) {
			return $theme_dir;
		};
		add_filter( 'stylesheet_directory', $filter );
		$located = tsoh_locate_timetable_template();
		remove_filter( 'stylesheet_directory', $filter );
		unlink( $template );
		$this->assertEquals( $template, $located );
	}

	/**
	 * Setup notice should not appear if any post type is available.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/107
	 */
	function test_needs_setup() {
		$admin = \Tarosky\OpenHour\Admin::instance();
		// Default: location post type is enabled.
		delete_option( 'tsoh_post_types' );
		delete_option( 'tsoh_place_post_type' );
		$this->assertFalse( $admin->needs_setup() );
		// Location disabled, but other post type treated as location.
		update_option( 'tsoh_place_post_type', '' );
		update_option( 'tsoh_place_post_types', [ 'page' ] );
		$this->assertFalse( $admin->needs_setup() );
		// Only business hours post types.
		update_option( 'tsoh_place_post_types', null );
		update_option( 'tsoh_post_types', [ 'post' ] );
		$this->assertFalse( $admin->needs_setup() );
		// Nothing at all.
		update_option( 'tsoh_post_types', [] );
		$this->assertTrue( $admin->needs_setup() );
	}

	/**
	 * Public API should not cause fatal error.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/101
	 */
	function test_is_open() {
		$post_id = self::factory()->post->create();
		// Open on Monday(0) 10:00 - 18:00.
		\Tarosky\OpenHour\Model::instance()->add( $post_id, 0, '10:00', '18:00' );
		// 2026-10-05 is Monday.
		$this->assertTrue( tsoh_is_open( $post_id, null, strtotime( '2026-10-05 12:00:00' ) ) );
		$this->assertFalse( tsoh_is_open( $post_id, null, strtotime( '2026-10-05 19:00:00' ) ) );
		$this->assertFalse( tsoh_is_open( $post_id, null, strtotime( '2026-10-06 12:00:00' ) ) );
		$this->assertFalse( tsoh_is_open( self::factory()->post->create(), null, strtotime( '2026-10-05 12:00:00' ) ) );
	}

	/**
	 * Deprecated function returns empty condition.
	 *
	 * @expectedDeprecated tsoh_current_time_condition
	 */
	function test_current_time_condition() {
		$this->assertEquals(
			[
				'time' => '',
				'days' => [],
			],
			tsoh_current_time_condition( false, false )
		);
	}

	/**
	 * Day and hour are calculated in site timezone from Unix timestamp.
	 *
	 * @see https://github.com/tarosky/taro-open-hour/issues/95
	 */
	function test_day_and_hour() {
		update_option( 'timezone_string', 'Asia/Tokyo' );
		// 2026-10-04 (Sun) 15:00 UTC is 2026-10-05 (Mon) 00:00 JST.
		$this->assertEquals( [ 0, '00:00' ], tsoh_day_and_hour( gmmktime( 15, 0, 0, 10, 4, 2026 ) ) );
		$post_id = self::factory()->post->create();
		\Tarosky\OpenHour\Model::instance()->add( $post_id, 0, '10:00', '18:00' );
		// Monday 12:00 JST.
		$this->assertTrue( tsoh_is_open( $post_id, null, gmmktime( 3, 0, 0, 10, 5, 2026 ) ) );
		// Monday 12:00 UTC is 21:00 JST.
		$this->assertFalse( tsoh_is_open( $post_id, null, gmmktime( 12, 0, 0, 10, 5, 2026 ) ) );
		// Default is now and returns valid format.
		list( $day, $hour ) = tsoh_day_and_hour();
		$this->assertContains( $day, range( 0, 6 ) );
		$this->assertMatchesRegularExpression( '/^\d{2}:\d{2}$/', $hour );
	}

}
