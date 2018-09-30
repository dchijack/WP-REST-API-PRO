<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义缩略图地址
function get_post_thumbnail($post_id){
    $post = get_post($post_id);
	$thumbnails = get_post_meta($post_id, 'thumbnail', true);
	$cdnsetting = wpjam_get_setting('wpjam-cdn','cdn_name');
	$cropwidth = wpjam_get_setting('wpjam-cdn','width');
     if (!empty($thumbnails)) {
		if ($cdnsetting) {
			$post_thumbnail = wpjam_get_thumbnail($thumbnails,$cropwidth);
		} else {
			$post_thumbnail = $thumbnails;
		}
		return $post_thumbnail;
	} else if(has_post_thumbnail()){
        $attachment = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),'full');
		$thumbnails = $attachment[0];
		if ($cdnsetting) {
			$post_thumbnail = wpjam_get_thumbnail($thumbnails,$cropwidth);
		} else {
			$post_thumbnail = $thumbnails;
		}
        return $post_thumbnail;
    } else { 
		$post_thumbnail = '';
		ob_start();
		ob_end_clean();
		$firstImage = preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches); 
		$thumbnails = $matches[1]; 
		if(!empty($thumbnails)){
			if ($cdnsetting) {
				$post_thumbnail = wpjam_get_thumbnail($thumbnails,$cropwidth);
			} else {
				$post_thumbnail = $thumbnails;
			}
		} else {
			$default = get_setting_option('prefix');
			if ($cdnsetting) {
				$post_thumbnail = wpjam_get_thumbnail($default,$cropwidth);
			} else {
				$post_thumbnail = $default;
			}
		}
		return $post_thumbnail;
    }
}