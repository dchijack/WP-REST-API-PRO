<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义随机文章 API
add_action( 'rest_api_init', function () {
  register_rest_route( 'wechat/v1', 'views/random', array(
    'methods' => 'GET',
    'callback' => 'get_wp_post_by_rand'    
  ));
});
function get_wp_post_by_rand( ) {
	$data = get_random_post_by_rand($limit = 10);
	if ( empty( $data ) ) {
		return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
function get_random_post_by_rand($limit = 10) {
	global $wpdb;
	$args = array(
		'post_type' => 'post',
		'orderby' => 'rand', 
		'posts_per_page' => $limit, 
		'date_query' => array(
			array(
				'year' 		  => date( 'Y'),
				'compare'   => '<=',
				//'week' => date( 'W' ),
			),
			array(
				'year'      	  => date( 'Y', strtotime("-1 year") ),
				'compare'   => '>=',
			),
		), 
	);
	$queryPosts = new WP_Query( $args );
	$posts = array();
	if ( $queryPosts->have_posts() ) {
		while ( $queryPosts->have_posts() ) {
			$queryPosts->the_post();
			$_data = array();
			$post_id = get_the_ID();
			$post_date = get_the_date();
			$category = get_the_category();
			$post_title = get_the_title();
			$post_excerpt = get_the_excerpt();
			$post_content = get_the_content();
			$post_thumbnail = get_post_thumbnail($post_id);
			$post_permalink = get_the_permalink($post_id);
			$post_comment = get_comments_number($post_id);
			$post_views = get_post_meta( $post_id, 'views' ,true );
			$thumbs = "SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=".$post_id."";
			$post_thumbs = $wpdb->get_var($thumbs);
			$_data["id"]  = $post_id;
			$_data["date"] = $post_date;
			$_data["category"] = $category[0]->cat_name;
			$_data["link"] =$post_permalink;
			if (get_setting_option('post_author')) {
				unset($_data['author']);
			} else {
				$_data['author'] = get_the_author_meta('display_name');
			}
			$_data["title"]["rendered"]  = $post_title;
			if (!get_setting_option('post_excerpt')) {
				if ($post_excerpt) {
					$_data["excerpt"]["rendered"] = $post_excerpt;
				} else {
					$_data["excerpt"]["rendered"] = wp_trim_words( $post_content, 160, '...' );
				}
			}
			if (get_setting_option('list_content')) { $_data["content"]["rendered"] = $post_content; }
			$_data["comments"]= $post_comment;
			$_data["thumbses"] = $post_thumbs;
			if (get_setting_option('post_meta')) {
				$_data["thumbnail"] = $post_thumbnail;
				$_data["views"] = $post_views;
			} else {
				//--------------------自定义标签-----------------------------
				if(wpjam_get_setting('wpjam-cdn','cdn_name')){
					$_data["meta"]["thumbnail"] = wpjam_get_thumbnail($post_thumbnail,array(600,300),1);
				} else {
					$_data["meta"]["thumbnail"] = $post_thumbnail;
				}
				$_data["meta"]["views"] = $post_views;
				$meta = get_setting_option('meta_list');
				if (!empty($meta)) {
					foreach ($meta as $meta=>$key) {
						$_data["meta"][$key] = get_post_meta( $post_id, $key ,true );
					}
				}
				//-----------------------------------------------------------
			}
			$posts[] = $_data;
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	} else {
		// no posts found
	}
	return $posts;
}