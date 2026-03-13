<?php
/*
 * You can override this template with /template-part/tsoh/time-table.php in your themes directory.
 * Copy and customise one.
 *
 * @package tsoh
 * @since 1.0.0
 */

/* @var WP_Post $post Post object */
/* @var array $time_table Time table array */
/* @var int $day Today's index. Monday(0) to Sunday(6) */
/** @var string $classes Class name */
?>

<figure class="tsoh-time-table-container">
	<?php do_action( 'tsoh_before_time_table', $post, $time_table, $classes ); ?>
	<table class="<?php echo esc_attr( $classes ); ?>">
		<thead class="tsoh-time-table-header">
		<tr>
			<th scope="col" class="time-belt"><?php esc_html_e( 'Open Hour', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Mon', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Tue', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Wed', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Thu', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Fri', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Sat', 'taro-open-hour' ); ?></th>
			<th scope="col" class="date"><?php esc_html_e( 'Sun', 'taro-open-hour' ); ?></th>
		</tr>
		</thead>
		<?php if ( tsoh_holiday_note( $post ) ) : ?>
			<tfoot class="tsoh-time-table-footer">
			<tr class="tsoh-holiday-note">
				<td colspan="8">
					<?php tsoh_the_holiday_note( '', $post ); ?>
				</td>
			</tr>
			</tfoot>
		<?php endif; ?>
		<tbody>
		<?php foreach ( $time_table as $time ) : ?>
			<tr class="tsoh-row
			<?php
			if ( $time['now'] ) {
				echo ' now';
			}
			?>
			"
				data-start="<?php echo esc_attr( $time['open'] ); ?>"
				data-end="<?php echo esc_attr( $time['close'] ); ?>">
				<th scope="row" class="tsoh-row-header">
					<?php echo esc_html( $time['open'] . '~' . $time['close'] ); ?>
				</th>
				<?php
				for ( $i = 0; $i < 7; $i++ ) :
					$classes = array( 'tsoh-cell' );
					if ( $i === $day ) {
						$classes[] = 'today';
					}
					$classes[] = isset( $time[ $i ] ) ? 'open' : 'close';
					?>
					<td data-open="<?php echo isset( $time[ $i ] ) ? 'true' : 'false'; ?>"
						class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php echo isset( $time[ $i ] ) ? '&#x2713;' : '-'; ?>
					</td>
				<?php endfor; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php do_action( 'tsoh_after_time_table', $post, $time_table, $classes ); ?>
</figure>
