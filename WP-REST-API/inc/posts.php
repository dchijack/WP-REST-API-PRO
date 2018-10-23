<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义Posts API
add_filter( 'rest_prepare_post', 'post_custom_fields_rest', 10, 3 );
function post_custom_fields_rest($data, $post, $request) {
	global $wpdb;
    $_data = $data->data;    
    $post_id = $post->ID;
    $post_views = (int)get_post_meta($post_id, 'views',true);
	$post_thumbnail = get_post_thumbnail($post_id);
	$post_comment = wp_count_comments($post_id);
	//$content = $post->post_content;
	$category = get_the_category($post_id);
	$categoryId=$category[0]->term_id;
	$next_post = get_next_post($categoryId, '', 'category');
    $previous_post = get_previous_post($categoryId, '', 'category');
	$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
	$post_thumbs = $wpdb->get_var($sql_thumbs);
	$params = $request->get_params();
	if ( isset( $params['id'] )) {
		$sql=$wpdb->prepare("SELECT meta_key , (SELECT ID from ".$wpdb->users." WHERE user_login=substring(meta_key,2)) as userID FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
		$zanuser = $wpdb->get_results($sql);
		$avatarurls =array();
		foreach ($zanuser as $userid) {
			$_avatarurl['avatar'] = get_user_meta( $userid->userID, 'wxavatar', true);
			$avatarurls[] = $_avatarurl;       
		}
		$_data['avatar']= $avatarurls;
	} else {
		if (get_setting_option('post_content')) { unset($_data['content'] ); }  	
	}
	//$_data['content']['rendered'] = $content;
	$_data['category'] = $category[0]->cat_name;
	$_data['comments'] = $post_comment->total_comments;
	$_data['thumbses'] = $post_thumbs;
	if (get_setting_option('post_meta')) {
		$_data["thumbnail"] = $post_thumbnail;
		$_data["views"] = $post_views;
	} else {
		$_data["meta"]["thumbnail"] = $post_thumbnail;
		$_data["meta"]["views"] = $post_views;
		$meta = get_setting_option('meta_list');
		if (!empty($meta)) {
			foreach ($meta as $meta=>$key) {
				$_data["meta"][$key] = get_post_meta( $post_id, $key ,true );
			}
		}
		//-----------------------------------------------------------
	}
	if (get_setting_option('post_prev')) {
		$_data['next_id'] = !empty($next_post->ID)?$next_post->ID:null;
		$_data['next_title'] = !empty($next_post->post_title)?$next_post->post_title:null;
		$_data['previous_id'] = !empty($previous_post->ID)?$previous_post->ID:null;
		$_data['previous_title'] = !empty($previous_post->post_title)?$previous_post->post_title:null;
	}
	if (get_setting_option('post_excerpt')) {unset($_data['excerpt']);}
	if (get_setting_option('post_meta')) {unset($_data['meta']); }
    if (get_setting_option('post_format')) {unset($_data['format']);}
	if (get_setting_option('post_type')) {unset($_data['type']);}
	if (get_setting_option('post_author')) {unset($_data['author']);} else {$_data['author'] = get_the_author_meta('display_name',$post->post_author);} // 显示文章作者昵称
	unset($_data['featured_media']);
    unset($_data['ping_status']);
    unset($_data['template']);
    unset($_data['slug']);
    unset($_data['modified_gmt']);
    unset($_data['date_gmt']);
	unset($_data['guid']);
    unset($_data['curies']);
    unset($_data['modified']);
    unset($_data['status']);
    unset($_data['comment_status']);
    unset($_data['sticky']);    
    unset($_data['_links']['self']); 
    $data->data = $_data; 
    return $data; 
}
// 定义是否开启投稿入口
add_action( 'rest_api_init', function () {
  register_rest_route( 'wechat/v1', 'creat/setting', array(
    'methods' => 'GET',
    'callback' => 'get_add_post_entry'    
  ));
});
function get_add_post_entry($data) {
	$data=get_enablePost_data(); 
	if (empty($data)) {
		return new WP_Error( 'no options', 'no options', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
function get_enablePost_data() {
    $en_posts=get_setting_option('enposts');
    if ($en_posts) {
        $result["code"]="success";
        $result["message"]="get enablePost success";
        $result["status"]="200";
        $result["enablePost"]="true";
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]="get enablePost success";
        $result["status"]="200";
        $result["enablePost"]="false";
        return $result;
    }
}