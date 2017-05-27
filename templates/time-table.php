<?php
/*
 * You can override this template with /template-part/tsoh/time-table.php in your themes directory.
 * Copy and customise one.
 *
 * @package tsoh
 * @since 1.0.0
 */

/* @var WP_Post $post       Post object */
/* @var array   $time_table Time table array */
/* @var int     $day        Today's index. Monday(0) to Sunday(6) */
/** @var string $classes    Class name */
?>

<table class="<?= esc_attr( $classes ) ?>">
	<thead>
	<tr>
		<th scope="col" class="time-belt"><?php _e( 'Open Hour', 'tsoh' ) ?></th>
		<th scope="col" class="date"><?php _e( 'Mon' ) ?></th>
		<th scope="col" class="date"><?php _e( "Tue" ) ?></th>
		<th scope="col" class="date"><?php _e( "Wed" ) ?></th>
		<th scope="col" class="date"><?php _e( "Thu" ) ?></th>
		<th scope="col" class="date"><?php _e( "Fri" ) ?></th>
		<th scope="col" class="date"><?php _e( "Sat" ) ?></th>
		<th scope="col" class="date"><?php _e( "Sun" ) ?></th>
	</tr>
	</thead>
	<?php if ( tsoh_holiday_note( $post ) ) : ?>
	<tfoot>
		<tr class="tsoh-holiday-note">
			<td colspan="8">
			<?php tsoh_the_holiday_note( '', $post ) ?>
			</td>
		</tr>
	</tfoot>
	<?php endif; ?>
	<tbody>
	<?php foreach ( $time_table as $time ) : ?>
		<tr class="tsoh-row">
			<th scope="row" class="tsoh-row-header<?php if ( $time['now'] ) { echo ' now'; } ?>">
				<?= esc_html( $time['open'] . "~" . $time['close'] ) ?>
			</th>
			<?php for ( $i = 0; $i < 7; $i++ ) : ?>
				<td class="tsoh-cell <?php if ( $day == $i ) { echo 'today'; } ?>">
					<?= isset( $time[ $i ] ) ? '&#x2713;' : '-' ?>
				</td>
			<?php endfor; ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
