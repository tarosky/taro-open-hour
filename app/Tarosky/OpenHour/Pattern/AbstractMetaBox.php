<?php

namespace Tarosky\OpenHour\Pattern;

use Tarosky\OpenHour\Formatter;
use Tarosky\OpenHour\Model;
use Tarosky\OpenHour\Places;

/**
 * Meta box class.
 *
 * @package tsoh
 * @property Model     $model
 * @property Formatter $formatter
 * @property Places $places
 */
abstract class AbstractMetaBox extends Singleton {
	
	protected static $is_duplicated = false;
	
	protected $id = '';
	
	protected $position = 'side';
	
	protected $priority = 'low';
	
	protected function init() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
	}
	
	/**
	 * Add meta box if supported.
	 *
	 * @param string $post_type
	 */
	public function add_meta_box( $post_type ) {
		if ( ! $this->should_display( $post_type ) ) {
			return;
		}
		add_meta_box( $this->id, $this->get_title(), [ $this, 'render_meta_box' ], $post_type, $this->position, $this->priority );
	}
	
	
	/**
	 * Detect if this meta box is duplicated.
	 *
	 * @return bool
	 */
	public static function is_duplicated() {
		return static::$is_duplicated;
	}
	
	/**
	 * Executed on save post hook.
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save_post( $post_id, $post ) {
		// Override this to do something.
	}
	
	/**
	 * Get meta box title.
	 *
	 * @return string
	 */
	abstract protected function get_title();
	
	/**
	 * Detect if post type should have meta box.
	 *
	 * @param string $post_type
	 *
	 * @return boolean
	 */
	abstract protected function should_display( $post_type );
	
	/**
	 * Render meta box content.
	 *
	 * @param \WP_Post $post
	 *
	 * @return void
	 */
	abstract public function render_meta_box( $post );
	
	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'places':
				return Places::instance();
			case 'model':
				return Model::instance();
				break;
			case 'formatter':
				return Formatter::instance();
				break;
			default:
				return null;
				break;
		}
	}
}
