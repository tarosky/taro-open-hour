<?php
/**
 * @var \Tarosky\OpenHour\MetaBoxes\OpenHourMetaBox $this
 * @package tsoh
 */
wp_nonce_field( 'tsoh_meta_box', '_tsohnonce' );
?>
<table class="form-table" id="tsoh-time-table">
	<thead>
	<tr>
		<td class="right" colspan="9">
			<strong><?php _e( 'Labels', 'tsoh' ); ?>:</strong>
			<span><?php _ex( 'Open', 'time-table', 'tsoh' ); ?>:</span> &#x2713;,
			<span><?php _ex( 'Close', 'time-table', 'tsoh' ); ?>:</span> -
		</td>
	</tr>
	<tr>
		<th scope="col"><?php esc_html_e( 'Time Shift', 'tsoh' ); ?></th>
		<?php
		foreach (
			array(
				__( 'Mon', 'tsoh' ),
				__( 'Tue', 'tsoh' ),
				__( 'Wed', 'tsoh' ),
				__( 'Thu', 'tsoh' ),
				__( 'Fri', 'tsoh' ),
				__( 'Sat', 'tsoh' ),
				__( 'Sun', 'tsoh' ),
			) as $d
		) :
			?>
			<th scope="col"><?php echo $d; ?></th>
		<?php endforeach; ?>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th scope="row">&nbsp;</th>
		<td colspan="5">
			<p>
				<input type="text" class="tsoh-time" id="tsoh-time-start" name="" value="" placeholder="ex. 09:00"/> ~
				<input type="text" class="tsoh-time" id="tsoh-time-end" name="" value="" placeholder="ex. 12:00"/>
				<a class="button" href="#" id="timeadd"><?php _e( 'Add', 'tsoh' ); ?></a>
			</p>
		</td>
		<td colspan="3">
			<p class="right">
				<a class="button" href="#" id="timeadd-default"><?php esc_html_e( 'Fill default time shift', 'tsoh' ); ?></a>
			</p>
		</td>
	</tr>
	<tr>
		<th>
			<label for="tsoh_note"><?php esc_html_e( 'Holiday Notes', 'tsoh' ); ?></label>
		</th>
		<td colspan="8">
			<textarea name="tsoh_note" id="tsoh_note" cols="40" rows="3"
					  placeholder="<?php esc_attr_e( 'ex. Closing every Monday & National Holidays.', 'tsoh' ); ?>"
					  style="width:90%;"><?php echo esc_textarea( get_post_meta( $post->ID, '_tsoh_holiday_note', true ) ); ?></textarea>
		</td>
	</tr>
	</tfoot>
	<tbody>
	<?php
	$counter = 0;
	foreach ( $this->model->get_timetable( $post->ID ) as $start => $time ) :
		if ( count( $time ) < 3 ) {
			continue;
		}
		$counter ++;
		?>
		<tr class="<?php echo esc_attr( ( 0 === $counter % 2 ) ? 'alt' : 'odd' ); ?>">
			<th scope="row">
				<input name="tsoh_open_hour[<?php echo $counter - 1; ?>]" type="text"
					   value="<?php echo "{$time['open']}-{$time['close']}"; ?>"/>
			</th>
			<?php for ( $index = 0; $index < 7; $index ++ ) : ?>
				<td>
					<select name="tsoh_date_<?php echo $index; ?>[<?php echo $counter - 1; ?>]">
						<option value=""<?php selected( ! isset( $time[ $index ] ) ); ?>>-</option>
						<option value="0"<?php selected( isset( $time[ $index ] ) ); ?>>&#x2713;</option>
					</select>
				</td>
			<?php endfor; ?>
			<td>
				<a class="delete-time-shift" href="#<?php echo $counter; ?>"><?php _e( 'Delete', 'tsoh' ); ?></a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<p class="description right">
	<?php esc_html_e( 'Changes are not saved until you push update button.', 'tsoh' ); ?>
</p>

<?php
/**
 * tsoh_after_meta_box
 *
 * Executed right after meta box section.
 * If you need extra settings, use this action.
 *
 * @package tsoh
 * @param WP_Post $post
 */
do_action( 'tsoh_after_meta_box', $post );
?>
