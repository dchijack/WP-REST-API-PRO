<?php
/*
 * 
 * WordPres 连接微信小程序
 * Author: JIANBO + Denis + 艾码汇
 * github:  https://www.imahui.com
 * 基于 守望轩 WP REST API For App 开源插件定制 , 使用 WPJAM BASIC 框架
 * 
 */
// 启用分类封面
add_filter( 'rest_prepare_category', 'rest_category_cover', 10, 3 ); // 获取分类的封面图片
function rest_category_cover($data, $item, $request) {	  
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
}
/*********   给分类添加封面 *********/
add_action( 'category_add_form_fields', 'the_category_cover_field' );
function the_category_cover_field() {
    wp_nonce_field( basename( __FILE__ ), 'the_category_term_cover_nonce' ); ?>
    <div class="form-field the-category-term-cover-wrap">
        <label for="the-category-cover">封面</label>
        <input type="url" name="the_category_term_cover" id="the-category-cover"  class="type-image regular-text" data-default-cover="" />
    </div>
<?php }
add_action( 'category_edit_form_fields', 'edit_category_cover_field' );
function edit_category_cover_field( $term ) {
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
<?php }
add_action( 'create_category', 'save_category_cover' );
add_action( 'edit_category',   'save_category_cover' );
function save_category_cover( $term_id ) {
    if ( ! isset( $_POST['the_category_term_cover_nonce'] ) || ! wp_verify_nonce( $_POST['the_category_term_cover_nonce'], basename( __FILE__ ) ) )
        return;
    $cover = isset( $_POST['the_category_term_cover'] ) ? $_POST['the_category_term_cover'] : '';
    if ( '' === $cover ) {
        delete_term_meta( $term_id, 'cover' );
    } else {
        update_term_meta( $term_id, 'cover', $cover );
    }
}
