<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
//解析腾讯视频，只支持一个腾讯视频
function get_tencent_video_filter($url_ID) {
	if(filter_var($url_ID, FILTER_VALIDATE_URL)){ 
		if(preg_match('#https://v.qq.com/x/page/(.*?).html#i',$url_ID, $matches)){
			$vids = $matches[1];
		}elseif(preg_match('#https://v.qq.com/x/cover/.*/(.*?).html#i',$url_ID, $matches)){
			$vids = $matches[1];
		}else{
			$vids = $url_ID;
		}
	}else{
		$vids = $url_ID;
	}
    if($vids) {
		if(strlen($vids) > 20){
			return $url_ID;
		}
		$url='http://vv.video.qq.com/getinfo?vid='.$vids.'&defaultfmt=auto&otype=json&platform=11001&defn=fhd&charge=0';
		
	    $response = file_get_contents($url);
		$response = substr($response,13,-1);
		$response = json_decode($response,true);

		$res	= $response['vl']['vi'][0];
		$p0		= $res['ul']['ui'][0]['url'];
		$p1		= $res['fn'];
		$p2		= $res['fvkey'];

		$mp4	= $p0.$p1.'?vkey='.$p2;
		
	    return $mp4;
    } else {
    	return $url_ID;
    } 
}

add_filter( 'the_content',function ($content) {
	$post_id = get_the_ID();
	$video_id = get_post_meta($post_id,'video',true);
	$audio_id = get_post_meta($post_id,'audio',true);
	$thumbnail = get_post_thumbnail($post_id);
	$format = get_post_format($post_id);
	if (!empty($video_id)) {
		$video = get_tencent_video_filter(strip_tags(trim($video_id)));
		$video_code = '<p><video poster="'.$thumbnail.'" src="'.$video.'"></video></p>';
		return $content.$video_code;
	} else {
		return $content;
	}
});