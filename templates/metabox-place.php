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
		<input type="checkbox" name="_tsoh_site_location" id="_tsoh_site_location" value="1" <?php checked( $this->places->is_site_location( $post ) ); ?> />
		<?php esc_html_e( 'Mark as site main place(e.g. company address)', 'tsoh' ); ?>
	</label>
</p>
<h3><?php esc_html_e( 'Address', 'tsoh' ); ?></h3>
<table class="form-table">
	<tbody>
	<?php
	foreach ( $this->places->get_address_parts() as $key => $label ) :
		$id = '_tsoh_' . $key;
		?>
		<tr>
			<th scope="row">
				<label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			</th>
			<td>
				<input type="text" class="regular-text" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>"
					   value="<?php echo esc_attr( get_post_meta( $post->ID, $id, true ) ); ?>"
					   />
			</td>
		</tr>
	<?php endforeach; ?>
	<tr>
		<th><label for="_tsoh_access"><?php esc_html_e( 'Access Information', 'tsoh' ); ?></label></th>
		<td>
			<textarea rows="5" class="widefat" name="_tsoh_access" id="_tsoh_access"><?php echo esc_textarea( get_post_meta( $post->ID, '_tsoh_access', true ) ); ?></textarea>
		</td>
	</tr>
	<tr>
		<th><label for="_tsoh_local_business_type"><?php esc_html_e( 'Business Type', 'tsoh' ); ?></label></th>
		<td>
			<input type="text" class="regular-text" name="_tsoh_local_business_type" id="_tsoh_local_business_type"
				   value="<?php echo esc_attr( get_post_meta( $post->ID, '_tsoh_local_business_type', true ) ); ?>"
				   placeholder="<?php esc_attr_e( 'Default: LocalBusiness', 'tsoh' ); ?>" />
			<p class="description">
				<?php
				printf(
					// translators: %s is entity type of schema.org.
					esc_html__( 'This type is used to display structured data for search engines. Default type is "%s". This should be sub type of "LocalBusiness" and find proper one at Schema.org', 'tsoh' ),
					esc_html( tsoh_get_default_local_business( $post->post_type ) )
				);
				?>
				<a class="button" href="https://schema.org/LocalBusiness#subtypes" target="_blank">
					<?php esc_html_e( 'Visit Schema.org', 'tsoh' ); ?>
				</a>
			</p>
		</td>
	</tr>
	</tbody>
</table>

<h3><?php esc_html_e( 'Contact', 'tsoh' ); ?></h3>

<table class="form-table">
	<tbody>
		<?php
		foreach ( array(
			'tel'   => __( 'Tel', 'tsoh' ),
			'email' => __( 'Email', 'tsoh' ),
			'url'   => __( 'URL', 'tsoh' ),
		) as $key => $label ) :
			$id = '_tsoh_' . $key;
			?>
		<tr>
			<th><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $id ); ?>" id="<?php echo esc_attr( $id ); ?>"
					   class="regular-text"
					   value="<?php echo esc_attr( get_post_meta( $post->ID, $id, true ) ); ?>" />
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

