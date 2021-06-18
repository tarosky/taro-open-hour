<?php

namespace Tarosky\OpenHour;


use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Admin interface
 * @package tsoh
 */
class Admin extends Singleton {

	protected function init() {

		add_action(
			'init',
			function() {
				wp_register_style( 'select2', tsoh_asset( 'css/select2.min.css' ), array(), '4.0.11' );
				wp_register_script( 'select2', tsoh_asset( 'js/select2.min.js' ), array( 'jquery' ), '4.0.11', true );
			}
		);

		add_action(
			'admin_enqueue_scripts',
			function() {
				wp_enqueue_style( 'tsoh-admin', tsoh_asset( 'css/admin.css' ), array( 'select2' ), tsoh_version() );
				wp_enqueue_script( 'tsoh-admin', tsoh_asset( 'js/admin.js' ), array( 'select2', 'wp-api-request' ), tsoh_version(), true );
			}
		);
		add_action(
			'admin_menu',
			function() {
				add_options_page( $this->get_title(), __( 'Business Places', 'tsoh' ), 'manage_options', 'tsoh', array( $this, 'admin_screen' ) );
			}
		);
		add_action( 'admin_init', array( $this, 'save_option' ) );
		// If no post type is selected, show link.
		if ( current_user_can( 'manage_options' ) && ! get_option( 'tsoh_post_types', array() ) ) {
			add_action(
				'admin_notices',
				function() {
					/* translators: %s link to admin screen. */
					$message = sprintf( __( '[Business Places] No post type is specified. Please go to <a href="%s">setting screen</a>.', 'tsoh' ), esc_url( admin_url( 'options-general.php?page=tsoh' ) ) );
					echo wp_kses_post( "<div class=\"error\"><p>{$message}</p></div>" );
				}
			);
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
		if ( isset( $_GET['page'], $_POST['_wpnonce'] ) && ( 'tsoh' === $_GET['page'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'tsoh_option' ) ) {
			// Save location.
			update_option( 'tsoh_place_post_type', filter_input( INPUT_POST, 'tsoh_place_post_type' ) );
			update_option( 'tsoh_place_post_type_public', filter_input( INPUT_POST, 'tsoh_place_post_type_public' ) );
			update_option( 'tsoh_place_post_types', filter_input( INPUT_POST, 'tsoh_place_post_types', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY ) );
			update_option( 'tsoh_google_api_key', filter_input( INPUT_POST, 'tsoh_google_api_key' ) );
			// Save post type
			$post_types = isset( $_POST['post_type'] ) ? (array) $_POST['post_type'] : array();
			update_option(
				'tsoh_post_types',
				array_filter(
					$post_types,
					function( $post_type ) {
						return post_type_exists( $post_type );
					}
				)
			);
			// Save time
			update_option( 'tsoh_default_time', sanitize_textarea_field( (string) $_POST['default-time'] ) );
			// days
			$days = isset( $_POST['default_days'] ) ? array_map( 'intval', (array) $_POST['default_days'] ) : array();
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
