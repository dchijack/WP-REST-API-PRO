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
	$data=get_random_post_data($limit = 10); 
	if ( empty( $data ) ) {
		return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
function get_random_post_data($limit = 10) {
    global $wpdb, $post;
    $today=date("Y-m-d H:i:s"); // 获取当天日期时间   
    $limit_date=date("Y-m-d H:i:s", strtotime("-1 year"));  // 获取指定日期时间
	$sql=$wpdb->prepare("SELECT ID, post_title,post_date,post_excerpt FROM $wpdb->posts WHERE post_status = 'publish' AND post_title != '' AND post_password ='' AND post_type = 'post' ORDER BY RAND() LIMIT 0 , %d",$limit);
    $randposts = $wpdb->get_results($sql);
    $posts=array();
    foreach ($randposts as $post) {
        $post_id = (int)$post->ID;
        $post_title = stripslashes($post->post_title);
		$post_excerpt = $post->post_excerpt;
        $post_views = (int)get_post_meta( $post_id, 'views' ,true );
        $post_date = $post->post_date;
        $post_permalink = get_permalink($post->ID);
		$post_thumbnail = get_post_thumbnail($post_id);
		$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
		$post_thumbs = $wpdb->get_var($sql_thumbs);
		$sql_comment = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->comments." where comment_approved = '1' and comment_post_ID = %d",$post_id);
		$post_comment = $wpdb->get_var($sql_comment);
		$category = get_the_category($post_id);
		$categoryId=$category[0]->term_id;
		$_data['category'] = $category[0]->cat_name;
        $_data["id"]  = $post_id;
		$_data["title"]["rendered"] = $post_title;
		if (!get_setting_option('post_excerpt')) { $_data["excerpt"]["rendered"] = $post_excerpt; }
		$_data["date"] = $post_date;
		$_data["link"] =$post_permalink;
		$_data['comments']= $post_comment;
		$_data['thumbses'] = $post_thumbs;
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
	return $posts;  
}