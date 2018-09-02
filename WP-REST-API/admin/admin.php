<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
add_filter('wpjam_pages', 'rest_api_admin_pages');
add_filter('wpjam_network_pages', 'rest_api_admin_pages');
function rest_api_admin_pages($wpjam_pages){
	$capability	= (is_multisite())?'manage_site':'manage_options';
	$subs = [];
	$subs['api-settings']	= [
		'menu_title'	=> '小程序设置',	
		'function'		=> 'option',
		'option_name'	=> 'wp-api',
	];
	$subs['api-about']	= [
		'menu_title'	=> '小程序 API',	
		'function'		=> 'wp_basic_about_page',	
	];
	$wpjam_pages['api-settings']	= [
		'menu_title'	=> '小程序',	
		'icon'			=> 'dashicons-share-alt',
		'position'		=> '110.4',	
		'function'		=> 'option',	
		'subs'			=> $subs
	];
	return $wpjam_pages;
}
add_filter('wpjam_settings',function($wpjam_settings){
	$wpjam_settings['wp-api'] = array(
		'sections' => array(
			'basic-setting'=>array(
				'title'=>'基本设置',
				'summary'=>'<p>WordPress + 微信小程序基本设置选项</p>',
				'fields'=> array(
					'appid'=>array('title'=>'微信小程序 AppId','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppId 需要到微信小程序后台获取'),
					'secretkey'=>array('title'=>'微信小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppSecret 需要到微信小程序后台获取'),
					'swipe'=>array('title'=>'小程序焦点幻灯片','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'焦点幻灯片文章 ID 需要到网站后台查看获取'),
					'formats'=>array('title'=>'文章格式类型','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'aside, gallery, link, image, quote, status, video, audio, chat'),
					'meta_list'=>array('title'=>'小程序自定义栏目','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'自定义标签 Key , 使用英文 "," 逗号隔开。注意：输助选项如果禁用 meta 标签，则此处设置无效'),
					'prasie'=>array('title'=>'赞赏通知信息','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'用户赞赏后发送的消息模板内容。比如：谢谢赞赏，你的支持是我前进的动力'),
					'use_role'=>array('title'=>'微信授权用户组','type'=>'select','options'=>array('subscriber'=>'订阅组','contributor'=>'投稿组','author'=>'作者组','editor'=>'编辑组')),
					'prefix'=>array('title'=>'默认缩略图','type'=>'img','item_type'=>'url'),
				),
			),
			'custom-setting'=>array(
				'title'=>'辅助选项',
				'summary'=>'<p>WordPress 自定义 API 选项</p>',
				'fields'=> array(
					'encomments'=>array('title'=>'小程序评论功能','type'=>'checkbox','description'=>'是否开启小程序评论功能'),
					'approved'=>array('title'=>'小程序评论审核','type'=>'checkbox','description'=>'是否开启小程序评论审核'),
					'user_users'=>array('title'=>'小程序用户列表','type'=>'checkbox','description'=>'是否禁用小程序用户列表'),
					'post_excerpt'=>array('title'=>'文章摘要标签','type'=>'checkbox','description'=>'是否禁止文章输出 excerpt 摘要项目'),
					'post_format'=>array('title'=>'文章格式标签','type'=>'checkbox','description'=>'是否禁止文章输出 format 文章格式'),
					'post_type'=>array('title'=>'文章类型标签','type'=>'checkbox','description'=>'是否禁止文章输出 type 标签项目'),
					'post_author'=>array('title'=>'文章作者标签','type'=>'checkbox','description'=>'是否禁止文章输出 author 标签项目'),
					'post_meta'=>array('title'=>'文章自定义标签','type'=>'checkbox','description'=>'是否禁止文章输出 meta 标签项目'),
					'post_prev'=>array('title'=>'文章上下篇','type'=>'checkbox','description'=>'是否开启文章输出上一篇及下一篇'),
					'qvideo'=>array('title'=>'解析腾讯视频','type'=>'checkbox','description'=>'视频格式文章,开启解析腾讯视频,仅支持一个视频'),
					'deletehtml'=>array('title'=>'清理HTML标签','type'=>'checkbox','description'=>'是否开启清理分类描述 HTML 标签'),
					'reupload'=>array('title'=>'图片重命名','type'=>'checkbox','description'=>'是否开启上传图片重命名,注意主题是否有冲突'),
				),
			),
			'adsense-setting'=>array(
				'title'=>'广告功能',
				'summary'=>'<p>微信小程序广告功能设置</p>',
				'fields'=> array(
					'index_adv'=>array('title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'),
					'index_option'=>array('title'=>'广告类型','type'=>'select','options'=>array('wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告')),
					'index_adpic'=>array('title'=>'广告图片','type'=>'img','item_type'=>'url'),
					'index_adpage'=>array('title'=>'广告页面','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 或 活动广告 时,请留空;类型为 微信小程序 时,请填写广告小程序页面路径;'),
					'index_adnum'=>array('title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 时,请填写组件ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;'),
					'list_adv'=>array('title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'),
					'list_option'=>array('title'=>'广告类型','type'=>'select','options'=>array('wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告')),
					'list_adpic'=>array('title'=>'广告图片','type'=>'img','item_type'=>'url'),
					'list_adpage'=>array('title'=>'广告页面','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 或 活动广告 时,请留空;类型为 微信小程序 时,请填写广告小程序页面路径;'),
					'list_adnum'=>array('title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 时,请填写组件ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;'),
					'detail_adv'=>array('title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'),
					'detail_option'=>array('title'=>'广告类型','type'=>'select','options'=>array('wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告')),
					'detail_adpic'=>array('title'=>'广告图片','type'=>'img','item_type'=>'url'),
					'detail_adpage'=>array('title'=>'广告页面','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 或 活动广告 时,请留空;类型为 微信小程序 时,请填写广告小程序页面路径;'),
					'detail_adnum'=>array('title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 时,请填写组件ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;'),
				),
			),
		),
	);
	return $wpjam_settings;
});
function wp_basic_about_page(){ ?>
	<div style="width:600px;">
	<p>以下 API 属于自定义增强 API 。未有列明的 API ，可以登录 http://v2.wp-api.org/ 查阅</p>
	<h3>文章相关 API</h3>
	<p>随机文章 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/random</p>
	<p>滑动文章 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/swipe</p>
	<p>阅读统计 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/update/id (文章ID)</p>
	<p>热门阅读 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/most</p>
	<p>热门点赞 API：<?php echo site_url(); ?>/wp-json/wechat/v1/thumbs/most</p>
	<h3>点赞相关 API</h3>
	<p>用户点赞 API：<?php echo site_url(); ?>/wp-json/wechat/v1/thumbs/up</p>
	<p>是否点赞 API：<?php echo site_url(); ?>/wp-json/wechat/v1/thumbs/get</p>
	<p>个人点赞文章：<?php echo site_url(); ?>/wp-json/wechat/v1/thumbs/user</p>
	<h3>订阅相关 API</h3>
	<p>订阅分类 API：<?php echo site_url(); ?>/wp-json/wechat/v1/category/sub</p>
	<p>是否订阅 API：<?php echo site_url(); ?>/wp-json/wechat/v1/category/get</p>
	<h3>评论相关 API</h3>
	<p>开启评论功能：<?php echo site_url(); ?>/wp-json/wechat/v1/comment/setting</p>
	<p>热门评论 API：<?php echo site_url(); ?>/wp-json/wechat/v1/comment/most</p>
	<p>最新评论 API：<?php echo site_url(); ?>/wp-json/wechat/v1/comment/recent</p>
	<p>评论回复 API：<?php echo site_url(); ?>/wp-json/wechat/v1/comment/comments</p>
	<p>微信提交评论：<?php echo site_url(); ?>/wp-json/wechat/v1/comment/add</p>
	<p>微信评论 API：<?php echo site_url(); ?>/wp-json/wechat/v1/comment/get</p>
	<h3>用户相关 API</h3>
	<p>获取用户 ID：<?php echo site_url(); ?>/wp-json/wechat/v1/user/get</p>
	<h3>用户微信 OPENID</h3>
	<p>微信 OPENID API：<?php echo site_url(); ?>/wp-json/wechat/v1/user/openid</p>
	<h3>消息相关 API</h3>
	<p>发送消息 API：<?php echo site_url(); ?>/wp-json/wechat/v1/message/send</p>
	<h3>海报相关 API</h3>
	<p>生成二维码 API：<?php echo site_url(); ?>/wp-json/wechat/v1/qrcode/creat</p>
	<h3>广告相关 API</h3>
	<p>开启首页广告：<?php echo site_url(); ?>/wp-json/wechat/v1/adenable/index</p>
	<p>首页广告 API：<?php echo site_url(); ?>/wp-json/wechat/v1/adsense/index</p>
	<p>开启列表页广告：<?php echo site_url(); ?>/wp-json/wechat/v1/adenable/list</p>
	<p>列表页广告 API：<?php echo site_url(); ?>/wp-json/wechat/v1/adsense/list</p>
	<p>开启详情页广告：<?php echo site_url(); ?>/wp-json/wechat/v1/adenable/detail</p>
	<p>详情页广告 API：<?php echo site_url(); ?>/wp-json/wechat/v1/adsense/detail</p>
	</div>
	<?php 
}