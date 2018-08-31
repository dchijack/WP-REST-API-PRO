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
    if(has_post_thumbnail()){
        $post_thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($post_id),'full');
        return $post_thumbnail[0];
    } else if (!empty($thumbnails)) {
		$post_thumbnail = $thumbnails;
		return $post_thumbnail;
	} else { 
		$post_thumbnail = '';
		ob_start();
		ob_end_clean();
		$post_images = preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches); 
		$post_img_src = $matches[1]; 
		if(!empty($post_img_src)){
			$path_parts = pathinfo($post_img_src);
			$first_img_name = $path_parts["basename"];
			$expired = 604800;
			$post_thumbnail = $post_img_src;
		} else {
			$post_thumbnail = get_setting_option('prefix');
		}
		return $post_thumbnail;
    }
}