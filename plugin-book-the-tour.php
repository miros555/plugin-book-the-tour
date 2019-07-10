<?php
/*
 * Plugin Name: Плагин бронирования туров
 * Plugin URI: https://fabrik.top
 * Description: Плагин для создания отдельных записей для туров
 * Version: 1.1
 * Author: Miro
 * Author URI: https://fabrik.top
 * License: GPLv2 or later
 */
 


add_action( 'init', 'true_register_post_type_init' ); 
 
function true_register_post_type_init() {
	$labels = array(
		'name' => 'ture',
		'singular_name' => 'tur', // админ панель Добавить->Тур
		'add_new' => 'Новый тур',
		'add_new_item' => 'Добавить новый тур', // заголовок тега <title>
		'edit_item' => 'Редактировать тур',
		'new_item' => 'Новый тур',
		'all_items' => 'Все туры',
		'view_item' => 'Просмотр туров на сайте',
		'search_items' => 'Искать туры',
		'not_found' =>  'Туров не найдено.',
		'not_found_in_trash' => 'В корзине нет туров.',
		'menu_name' => 'ТУРЫ' // ссылка в меню в админке
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_ui' => true, // показывать интерфейс в админке
		'has_archive' => true, 
		'menu_icon' => get_stylesheet_directory_uri() .'/img/function_icon.png', // иконка в меню
		'menu_position' => 20, // порядок в меню
		'supports' => array( 'title', 'editor', 'thumbnail')
	);
	register_post_type('tures', $args);
}




class trueMetaBox {
	function __construct($options) {
		$this->options = $options;
		$this->prefix = $this->options['id'] .'_';
		add_action( 'add_meta_boxes', array( &$this, 'create' ) );
		add_action( 'save_post', array( &$this, 'save' ), 1, 2 );
	}
	function create() {
		foreach ($this->options['post'] as $post_type) {
			if (current_user_can( $this->options['cap'])) {
				add_meta_box($this->options['id'], $this->options['name'], array(&$this, 'fill'), $post_type, $this->options['pos'], $this->options['pri']);
			}
		}
	}
	function fill(){
		global $post; $p_i_d = $post->ID;
		wp_nonce_field( $this->options['id'], $this->options['id'].'_wpnonce', false, true );
		?>
		<table class="form-table"><tbody><?php
		foreach ( $this->options['args'] as $param ) {
			if (current_user_can( $param['cap'])) {
			?><tr><?php
				if(!$value = get_post_meta($post->ID, $this->prefix .$param['id'] , true)) $value = $param['std'];
				switch ( $param['id'] ) {
					
					case 'field_1':{ ?>
						<th scope="row"><label for="<?php echo $this->prefix .$param['id'] ?>"><?php echo $param['title'] ?></label></th>
						<td>
							<input name="<?php echo $this->prefix .$param['id'] ?>" type="<?php echo $param['type'] ?>" id="<?php echo $this->prefix .$param['id'] ?>" value="<?php echo $value ?>" placeholder="<?php echo $param['placeholder'] ?>" class="regular-text" /><br />
							<span class="description"><?php echo $param['desc'] ?></span>
						</td>
						<?php
						break;							
					}
			
					case 'field_2':{ ?>
						<th scope="row"><label for="<?php echo $this->prefix .$param['id'] ?>"><?php echo $param['title'] ?></label></th>
						<td>
							<input name="<?php echo $this->prefix .$param['id'] ?>" type="<?php echo $param['type'] ?>" id="<?php echo $this->prefix .$param['id'] ?>" value="<?php echo $value ?>" placeholder="<?php echo $param['placeholder'] ?>" class="regular-text" /><br />
							<span class="description"><?php echo $param['desc'] ?></span>
						</td>
						<?php
						break;							
					}
					
					
					case 'select':{ ?>
						<th scope="row"><label for="<?php echo $this->prefix .$param['id'] ?>"><?php echo $param['title'] ?></label></th>
						<td>
							<label for="<?php echo $this->prefix .$param['id'] ?>">
							<select name="<?php echo $this->prefix .$param['id'] ?>" id="<?php echo $this->prefix .$param['id'] ?>"><option>...</option><?php
								foreach($param['args'] as $val=>$name){
									?><option value="<?php echo $val ?>"<?php echo ( $value == $val ) ? ' selected="selected"' : '' ?>><?php echo $name ?></option><?php
								}
							?></select></label><br />
							<span class="description"><?php echo $param['desc'] ?></span>
						</td>
						<?php
						break;							
					}
				} 
			?></tr><?php
			}
		}
		?></tbody></table><?php
	}
	function save($post_id, $post){
		if ( !wp_verify_nonce( $_POST[ $this->options['id'].'_wpnonce' ], $this->options['id'] ) ) return;
		if ( !current_user_can( 'edit_post', $post_id ) ) return;
		if ( !in_array($post->post_type, $this->options['post'])) return;
		foreach ( $this->options['args'] as $param ) {
			if ( current_user_can( $param['cap'] ) ) {
				if ( isset( $_POST[ $this->prefix . $param['id'] ] ) && trim( $_POST[ $this->prefix . $param['id'] ] ) ) {
					update_post_meta( $post_id, $this->prefix . $param['id'], trim($_POST[ $this->prefix . $param['id'] ]) );
				} else {
					delete_post_meta( $post_id, $this->prefix . $param['id'] );
				}
			}
		}
	}
}





$options = array(
	array( // первый метабокс
		'id'	=>	'meta1', // ID метабокса, а также префикс названия произвольного поля
		'name'	=>	'Дополнительные настройки', // заголовок метабокса
		'post'	=>	array('tures'), // типы постов для которых нужно отобразить метабокс
		'pos'	=>	'normal', // расположение, параметр $context функции add_meta_box()
		'pri'	=>	'high', // приоритет, параметр $priority функции add_meta_box()
		'cap'	=>	'edit_posts', // какие права должны быть у пользователя
		'args'	=>	array(
			array(
				'id'			=>	'field_1', // атрибуты name и id без префикса, например с префиксом будет meta1_field_1
				'title'			=>	'Даты тура', // лейбл поля
				'type'			=>	'text', // тип, в данном случае обычное текстовое поле
				'placeholder'		=>	'Укажите здесь дату проведения тура', // атрибут placeholder
				'desc'			=>	'Например: "25.06.2019 - 15.07.2019"', // что-то типа пояснения, подписи к полю
				'cap'			=>	'edit_posts'
			),
			
			
			array(
				'id'			=>	'select',
				'title'			=>	'Выберите страну',
				'type'			=>	'select', // выпадающий список
				'desc'			=>	'Выберите из выпадающего списка страну для тура',
				'cap'			=>	'edit_posts',
				'args'			=>	array('value_1' => 'Англия', '2' => 'Германия', 'Значение_3' => 'Франция', '4' => 'Египет',
				'5' => 'Черногория', '6' => 'Япония') // элементы списка задаются через массив args, по типу value=>лейбл
			),
			
			
				array(
				'id'			=>	'field_2', // атрибуты name и id без префикса, например с префиксом будет meta1_field_1
				'title'			=>	'Стоимость', // лейбл поля
				'type'			=>	'text', // тип, в данном случае обычное текстовое поле
				'placeholder'		=>	'Укажите в этом поле стоимость тура в долларах', // атрибут placeholder
				'desc'			=>	'Например: "400 $"', // что-то типа пояснения, подписи к полю
				'cap'			=>	'edit_posts'
			),
			
			
			
		)
	),
	

);
 
foreach ($options as $option) {
	$truemetabox = new trueMetaBox($option);
}



/*
* Media/Галлерея дополнительных изображений *Metabox
*/
define('WPDS_NUM_ADD_IMAGES',10); // Изменяем количество изображений
add_action('add_meta_boxes', 'wpds_media_meta_box');
add_action('save_post','wpds_save_media_mb');
function wpds_media_meta_box() {
    // меняем 'tures' на любой другой произвольный тип записи
    add_meta_box('additional_images', 'Дополнительные изображения для туров', 'additional_images_cb', 'tures', 'normal');
}

function additional_images_cb($post) {
    
    echo '<table>';
    for($i=0; $i<WPDS_NUM_ADD_IMAGES; $i++){
        echo ($i%2==0) ? '<tr valign="top">':'';
        echo '<td valign=top width="40%"><input id="add_img'.$i.'_field" type="text" size="36" 
        name="add_img'.$i.'" value="'.get_post_meta($post->ID,'add_img'.$i,true).'" />
        <input id="add_img'.$i.'" class="upload_buttons" type="button" value="Загрузить/Выбрать" /></td><td width="10%">';
        if(has_additional_image($post->ID,$i) )
                echo '<a href="'.the_additional_image_url($post->ID,$i).' target="_blank">
                <img src="'.the_additional_image_url($post->ID,$i,array(30,30)).'" height=30 /></a>';
        echo '</td>';
        echo (($i+1)%2==0) ? '</tr>':'';
    }
    echo '
        <tr><td colspan="4"><br>* Вы можете указать URL-адрес изображения или идентификатор вложения с помощью кнопки Загрузить/Выбрать</td></tr>
    </table>';
    ?>
    <script>
        jQuery(document).ready(function() {
            var formfieldID = '';
            var wpds_orig_send_to_editor = window.send_to_editor;
            jQuery('.upload_buttons').click(function() {
                formfieldID = jQuery(this).attr('id')+'_field';
                tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
                
                window.send_to_editor = function(html) {
                    attachmentID = html.match(/wp-image-([0-9]+)/);
                    if(attachmentID)
                        pasteValue = attachmentID[1];
                    else
                        pasteValue = jQuery(html).filter('img').attr('src');

                    jQuery('#'+formfieldID).val(pasteValue);
                    tb_remove();
                    window.send_to_editor = wpds_orig_send_to_editor;
                }
                return false;
            });

        });
    </script>
    <?
}

function wpds_save_media_mb($post_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id))
        return;

    for($i=0; $i<WPDS_NUM_ADD_IMAGES; $i++){
        if(isset($_POST['add_img'.$i]))
            update_post_meta($post_id, 'add_img'.$i, $_POST['add_img'.$i]);
    }
}


function has_additional_image($post_id, $id=1) {
   
    $meta = get_post_meta($post_id,'add_img'.$id, true);
    if(empty($meta))    return false;
    return true;
}

function the_additional_image_url($post_id, $id=1, $size='post-thumbnail') {
    $meta = get_post_meta($post_id,'add_img'.$id, true);
    if(empty($meta))    return false;
    if(is_numeric($meta)){
        $image = wp_get_attachment_image_src($meta, $size);
        if(!empty($image))
            return $image[0];
        return false;
    }
    else{
        return $meta;
    }
}
