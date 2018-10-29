<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 定义微信头像
function add_wechat_user_avatar( $user_contact ) {
	$user_contact['wxavatar'] = __( '微信头像' );
	$user_contact['openid'] = __( 'OpenId' );
	$user_meta = get_setting_option('use_meta');
	if (!empty($user_meta)) {
		foreach ($user_meta as $meta) {
			$key = $meta['meta_key'];
			$name = $meta['meta_value'];
			$user_contact[$key] = __( $name );
		}
	}
	return $user_contact;
}
add_filter( 'user_contactmethods', 'add_wechat_user_avatar' );
// 禁止用户列表
if (wp_get_option('user_users')) {
	add_filter( 'rest_endpoints', function( $endpoints ){
		if ( isset( $endpoints['/wp/v2/users'] ) ) {
			unset( $endpoints['/wp/v2/users'] );
		}
		if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
			unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
		}
		return $endpoints;
	});
}