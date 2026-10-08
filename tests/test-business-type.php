<?php
/**
 * LocalBusiness type test.
 *
 * @package tsoh
 */

use Tarosky\OpenHour\MetaBoxes\LocationMetaBox;
use Tarosky\OpenHour\Services\MetaInfo;

/**
 * Test business type selection and output.
 */
class Tsoh_Business_Type_Test extends WP_UnitTestCase {

	/**
	 * Saved type is output as @type in JSON-LD.
	 */
	public function test_saved_type_in_json() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_tsoh_local_business_type', 'MedicalClinic' );
		$json = MetaInfo::instance()->get_json( get_post( $post_id ) );
		$this->assertSame( 'MedicalClinic', $json['@type'] );
	}

	/**
	 * Empty type falls back to default.
	 */
	public function test_default_type() {
		$post_id = self::factory()->post->create();
		$json    = MetaInfo::instance()->get_json( get_post( $post_id ) );
		$this->assertSame( 'LocalBusiness', $json['@type'] );
	}

	/**
	 * Dirty stored value is sanitized on output.
	 */
	public function test_dirty_type_is_sanitized() {
		$post_id = self::factory()->post->create();
		update_post_meta( $post_id, '_tsoh_local_business_type', '<script>Dentist</script>' );
		$json = MetaInfo::instance()->get_json( get_post( $post_id ) );
		$this->assertSame( 'scriptDentistscript', $json['@type'] );
	}

	/**
	 * Sanitizer keeps only alphanumeric characters.
	 */
	public function test_sanitize() {
		$this->assertSame( 'HairSalon', tsoh_sanitize_local_business_type( ' Hair Salon ' ) );
		$this->assertSame( 'CafeOrCoffeeShop', tsoh_sanitize_local_business_type( 'CafeOrCoffeeShop' ) );
		$this->assertSame( '', tsoh_sanitize_local_business_type( '"\'<>' ) );
	}

	/**
	 * Suggestions contain common subtypes and all are valid type names.
	 */
	public function test_suggestions() {
		$types = tsoh_local_business_type_suggestions();
		$this->assertContains( 'MedicalClinic', $types );
		$this->assertContains( 'Dentist', $types );
		foreach ( $types as $type ) {
			$this->assertSame( $type, tsoh_sanitize_local_business_type( $type ) );
		}
	}

	/**
	 * Metabox renders a datalist bound to the input.
	 */
	public function test_metabox_datalist() {
		$post = get_post( self::factory()->post->create() );
		ob_start();
		LocationMetaBox::instance()->render_meta_box( $post );
		$html = ob_get_clean();
		$this->assertStringContainsString( 'list="tsoh-local-business-types"', $html );
		$this->assertStringContainsString( '<datalist id="tsoh-local-business-types">', $html );
		$this->assertStringContainsString( 'value="MedicalClinic"', $html );
	}
}
