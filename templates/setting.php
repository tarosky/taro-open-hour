<?php
/** @var \Tarosky\OpenHour\Admin $this */

$places = \Tarosky\OpenHour\Places::instance();
?>


<div class="wrap">

	<h2>
		<span style="font-size: 1em; vertical-align: middle; line-height: 1; height: 1em; width: 1em;" class="dashicons dashicons-location"></span>
		<?php echo esc_html( $this->get_title() ); ?>
	</h2>

	<form action="<?php echo admin_url( 'options-general.php?page=tsoh' ); ?>" method="post">

		<?php wp_nonce_field( 'tsoh_option' ); ?>

		<h2><?php esc_html_e( 'Business Places', 'tsoh' ); ?></h2>

		<table class="form-table">
			<tr>
				<th><label for="tsoh_place_post_type"><?php esc_html_e( 'Post Type', 'tsoh' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="tsoh_place_post_type" id="tsoh_place_post_type" value="1"
						   <?php checked( get_option( 'tsoh_place_post_type', 1 ) ); ?> />
						<?php esc_html_e( 'Create post type for business location.', 'tsoh' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If you don\'t need post type for locations, leave unchecked.', 'tsoh' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="tsoh_place_post_type_public"><?php esc_html_e( 'Post Type Visibility', 'tsoh' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="tsoh_place_post_type_public" id="tsoh_place_post_type_public"
							   value="1" <?php checked( get_option( 'tsoh_place_post_type_public' ) ); ?> />
						<?php esc_html_e( 'Post type for business places is public.', 'tsoh' ); ?>
					</label>

					<p class="description">
						<?php esc_html_e( 'If you need each single page for business places, check above as true. Default is false.', 'tsoh' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<label><?php esc_html_e( 'Treated as Location', 'tsoh' ); ?></label>
				</th>
				<td>
					<?php
					foreach ( get_post_types( array( 'public' => true ), OBJECT ) as $post_type ) :
						if ( 'location' === $post_type->name ) {
							continue;
						}
						?>
						<label class="tsoh-label">
							<input type="checkbox" name="tsoh_place_post_types[]"
								   value="<?php echo esc_attr( $post_type->name ); ?>"
								<?php checked( $places->is_supported( $post_type->name ) ); ?> />
							<?php echo esc_html( $post_type->label ); ?>
						</label>
					<?php endforeach; ?>
					<p class="description">
						<?php esc_html_e( 'Selected post types will be recognized as business place same as above.', 'tsoh' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="tsoh_google_api_key"><?php esc_html_e( 'API Key', 'tsoh' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" name="tsoh_google_api_key" id="tsoh_google_api_key"
						   value="<?php echo esc_attr( get_option( 'tsoh_google_api_key', '' ) ); ?>" />
					<p class="description">
						<?php esc_html_e( 'To display Google MAP with business location, enter Google API Key here.', 'tsoh' ); ?>
						<a class="button" href="https://developers.google.com/maps/gmp-get-started" target="_blank">
							<?php esc_html_e( 'Google API Documentation', 'tsoh' ); ?>
						</a>
					</p>
				</td>
			</tr>
		</table>

		<hr />

		<h2><?php esc_html_e( 'Business Hours', 'tsoh' ); ?></h2>

		<table class="form-table">
			<tr>
				<th>
					<label><?php esc_html_e( 'Post Type', 'tsoh' ); ?></label>
				</th>
				<td>
					<?php
					foreach ( get_post_types( array( 'public' => true ), OBJECT ) as $post_type ) :
						?>
						<label class="tsoh-label">
							<input type="checkbox" name="post_type[]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( tsoh_supported( $post_type->name ) ); ?> />
							<?php echo esc_html( $post_type->label ); ?>
						</label>
					<?php endforeach; ?>
					<p class="description">
						<?php esc_html_e( 'Select post types which have open hour.', 'tsoh' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="tsoh-default"><?php esc_html_e( 'Default Time Shift', 'tsoh' ); ?></label>
				</th>
				<td>
					<textarea name="default-time" id="default-time" rows="3" style="width: 90%;"
							  placeholder="<?php esc_attr_e( '09:00, 18:00', 'tsoh' ); ?>"
							  ><?php echo esc_textarea( tsoh_default( true ) ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Enter time shift in CSV format on each line. 1st is start time, 2nd is end time.', 'tsoh' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="default-days"><?php esc_html_e( 'Default Open day', 'tsoh' ); ?></label>
				</th>
				<td>
					<?php
					$days = (array) get_option( 'tsoh_default_days', range( 0, 4 ) );
					foreach ( array(
						__( 'Mon' ),
						__( 'Tue' ),
						__( 'Wed' ),
						__( 'Thu' ),
						__( 'Fri' ),
						__( 'Sat' ),
						__( 'Sun' ),
					) as $index => $label ) {
						$format = <<<HTML
							<label style="display: inline-block; margin-right: 1em; margin-bottom: 0.5em; background: #f9f9f9; border: 1px solid #ddd; padding: 5px 10px;">
								<input type="checkbox" name="default_days[]" value="%d" %s/> %s
							</label>
HTML;

						printf(
							$format,
							$index,
							checked( false !== array_search( $index, $days, true ), true, false ),
							$label
						);
					}
					?>
				</td>
			</tr>


		</table>

		<?php submit_button( __( 'Update Option', 'tsoh' ) ); ?>

	</form>

</div><!-- //.wrap -->
