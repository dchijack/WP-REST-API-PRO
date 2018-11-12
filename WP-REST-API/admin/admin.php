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
				'summary'=>'<p>WordPress + 小程序基本设置选项</p>',
				'fields'=> [
					'appid'=>['title'=>'微信小程序 AppId','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppId 需要到微信小程序后台获取'],
					'secretkey'=>['title'=>'微信小程序 AppSecret ','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'微信小程序 AppSecret 需要到微信小程序后台获取'],
					'prefix'=>['title'=>'默认缩略图','type'=>'image','item_type'=>'url'],
					'use_role'=>['title'=>'微信授权用户组','type'=>'select','options'=>['subscriber'=>'订阅组','contributor'=>'投稿组','WxAuthor'=>'微作者','author'=>'作者组','editor'=>'编辑组']],
				],
			],
			'custom-setting'=>[
				'title'=>'自定义设置',
				'summary'=>'<p>WordPress + 小程序自定义设置选项</p>',
				'fields'=> [
					'swipe'=>['title'=>'小程序焦点幻灯片','type'=>'mu-text','class'=>'regular-text','rows'=>4,'description'=>'焦点幻灯片文章 ID 需要到网站后台查看获取'],
					'formats'=>['title'=>'文章格式类型','type'=>'mu-text','class'=>'regular-text','rows'=>4,'description'=>'文章格式：aside, gallery, link, image, quote, status, video, audio, chat'],
					'meta_list'=>['title'=>'小程序自定义栏目','type'=>'mu-text','class'=>'regular-text','rows'=>4,'description'=>'自定义标签 Key , 可以添加多个自定义标签。'],
					'use_meta'=>['title'=>'自定义用户字段','type'=>'mu-fields','fields'=>[
						'meta_key'	=> ['title'=>'', 'type'=>'text', 'description'=>'自定义用户资料字段键名(英文或拼音)'],
						'meta_value'	=> ['title'=>'', 'type'=>'text', 'description'=>'自定义用户资料字段标签名称(中文名称)'],
						]
					],
				],
			],
			'optimize-setting'=>[
				'title'=>'优化设置',
				'summary'=>'<p>WordPress API 数据屏蔽与开启</p>',
				'fields'=> [
					'post_content'=>['title'=>'文章列表内容','type'=>'checkbox','description'=>'是否禁止文章列表 content 项目'],
					'post_excerpt'=>['title'=>'文章摘要标签','type'=>'checkbox','description'=>'是否禁止文章 excerpt 摘要项目'],
					'post_format'=>['title'=>'文章格式标签','type'=>'checkbox','description'=>'是否禁止文章 format 文章格式'],
					'post_type'=>['title'=>'文章类型标签','type'=>'checkbox','description'=>'是否禁止文章 type 标签项目'],
					'post_author'=>['title'=>'文章作者标签','type'=>'checkbox','description'=>'是否禁止文章 author 标签项目'],
					'post_meta'=>['title'=>'文章自定义标签','type'=>'checkbox','description'=>'是否禁止文章 meta 标签项目'],
					'user_users'=>['title'=>'小程序用户列表','type'=>'checkbox','description'=>'是否禁用小程序用户列表项目'],
					'list_content'=>['title'=>'列表内容选项','type'=>'checkbox','description'=>'是否开启热门/点赞/随机列表内容'],
					'post_prev'=>['title'=>'文章上下篇','type'=>'checkbox','description'=>'是否开启文章输出上一篇及下一篇'],
				],
			],
			'increase-setting'=>[
				'title'=>'功能扩展',
				'summary'=>'<p>WordPress API 功能扩展设置,部分设置需要保存并刷新方可设置</p>',
				'fields'=> [
					'enposts'=>['title'=>'小程序投稿功能','type'=>'checkbox','description'=>'是否开启小程序文章投稿'],
					'encomments'=>['title'=>'小程序评论功能','type'=>'checkbox','description'=>'是否开启小程序评论功能'],
					'approved'=>['title'=>'小程序评论审核','type'=>'checkbox','description'=>'是否开启小程序评论审核'],
					'qvideo'=>['title'=>'解析腾讯视频','type'=>'checkbox','description'=>'视频格式文章,开启解析腾讯视频,仅支持一个视频'],
					'deletehtml'=>['title'=>'清理HTML标签','type'=>'checkbox','description'=>'是否开启清理分类描述 HTML 标签'],
					'reupload'=>['title'=>'图片重命名','type'=>'checkbox','description'=>'是否开启上传图片重命名,注意主题是否有冲突'],
					'custom_post'=>['title'=>'自定义文章类型','type'=>'checkbox','description'=>'是否开启自定义文章类型,仅支持一个文章类型设置'],
					'advert_set'=>['title'=>'广告功能设置','type'=>'checkbox','description'=>'是否开启小程序广告功能设置,四种广告类型,增强广告投放'],
					'notice'=>['title'=>'消息推送功能','type'=>'checkbox','description'=>'是否开启古人云消息推送服务,更新发布文章即时推送(仅针对微语录小程序用户开放)'],
				],
			],
		],
	];
	if (wp_get_option('custom_post')) {
		$wpjam_settings['wp-api']['sections']['custom-posts'] = [
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
		];
	}
	if (wp_get_option('advert_set')) {
		$wpjam_settings['wp-api']['sections']['adsense-setting'] = [
			'title'=>'广告功能',
			'summary'=>'<p>微信小程序广告功能设置：类型为 微信广告组件 时,请填写组件 广告位 ID;类型为 微信小程序 时,请填写广告小程序 AppId ;类型为 活动广告 时,请填写活动电话号码;类型为 淘宝口令 时,请填写淘宝口令;</p>',
			'fields'=> [
				'index_adv'=>['title'=>'首页广告','type'=>'checkbox','description'=>'是否开启首页广告'],
				'index_option'=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','taobao'=>'淘宝口令']],
				'index_adpic'=>['title'=>'广告图片','type'=>'mu-image','fields'=>[
					'ad_picture'	=> ['title'=>'','type'=>'img','item_type'=>'url'],
					]
				],
				'index_adnum'=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				'list_adv'=>['title'=>'列表广告','type'=>'checkbox','description'=>'是否开启列表页广告'],
				'list_option'=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','taobao'=>'淘宝口令']],
				'list_adpic'=>['title'=>'广告图片','type'=>'mu-image','fields'=>[
					'ad_picture'	=> ['title'=>'','type'=>'img','item_type'=>'url'],
					]
				],
				'list_adnum'=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
				'detail_adv'=>['title'=>'详情广告','type'=>'checkbox','description'=>'是否开启详情页广告'],
				'detail_option'=>['title'=>'广告类型','type'=>'select','options'=>['wechat'=>'微信广告组件','minapp'=>'微信小程序','picture'=>'活动广告','taobao'=>'淘宝口令']],
				'detail_adpic'=>['title'=>'广告图片','type'=>'mu-image','fields'=>[
					'ad_picture'	=> ['title'=>'','type'=>'img','item_type'=>'url'],
					]
				],
				'detail_adnum'=>['title'=>'广告参数','type'=>'text','class'=>'regular-text','rows'=>4,'description'=>'填写对应的广告类型参数'],
			],
		];
	}
	if (wp_get_option('notice')) {
		$wpjam_settings['wp-api']['sections']['notice-setting'] = [
			'title'=>'消息推送',
			'summary'=>'<p>古人云小程序 vPush 推送服务. 服务消息推送服务设置.</p>',
			'fields'=> [
				'notice_approved' =>	['title'=>'即时推送','type'=>'checkbox','description'=>'是否开启即时推送消息服务,开启时,发布新文章即时推送'],
				'notice_id'	=> ['title'=>'API ID', 'type'=>'text','description'=>'古人云小程序推送 API ID'],
				'notice_secret'	=> ['title'=>'API SECRET', 'type'=>'text','description'=>'古人云小程序推送 API SECRET'],
				'notice_detail' =>	['title'=>'文章页面','type'=>'checkbox','description'=>'是否设置页面路径为文章详情页.路径为 pages/detail/detail?id=文章id'],
				'notice_path' => ['title'=>'指定页面', 'type'=>'text','description'=>'点击消息通知打开页面,默认为 pages/index/index'],
				'notice_data' => ['title'=>'数据类型', 'type'=>'mu-text','description'=>'推送消息数组,根据消息模板填写内容项.默认：标题,时间,内容摘要'],
			],
		];
	}
	return $wpjam_settings;
});
