<?php

class IsusAPI {
	
	private static $instance = null;
	
	/**
	 * create or get object instance 
	 * @return IsusAPI
	 */
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
		$this->create_isus_tax();
	}
	
	/**
	 * Re-registry post content field
	 * @param array $param
	 */
	public function rest_api_init($param) {
			register_rest_field(
					array('post', 'page', 'project'),
					'content',
					array(
							'get_callback'    => array($this, 'do_divi_shortcodes'),
							'update_callback' => null,
							'schema'          => null,
					)
			);
			
			register_rest_field(
					array('post', 'page', 'project'),
					'excerpt',
					array(
							'get_callback'    => array($this, 'do_divi_shortcodes'),
							'update_callback' => null,
							'schema'          => null,
					)
			);
	}
	
	/**
	 * 
	 * redo content field resolving the shorcodes or removing
	 * 
	 * @param WP_Post $object
	 * @param string $field_name
	 * @param WP_REST_Request $request
	 * @return array
	 */
	public function do_divi_shortcodes( $object, $field_name, $request ) {
		global $post;
		$post = get_post($object['id']);
		
		global $wp_query;
		$wp_query->setup_postdata($post);
		$wp_query->queried_object = $post;
		$wp_query->queried_object_id = $object['id'];
		$wp_query->is_singular = true;
		
		$output = array();
		
		switch( $field_name ) {
			case 'content':
				$output['striped'] = preg_replace('/\[\/?et_pb.*?\]/', '', strip_shortcodes(get_the_content(null, false, $post) ));
				break;
			case 'excerpt':
				$output['striped'] = preg_replace('/\[\/?et_pb.*?\]/', '', strip_shortcodes(get_the_excerpt($post) ));
				break;
		}
		
		//Check if has Divi Builder and if is loaded
		if( ! class_exists('ET_Builder_Module_Text') ) {
			if(function_exists('et_builder_add_main_elements') ) {
				et_builder_add_main_elements();
			}
		}
		
		switch( $field_name ) {
			case 'content':
				$output['rendered'] = apply_filters( 'the_content', get_the_content(null, false, $post));
				break;
			case 'excerpt':
				$output['rendered'] = apply_filters( 'the_excerpt', get_the_excerpt($post));
				break;
		}
		
		$reW = '/(<iframe )(.*)(?\'width\'width=[\'"]\d*[\'"])(.*)((.*\/>)|(>.*<\/iframe>))/i';
		$reH = '/(<iframe )(.*)(?\'height\'height=[\'"]\d*[\'"])(.*)((.*\/>)|(>.*<\/iframe>))/i';
		$subst = '$1$2$4$5';
		
		$output['rendered'] = preg_replace($reW, $subst, $output['rendered']);
		$output['rendered'] = preg_replace($reH, $subst, $output['rendered']);
		return $output;
	}
	
	function create_isus_tax() {
		// Add new taxonomy, make it hierarchical (like categories)
		$labels = array(
				'name'              => __( 'iSUS', 'isus' ),
				'singular_name'     => __( 'iSUS', 'isus' ),
				'search_items'      => __( 'Procurar iSUS', 'isus' ),
				'all_items'         => __( 'Todo o iSUS', 'isus' ),
				'parent_item'       => __( 'Parente iSUS', 'isus' ),
				'parent_item_colon' => __( 'Parente iSUS:', 'isus' ),
				'edit_item'         => __( 'Editar iSUS', 'isus' ),
				'update_item'       => __( 'Atualizar iSUS', 'isus' ),
				'add_new_item'      => __( 'Adcionar iSUS', 'isus' ),
				'new_item_name'     => __( 'Novo iSUS', 'isus' ),
				'menu_name'         => __( 'iSUS', 'isus' ),
		);
		
		$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'isus' ),
		);
		
		register_taxonomy( 'isus', array( 'project', 'post' ), $args );	
	}
}

\IsusAPI::get_instance();