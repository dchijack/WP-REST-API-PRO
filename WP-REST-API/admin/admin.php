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
		'menu_title'	=> '基本设置',	
		'function'		=> 'option',
		'option_name'	=> 'wp-api',
	];
	$subs['api-about']	= [
		'menu_title'	=> '使用指南',	
		'function'		=> 'tab',
		'tabs'			=> [
			'api'		=> ['title'=>'小程序 API 列表',	'function'=>'wp_rest_api_about'],
			'about'	=> ['title'=>'关于小程序插件',	'function'=>'wp_rest_api_dev'],
		],
	];
	$wpjam_pages['api-settings']	= [
		'menu_title'	=> '小程序',	
		'icon'			=> 'dashicons-editor-code',
		'position'		=> '2',	
		'function'		=> 'option',	
		'subs'			=> $subs
	];
	return $wpjam_pages;
}
add_filter('wpjam_settings',function($wpjam_settings){
	$wpjam_settings['wp-api'] = [
		'sections' => [
			'basic-setting'=>[
				'title'=>'基本设置',
				'summary'=>'<p>WordPress + 微信小程序基本设置选项</p>',
				'fields'=> [
					'appid'=>['title'=>'微信小程序 AppId','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppId 需要到微信小程序后台获取'],
					'secretkey'=>['title'=>'微信小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppSecret 需要到微信小程序后台获取'],
					'swipe'=>['title'=>'小程序焦点幻灯片','type'=>'mu-text','class'=>'regular-text','rows'=>4,'description'=>'焦点幻灯片文章 ID 需要到网站后台查看获取'],
					'formats'=>['title'=>'文章格式类型','type'=>'mu-text','class'=>'regular-text','rows'=>4,'description'=>'文章格式：aside, gallery, link, image, quote, status, video, audio, chat'],
					'meta_list'=>['title'=>'小程序自定义栏目','type'=>'mu-text','class'=>'regular-text','rows'=>4,'description'=>'自定义标签 Key , 可以添加多个自定义标签。注意：输助选项如果禁用 meta 标签，则此处设置无效'],
					'prefix'=>['title'=>'默认缩略图','type'=>'image','item_type'=>'url'],
					'use_role'=>['title'=>'微信授权用户组','type'=>'select','options'=>['subscriber'=>'订阅组','contributor'=>'投稿组','WxAuthor'=>'微作者','author'=>'作者组','editor'=>'编辑组']],
					'use_meta'=>['title'=>'定义用户字段','type'=>'mu-fields','fields'=>[
						'meta_key'	=> ['title'=>'', 'type'=>'text', 'description'=>'自定义用户资料字段键名(英文或拼音)'],
						'meta_value'	=> ['title'=>'', 'type'=>'text', 'description'=>'自定义用户资料字段标签名称(中文名称)'],
						]
					],
				],
			],
			'custom-setting'=>[
				'title'=>'辅助选项',
				'summary'=>'<p>WordPress 自定义 API 选项</p>',
				'fields'=> [
					'enposts'=>['title'=>'小程序投稿功能','type'=>'checkbox','description'=>'是否开启小程序文章投稿'],
					'encomments'=>['title'=>'小程序评论功能','type'=>'checkbox','description'=>'是否开启小程序评论功能'],
					'approved'=>['title'=>'小程序评论审核','type'=>'checkbox','description'=>'是否开启小程序评论审核'],
					'user_users'=>['title'=>'小程序用户列表','type'=>'checkbox','description'=>'是否禁用小程序用户列表'],
					'post_content'=>['title'=>'文章列表内容','type'=>'checkbox','description'=>'是否禁止文章列表输出 content 项目'],
					'post_excerpt'=>['title'=>'文章摘要标签','type'=>'checkbox','description'=>'是否禁止文章输出 excerpt 摘要项目'],
					'post_format'=>['title'=>'文章格式标签','type'=>'checkbox','description'=>'是否禁止文章输出 format 文章格式'],
					'post_type'=>['title'=>'文章类型标签','type'=>'checkbox','description'=>'是否禁止文章输出 type 标签项目'],
					'post_author'=>['title'=>'文章作者标签','type'=>'checkbox','description'=>'是否禁止文章输出 author 标签项目'],
					'post_meta'=>['title'=>'文章自定义标签','type'=>'checkbox','description'=>'是否禁止文章输出 meta 标签项目'],
					'list_content'=>['title'=>'列表内容选项','type'=>'checkbox','description'=>'是否开启热门、点赞、随机文章列表内容'],
					'post_prev'=>['title'=>'文章上下篇','type'=>'checkbox','description'=>'是否开启文章输出上一篇及下一篇'],
					'qvideo'=>['title'=>'解析腾讯视频','type'=>'checkbox','description'=>'视频格式文章,开启解析腾讯视频,仅支持一个视频'],
					'deletehtml'=>['title'=>'清理HTML标签','type'=>'checkbox','description'=>'是否开启清理分类描述 HTML 标签'],
					'reupload'=>['title'=>'图片重命名','type'=>'checkbox','description'=>'是否开启上传图片重命名,注意主题是否有冲突'],
				],
			],
			'custom-posts'=>[
				'title'=>'文章类型',
				'summary'=>'<p>WordPress 自定义文章类型</p>',
				'fields'=> [
					'custom_menu'	=> ['title'=>'类型名称', 'type'=>'text','description'=>'自定义文章类型菜单名称'],
					'custom_singular'	=> ['title'=>'类型别名', 'type'=>'text','description'=>'自定义文章类型别名,建议英文或拼音'],
					'custom_category'	=> ['title'=>'分类别名', 'type'=>'text','description'=>'自定义文章类型分类别名,建议英文或拼音'],
					'custom_tags'	=> ['title'=>'标签别名', 'type'=>'text','description'=>'自定义文章类型标签别名,建议英文或拼音'],
					'custom_icon'	=> ['title'=>'图标ICON', 'type'=>'text', 'description'=>'Dashicons 图标, 可在 WPJAM - Dashicons 中查找使用'],
					'custom_supports' => ['title'=>'支持类型', 'type'=>'mu-text','description'=>'title: 标题; editor: 内容; author: 作者; thumbnail: 特色图像; excerpt: 摘要; trackbacks: 引用; custom-fields: 自定义标签; comments: 评论; post-formats: 格式'],
				],
			],
			'adsense-setting'=>[
				'title'=>'广告功能',
				'summary'=>'<p>微信小程序广告功能设置</p>',
				'fields'=> [
					'index_adv'=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
					'index_option'=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告']],
					'index_adpic'=>['title'=>'广告图片','type'=>'img','item_type'=>'url'],
					'index_adpage'=>['title'=>'广告页面','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 或 活动广告 时,请留空;类型为 微信小程序 时,请填写广告小程序页面路径;'],
					'index_adnum'=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 时,请填写组件ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;'],
					'list_adv'=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
					'list_option'=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告']],
					'list_adpic'=>['title'=>'广告图片','type'=>'img','item_type'=>'url'],
					'list_adpage'=>['title'=>'广告页面','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 或 活动广告 时,请留空;类型为 微信小程序 时,请填写广告小程序页面路径;'],
					'list_adnum'=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 时,请填写组件ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;'],
					'detail_adv'=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
					'detail_option'=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告']],
					'detail_adpic'=>['title'=>'广告图片','type'=>'img','item_type'=>'url'],
					'detail_adpage'=>['title'=>'广告页面','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 或 活动广告 时,请留空;类型为 微信小程序 时,请填写广告小程序页面路径;'],
					'detail_adnum'=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'类型为 微信广告组件 时,请填写组件ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;'],
				],
			],
		],
	];
	return $wpjam_settings;
});
