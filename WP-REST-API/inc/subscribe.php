<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 订阅分类
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'category/sub', array(
		'methods' => 'POST',
		'callback' => 'SubscriptionCate'
	));
});
function SubscriptionCate($request) {
    global $wpdb;
    $openid = $request['openid'];
    $categoryid = $request['categoryid'];
    if(empty($openid) || empty($categoryid) ) {
        return new WP_Error( 'error', 'openid or categoryid is empty', array( 'status' => 500 ) );
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        } else {
            $user_id =0;
            $users = get_user_by('login',$openid);
			$user_id = (int)$users->ID;
            if($user_id !=0) {
                $data = post_subscription_data($user_id,$categoryid); 
                if (empty($data)) {
                    return new WP_Error( 'error', 'post subscription error', array( 'status' => 404 ) );
                }
                $response = new WP_REST_Response($data);
                $response->set_status( 200 ); 
                return $response;
            } else {
                return new WP_Error( 'error', 'userid id is error ', array( 'status' => 500 ) );
            }  
        }
    }
}
function post_subscription_data($user_id,$categoryid) {     
    global $wpdb;
    $sql = $wpdb->prepare("SELECT * FROM ".$wpdb->usermeta ." WHERE user_id=%d and meta_key='subscribe' and meta_value=%s",$user_id,$categoryid);
    $usermetas = $wpdb->get_results($sql);
    $count = count($usermetas);
    if ($count==0) {
        if (add_user_meta($user_id, "subscribe",$categoryid,false)) {
            $result["code"]="success";
            $result["message"]="post subscription success  ";
            $result["status"]="200";    
            return $result;
        } else {
            $result["code"]="success";
            $result["message"]="post subscription error";
            $result["status"]="500";                   
            return $result;
        }  
    } else {
        if (delete_user_meta($user_id,'subscribe',$categoryid)) {
            $result["code"]="success";
            $result["message"]= "you have delete success subscription  ";
            $result["status"]="201";                           
            return $result;    
        } else {
            $result["code"]="success";
            $result["message"]= "delete subscription fail ";
            $result["status"]="501";                   
            return $result;    
        }
    }        
}
// 获取订阅分类
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'category/get', array(
		'methods' => 'GET',
		'callback' => 'getSubscription'
	));
});
function getSubscription($request) {
    global $wpdb;
    $openid = $request['openid'];
    if(empty($openid) ) {
        return new WP_Error( 'error', 'openid is empty', array( 'status' => 500 ) );
    } else { 
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array( 'status' => 500 ) );
        } else {
            $user_id =0;
            $users = get_user_by('login',$openid);
			$user_id = (int)$users->ID;
            if($user_id !=0) {
                $data=get_subscription_data($user_id); 
                if (empty($data)) {
                    return new WP_Error( 'error', 'post subscription error', array( 'status' => 404 ) );
                }
                $response = new WP_REST_Response($data);
                $response->set_status( 200 ); 
                return $response;
            } else {
                return new WP_Error( 'error', 'userid id is error ', array( 'status' => 500 ) );
            } 
        }
    }
}
function get_subscription_data($user_id) {
    global $wpdb;
    $usermeta = get_user_meta($user_id);
    if (!empty($usermeta)) {      
        $result["code"]="success";
        $result["message"]= "get subscription success ";
        $result["status"]="200";
        if(!empty($usermeta['subscribe'])) {
            $result["subscription"]=$usermeta['subscribe'];
            $substr = implode(",",$usermeta['subscribe']);
            $result["substr"]=$substr; 
            $sql="SELECT SQL_CALC_FOUND_ROWS ".$wpdb->posts.".ID ,".$wpdb->posts.".post_title  FROM ".$wpdb->posts."  LEFT JOIN ".$wpdb->term_relationships." ON (".$wpdb->posts.".ID = ".$wpdb->term_relationships.".object_id) WHERE 1=1  AND ( ".$wpdb->term_relationships.".term_taxonomy_id IN (".$substr.")) AND ".$wpdb->posts.".post_type = 'post' AND (".$wpdb->posts.".post_status = 'publish') GROUP BY ".$wpdb->posts.".ID ORDER BY ".$wpdb->posts.".post_date DESC LIMIT 0, 20";
	        $usermetaList =$wpdb->get_results($sql); 
	        $result["usermetaList"]=$usermetaList;
        }                 
        return $result;        
    } else {
        $result["code"]="success";
        $result["message"]= "you have not posted subscription ";
        $result["status"]="501";                   
        return $result; 
    }
}
