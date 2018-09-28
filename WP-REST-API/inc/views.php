<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义阅读统计 API
add_action( 'rest_api_init', function () {
  register_rest_route('wechat/v1', 'views/update/(?P<id>\d+)', array(
    'methods' => 'GET',
    'callback' => 'updateViews'
  ));
});
function updateViews($data) {
    $post_id = $data['id'];
    if(!is_numeric($post_id)){
        return new WP_Error( 'error', 'ID is not numeric', array( 'status' => 500 ) );
    } else if(get_post($post_id)==null){
        return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    } else {
        $data=post_update_views($post_id); 
        if (empty($data)) {
			return new WP_Error( 'error', 'no find post', array( 'status' => 404 ) );
        }  
        // Create the response object
        $response = new WP_REST_Response($data); 
        // Add a custom status code
        $response->set_status( 200 );
        return $response;
    }   
}
function post_update_views($post_id) {
    $posts = get_post($post_id);         
    if (empty( $posts )) {
        return null;
    } else {
        $post_views = (int)get_post_meta($post_id, 'views', true);  
        if(!update_post_meta($post_id, 'views', ($post_views+1))) {  
            add_post_meta($post_id, 'views', 1, true);  
        } 
        $result =array();
        $result["code"]="success";
        $result["message"]="update posts views success";
        $result["status"]="200";
        return $result;
    }
}
// 定义热门阅读 API
add_action( 'rest_api_init', function () {
  register_rest_route( 'wechat/v1', 'views/most', array(
    'methods' => 'GET',
    'callback' => 'getMostViewsPosts'    
  ));
});
function getMostViewsPosts( ) {
	$data=get_most_views_post_data(10); 
	if ( empty( $data ) ) {
		return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
function get_most_views_post_data($limit = 10) {
    global $wpdb, $post;
    $today=date("Y-m-d H:i:s"); // 获取当天日期时间   
    $limit_date=date("Y-m-d H:i:s", strtotime("-1 year"));  // 获取指定日期时间
	$sql=$wpdb->prepare("SELECT ".$wpdb->posts.".ID as ID, post_title, post_name,post_excerpt,post_content,post_date, CONVERT(".$wpdb->postmeta.".meta_value,SIGNED) AS 'views_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_key = 'views' AND post_date BETWEEN '".$limit_date."' AND '".$today."' AND post_status = 'publish' AND post_password = '' ORDER  BY views_total DESC LIMIT %d",$limit);
    $hotviews = $wpdb->get_results($sql);
    $posts=array();
    foreach ($hotviews as $post) {
        $post_id = (int) $post->ID;
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
			if(wpjam_get_setting('wpjam-cdn','cdn_name')){
				$_data["thumbnail"] = wpjam_get_thumbnail($post_thumbnail,array(600,300),1);
			} else {
				$_data["thumbnail"] = $post_thumbnail;
			}
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