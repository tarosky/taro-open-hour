<?php

namespace Tarosky\OpenHour;


use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Admin interface
 * @package tsoh
 */
class Admin extends Singleton {

	/**
	 * Bootstrap
	 *
	 * @param array $settings
	 */
	protected function init( array $settings = [] ) {
		add_action( 'admin_enqueue_scripts', function() {
			wp_enqueue_style( 'tsoh-admin', tsoh_asset( 'css/admin.css' ), [], tsoh_version() );
		} );
		add_action( 'admin_menu', function() {
			add_options_page( $this->get_title(), __( 'Business Places', 'tsoh' ), 'manage_options', 'tsoh', [ $this, 'admin_screen' ] );
		} );
		add_action( 'admin_init', [ $this, 'save_option' ] );
		// If no post type is selected, show link.
		if ( current_user_can( 'manage_options' ) && ! get_option( 'tsoh_post_types', [] ) ) {
			add_action( 'admin_notices', function() {
				/* translators: %s link to admin screen. */
				$message = sprintf( __( '[Taro Open Hour] No post type is specified. Please go to <a href="%s">setting screen</a>.', 'tsoh' ), admin_url( 'options-general.php?page=tsoh' ) );
				echo "<div class=\"error\"><p>{$message}</p></div>";
			} );
		}
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Business Places Setting', 'tsoh' );
	}

	/**
	 * Save post types
	 */
	public function save_option() {
		if ( isset( $_GET['page'], $_POST['_wpnonce'] ) && ( 'tsoh' == $_GET['page'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'tsoh_option' ) ) {
			// Save location.
			update_option( 'tsoh_place_post_type', filter_input( INPUT_POST, 'tsoh_place_post_type' ) );
			update_option( 'tsoh_place_post_type_public', filter_input( INPUT_POST, 'tsoh_place_post_type_public' ) );
			update_option( 'tsoh_place_post_types', filter_input( INPUT_POST, 'tsoh_place_post_types', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
			// Save post type
			$post_types = isset( $_POST['post_type'] ) ? (array) $_POST['post_type'] : [];
			update_option( 'tsoh_post_types', array_filter( $post_types, function( $post_type ) {
				return post_type_exists( $post_type );
			} ) );
			// Save time
			update_option( 'tsoh_default_time', sanitize_textarea_field( (string) $_POST['default-time'] ) );
			// days
			$days = isset( $_POST['default_days'] ) ? array_map( 'intval', (array) $_POST['default_days'] ) : [];
			update_option( 'tsoh_default_days', $days );
			wp_redirect( admin_url( 'options-general.php?page=tsoh' ) );
			exit;
		}
	}

	/**
	 * Register submenu
	 */
	public function admin_screen() {
		$path = tsoh_template( 'setting.php' );
		if ( $path ) {
			include $path;
		}
	}

}
