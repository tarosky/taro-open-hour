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

}
