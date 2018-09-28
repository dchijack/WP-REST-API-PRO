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
function video_content_filter($content) {
    preg_match('/https\:\/\/v.qq.com\/x\/(\S*)\/(\S*)\.html/',$content,$matches);
    if($matches) {
    	$vids=$matches[2];
	    //  defaultfmt： 1080P-fhd，超清-shd，高清-hd，标清-sd
	    $url='http://vv.video.qq.com/getinfo?vid='.$vids.'&defaultfmt=auto&otype=json&platform=11001&defn=fhd&charge=0';
	    $res = file_get_contents($url);
	    if($res) {
	    	$str = substr($res,13,-1);
		    $newStr =json_decode($str,true);	    
		    $videoUrl= $newStr['vl']['vi'][0]['ul']['ui'][0]['url'].$newStr['vl']['vi'][0]['fn'].'?vkey='.$newStr['vl']['vi'][0]['fvkey']; 
		    $contents = preg_replace('~<video (.*?)></video>~s','<video src="'.$videoUrl.'" controls="controls" width="100%"></video>',$content);
		    return $contents;
	    } else {
	    	return $content;
	    }  
    } else {
    	return $content;
    }    
}
if (has_post_format( 'video' )) {
	add_filter( 'the_content', 'video_content_filter' );
}