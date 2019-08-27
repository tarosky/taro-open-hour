<?php
/**
 * @var \Tarosky\OpenHour\MetaBoxes\LocationMetaBox $this
 * @var WP_Post $post
 * @package tsoh
 */
wp_nonce_field( 'tsoh_place_meta_box', '_tsohplacenonce' );
?>
<p>
	<label>
		<input type="checkbox" name="_tsoh_site_location" id="_tsoh_site_location" value="1" <?php checked( $this->places->is_site_location( $post ) ) ?> />
		<?php esc_html_e( 'Mark as site main place(e.g. company address)', 'tsoh' ) ?>
	</label>
</p>
<h3><?php esc_html_e( 'Address', 'tsoh' ) ?></h3>
<table class="form-table">
	<tbody>
	<?php foreach ( $this->places->get_address_parts() as $key => $label ) :
		$id = '_tsoh_' . $key;
		?>
		<tr>
			<th scope="row">
				<label for="<?php echo esc_attr( $id ) ?>"><?php echo esc_html( $label ) ?></label>
			</th>
			<td>
				<input type="text" class="regular-text" name="<?php echo esc_attr( $id ) ?>" id="<?php echo esc_attr( $id ) ?>"
					   value="<?php echo esc_attr( get_post_meta( $post->ID, $id, true ) ) ?>"
					   />
			</td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<th><label for="_tsoh_access"><?php esc_html_e( 'Access Information', '' ) ?></label></th>
		<td>
			<textarea rows="5" class="widefat" name="_tsoh_access" id="_tsoh_access"><?php echo esc_textarea( get_post_meta( $post->ID, '_tsoh_access', true ) ) ?></textarea>
		</td>
	</tr>
	</tbody>
</table>

<h3><?php esc_html_e( 'Contact', 'tsoh' ) ?></h3>

<table class="form-table">
	<tbody>
		<?php foreach ( [
			'tel'   => __( 'Tel', 'tsoh' ),
			'email' => __( 'Email', 'tsoh' ),
			'url'   => __( 'URL', 'tsoh' ),
		] as $key => $label) :
			$id = '_tsoh_' . $key;
			?>
		<tr>
			<th><label for="<?php echo esc_attr( $id ) ?>"><?php echo esc_html( $label ) ?></label></th>
			<td>
				<input type="<?php echo esc_attr( $key ) ?>" name="<?php echo esc_attr( $id ) ?>" id="<?php echo esc_attr( $id ) ?>"
					   class="regular-text"
					   value="<?php echo esc_attr( get_post_meta( $post->ID, $id, true ) ) ?>" />
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

