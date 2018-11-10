<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义首页广告 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'adsense/index', array(
		'methods' => 'GET',
		'callback' => 'getIndexAdsense'
	));
});
function getIndexAdsense($request) {    
    $data=get_index_ad_data(); 
    if (empty($data)) {
        return new WP_Error( 'error', 'post adsense is error', array( 'status' => 404 ) );
    }
    $response = new WP_REST_Response($data);
    $response->set_status( 200 ); 
    return $response;
}
function get_index_ad_data(){
	$enableAds = get_setting_option('index_adv');
    $adType = get_setting_option('index_option');
	$adImages = get_setting_option('index_adpic');
	$adNumber = get_setting_option('index_adnum');
	if (is_array($adImages)) {
		$random_Img = array_rand($adImages,1);
		$adImage = $adImages[$random_Img];
	}     
    if($enableAds && !empty($adType) && !empty($adNumber)) {
		if ($adType=='wechat') {
			$_data["type"] = $adType;
			$_data['unitid'] = $adNumber;
		}
		if($adType=='minapp') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['appid'] = $adNumber;
		}
		if($adType=='picture') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['telphone'] = $adNumber;
		}
		if($adType=='taobao') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['code'] = $adNumber;
		}
        $result["code"] = "success";
        $result["message"] = "get post adsense success";
        $result["status"] = 200;
        $result["data"] = $_data;      
        return $result;
    } else {
        $result["code"] = "success";
        $result["message"] = "get post adsense error";
        $result["status"] = 500;                   
        return $result;
    }
}
// 定义列表页广告 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'adsense/list', array(
		'methods' => 'GET',
		'callback' => 'getListAdsense'
	));
});
function getListAdsense($request) {    
    $data=get_list_ad_data(); 
    if (empty($data)) {
        return new WP_Error( 'error', 'post adsense is error', array( 'status' => 404 ) );
    }
    $response = new WP_REST_Response($data);
    $response->set_status( 200 ); 
    return $response;
}
function get_list_ad_data(){
	$enableAds = get_setting_option('list_adv');
    $adType = get_setting_option('list_option');
	$adImages = get_setting_option('list_adpic');
	$adNumber = get_setting_option('list_adnum');
	if (is_array($adImages)) {
		$random_Img = array_rand($adImages,1);
		$adImage = $adImages[$random_Img];
	}     
    if($enableAds && !empty($adType) && !empty($adNumber)) {
		if ($adType=='wechat') {
			$_data["type"] = $adType;
			$_data['unitid'] = $adNumber;
		}
		if($adType=='minapp') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['appid'] = $adNumber;
		}
		if($adType=='picture') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['telphone'] = $adNumber;
		}
		if($adType=='taobao') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['code'] = $adNumber;
		}
        $result["code"]="success";
        $result["message"]="get post adsense success";
        $result["status"]=200;
        $result["data"]=$_data;      
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]="get post adsense error";
        $result["status"]=500;                   
        return $result;
    }
}
// 定义详情页广告 API
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'adsense/detail', array(
		'methods' => 'GET',
		'callback' => 'getDetailAdsense'
	));
});
function getDetailAdsense($request) {    
    $data=get_detail_ad_data(); 
    if (empty($data)) {
        return new WP_Error( 'error', 'post adsense is error', array( 'status' => 404 ) );
    }
    $response = new WP_REST_Response($data);
    $response->set_status( 200 ); 
    return $response;
}
function get_detail_ad_data(){
	$enableAds = get_setting_option('detail_adv');
    $adType = get_setting_option('detail_option');
	$adImages = get_setting_option('detail_adpic');
	$adNumber = get_setting_option('detail_adnum');
	if (is_array($adImages)) {
		$random_Img = array_rand($adImages,1);
		$adImage = $adImages[$random_Img];
	}    
    if($enableAds && !empty($adType) && !empty($adNumber)) {
		if ($adType=='wechat') {
			$_data["type"] = $adType;
			$_data['unitid'] = $adNumber;
		}
		if($adType=='minapp') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['appid'] = $adNumber;
		}
		if($adType=='picture') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['telphone'] = $adNumber;
		}
		if($adType=='taobao') {
			$_data["type"] = $adType;
			$_data["thumbnail"] = $adImage;
			$_data['code'] = $adNumber;
		}
        $result["code"]="success";
        $result["message"]="get post adsense success";
        $result["status"]=200;
        $result["data"]=$_data;      
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]="get post adsense error";
        $result["status"]=500;                   
        return $result;
    }
}
