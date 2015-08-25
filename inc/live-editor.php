<?php

class SiteOrigin_Panels_Live_Editor {

	private $post_id;

	function __construct(){

		if( !empty($_GET['panels_customize_post_id']) ) {
			$this->post_id = intval($_GET['panels_customize_post_id']);

			add_action('customize_register', array($this, 'customize_register') );
			add_action('widgets_init', array($this, 'widgets_init') );
			add_filter('customizer_widgets_section_args', array($this, 'customizer_widgets_section_args'), 10, 3 );

			// Lets add in the existing Page options
			add_filter( 'option_sidebars_widgets', array($this, 'option_sidebars_widgets') );
		}

	}

	static function single(){

		static $single;

		if( empty($single) ) {
			$single = new SiteOrigin_Panels_Live_Editor();
		}

		return $single;

	}

	/**
	 * @param WP_Customize_Manager $customize
	 */
	function customize_register($customize){
		$customize->add_panel( 'siteorigin-panels-widgets', array(
			'title'	=> __('Page Builder', 'siteorigin-panels'),
			'priority'	=> 5,
		) );

	}

	/**
	 * Change the section args
	 *
	 * @param $section_args
	 * @param $section_id
	 * @param $sidebar_id
	 */
	function customizer_widgets_section_args( $section_args, $section_id, $sidebar_id ){
		if( preg_match('/panels-sidebar-[0-9]+-[0-9]+/', $sidebar_id) ) {
			$section_args['panel'] = 'siteorigin-panels-widgets';
		}
		return $section_args;
	}

	function widgets_init( ){
		$panels_data = get_post_meta( $this->post_id, 'panels_data', true );

		if( !empty($panels_data) && !empty( $panels_data['grid_cells'] ) ) {

			add_filter( 'siteorigin_panels_cell_html', array($this, 'cell_html'), 10, 6 );

			$title_html = siteorigin_panels_setting( 'title-html' );
			if( strpos($title_html, '{{title}}') !== false ) {
				list( $before_title, $after_title ) = explode( '{{title}}', $title_html, 2 );
			}
			else {
				$before_title = '<h3 class="widget-title">';
				$after_title = '</h3>';
			}

			$row = 0;
			$column = 0;
			foreach( $panels_data['grid_cells'] as $i => $cell ) {

				if( $i === 0 || $panels_data['grid_cells'][$i - 1]['grid'] != $cell['grid'] ) {
					$row++;
					$column = 1;
				}

				$name = sprintf( __('Row %d Column %d', 'siteorigin-panels'), $row, $column++ );

				register_sidebar( array(
					'name'          => $name,
					'id'            => 'panels-sidebar-' . $this->post_id . '-' . $i,
					'description'   => '',
					'before_widget' => '<aside id="%1$s" class="widget %2$s">',
					'after_widget'  => '</aside>',
					'before_title'  => $before_title,
					'after_title'   => $after_title,
				) );

				dynamic_sidebar();

			}
		}

	}

	function cell_html( $html, $panels_data, $post_id, $widgets, $gi, $ci ){

		if( $this->post_id == $post_id ) {
			static $i = 0;
			ob_start();
			dynamic_sidebar( 'panels-sidebar-' . $post_id . '-' . $i++ );
			return ob_get_clean();
		}

		return $html;
	}

	function option_sidebars_widgets($value){

		$panels_data = get_post_meta( $this->post_id, 'panels_data', true );
		if( !empty($panels_data) && !empty( $panels_data['widgets'] ) ) {
			foreach( $panels_data['widgets'] as $widget ) {

			}
		}

		var_dump($value);
		die();

		return $value;
	}

}
SiteOrigin_Panels_Live_Editor::single();