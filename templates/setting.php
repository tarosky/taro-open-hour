<?php /** @var \Tarosky\OpenHour\Admin $this */ ?>


<div class="wrap">

	<h2>
		<span style="font-size: 1em; vertical-align: middle; line-height: 1; height: 1em; width: 1em;" class="dashicons dashicons-clock"></span>
		<?= esc_html( $this->get_title() ) ?>
	</h2>

	<form action="<?= admin_url( 'options-general.php?page=tsoh' ) ?>" method="post">
		<?php wp_nonce_field( 'tsoh_option' ) ?>

		<table class="form-table">

			<tr>
				<th>
					<label><?php esc_html_e( 'Post Type', 'tsoh' ) ?></label>
				</th>
				<td>
					<?php
					foreach ( get_post_types( [], OBJECT ) as $post_type ) :
						if ( false !== array_search( $post_type->name, [ 'attachment', 'nav_menu_item', 'customize_changeset', 'custom_css', 'revision' ] ) ) {
							continue;
						}
						?>
						<label style="display: inline-block; margin-right: 1em; margin-bottom: 0.5em; background: #f9f9f9; border: 1px solid #ddd; padding: 5px 10px;">
							<input type="checkbox" name="post_type[]" value="<?= esc_attr( $post_type->name ) ?>" <?php checked( tsoh_supported( $post_type->name ) ) ?> />
							<?= esc_html( $post_type->label ) ?>
						</label>
					<?php endforeach;  ?>
					<p class="description">
						<?php esc_html_e( 'Select post types which have open hour.', 'tsoh' ) ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="tsoh-default"><?php esc_html_e( 'Default Time Shift', 'tsoh' ) ?></label>
				</th>
				<td>
					<textarea name="default-time" id="default-time" rows="3" style="width: 90%;"
							  placeholder="<?php esc_attr_e( '09:00, 18:00', 'tsoh' ) ?>"
							  ><?= esc_textarea( tsoh_default( true ) ) ?></textarea>
					<p class="description">
						<?php esc_html_e( 'Enter time shift in CSV format on each line. 1st is start time, 2nd is end time.' ) ?>
					</p>
				</td>
			</tr>

			<tr>
				<th>
					<label for="default-days"><?php esc_html_e( 'Default Open day', 'tsoh' ) ?></label>
				</th>
				<td>
					<?php
					$days = (array) get_option( 'tsoh_default_days', range( 0, 4 ) );
					foreach ( [
						__( 'Mon' ), __( 'Tue' ), __( 'Wed' ), __( 'Thu' ), __( 'Fri' ), __( 'Sat' ), __( 'Sun' )
					] as $index => $label ) {
						$format = <<<HTML
							<label style="display: inline-block; margin-right: 1em; margin-bottom: 0.5em; background: #f9f9f9; border: 1px solid #ddd; padding: 5px 10px;">
								<input type="checkbox" name="default_days[]" value="%d" %s/> %s
							</label>
HTML;

						printf(
							$format,
							$index,
							checked( false !== array_search( $index, $days ), true, false ),
							$label
						);
					}
					?>
				</td>
			</tr>


		</table>

		<?php submit_button( __( 'Update Option', 'tsoh' ) ) ?>

	</form>

</div><!-- //.wrap -->
