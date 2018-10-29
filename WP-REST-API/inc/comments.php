<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
//获取是否启动小程序评论选项
add_action( 'rest_api_init', function () {
  register_rest_route( 'wechat/v1', 'comment/setting', array(
    'methods' => 'GET',
    'callback' => 'getEnableComment'    
  ));
});
function getEnableComment($data) {
	$data=get_enableComment_data(); 
	if (empty($data)) {
		return new WP_Error( 'no options', 'no options', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
function get_enableComment_data() {
    $en_comments = get_setting_option('encomments');
    if ($en_comments) {
        $result["code"]="success";
        $result["message"]="get enableComment success";
        $result["status"]="200";
        $result["encomments"]='true';
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]="get enableComment success";
        $result["status"]="200";
        $result["encomments"]='false';
        return $result;
    }
}
// 增加评论自定义字段
add_filter( 'rest_prepare_comment', 'rest_comments_custom_fields', 10, 3 );
function rest_comments_custom_fields( $data, $comment, $request) { 
    global $wpdb;
    $_data=$data->data;  
    $comment_id=$comment->comment_ID;
    $comments=get_comments( array('ID' =>$comment_id) );
	foreach($comments as $comment) {
		$userid=$comment->user_id;
		$parent_name=$comment->parent_name;
		$parent_date=$comment->parent_date;
		$formId=$comment->formId;
		if(empty($formId)) {
			$formId="";
		}
		if(empty($parent_name)) {
			$parent_name="";
		}
		if(empty($parent_date)) {
			$parent_date="";
		}
		$_data['parent_name']=$parent_name; 
		$_data['parent_date']=$parent_date;  
		$_data['userid']=$userid;
		$_data['formId']=$formId;
	}
    $data->data = $_data;
    return $data; 
}
// 定义热门评论 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'comment/most', array(
		'methods' => 'GET',
		'callback' => 'getMostCommentsPosts'    
	));
});
function getMostCommentsPosts( ) {
	$data = get_most_comments_post_by_comment($limit = 10); 
	if (empty($data)) {
		return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
	} 
	// Create the response object
	$response = new WP_REST_Response( $data ); 
	// Add a custom status code
	$response->set_status( 200 );
	return $response;
}
// 获取年度评论最多的文章
function get_most_comments_post_by_comment($limit = 10) {
	global $wpdb;
	$args = array(
		'post_type' => 'post',
		'orderby' => 'comment_count', 
		'posts_per_page' => $limit, 
		'date_query' => array(
			array(
				'year' 		  => date( 'Y'),
				'compare'   => '<=',
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
// 定义最新评论文章 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'comment/recent', array(
		'methods' => 'GET',
		'callback' => 'getNewCommentsPosts'
	));
});
function getNewCommentsPosts( ) {
	$data = get_new_comments_post_by_recent($limit = 10); 
	if (empty( $data )) {
		return new WP_Error( 'noposts', 'noposts', array( 'status' => 404 ) );
	}  
	// Create the response object
	$response = new WP_REST_Response($data); 
	// Add a custom status code
	$response->set_status( 200 ); 
	// Add a custom header
	return $response;
}
// 获取近期评论文章
function get_new_comments_post_by_recent($limit = 10) {
    global $wpdb;
	$args = array(
		'post_type' => 'post',
		'order' => 'DESC',
		'orderby' => 'comment_count date',
		'posts_per_page' => $limit, 
		'date_query' => array(
			array(
				'year' 		  => date( 'Y'),
				'week' 		  => date( 'W' ),
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
// 获取评论及回复 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'comment/comments', array(
		'methods' => 'get',
		'callback' => 'getcomments'
	));
});
function getcomments($request) {
	$postid = isset($request['postid'])?(int)$request['postid']:0;
 	$limit = isset($request['limit'])?(int)$request['limit']:0;
	$page = isset($request['page'])?(int)$request['page']:0;
	$order = isset($request['order'])?$request['order']:'';
	if (empty($order)) {
		$order ="asc";
	}
	if (empty($postid) || empty($limit) || empty($page) || get_post($postid)==null) {
		return new WP_Error( 'error', 'postid or limit or page or post is empty', array( 'status' => 500 ) );
	} else {
		$data=get_comments_data($postid,$limit,$page,$order); 
		if (empty($data)) {
			return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
		}
		$response = new WP_REST_Response($data);
		$response->set_status( 200 ); 
		return $response;
	}
}
function get_comments_data($postid,$limit,$page,$order) {
	global $wpdb;
	$page=($page-1)*$limit;
	$sql=$wpdb->prepare("SELECT t.*,(SELECT t2.meta_value  from ".$wpdb->commentmeta." t2 where t.comment_ID = t2.comment_id AND t2.meta_key = 'formId') AS formId FROM ".$wpdb->comments." t WHERE t.comment_post_ID = %d and t.comment_parent=0 and t.comment_approved='1' order by t.comment_date ".$order." limit %d,%d",$postid,$page,$limit);
	$comments = $wpdb->get_results($sql); 
	$commentslist  =array();
	foreach($comments as $comment){
		if($comment->comment_parent==0){
			$data["id"]=$comment->comment_ID;
			$data["author_name"]=$comment->comment_author;
			$author_url=$comment->comment_author_url;
			$data["author_url"]=strpos($author_url, "wx.qlogo.cn")?$author_url:"../../images/gravatar.png";
			$data["date"]=time_tran($comment->comment_date);
			$data["content"]=$comment->comment_content;
			$data["formId"]=$comment->formId;
			$data["userid"]=$comment->user_id;
			$order="asc";
			$data["child"]=getchaildcomment($postid,$comment->comment_ID,5,$order);
			$commentslist[] =$data;
		}
	}
	$result["code"]="success";
    $result["message"]="get comments success";
    $result["status"]="200";
    $result["data"]=$commentslist;              
    return $result;         
}
function getchaildcomment($postid,$comment_id,$limit,$order) {
	global $wpdb;
	if ($limit>0) {
		$commentslist  =array();
		$sql=$wpdb->prepare("SELECT t.*,(SELECT t2.meta_value  from ".$wpdb->commentmeta." t2 where t.comment_ID = t2.comment_id  AND t2.meta_key = 'formId') AS formId FROM ".$wpdb->comments." t WHERE t.comment_post_ID = %d and t.comment_parent=%d and t.comment_approved='1' order by comment_date ".$order,$postid,$comment_id);
		$comments = $wpdb->get_results($sql); 
		foreach($comments as $comment){						
			$data["id"]=$comment->comment_ID;
			$data["author_name"]=$comment->comment_author;
			$author_url =$comment->comment_author_url;
			$data["author_url"]=strpos($author_url, "wx.qlogo.cn")?$author_url:"../../images/gravatar.png";
			$data["date"]=time_tran($comment->comment_date);
			$data["content"]=$comment->comment_content;
			$data["formId"]=$comment->formId;
			$data["userid"]=$comment->user_id;
			$data["child"]=getchaildcomment($postid,$comment->comment_ID,$limit-1,$order);
			$commentslist[]=$data;			
		}
	}
	return $commentslist;
}
// 微信提交评论 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'comment/add', array(
		'methods' => 'POST',
		'callback' => 'addcomment'
	));
});
function addcomment($request) {
	$post=(int)$request['post'];
    $author_name=$request['author_name'];
    $author_email=$request['author_email'];
    $content=$request['content'];
    $author_url=$request['author_url'];    
    $openid=$request['openid'];
    $reqparent ='0';
    $userid=0;
    $formId='';
    if(isset($request['userid'])){
        $userid=(int)$request['userid']; 
    }
    if(isset($request['formId'])){
        $formId=$request['formId']; 
    }
    if(isset($request['parent'])){
        $reqparent=$request['parent']; 
    }
    $parent = 0;
    if(is_numeric($reqparent)){
        $parent = (int)$reqparent;
        if($parent<0){
            $parent=0;
        }
    }
    if($parent != 0){
        $comment = get_comment($parent);
        if (empty( $comment ) ) {
			{
                return new WP_Error( 'error', 'parent id is error', array( 'status' => 500 ) );
            }
        }
    }
    if(empty($openid) || empty($post)  || empty($author_url)  || empty($author_email)  || empty($content) || empty($author_name)) {
        return new WP_Error( 'error', 'openid or post or author_name or author_url or author_email or content is empty', array( 'status' => 500 ) );
    } else if(get_post($post)==null) {
        return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array('status' => 500 ));
        } else if(is_wp_error(get_post($postid))) {
            return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        } else {
            $data=add_comment_data($post,$author_name,$author_email,$author_url,$content,$parent,$openid,$userid,$formId); 
            if (empty($data)) {
                return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
            }
            $response = new WP_REST_Response($data);
            $response->set_status( 200 ); 
            return $response;  
        }
    }
}
function add_comment_data($post,$author_name,$author_email,$author_url,$content,$parent,$openid,$userid,$formId) {
	global $wpdb;
    $user_id =0;
    $useropenid="";
	$approved = get_setting_option('approved');
	$users = get_user_by('login',$openid);
	$user_id = (int)$users->ID;
    $commentdata = array(
        'comment_post_ID' => $post, // to which post the comment will show up
        'comment_author' => $author_name, //fixed value - can be dynamic 
        'comment_author_email' => $author_email, //fixed value - can be dynamic 
        'comment_author_url' => $author_url, //fixed value - can be dynamic 
        'comment_content' => $content, //fixed value - can be dynamic 
        'comment_type' => '', //empty for regular comments, 'pingback' for pingbacks, 'trackback' for trackbacks
        'comment_parent' => $parent, //0 if it's not a reply to another comment; if it's a reply, mention the parent comment ID here
		'comment_approved' => $approved?0:1, // Whether the comment has been approved
        'user_id' => $user_id, //passing current user ID or any predefined as per the demand
    );
    $comment_id = wp_insert_comment( wp_filter_comment($commentdata));
    if($comment_id) {
        $useropenid="";
        if($userid != 0) {
            $sql ="SELECT user_login FROM ".$wpdb->users ." WHERE ID=".$userid;         
            $users = $wpdb->get_results($sql);
            foreach ($users as $user) {
                $useropenid = $user->user_login;    
            }
        }
        $addcommentmetaflag=false;
        if($formId != '') {
            $addcommentmetaflag = add_comment_meta($comment_id, 'formId', $formId,false); 
        }
        $result["code"]="success";
        if($addcommentmetaflag) {
            $result["message"] = "add comment and formId success";  
        } else {
            $result["message"] = "add comment success,add formId fail";
        } 
        $result["status"]="200"; 
        $result["useropenid"]=$useropenid;  
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]= "add comment error";
        $result["status"]="500";                   
        return $result;
    }
}
// 获取微信评论 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'comment/get', array(
		'methods' => 'GET',
		'callback' => 'getcomment'
	));
});
function getcomment($request) {
	$openid = isset($request['openid'])?$request['openid']:'';
    if(empty($openid)) {
        return new WP_Error( 'error', 'openid  is  empty', array( 'status' => 500 ) );
    } else{
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array('status' => 500 ));
        } else {
            $data = get_comment_data($openid); 
            if (empty($data)) {
                return new WP_Error( 'error', 'add comment error', array( 'status' => 404 ) );
            }
            $response = new WP_REST_Response($data);
            $response->set_status( 200 ); 
            return $response;
        }
    }
}
function get_comment_data($openid){
	global $wpdb;
    $user_id = 0;
	$users = get_user_by('login',$openid);
	$user_id = (int)$users->ID;
    if($user_id==0) {
        $result["code"]="success";
        $result["message"]= "user_id is empty";
        $result["status"]="500";                   
        return $result;
    } else {
        $sql = $wpdb->prepare("SELECT * from ".$wpdb->posts." where ID in (SELECT comment_post_ID from ".$wpdb->comments." where user_id=%s GROUP BY comment_post_ID order by comment_date ) LIMIT 10",$user_id);
		$_posts = $wpdb->get_results($sql);
        $posts =array();
        foreach ($_posts as $post) {
            $post_id = $post->ID;
			$post_thumbnail = get_post_thumbnail($post_id);
			$post_views = (int)get_post_meta($post_id, 'views',true);
			$post_comment = get_comments_number($post_id);
			$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
			$post_thumbs = $wpdb->get_var($sql_thumbs);	
            $_data["id"]  = $post_id;
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
				$_data["meta"]["thumbnail"] = $post_thumbnail;
				$_data['meta']["views"] = $post_views;
				$meta = get_setting_option('meta_list');
				if (!empty($meta)) {
					foreach ($meta as $meta=>$key) {
						$_data["meta"][$key] = get_post_meta( $post_id, $key ,true );
					}
				}
			}
            $posts[]=$_data;
        }
        $result["code"]="success";
        $result["message"]="get comments success";
        $result["status"]="200";
        $result["data"]=$posts;                   
        return $result;         
    }
}