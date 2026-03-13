<?php
/** @var \Tarosky\OpenHour\Admin $this */

$places = \Tarosky\OpenHour\Places::instance();
?>


<div class="wrap">

	<h2>
		<span style="font-size: 1em; vertical-align: middle; line-height: 1; height: 1em; width: 1em;" class="dashicons dashicons-location"></span>
		<?php echo esc_html( $this->get_title() ); ?>
	</h2>

	<form action="<?php echo esc_url( admin_url( 'options-general.php?page=taro-open-hour' ) ); ?>" method="post">

		<?php wp_nonce_field( 'tsoh_option' ); ?>

		<h2><?php esc_html_e( 'Business Places', 'taro-open-hour' ); ?></h2>

		<table class="form-table">
			<tr>
				<th><label for="tsoh_place_post_type"><?php esc_html_e( 'Post Type', 'taro-open-hour' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="tsoh_place_post_type" id="tsoh_place_post_type" value="1"
							<?php checked( get_option( 'tsoh_place_post_type', 1 ) ); ?> />
						<?php esc_html_e( 'Create post type for business location.', 'taro-open-hour' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'If you don\'t need post type for locations, leave unchecked.', 'taro-open-hour' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th><label for="tsoh_place_post_type_public"><?php esc_html_e( 'Post Type Visibility', 'taro-open-hour' ); ?></label></th>
				<td>
					<label>
						<input type="checkbox" name="tsoh_place_post_type_public" id="tsoh_place_post_type_public"
								value="1" <?php checked( get_option( 'tsoh_place_post_type_public' ) ); ?> />
						<?php esc_html_e( 'Post type for business places is public.', 'taro-open-hour' ); ?>
					</label>

					<p class="description">
						<?php esc_html_e( 'If you need each single page for business places, check above as true. Default is false.', 'taro-open-hour' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<label><?php esc_html_e( 'Treated as Location', 'taro-open-hour' ); ?></label>
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
						<?php esc_html_e( 'Selected post types will be recognized as business place same as above.', 'taro-open-hour' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="tsoh_google_api_key"><?php esc_html_e( 'API Key', 'taro-open-hour' ); ?></label>
				</th>
				<td>
					<input type="text" class="regular-text" name="tsoh_google_api_key" id="tsoh_google_api_key"
							value="<?php echo esc_attr( get_option( 'tsoh_google_api_key', '' ) ); ?>" />
					<p class="description">
						<?php esc_html_e( 'To display Google MAP with business location, enter Google API Key here.', 'taro-open-hour' ); ?>
						<a class="button" href="https://developers.google.com/maps/gmp-get-started" target="_blank">
							<?php esc_html_e( 'Google API Documentation', 'taro-open-hour' ); ?>
						</a>
					</p>
				</td>
			</tr>
		</table>

		<p class="description">
			<?php esc_html_e( 'This plugin outputs structured data (JSON-LD) for business locations. You can validate your markup with Google\'s testing tools:', 'taro-open-hour' ); ?>
			<a href="https://search.google.com/test/rich-results" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Rich Results Test', 'taro-open-hour' ); ?>
			</a>
			|
			<a href="https://validator.schema.org/" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Schema Markup Validator', 'taro-open-hour' ); ?>
			</a>
		</p>

		<hr />

		<h2><?php esc_html_e( 'Business Hours', 'taro-open-hour' ); ?></h2>

		<table class="form-table">
			<tr>
				<th>
					<label><?php esc_html_e( 'Post Type', 'taro-open-hour' ); ?></label>
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
						<?php esc_html_e( 'Select post types which have open hour.', 'taro-open-hour' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="tsoh-default"><?php esc_html_e( 'Default Time Shift', 'taro-open-hour' ); ?></label>
				</th>
				<td>
					<textarea name="default-time" id="default-time" rows="3" style="width: 90%;"
								placeholder="<?php esc_attr_e( '09:00, 18:00', 'taro-open-hour' ); ?>"
								><?php echo esc_textarea( tsoh_default( true ) ); ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Enter time shift in CSV format on each line. 1st is start time, 2nd is end time.', 'taro-open-hour' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="default-days"><?php esc_html_e( 'Default Open day', 'taro-open-hour' ); ?></label>
				</th>
				<td>
					<?php
					$days = (array) get_option( 'tsoh_default_days', range( 0, 4 ) );
					foreach ( array(
						__( 'Mon', 'taro-open-hour' ),
						__( 'Tue', 'taro-open-hour' ),
						__( 'Wed', 'taro-open-hour' ),
						__( 'Thu', 'taro-open-hour' ),
						__( 'Fri', 'taro-open-hour' ),
						__( 'Sat', 'taro-open-hour' ),
						__( 'Sun', 'taro-open-hour' ),
					) as $index => $label ) {
						$format = <<<HTML
							<label style="display: inline-block; margin-right: 1em; margin-bottom: 0.5em; background: #f9f9f9; border: 1px solid #ddd; padding: 5px 10px;">
								<input type="checkbox" name="default_days[]" value="%d" %s/> %s
							</label>
HTML;

						printf(
							$format,
							(int) $index,
							checked( false !== array_search( $index, $days, true ), true, false ),
							esc_html( $label )
						);
					}
					?>
				</td>
			</tr>


		</table>

		<?php submit_button( __( 'Update Option', 'taro-open-hour' ) ); ?>

	</form>

</div><!-- //.wrap -->
