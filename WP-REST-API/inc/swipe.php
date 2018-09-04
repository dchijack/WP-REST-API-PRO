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
		'callback' => 'getPostSwipe'
	));
});
function getPostSwipe($request) {    
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
    $postSwipeIds = get_setting_option('swipe');
	$posts =array();    
    if(!empty($postSwipeIds)) {
		$sql=$wpdb->prepare("SELECT * from ".$wpdb->posts." where id in (%d)",$postSwipeIds);
        $_posts = $wpdb->get_results($sql);
        foreach ($_posts as $post) {
			$post_id = (int)$post->ID;
			$post_title = stripslashes($post->post_title);
			$post_excerpt = $post->post_excerpt;
			$post_views = (int)$post->views_total;
			$post_date = $post->post_date;
			$post_permalink = get_permalink($post->ID);
			$post_thumbnail = get_post_thumbnail($post_id);
			$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
			$post_thumbs = $wpdb->get_var($sql_thumbs);
			$sql_comment = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->comments." where comment_approved = '1' and comment_post_ID = %d",$post_id);
			$post_comment = $wpdb->get_var($sql_comment);
			$_data["id"]  = $post_id;
			$_data["title"]["rendered"] = $post_title;
			if (!get_setting_option('post_excerpt')) { $_data["excerpt"]["rendered"] = $post_excerpt; }
			$_data["date"] = $post_date;
			$_data["link"] =$post_permalink;
			$_data['comments']= $post_comment;
			$_data['thumbses'] = $post_thumbs;
			if (get_setting_option('post_meta')) {
				if(wpjam_get_setting('wpjam-extends','wpjam-qiniu.php')){
					$_data["thumbnail"] = wpjam_get_thumbnail($post_thumbnail,array(600,300),1);
				} else {
					$_data["thumbnail"] = $post_thumbnail;
				}
				$_data["views"] = $post_views;
			} else {
				if(wpjam_get_setting('wpjam-extends','wpjam-qiniu.php')){
					$_data["meta"]["thumbnail"] = wpjam_get_thumbnail($post_thumbnail,array(600,300),1);
				} else {
					$_data["meta"]["thumbnail"] = $post_thumbnail;
				}
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