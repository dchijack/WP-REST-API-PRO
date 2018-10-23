<?php
/*
Plugin Name: WP REST API
Plugin URI: https://github.com/dchijack/WP-REST-API
Description: 基于 <a href="https://wordpress.org/plugins/wpjam-basic/" target="_blank">WPJAM BASIC</a> 框架 WordPress REST API 。参考 <a href="https://github.com/iamxjb/wp-rest-api-for-app" target="_blank">守望轩</a> 小程序开源插件定制开发。
Version: 2018.10
Author: 艾码汇
Author URI: https://www.imahui.com/
*/
// DEFINE PLUGIN PATH
define('WP_REST_API', plugin_dir_path(__FILE__));
// WP REST API MENU
include(WP_REST_API.'admin/admin.php');
include(WP_REST_API.'admin/about.php');
include(WP_REST_API.'inc/category.php');
// WP REST API FUNCTIONS
include(WP_REST_API.'inc/adsense.php');
include(WP_REST_API.'inc/comments.php');
include(WP_REST_API.'inc/message.php');
include(WP_REST_API.'inc/openid.php');
include(WP_REST_API.'inc/posts.php');
include(WP_REST_API.'inc/prefix.php');
include(WP_REST_API.'inc/random.php');
include(WP_REST_API.'inc/subscribe.php');
include(WP_REST_API.'inc/swipe.php');
include(WP_REST_API.'inc/thumbnail.php');
include(WP_REST_API.'inc/thumbs.php');
include(WP_REST_API.'inc/usermeta.php');
include(WP_REST_API.'inc/views.php');
include(WP_REST_API.'inc/custom.php');
// 腾讯视频解析
if (wp_get_option('qvideo')) { 
	include(WP_REST_API.'inc/video.php');
}
// 定义设置数据
function get_setting_option($name) {
	return wpjam_get_setting('wp-api',$name);
}
function wp_get_option($name) {
	return get_option('wp-api')[$name];
}
// 文章格式类型
if (wp_get_option('formats')) {
	$formats = wp_get_option('formats');
	add_theme_support( 'post-formats', $formats );
}
// 描述清理HTML标签
if (wp_get_option('deletehtml')) {
	function deletehtml($description) {
		$description = trim($description);
		$description = strip_tags($description,"");
		return ($description);
	}
	add_filter('category_description', 'deletehtml');
}
// 图片上传重命名
if (wp_get_option('reupload')) {
	function git_upload_filter($file) {
		$time = date("YmdHis");
		$file['name'] = $time . "" . mt_rand(1, 100) . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
		return $file;
	}
	add_filter('wp_handle_upload_prefilter', 'git_upload_filter');
}
// 时间格式
function time_tran($the_time) {
    $now_time = date("Y-m-d H:i:s",time()+8*60*60); 
    $now_time = strtotime($now_time);
    $show_time = strtotime($the_time);
    $dur = $now_time - $show_time;
    if ($dur < 0) {
        return $the_time; 
    } else {
        if ($dur < 60) {
            return $dur.'秒前'; 
        } else {
            if ($dur < 3600) {
				return floor($dur/60).'分钟前'; 
			} else {
				if ($dur < 86400) {
					return floor($dur/3600).'小时前';
				} else {
					if ($dur < 259200) {//3天内
						return floor($dur/86400).'天前';
					} else {
						return date("Y-m-d",$show_time); 
					}
				}
			}
		}
	}
}
// 获取数据请求
function get_content_post($url,$post_data=array(),$header=array()) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_AUTOREFERER,true);
    $content = curl_exec($ch);
    $info = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL);
    $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    if($code == "200"){
        return $content;
    }else{
        return "error";
    }
}
// 发起 HTTPS 请求
function https_request($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl,  CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);  
    $data = curl_exec($curl);
    if (curl_errno($curl)){
        return 'ERROR';
    }
    curl_close($curl);
    return $data;
}
