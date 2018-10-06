<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 自定义文章类型
if (wp_get_option('custom_menu')) {
	add_action('init', function ()   {
		$name = wp_get_option('custom_menu');
		$singular = wp_get_option('custom_singular');
		$icon = wp_get_option('custom_icon');
		$supports = wp_get_option('custom_supports');
		$labels = array(   
			'name' => $name,   
			'singular_name' => $singular,
			'menu_name' => $name,
			'name_admin_bar' => $name,
			'add_new' => '添加',   
			'add_new_item' => '新建',   
			'edit_item' => '编辑',   
			'new_item' => '新增'  
		);   
		$args = array(   
			'labels' => $labels,  
			'public' => true,
			'show_ui' => true,    
			'show_in_menu' => true,    
			'query_var' => true,   
			'rewrite'   => array( 'slug' => $singular ),
			'capability_type' => 'post',   
			'has_archive' => false,    
			'exclude_from_search' => true,
			'menu_position' => 8,
			'supports' => $supports,
			'menu_icon' => $icon,
			'show_in_rest'       => true,
			'rest_base'          => $singular,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		); 
		register_post_type($singular,$args); 
	});
	// 自定义文章类型固定链接
	add_filter('post_type_link', function ( $link, $post = 0 ){
		$singular = wp_get_option('custom_singular');
		if ( $post->post_type == $singular ){
			return home_url( $singular.'/' . $post->ID .'.html' );
		} else {
			return $link;
		}
	},1, 3);
	add_action( 'init', function (){
		$singular = wp_get_option('custom_singular');
		add_rewrite_rule(
			$singular.'/([0-9]+)?.html$',
			'index.php?post_type='.$singular.'&p=$matches[1]',
			'top' 
		);
		add_rewrite_rule(
			$singular.'/([0-9]+)?.html/comment-page-([0-9]{1,})$',
			'index.php?post_type='.$singular.'&p=$matches[1]&cpage=$matches[2]',
			'top'
		);
	});
	add_filter( 'rest_prepare_'.wp_get_option('custom_singular'), function ($data, $post, $request) {
		global $wpdb;
		$_data = $data->data;    
		$post_id = $post->ID;
		$post_views = (int)get_post_meta($post_id, 'views',true);
		$post_thumbnail = get_post_thumbnail($post_id);
		$post_comment = wp_count_comments($post_id);
		//$content = $post->post_content;
		$category = get_the_category($post_id);
		$categoryId=$category[0]->term_id;
		$next_post = get_next_post($categoryId, '', 'category');
		$previous_post = get_previous_post($categoryId, '', 'category');
		$sql_thumbs = $wpdb->prepare("SELECT COUNT(1) FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
		$post_thumbs = $wpdb->get_var($sql_thumbs);
		$params = $request->get_params();
		if ( isset( $params['id'] )) {
			$sql=$wpdb->prepare("SELECT meta_key , (SELECT ID from ".$wpdb->users." WHERE user_login=substring(meta_key,2)) as userID FROM ".$wpdb->postmeta." where meta_value='thumbs' and post_id=%d",$post_id);
			$zanuser = $wpdb->get_results($sql);
			$avatarurls =array();
			foreach ($zanuser as $userid) {
				$_avatarurl['avatar'] = get_user_meta( $userid->userID, 'wxavatar', true);
				$avatarurls[] = $_avatarurl;       
			}
		} else {
			if (get_setting_option('post_content')) { unset($_data['content'] ); }  	
		}
		//$_data['content']['rendered'] = $content;
		$_data['category'] = $category[0]->cat_name;
		$_data['comments'] = $post_comment->total_comments;
		$_data['thumbses'] = $post_thumbs;
		if (get_setting_option('post_meta')) {
			$_data["thumbnail"] = $post_thumbnail;
			$_data["views"] = $post_views;
		} else {
			$_data["meta"]["thumbnail"] = $post_thumbnail;
			$_data["meta"]["views"] = $post_views;
			$meta = get_setting_option('meta_list');
			if (!empty($meta)) {
				foreach ($meta as $meta=>$key) {
					$_data["meta"][$key] = get_post_meta( $post_id, $key ,true );
				}
			}
			//-----------------------------------------------------------
		}
		$_data['avatar']= $avatarurls;
		if (get_setting_option('post_prev')) {
			$_data['next_id'] = !empty($next_post->ID)?$next_post->ID:null;
			$_data['next_title'] = !empty($next_post->post_title)?$next_post->post_title:null;
			$_data['previous_id'] = !empty($previous_post->ID)?$previous_post->ID:null;
			$_data['previous_title'] = !empty($previous_post->post_title)?$previous_post->post_title:null;
		}
		if (get_setting_option('post_excerpt')) {unset($_data['excerpt']);}
		if (get_setting_option('post_meta')) {unset($_data['meta']); }
		if (get_setting_option('post_format')) {unset($_data['format']);}
		if (get_setting_option('post_type')) {unset($_data['type']);}
		if (get_setting_option('post_author')) {unset($_data['author']);} else {$_data['author'] = get_the_author_meta('display_name',$post->post_author);} // 显示文章作者昵称
		unset($_data['featured_media']);
		unset($_data['ping_status']);
		unset($_data['template']);
		unset($_data['slug']);
		unset($_data['modified_gmt']);
		unset($_data['date_gmt']);
		unset($_data['guid']);
		unset($_data['curies']);
		unset($_data['modified']);
		unset($_data['status']);
		unset($_data['comment_status']);
		unset($_data['sticky']);    
		unset($_data['_links']['self']); 
		$data->data = $_data; 
		return $data; 
	},10, 3 );
}
if (wp_get_option('custom_category')) {
	// 自定义文章类型分类
	add_action( 'init',function () {
		$singular = wp_get_option('custom_singular');
		$category = wp_get_option('custom_category');
		$labels = array(
			'name'              => _x( '分类', '分类名称' ),
			'singular_name'     => _x( $category, '分类别名' ),
			'search_items'      => __( '搜索分类' ),
			'all_items'         => __( '所有分类' ),
			'parent_item'       => __( '上级分类' ),
			'parent_item_colon' => __( '父级分类:' ),
			'edit_item'         => __( '编辑' ),
			'update_item'       => __( '更新' ),
			'add_new_item'      => __( '新建' ),
			'new_item_name'     => __( '新增' ),
			'menu_name'         => __( '分类' ),
		);
		$args = array(
			'hierarchical'          => true,
			'labels'                => $labels,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => $category ),
			'show_in_rest'          => true,
			'rest_base'             => $category,
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy( $category, array( $singular ), $args );
	}, 30 );
	add_filter( 'rest_prepare_'.wp_get_option('custom_category'), function ($data, $item, $request) {
		$category_cover_image = '';
		$temp = '';
		$term_id = $item->term_id;
		$args = array('category'=>$term_id,'numberposts' => 1);
		$posts = get_posts($args);
		if (!empty($posts)) {
			$recent_date = $posts[0]->post_date;
		} else {
			$recent_date = '暂无更新';
		}
		if($temp = get_term_meta($item->term_id,'cover',true)) {
			$category_cover_image = $temp; 
		}
		$data->data['cover'] = $category_cover_image;
		$data->data['date'] = $recent_date; 	
		return $data;
	}, 10, 3 );
	/*********   给分类添加封面 *********/
	add_action( wp_get_option('custom_category').'_add_form_fields', function () {
		wp_nonce_field( basename( __FILE__ ), 'the_category_term_cover_nonce' ); ?>
		<div class="form-field the-category-term-cover-wrap">
			<label for="the-category-cover">封面</label>
			<input type="url" name="the_category_term_cover" id="the-category-cover"  class="type-image regular-text" data-default-cover="" />
		</div>
	<?php });
	add_action( wp_get_option('custom_category').'_edit_form_fields', function ( $term ) {
		$default = '';
		$cover   = get_term_meta( $term->term_id, 'cover', true );
		if (!$cover)
		   $cover = $default; 
	?>
		<tr class="form-field the-category-term-cover-wrap">
			<th scope="row"><label for="the-category-cover">封面 </label></th>
			<td>
				<?php echo wp_nonce_field( basename( __FILE__ ), 'the_category_term_cover_nonce' ); ?>
				<input type="url" name="the_category_term_cover" id="the-category-cover" class="type-image regular-text" value="<?php echo esc_attr( $cover ); ?>" data-default-cover="<?php echo esc_attr( $default ); ?>" />
			</td>
		</tr>
	<?php });
	add_action( 'create_'.wp_get_option('custom_category'), 'save_custom_category_cover' );
	add_action( 'edit_'.wp_get_option('custom_category'),'save_custom_category_cover' );
	function save_custom_category_cover( $term_id ) {
		if ( ! isset( $_POST['the_category_term_cover_nonce'] ) || ! wp_verify_nonce( $_POST['the_category_term_cover_nonce'], basename( __FILE__ ) ) )
			return;
		$cover = isset( $_POST['the_category_term_cover'] ) ? $_POST['the_category_term_cover'] : '';
		if ( '' === $cover ) {
			delete_term_meta( $term_id, 'cover' );
		} else {
			update_term_meta( $term_id, 'cover', $cover );
		}
	}
}
if (wp_get_option('custom_tags')) {
	// 注册自定义文章类型分类标签
	add_action( 'init', function () {
		$singular = wp_get_option('custom_singular');
		$tags = wp_get_option('custom_tags');
		register_taxonomy($tags,$singular, array( 'hierarchical' => false,  'label' => '标签', 'query_var' => true, 'rewrite' =>  array( 'slug' => $tags ), 'show_in_rest'  => true, 'rest_base'  => $tags, 'rest_controller_class' => 'WP_REST_Terms_Controller',)); 
	}, 30 );
}