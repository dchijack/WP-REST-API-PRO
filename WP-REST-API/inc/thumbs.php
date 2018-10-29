<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义点赞 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'thumbs/up', array(
		'methods' => 'POST',
		'callback' => 'post_thumbs_up'
	));
});
function post_thumbs_up($request) {
    $openid = $request['openid'];
    $postid = $request['postid'];
    if(empty($openid) || empty($postid) ) {
		return new WP_Error( 'error', 'openid or postid is empty', array( 'status' => 500 ) );
    } else if(get_post($postid)==null) {
        return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        } else if(is_wp_error(get_post($postid))) {
            return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        } else {
            $data = post_thumbs_up_data($openid,$postid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post thumbs up error', array( 'status' => 404 ) );
            }
            $response = new WP_REST_Response($data);
            $response->set_status( 200 ); 
            return $response;
        }
    }
}
function post_thumbs_up_data($openid,$postid) { 
    $openid="_".$openid;
    $postmeta = get_post_meta($postid, $openid,true);
    if (empty($postmeta)) {
        if(add_post_meta($postid, $openid,'thumbs', true)) {
            $result["code"]="success";
            $result["message"]="post thumbs up success";
            $result["status"]="200";    
            return $result;
        } else {
            $result["code"]="success";
            $result["message"]="post thumbs up error";
            $result["status"]="500";                   
            return $result;
        }  
    } else {
            $result["code"]="success";
            $result["message"]= "you have thumbsed up post ";
            $result["status"]="501";                   
            return $result;
    } 
}
// 是否点赞 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'thumbs/get', array(
		'methods' => 'POST',
		'callback' => 'get_thumbsed_post'
	));
});
function get_thumbsed_post($request) {
    $openid=$request['openid'];
    $postid=$request['postid'];
    if(empty($openid) || empty($postid) ) {
        return new WP_Error( 'error', 'openid or postid is empty', array( 'status' => 500 ) );
    } else if(get_post($postid)==null) {
         return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        } else if(is_wp_error(get_post($postid))) {
            return new WP_Error( 'error', 'post id is error', array( 'status' => 500 ) );
        } else {
            $data=post_thumbsed_up_data($openid,$postid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post thumbsed up error', array( 'status' => 404 ) );
            }
            $response = new WP_REST_Response($data);
            $response->set_status( 200 ); 
            return $response;
        }
    }
}
function post_thumbsed_up_data($openid,$postid) {
    $openid="_".$openid; 
    $postmeta = get_post_meta($postid, $openid,true);
    if (!empty($postmeta)) {
        $result["code"]="success";
        $result["message"]="you have thumbsed up post ";
        $result["status"]="200";                   
        return $result;  
    } else {
        $result["code"]="success";
        $result["message"]="you have not thumbsed up post";
        $result["status"]="501";                   
        return $result;
    }
}
// 定义“我”点赞的文章 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'thumbs/user', array(
		'methods' => 'GET',
		'callback' => 'getmythumbsup'
	));
});
function getmythumbsup($request) {
    $openid=$request['openid'];   
    if(empty($openid)) {
        return new WP_Error( 'error', 'openid is empty', array( 'status' => 500 ) );
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        } else {
            $data=post_my_thumbs_up_data($openid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'post thumbs up error', array( 'status' => 404 ) );
            }
            $response = new WP_REST_Response($data);
            $response->set_status( 200 ); 
            return $response;
        }
    }
}
function post_my_thumbs_up_data($openid) {
    global $wpdb; 
    $sql="SELECT * from ".$wpdb->posts." where ID in (SELECT post_id from ".$wpdb->postmeta." where meta_value='thumbs' and meta_key='_".$openid."') ORDER BY post_date desc LIMIT 20";
	$_posts = $wpdb->get_results($sql);
    $posts = array();
    foreach ($_posts as $post) {
		$post_id = $post->ID;
		$post_thumbnail = get_post_thumbnail($post_id);
		$post_views = (int)get_post_meta($post_id, 'views',true);
		$post_comment = get_comments_number($post_id);
		$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
		$post_thumbs = $wpdb->get_var($sql_thumbs);	
        $_data["id"] = $post_id;
        $_data["title"]["rendered"] = $post->post_title;
		if (get_setting_option('post_author')) {unset($_data['author']);} else {$_data['author'] = get_the_author_meta('display_name',$post->post_author);}
		if (!get_setting_option('post_excerpt')) { 
			if ($post->post_excerpt) {
				$_data["excerpt"]["rendered"] = $post->post_excerpt;
			} else {
				$_data["excerpt"]["rendered"] = wp_trim_words( $post->post_content, 160, '...' );
			}
		}
		if (get_setting_option('list_content')) { $_data["content"]["rendered"] = $post->post_content; }
		$_data['comments']= $post_comment;
		$_data['thumbses'] = $post_thumbs;
		if (get_setting_option('post_meta')) {
			$_data["thumbnail"] = $post_thumbnail;
			$_data["views"] = $post_views;
		} else {
			//--------------------自定义标签-----------------------------
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
        $posts[]=$_data;
    }
    $result["code"]="success";
    $result["message"]= "get my thumbs up post success";
    $result["status"]="200";
    $result["data"]=$posts;                   
    return $result;         
}
// 热门点赞 API
add_action( 'rest_api_init', function () {
  register_rest_route( 'wechat/v1', 'thumbs/most', array(
    'methods' => 'GET',
    'callback' => 'getMostThumbsUpPost'    
  ) );
} );
function getMostThumbsUpPost( ) {
	$data=get_most_thumbsed_post_data(10); 
	if ( empty( $data ) ) {
		return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
// Get Top Thumbs Up  this year 
function get_most_thumbsed_post_data($limit = 10) {
	global $wpdb, $post;
    $today = date("Y-m-d H:i:s"); // 获取今天日期时间   
    $limit_date=date("Y-m-d H:i:s", strtotime("-1 year"));  
	$sql=$wpdb->prepare("SELECT ".$wpdb->posts.".ID as ID, post_title, post_name, post_excerpt, post_content, post_date, post_author, COUNT(".$wpdb->postmeta.".post_id) AS 'thumbs_total' FROM ".$wpdb->posts." LEFT JOIN ".$wpdb->postmeta." ON ".$wpdb->posts.".ID = ".$wpdb->postmeta.".post_id WHERE ".$wpdb->postmeta.".meta_value='thumbs' AND post_date BETWEEN '".$limit_date."'AND'".$today."'AND post_status ='publish' AND post_password ='' GROUP BY ".$wpdb->postmeta.".post_id ORDER BY thumbs_total DESC LIMIT %d",$limit);
    $mostthumbsed = $wpdb->get_results($sql);
    $posts =array();
    foreach ($mostthumbsed as $post) {
		$post_id = (int) $post->ID;
        $post_title = stripslashes($post->post_title);
        $post_views = (int)get_post_meta($post_id, 'views',true);
		$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
		$post_thumbs = $wpdb->get_var($sql_thumbs);
		$post_comment = get_comments_number($post_id);
		$post_date = $post->post_date;
        $post_permalink = get_permalink($post->ID);
		$post_thumbnail = get_post_thumbnail($post_id);
		$_data["id"] = $post_id;
        $_data["title"]["rendered"] = $post_title;
		if (get_setting_option('post_author')) {unset($_data['author']);} else {$_data['author'] = get_the_author_meta('display_name',$post->post_author);}
		if (!get_setting_option('post_excerpt')) {
			if ($post->post_excerpt) {
				$_data["excerpt"]["rendered"] = $post->post_excerpt;
			} else {
				$_data["excerpt"]["rendered"] = wp_trim_words( $post->post_content, 160, '...' );
			}
		}
		if (get_setting_option('list_content')) { $_data["content"]["rendered"] = $post->post_content; }
        $_data["thumbses"] = $post_thumbs;
		$_data['comments']= $post_comment;
        $_data["date"] = $post_date; 
        $_data["link"] = $post_permalink;
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