<?php

class IsusAPI {
	
	private static $instance = null;
	
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function __construct() {
		add_action('init', array($this, 'init'));
	}
	
	public function init() {
		add_action( 'rest_api_init', array($this, 'rest_api_init') );
	}
	
	public function rest_api_init($param) {
			register_rest_field(
					array('post', 'page', project),
					'content',
					array(
							'get_callback'    => array($this, 'do_divi_shortcodes'),
							'update_callback' => null,
							'schema'          => null,
					)
			);
			
			register_rest_field(
					array('post', 'page', project),
					'excerpt',
					array(
							'get_callback'    => array($this, 'do_divi_shortcodes'),
							'update_callback' => null,
							'schema'          => null,
					)
			);
	}
		
	public function do_divi_shortcodes( $object, $field_name, $request ) {
		global $post;
		$post = get_post($object['id']);
		
		global $wp_query;
		$wp_query->is_singular = true;
		
		$output = array(
				//'protected' => false
		);
		
		switch( $field_name ) {
			case 'content':
				$output['rendered'] =   apply_filters( 'the_content', get_the_content(null, false, $post));
				$output['striped'] = preg_replace('/\[\/?et_pb.*?\]/', '', strip_shortcodes(get_the_content(null, false, $post) ));
				break;
			case 'excerpt':
				$output['rendered'] =  apply_filters( 'the_excerpt', get_the_excerpt($post));
				$output['striped'] = preg_replace('/\[\/?et_pb.*?\]/', '', strip_shortcodes(get_the_excerpt($post) ));
				break;
		}
		
		return $output;
	}
}

\IsusAPI::get_instance();