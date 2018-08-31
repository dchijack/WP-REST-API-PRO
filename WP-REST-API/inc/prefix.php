<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 文章二维码生成
add_action( 'rest_api_init', function () {
	register_rest_route( 'wechat/v1', 'qrcode/creat', array(
		'methods' => 'POST',
		'callback' => 'getPostQrcode'
	));
});
function getPostQrcode($request) {
	$postid=$request['postid'];      
    $path=$request['path'];
    $openid=$request['openid']; 
    if(empty($openid) || empty($postid)  || empty($path)) {
        return new WP_Error( 'error', 'openid or postid or path empty', array( 'status' => 500 ) );
    } else if(get_post($postid)==null) {
        return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
    } else {
        if(!username_exists($openid)) {
            return new WP_Error( 'error', 'Not allowed to submit', array('status' => 500 ));
        } else if(is_wp_error(get_post($postid))) {
            return new WP_Error( 'error', 'post id is error ', array( 'status' => 500 ) );
        } else {
            $data=get_qrcode_data($postid,$path); 
            if (empty($data)) {
                return new WP_Error( 'error', 'creat qrcode error', array( 'status' => 404 ) );
            }
            $response = new WP_REST_Response($data);
            $response->set_status( 200 ); 
            return $response;
        }
    }
}
function get_qrcode_data($postid,$path){
	$uploads = wp_upload_dir();
	$uploads_path = $uploads['basedir'] .'/qrcode/';
	if (!is_dir($uploads_path)) {mkdir($uploads_path, 0777);}
	$qrcode = 'qrcode-'.$postid.'.png';// 文章小程序二维码文件名     
	$qrlink = $uploads_path.$qrcode;// 文章小程序二维码路径   
    $appid = get_setting_option('appid');
    $appsecret = get_setting_option('secretkey');
    if(!is_file($qrlink)) {
        $access_token_url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$appsecret;
        $access_token_result = https_request($access_token_url);
        if($access_token_result !="ERROR") {
            $access_token_array= json_decode($access_token_result,true);
            if(empty($access_token_array['errcode'])) {
                $access_token =$access_token_array['access_token'];
                if(!empty($access_token)) {
					//接口A小程序码,总数10万个（永久有效，扫码进入path对应的动态页面）
					$url = 'https://api.weixin.qq.com/wxa/getwxacode?access_token='.$access_token;
					//接口B小程序码,不限制数量（永久有效，将统一打开首页，可根据scene跟踪推广人员或场景）
					//$url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN;
					//接口C小程序二维码,总数10万个（永久有效，扫码进入path对应的动态页面）
					//$url = 'http://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$ACCESS_TOKEN;
					//header('content-type:image/png');
					$color = array(
						"r" => "0",  //这个颜色码自己到Photoshop里设
						"g" => "0",  //这个颜色码自己到Photoshop里设
						"b" => "0",  //这个颜色码自己到Photoshop里设
					);
					$data = array(
						//$data['scene'] = "scene"; //自定义信息，可以填写诸如识别用户身份的字段，注意用中文时的情况
						//$data['page'] = "pages/index/index"; //扫码后对应的path，只能是固定页面
						'path' => $path, // 前端传过来的页面path,不能为空，最大长度 128 字节
						'width' => intval(100), // 设置二维码尺寸,二维码的宽度
						'auto_color' => false, // 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
						'line_color' => $color, // auth_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"},十进制表示
						'is_hyaline' => true, // 是否需要透明底色， is_hyaline 为true时，生成透明底色的小程序码
					);
					$data = json_encode($data);
					//可在此处添加或者减少来自前端的字段
					$QRCode = get_content_post($url,$data);//小程序二维码
					if($QRCode !='error') {
						//输出二维码
						file_put_contents($qrlink,$QRCode);
						$flag=true;
					}
				} else {
					$flag=false;
                }
            } else {
                $flag=false;
            } 
        } else {
            $flag=false;
        } 
    } else {
        $flag=true;
    }
    if($flag) {
        $result["code"]="success";
        $result["message"]="qrcode creat success"; 
        $result["status"]="200"; 
        return $result;
    } else {
        $result["code"]="success";
        $result["message"]="qrcode creat error"; 
        $result["status"]="500"; 
        return $result;
    }
}