<?php
/**
 * REST API test
 *
 * @package tsoh
 */

/**
 * Test REST API endpoints.
 */
class Tsoh_Rest_Api_Test extends WP_UnitTestCase {

	/**
	 * Editor user ID.
	 *
	 * @var int
	 */
	protected static $editor_id = 0;

	/**
	 * Place post IDs.
	 *
	 * @var int[]
	 */
	protected static $place_ids = array();

	/**
	 * Create fixtures.
	 *
	 * @param WP_UnitTest_Factory $factory Factory.
	 */
	public static function wpSetUpBeforeClass( $factory ) {
		self::$editor_id = $factory->user->create( array( 'role' => 'editor' ) );
		for ( $i = 0; $i < 3; $i++ ) {
			self::$place_ids[] = $factory->post->create(
				array(
					'post_type'     => 'location',
					'post_status'   => 'publish',
					'post_title'    => 'Place ' . $i,
					'post_content'  => 'Secret content ' . $i,
					'post_password' => 'secret-password',
					'post_date'     => sprintf( '2020-01-0%d 00:00:00', $i + 1 ),
				)
			);
		}
	}

	/**
	 * Set up REST server.
	 */
	public function set_up() {
		parent::set_up();
		global $wp_rest_server;
		$wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init', $wp_rest_server );
	}

	/**
	 * Tear down REST server.
	 */
	public function tear_down() {
		global $wp_rest_server;
		$wp_rest_server = null;
		parent::tear_down();
	}

	/**
	 * Anonymous users cannot access endpoints.
	 */
	public function test_anonymous_rejected() {
		wp_set_current_user( 0 );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/business-places/v1/places' ) );
		$this->assertContains( $response->get_status(), array( 401, 403 ) );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/business-places/v1/place/' . self::$place_ids[0] ) );
		$this->assertContains( $response->get_status(), array( 401, 403 ) );
	}

	/**
	 * Editor gets results without sensitive fields.
	 */
	public function test_editor_gets_safe_fields() {
		wp_set_current_user( self::$editor_id );
		$response = rest_do_request( new WP_REST_Request( 'GET', '/business-places/v1/places' ) );
		$this->assertSame( 200, $response->get_status() );
		$data = $response->get_data();
		$this->assertCount( 3, $data );
		foreach ( $data as $item ) {
			$this->assertSame( array( 'ID', 'post_title', 'post_type', 'post_status', 'label' ), array_keys( $item ) );
			$this->assertArrayNotHasKey( 'post_password', $item );
			$this->assertArrayNotHasKey( 'post_content', $item );
		}
		$response = rest_do_request( new WP_REST_Request( 'GET', '/business-places/v1/place/' . self::$place_ids[0] ) );
		$this->assertSame( 200, $response->get_status() );
		$item = $response->get_data();
		$this->assertSame( self::$place_ids[0], $item['ID'] );
		$this->assertNotEmpty( $item['label'] );
		$this->assertArrayNotHasKey( 'post_password', $item );
		$this->assertArrayNotHasKey( 'post_content', $item );
	}

	/**
	 * Per page is clamped between 1 and 100.
	 */
	public function test_per_page_clamped() {
		wp_set_current_user( self::$editor_id );
		$request = new WP_REST_Request( 'GET', '/business-places/v1/places' );
		$request->set_param( 'posts_per_page', 0 );
		$this->assertCount( 1, rest_do_request( $request )->get_data() );

		$captured = 0;
		$capture  = function ( $query ) use ( &$captured ) {
			if ( in_array( 'location', (array) $query->get( 'post_type' ), true ) ) {
				$captured = (int) $query->get( 'posts_per_page' );
			}
		};
		add_action( 'pre_get_posts', $capture );
		$request = new WP_REST_Request( 'GET', '/business-places/v1/places' );
		$request->set_param( 'posts_per_page', 10000 );
		rest_do_request( $request );
		remove_action( 'pre_get_posts', $capture );
		$this->assertSame( 100, $captured );
	}

	/**
	 * Pagination works with "page" parameter.
	 */
	public function test_pagination() {
		wp_set_current_user( self::$editor_id );
		$ids = array();
		for ( $page = 1; $page <= 3; $page++ ) {
			$request = new WP_REST_Request( 'GET', '/business-places/v1/places' );
			$request->set_param( 'posts_per_page', 1 );
			$request->set_param( 'page', $page );
			$response = rest_do_request( $request );
			$this->assertSame( 200, $response->get_status() );
			$headers = $response->get_headers();
			$this->assertSame( $page, $headers['X-WP-Page'] );
			$data = $response->get_data();
			$this->assertCount( 1, $data );
			$ids[] = $data[0]['ID'];
		}
		$this->assertCount( 3, array_unique( $ids ) );
	}
}
