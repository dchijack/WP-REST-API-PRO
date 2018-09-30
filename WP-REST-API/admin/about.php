<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
function wp_rest_api_about(){ ?>
	<div style="width:600px;">
	<h2>小程序 API</h2>
	<p>以下 API 属于自定义增强 API 。未有列明的 API ，可以登录 http://v2.wp-api.org/ 查阅</p>
	<h3>文章相关 API</h3>
	<p>随机文章 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/random</p>
	<p>滑动文章 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/swipe</p>
	<p>阅读统计 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/update/id (文章ID)</p>
	<p>热门阅读 API：<?php echo site_url(); ?>/wp-json/wechat/v1/views/most</p>
	<p>热门点赞 API：<?php echo site_url(); ?>/wp-json/wechat/v1/thumbs/most</p>
	<p>开启投稿 API：<?php echo site_url(); ?>/wp-json/wechat/v1/creat/setting</p>
	<h3>自定义文章类型</h3>
	<p>文章列表 API：<?php echo site_url(); ?>/wp-json/wp/v2/xx (xx 表示 自定义文章类型别名)</p>
	<p>分类列表 API：<?php echo site_url(); ?>/wp-json/wp/v2/xx (xx 表示 自定义文章类型分类别名)</p>
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
<?php }
function wp_rest_api_dev(){ ?>
	<div style="width:600px;">
	<h2>关于 WordPress 连接微信小程序的插件</h2>
	<p><a href="https://www.imahui.com/design/840.html" target="_blank">WordPress 连接微信小程序的插件</a>是 <a href="https://www.imahui.com/" target="_blank">艾码汇</a> 基于守望轩开源插件 <a href="https://github.com/iamxjb/wp-rest-api-for-app">wp-rest-api-for-app</a> 整合定制优化版。部分功能与守望轩原版相同，但是整体的数据展现方式有所不同，以及后台设置分别采用 <a href="http://blog.wpjam.com/project/wpjam-basic/" target="_blank">WPJAM BASIC</a> 插件框架以及普通设置版本。</p>
	<h3>公众号</h3>
	<p>欢迎关注个人微信公众号，关于 WordPress 连接微信小程序的插件以及相关小程序，以后将会在这里第一时间发布。</p>
	<p><img src="https://www.imahui.com/uploads/mpconsole-code.jpg" /></p>
	</div>
	<?php 
}