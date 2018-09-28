<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 自定义文章类型
if (wp_get_option('custom_menu')) {
	add_action('init', function ()   {
		$name = wp_get_option('custom_menu');
		$singular = wp_get_option('custom_singular');
		$icon = wp_get_option('custom_icon');
		$supports = wp_get_option('custom_supports');
		$labels = array(   
			'name' => $name,   
			'singular_name' => $singular,
			'menu_name' => $name,
			'name_admin_bar' => $name,
			'add_new' => '添加',   
			'add_new_item' => '新建',   
			'edit_item' => '编辑',   
			'new_item' => '新增'  
		);   
		$args = array(   
			'labels' => $labels,  
			'public' => true,
			'show_ui' => true,    
			'show_in_menu' => true,    
			'query_var' => true,   
			'rewrite'   => array( 'slug' => $singular ),
			'capability_type' => 'post',   
			'has_archive' => false,    
			'exclude_from_search' => true,
			'menu_position' => 8,
			'supports' => $supports,
			'menu_icon' => $icon,
			'show_in_rest'       => true,
			'rest_base'          => $singular,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		); 
		register_post_type($singular,$args); 
	});
	// 自定义文章类型固定链接
	add_filter('post_type_link', function ( $link, $post = 0 ){
		$singular = wp_get_option('custom_singular');
		if ( $post->post_type == 'bbs' ){
			return home_url( $singular.'/' . $post->ID .'.html' );
		} else {
			return $link;
		}
	},1, 3);
	add_action( 'init', function (){
		$singular = wp_get_option('custom_singular');
		add_rewrite_rule(
			$singular.'/([0-9]+)?.html$',
			'index.php?post_type='.$singular.'&p=$matches[1]',
			'top' 
		);
		add_rewrite_rule(
			$singular.'/([0-9]+)?.html/comment-page-([0-9]{1,})$',
			'index.php?post_type='.$singular.'&p=$matches[1]&cpage=$matches[2]',
			'top'
		);
	}); 
}
if (wp_get_option('custom_category')) {
	// 自定义文章类型分类
	add_action( 'init',function () {
		$singular = wp_get_option('custom_singular');
		$category = wp_get_option('custom_category');
		$labels = array(
			'name'              => _x( '分类', '分类名称' ),
			'singular_name'     => _x( $category, '分类别名' ),
			'search_items'      => __( '搜索分类' ),
			'all_items'         => __( '所有分类' ),
			'parent_item'       => __( '上级分类' ),
			'parent_item_colon' => __( '父级分类:' ),
			'edit_item'         => __( '编辑' ),
			'update_item'       => __( '更新' ),
			'add_new_item'      => __( '新建' ),
			'new_item_name'     => __( '新增' ),
			'menu_name'         => __( '分类' ),
		);
		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => $category ),
			'show_in_rest'          => true,
			'rest_base'             => $value['custom_category'],
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy( $category, array( $singular ), $args );
	}, 30 );
}
if (wp_get_option('custom_tags')) {
	// 注册自定义文章类型分类标签
	add_action( 'init', function () {
		$singular = wp_get_option('custom_singular');
		$tags = wp_get_option('custom_tags');
		register_taxonomy($tags,$singular, array( 'hierarchical' => false,  'label' => '标签', 'query_var' => true, 'rewrite' =>  array( 'slug' => $tags ), 'show_in_rest'  => true, 'rest_base'  => $tags, 'rest_controller_class' => 'WP_REST_Terms_Controller',)); 
	}, 30 );
}