<?php
/**
 * Time table marks test.
 *
 * @package tsoh
 * @see https://github.com/tarosky/taro-open-hour/issues/27
 */

/**
 * Test open/close marks.
 */
class Tsoh_Marks_Test extends WP_UnitTestCase {

	/**
	 * Default values.
	 */
	public function test_defaults() {
		delete_option( 'tsoh_open_mark' );
		delete_option( 'tsoh_close_mark' );
		$this->assertSame( '✓', tsoh_open_mark() );
		$this->assertSame( '-', tsoh_close_mark() );
	}

	/**
	 * Option values are returned.
	 */
	public function test_option_values() {
		update_option( 'tsoh_open_mark', '○' );
		update_option( 'tsoh_close_mark', '×' );
		$this->assertSame( '○', tsoh_open_mark() );
		$this->assertSame( '×', tsoh_close_mark() );
	}

	/**
	 * Empty open mark falls back to default, empty close mark is kept (blank cell).
	 */
	public function test_empty_option() {
		update_option( 'tsoh_open_mark', '' );
		update_option( 'tsoh_close_mark', '' );
		$this->assertSame( '✓', tsoh_open_mark() );
		$this->assertSame( '', tsoh_close_mark() );
	}

	/**
	 * Filters can override marks.
	 */
	public function test_filters() {
		$open  = function () {
			return 'OPEN';
		};
		$close = function () {
			return 'CLOSED';
		};
		add_filter( 'tsoh_open_mark', $open );
		add_filter( 'tsoh_close_mark', $close );
		$this->assertSame( 'OPEN', tsoh_open_mark() );
		$this->assertSame( 'CLOSED', tsoh_close_mark() );
		remove_filter( 'tsoh_open_mark', $open );
		remove_filter( 'tsoh_close_mark', $close );
	}

	/**
	 * Sanitization of submitted values.
	 */
	public function test_sanitize_mark() {
		// Presets.
		$this->assertSame( '◎', tsoh_sanitize_mark( 'open', '◎' ) );
		$this->assertSame( '', tsoh_sanitize_mark( 'close', '' ) );
		$this->assertSame( '✕', tsoh_sanitize_mark( 'close', '✕' ) );
		// Unknown preset falls back to default.
		$this->assertSame( '✓', tsoh_sanitize_mark( 'open', '<b>x</b>' ) );
		$this->assertSame( '-', tsoh_sanitize_mark( 'close', 'evil' ) );
		// Empty is not a preset for open.
		$this->assertSame( '✓', tsoh_sanitize_mark( 'open', '' ) );
		// Custom text.
		$this->assertSame( '営業', tsoh_sanitize_mark( 'open', 'custom', '  営業 ' ) );
		$this->assertSame( 'Open', tsoh_sanitize_mark( 'open', 'custom', '<script>alert(1)</script>Open' ) );
		// Multibyte safe truncation to 10 chars.
		$this->assertSame( 'あいうえおかきくけこ', tsoh_sanitize_mark( 'close', 'custom', 'あいうえおかきくけこさしすせそ' ) );
	}

	/**
	 * Time table outputs custom marks with screen reader text.
	 */
	public function test_timetable_output() {
		update_option( 'tsoh_post_types', array( 'post' ) );
		update_option( 'tsoh_open_mark', '営業' );
		update_option( 'tsoh_close_mark', '×' );
		$post_id = self::factory()->post->create();
		\Tarosky\OpenHour\Model::instance()->add( $post_id, 0, '10:00', '18:00' );
		$html = tsoh_get_timetable( false, array(), $post_id );
		$this->assertStringContainsString( '<span aria-hidden="true">営業</span>', $html );
		$this->assertStringContainsString( '<span aria-hidden="true">×</span>', $html );
		$this->assertStringContainsString( '<span class="screen-reader-text">Open</span>', $html );
		$this->assertStringContainsString( '<span class="screen-reader-text">Closed</span>', $html );
		$this->assertStringNotContainsString( '&#x2713;', $html );
	}

	/**
	 * Marks are escaped in the time table.
	 */
	public function test_timetable_escapes_mark() {
		update_option( 'tsoh_post_types', array( 'post' ) );
		$filter = function () {
			return '<i>x</i>';
		};
		add_filter( 'tsoh_open_mark', $filter );
		$post_id = self::factory()->post->create();
		\Tarosky\OpenHour\Model::instance()->add( $post_id, 0, '10:00', '18:00' );
		$html = tsoh_get_timetable( false, array(), $post_id );
		remove_filter( 'tsoh_open_mark', $filter );
		$this->assertStringContainsString( '&lt;i&gt;x&lt;/i&gt;', $html );
		$this->assertStringNotContainsString( '<i>x</i>', $html );
	}
}
