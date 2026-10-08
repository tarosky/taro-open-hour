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

}
