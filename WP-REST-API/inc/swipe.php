<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义滑动文章 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'views/swipe', array(
		'methods' => 'GET',
		'callback' => 'get_post_by_swipe_id'
	));
});
function get_post_by_swipe_id($request) {    
    $data=get_swipe_post_data(); 
    if (empty($data)) {
        return new WP_Error( 'error', 'post swipe is error', array( 'status' => 404 ) );
    }
    $response = new WP_REST_Response($data);
    $response->set_status( 200 ); 
    return $response;
}
function get_swipe_post_data(){
	global $wpdb;
	$Swipe =  get_setting_option('swipe');
	$posts =array();    
    if(!empty($Swipe)) {
		foreach ($Swipe as $Swipe=>$post_id) {
			$post = get_post($post_id);
			$post_title = stripslashes($post->post_title);
			$post_excerpt = $post->post_excerpt;
			$post_views = (int)get_post_meta( $post_id, 'views' ,true );
			$post_date = $post->post_date;
			$post_permalink = get_permalink($post_id);
			$post_thumbnail = get_post_thumbnail($post_id);
			$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
			$post_thumbs = $wpdb->get_var($sql_thumbs);
			$post_comment = get_comments_number($post_id);;
			$_data["id"]  = $post_id;
			$_data["title"]["rendered"] = $post_title;
			if (!get_setting_option('post_excerpt')) {
				if ($post->post_excerpt) {
					$_data["excerpt"]["rendered"] = $post->post_excerpt;
				} else {
					$_data["excerpt"]["rendered"] = wp_trim_words( $post->post_content, 160, '...' );
				}
			}
			$_data["date"] = $post_date;
			$_data["link"] =$post_permalink;
			$_data['comments']= $post_comment;
			$_data['thumbses'] = $post_thumbs;
			if (get_setting_option('post_meta')) {
				$_data["thumbnail"] = $post_thumbnail;
				$_data["views"] = $post_views;
			} else {
				$_data["meta"]["thumbnail"] = $post_thumbnail;
				$_data["meta"]["views"] = $post_views;
			}
			$posts[] = $_data; 
        }
        $result["code"]="success";
        $result["message"]="get post swipe success  ";
        $result["status"]="200";
        $result["posts"]=$posts;      
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]="get post swipe error";
        $result["status"]="500";                   
        return $result;
    }
}