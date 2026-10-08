<?php

namespace Tarosky\OpenHour;

use Tarosky\OpenHour\Pattern\ControllerAccessor;
use Tarosky\OpenHour\Pattern\Singleton;

/**
 * Register dynamic blocks.
 *
 * @package tsoh
 */
class Blocks extends Singleton {

	use ControllerAccessor;

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		add_action( 'init', array( $this, 'register_blocks' ), 20 );
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function register_blocks() {
		register_block_type(
			'tsoh/open-hour',
			array(
				'api_version'     => '3',
				'title'           => __( 'Open Hour', 'taro-open-hour' ),
				'category'        => 'widgets',
				'editor_script'   => 'tsoh-block-editor',
				'editor_style'    => 'tsoh-style',
				'attributes'      => array(
					'postId' => array(
						'type'    => 'number',
						'default' => 0,
					),
				),
				'render_callback' => array( $this, 'render_open_hour' ),
			)
		);
		register_block_type(
			'tsoh/business-place',
			array(
				'api_version'     => '3',
				'title'           => __( 'Business Place', 'taro-open-hour' ),
				'category'        => 'widgets',
				'editor_script'   => 'tsoh-block-editor',
				'editor_style'    => 'tsoh-style',
				'attributes'      => array(
					'postId'   => array(
						'type'    => 'number',
						'default' => 0,
					),
					'noMap'    => array(
						'type'    => 'boolean',
						'default' => false,
					),
					'noAccess' => array(
						'type'    => 'boolean',
						'default' => false,
					),
				),
				'render_callback' => array( $this, 'render_business_place' ),
			)
		);
	}

	/**
	 * Resolve the post to display.
	 *
	 * 1. Specified post ID.
	 * 2. Current post if it matches the condition.
	 * 3. Site location.
	 *
	 * Non-public posts are displayed only for users who can read them.
	 *
	 * @param int      $post_id   Post ID specified in block attributes.
	 * @param callable $condition Callback to check whether the post is valid.
	 * @return \WP_Post|null
	 */
	protected function resolve_post( $post_id, $condition ) {
		$candidates = array();
		if ( $post_id ) {
			$candidates[] = get_post( $post_id );
		} else {
			$candidates[] = get_post();
			$candidates[] = $this->places->get_site_location();
		}
		foreach ( $candidates as $post ) {
			if ( ! $post ) {
				continue;
			}
			if ( 'publish' !== $post->post_status && ! current_user_can( 'read_post', $post->ID ) ) {
				continue;
			}
			if ( $condition( $post ) ) {
				return $post;
			}
		}
		return null;
	}

	/**
	 * Wrap output with block wrapper.
	 *
	 * @param string $html  HTML content.
	 * @param string $class Additional class name.
	 * @return string
	 */
	protected function wrap( $html, $class ) {
		if ( ! $html ) {
			return '';
		}
		$attributes = \WP_Block_Supports::$block_to_render ? get_block_wrapper_attributes( array( 'class' => $class ) ) : sprintf( 'class="%s"', esc_attr( $class ) );
		return sprintf( '<div %s>%s</div>', $attributes, $html );
	}

	/**
	 * Render open hour block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_open_hour( $attributes ) {
		$post = $this->resolve_post(
			(int) ( $attributes['postId'] ?? 0 ),
			function ( $post ) {
				return tsoh_has_timetable( $post );
			}
		);
		if ( ! $post ) {
			return '';
		}
		tsoh_load_style();
		return $this->wrap( (string) tsoh_get_timetable( false, array(), $post ), 'wp-block-tsoh-open-hour' );
	}

	/**
	 * Render business place block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string
	 */
	public function render_business_place( $attributes ) {
		$post = $this->resolve_post(
			(int) ( $attributes['postId'] ?? 0 ),
			function ( $post ) {
				return $this->places->is_supported( $post->post_type );
			}
		);
		if ( ! $post ) {
			return '';
		}
		tsoh_load_style();
		$settings = array(
			'no_map'    => ! empty( $attributes['noMap'] ),
			'no_access' => ! empty( $attributes['noAccess'] ),
		);
		return $this->wrap( $this->places->display_location( $post, 'card', $settings ), 'wp-block-tsoh-business-place' );
	}
}
