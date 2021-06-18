<?php

namespace Tarosky\OpenHour;


use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Model class
 *
 * @package tsoh
 * @property \wpdb $db
 * @property string $table
 * @property Formatter $formatter
 */
class Model extends Singleton {

	public $db_version = '1.0.0';

	/**
	 * Check if posts have time table
	 *
	 * @param int $post_id
	 *
	 * @return int
	 */
	public function has_time_table( $post_id ) {
		$sql = <<<EOS
			SELECT COUNT(time_id) FROM {$this->table}
			WHERE object_id = %d
EOS;

		return (int) $this->db->get_var( $this->db->prepare( $sql, $post_id ) );
	}

	/**
	 * Get time table array of post
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_timetable( $post_id ) {
		$sql         = <<<EOS
			SELECT day, open, close, crowdedness
			FROM {$this->table}
			WHERE object_id = %d
			ORDER BY open ASC
EOS;
		$time_tables = $this->db->get_results( $this->db->prepare( $sql, $post_id ) );
		$times       = array();
		if ( ! empty( $time_tables ) ) {
			// Parse all time and create table.
			$time_segments = array();
			foreach ( $time_tables as $time ) {
				foreach ( array( 'open', 'close' ) as $key ) {
					if ( ! array_search( $time->{$key}, $time_segments, true ) ) {
						$time_segments[] = $time->{$key};
					}
				}
			}
			sort( $time_segments );
			// Create table base.
			for ( $i = 0, $l = count( $time_segments ); $i < $l; $i ++ ) {
				if ( isset( $time_segments[ $i + 1 ] ) ) {
					$start           = $this->formatter->my2time( $time_segments[ $i ] );
					$end             = $this->formatter->my2time( $time_segments[ $i + 1 ] );
					$times[ $start ] = array(
						'open'  => $start,
						'close' => $end,
					);
				}
			}
			// Compare time shift and if conditions match, insert it.
			foreach ( $times as $key => $time ) {
				foreach ( $time_tables as $data ) {
					$range = array(
						'open'  => $this->formatter->my2time( $data->open ),
						'close' => $this->formatter->my2time( $data->close ),
					);
					if ( $this->is_included( $time, $range ) ) {
						$times[ $key ][ $data->day ] = $data->crowdedness;
					}
				}
			}
		}

		return $times;
	}

	/**
	 * Get time belt for post
	 *
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function get_time_belt( $post_id ) {
		global $wpdb;
		$sql        = <<<EOS
			SELECT open, close FROM {$this->table}
			WHERE object_id = %d
			GROUP BY open
			ORDER BY open ASC
EOS;
		$results    = $this->db->get_results( $this->db->prepare( $sql, $post_id ) );
		$time_belts = array();
		foreach ( $results as $r ) {
			$time_belts[ $this->formatter->my2time( $r->open ) ] = $this->formatter->my2time( $r->close );
		}

		return $time_belts;
	}

	/**
	 * Get days on specified time
	 *
	 * @param int $post_id
	 * @param string $start
	 * @param string $end
	 *
	 * @return array
	 */
	public function get_open_date( $post_id, $start = null, $end = null ) {
		$wheres = array(
			$this->db->prepare( '( object_id = %d )', $post_id ),
		);
		if ( ! is_null( $start ) ) {
			$wheres[] = $this->db->prepare( ' (open <= %s) ', $start );
		}
		if ( ! is_null( $end ) ) {
			$wheres[] = $this->db->prepare( ' (close >= %s) ', $end );
		}
		$where_clause = implode( ' AND ', $wheres );
		$sql          = <<<EOS
			SELECT day, crowdedness FROM {$this->table}
			WHERE {$where_clause}
			GROUP BY day
			ORDER BY day ASC
EOS;
		$dates        = $this->db->get_results( $sql );
		$date_arr     = array();
		foreach ( $dates as $d ) {
			$date_arr[ $d->day ] = $d->crowdedness;
		}
		$results = array();
		for ( $i = 0; $i < 7; $i ++ ) {
			$results[ $i ] = array_key_exists( $i, $date_arr ) ? $date_arr[ $i ] : false;
		}

		return $results;
	}

	/**
	 * If
	 *
	 * @param string $time HH:ii
	 * @param string $from HH:ii
	 * @param string $to HH:ii
	 *
	 * @return bool
	 */
	public function between( $time = '00:00', $from = '12:00', $to = '16:00' ) {
		$from = $this->formatter->make_time_comparable( $from );
		$to   = $this->formatter->make_time_comparable( $to );
		$time = $this->formatter->make_time_comparable( $time );

		return $time >= $from && $time <= $to;
	}

	/**
	 * Check if time range is included
	 *
	 * @param array $segments Array of 'start' and'end'
	 * @param array $range Array of 'start' and'end'
	 *
	 * @return bool
	 */
	private function is_included( array $segments, array $range ) {
		if ( isset( $segments['open'], $segments['close'], $range['open'], $range['close'] ) ) {
			$start = $this->formatter->make_time_comparable( $segments['open'] );
			$end   = $this->formatter->make_time_comparable( $segments['close'] );
			$from  = $this->formatter->make_time_comparable( $range['open'] );
			$to    = $this->formatter->make_time_comparable( $range['close'] );

			return $start >= $from && $start <= $end && $end >= $from && $end <= $to;
		}

		return false;
	}

	/**
	 * Detect if post is open.
	 *
	 * @param int|\WP_Post $post Post object.
	 * @param array $days Array of days as int.
	 * @param string $time Time format should be HH:ii.
	 *
	 * @return bool
	 */
	public function is_open( $post = null, $days = array(), $time = '' ) {
		$post  = get_post( $post );
		$where = array(
			$this->db->prepare( '( object_id = %d )', $post->ID ),
		);
		// If time is specified.
		if ( $time ) {
			$where[] = $this->db->prepare( '( open <= %s )', $time );
			$where[] = $this->db->prepare( '( close >= %s )', $time );
		}
		// 曜日指定されていたら
		if ( $days ) {
			$where[] = sprintf( '( day IN (%s) )', implode( ', ', array_map( 'intval', $days ) ) );
		}
		$where_clause = implode( ' AND ', $where );
		$query        = <<<EOS
        SELECT time_id FROM {$this->table}
        WHERE {$where_clause}
        LIMIT 1
EOS;

		return (bool) $this->db->get_var( $query );
	}

	/**
	 * Add new data
	 *
	 * @param int $post_id
	 * @param int $day
	 * @param string $open
	 * @param string $close
	 * @param int|bool $crowdedness
	 *
	 * @return false|int
	 */
	public function add( $post_id, $day, $open, $close, $crowdedness = false ) {
		$data  = array(
			'object_id' => $post_id,
			'day'       => $day,
			'open'      => $open,
			'close'     => $close,
		);
		$where = array( '%d', '%d', '%s', '%s' );
		if ( false !== $crowdedness ) {
			$data['crowdedness'] = $crowdedness;
			$where[]             = '%d';
		}

		return $this->db->insert( $this->table, $data, $where );
	}

	/**
	 * Clear all time table
	 *
	 * @param int $post_id
	 *
	 * @return false|int
	 */
	public function clear( $post_id ) {
		return $this->db->delete(
			$this->table,
			array(
				'object_id' => $post_id,
			),
			array( '%d' )
		);
	}

	/**
	 * Create db
	 */
	public static function create_db() {
		/** @var Model $self */
		$self = static::instance();
		if ( $self->needs_update() ) {
			$self->activate();
		}
	}

	/**
	 * Detect if run update
	 *
	 * @return bool
	 */
	public function needs_update() {
		$current_version = get_option( 'tsoh_db_version', '0.0.0' );

		return version_compare( $current_version, $this->db_version, '<' );
	}

	/**
	 * Register db
	 */
	public function activate() {
		$char = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8';
		$sql  = <<<EOS
			CREATE TABLE {$this->table} (
				`time_id` BIGINT(11) NOT NULL AUTO_INCREMENT,
				`object_id` BIGINT(11) NOT NULL,
				`day` INT(1) NOT NULL,
				`crowdedness` INT(11) NOT NULL,
				`open` TIME NOT NULL,
				`close` TIME NOT NULL,
				UNIQUE(`time_id`),
				INDEX by_object( `object_id` ),
				INDEX by_day( `day`, `open`, `close` ),
				INDEX by_time( `open`, `close` )
			) ENGINE = InnoDB DEFAULT CHARSET = {$char} ;
EOS;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		update_option( 'tsoh_db_version', $this->db_version );
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
			case 'db':
				global $wpdb;

				return $wpdb;
				break;
			case 'table':
				return "{$this->db->prefix}ts_open_hour";
				break;
			case 'formatter':
				return Formatter::instance();
				break;
			default:
				return null;
				break;
		}
	}

}
