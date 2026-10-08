<?php
/**
 * Tests for Model DB schema.
 *
 * @package tsoh
 */

use Tarosky\OpenHour\Model;

/**
 * Model test case.
 */
class Test_Model extends WP_UnitTestCase {

	/**
	 * Running activate() repeatedly must not produce SQL errors or ALTER queries.
	 */
	public function test_activate_is_idempotent() {
		global $wpdb;
		$model = Model::instance();
		// The first run may create the table (or confirm it exists).
		$model->activate();
		$this->assertSame( '', $wpdb->last_error );
		// Subsequent runs must be no-op.
		foreach ( array( 1, 2 ) as $round ) {
			$result = $model->activate();
			$this->assertSame( '', $wpdb->last_error, "SQL error on run {$round}." );
			$this->assertSame( array(), $result, "dbDelta returned changes on run {$round}." );
		}
	}

	/**
	 * Table must keep the expected indexes.
	 */
	public function test_table_indexes() {
		global $wpdb;
		Model::instance()->activate();
		$table   = $wpdb->prefix . 'ts_open_hour';
		$indexes = array();
		foreach ( $wpdb->get_results( "SHOW INDEX FROM {$table}" ) as $row ) {
			$indexes[ $row->Key_name ] = '0' === (string) $row->Non_unique;
		}
		$this->assertSame(
			array(
				'time_id'   => true,
				'by_object' => false,
				'by_day'    => false,
				'by_time'   => false,
			),
			$indexes
		);
	}

	/**
	 * Table should be registered for drop on site deletion.
	 */
	public function test_wpmu_drop_tables() {
		global $wpdb;
		$tables = apply_filters( 'wpmu_drop_tables', array(), 5 );
		$this->assertContains( $wpdb->get_blog_prefix( 5 ) . 'ts_open_hour', $tables );
	}
}
