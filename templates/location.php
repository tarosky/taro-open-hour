<?php
/** @var WP_Post                 $post */
/** @var Tarosky\OpenHour\Places $this */
/** @var array                   $settings */
$src    = $this->get_map_src( $post );
$access = get_post_meta( $post->ID, '_tsoh_access', true );
$contacts = $this->location_contacts( $post );
?>

<div class="tsoh-location tsoh-location-card">

	<?php if ( $src ) : ?>
	<figure class="tsoh-location-map">
		<iframe src="<?php echo esc_url( $src ) ?>" class="tsoh-location-map-iframe" frameborder="0"></iframe>
	</figure>
	<?php endif; ?>
	
	<address class="tsoh-location-address">
		<p class="tsoh-location-address-text">
			<strong><?php echo wp_kses_post( get_the_title( $post ) ) ?></strong><br />
			<?php echo esc_html( $this->format_address( $post ) ) ?>
		</p>
		<?php if ( $access && ! $settings['no_access'] ) : ?>
		<p class="tsoh-location-address-access">
			<?php echo esc_html( $access ) ?>
		</p>
		<?php endif; ?>
	</address>
	
	<?php if ( $contacts ) : ?>
	<div class="tsoh-location-contacts">
		<?php foreach ( $contacts as $key => $contact ) :
			?>
		<p class="tsoh-location-contact">
			<span class="dashicons dashicons-<?php echo esc_attr( $contact['icon'] ) ?> tsoh-location-contact-icon"></span>
			<a href="<?php echo esc_url( $contact['url'] ) ?>"><?php echo esc_html( $contact['label'] ) ?></a>
		</p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
	
</div>
