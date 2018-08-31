<?php
// 定义Posts API
add_filter( 'rest_prepare_post', 'post_custom_fields_rest', 10, 3 );
function post_custom_fields_rest($data, $post, $request) {
	global $wpdb;
    $_data = $data->data;    
    $post_id = $post->ID;
    $post_views = (int)get_post_meta($post_id, 'views',true);
	$post_thumbnail = get_post_thumbnail($post_id);
	$post_comment = wp_count_comments($post_id);
	$content = $post->post_content;
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
	} else {
		unset($_data['content'] );   	
	}
	$_data['content']['rendered'] = $content;
	$_data['category'] = $category[0]->cat_name;
	$_data['comments'] = $post_comment->total_comments;
	$_data['thumbses'] = $post_thumbs;
	if (get_setting_option('post_meta')) {
		$_data["thumbnail"] = $post_thumbnail;
		$_data["views"] = $post_views;
	}
    $_data['avatar']= $avatarurls;
	//--------------------自定义标签-----------------------------
	if (!get_setting_option('post_meta')) {
		$_data["meta"]["thumbnail"] = $post_thumbnail;
		$_data['meta']["views"] = $post_views;
		$metastr = get_setting_option('meta_list');
		if (!empty($metastr)) {
			$metaarr = explode(',',$metastr);
			foreach ($metaarr as $value) {
				$_data["meta"][$value] = get_post_meta( $post_id, $value ,true );
			}
		}
	}
	//-----------------------------------------------------------
	//--------------------相同 Tags 文章-----------------------------
	date_default_timezone_set('Asia/Shanghai');
    $limitday= date("Y-m-d H:i:s", strtotime("-5 year")); 
    $today = date("Y-m-d H:i:s"); //获取今天日期时间
    $tags= $_data["tags"];
    if(count($tags)>0) {
        $tags=implode(",",$tags);
        $sql="
			SELECT DISTINCT ID, post_title
			FROM ".$wpdb->posts." , ".$wpdb->term_relationships.", ".$wpdb->term_taxonomy."
			WHERE ".$wpdb->term_taxonomy.".term_taxonomy_id =  ".$wpdb->term_relationships.".term_taxonomy_id
			AND ID = object_id
			AND taxonomy = 'post_tag'
			AND post_status = 'publish'
			AND post_type = 'post'
			AND term_id IN (" . $tags . ")
			AND ID != '" . $post_id . "'
			AND post_date BETWEEN '".$limitday."' AND '".$today."' 
			ORDER BY  RAND()
			LIMIT 5";
			$related_posts = $wpdb->get_results($sql);
			$_data['post_related'] = $related_posts;
    } else{
        $_data['post_related']=null;
    }
	//-----------------------------------------------------------
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