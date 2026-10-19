<?php
/**
 * Поля досье бойца: звание, подразделение, имя для алфавита.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function at_add_meta_box() {
	add_meta_box(
		'at_dossier',
		'Досье',
		'at_render_meta_box',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'at_add_meta_box' );

function at_render_meta_box( $post ) {
	wp_nonce_field( 'at_save_dossier', 'at_dossier_nonce' );
	$fields = array(
		'_at_rank'      => 'Звание',
		'_at_unit'      => 'Подразделение',
		'_at_sort_name' => 'Имя для алфавита',
	);
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		echo '<input type="text" class="widefat" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '"></p>';
	}
	echo '<p class="description">Для списка бойцов укажите фамилию и имя в поле алфавита, например «Дяченко Сергей». Звание тогда не собьёт букву.</p>';
}

function at_save_dossier( $post_id ) {
	if ( ! isset( $_POST['at_dossier_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_dossier_nonce'] ) ), 'at_save_dossier' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( '_at_rank', '_at_unit', '_at_sort_name' ) as $key ) {
		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( $value ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}
add_action( 'save_post_post', 'at_save_dossier' );

/**
 * Album meta for heroes.
 */
function at_add_album_meta_box() {
	add_meta_box( 'at_album', 'Альбом героя', 'at_render_album_meta_box', 'post', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'at_add_album_meta_box' );

function at_render_album_meta_box( $post ) {
	wp_nonce_field( 'at_save_album', 'at_album_nonce' );
	$ids = get_post_meta( $post->ID, '_at_album', true );
	$ids = is_array( $ids ) ? $ids : ( $ids ? array_map( 'intval', explode( ',', $ids ) ) : array() );
	echo '<p class="description">Выберите изображения и видео для альбома героя.</p>';
	echo '<input type="hidden" id="at_album_ids" name="at_album_ids" value="' . esc_attr( implode( ',', $ids ) ) . '">';
	echo '<button type="button" class="button" id="at_album_select">Выбрать медиа</button>';
	echo '<div id="at_album_preview" style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;">';
	foreach ( $ids as $id ) {
		if ( ! $id ) continue;
		$url = wp_get_attachment_image_url( $id, 'thumbnail' );
		echo $url ? '<img src="' . esc_url( $url ) . '" style="width:80px;height:80px;object-fit:cover;border-radius:4px;" alt="">' : '<span style="padding:20px;background:#eee;border-radius:4px;">Видео</span>';
	}
	echo '</div>';
	?>
	<script>
	jQuery(function($){
		var frame;
		$('#at_album_select').on('click', function(e){
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({ title: 'Альбом героя', button: { text: 'Добавить' }, multiple: true, library: { type: ['image','video'] } });
			frame.on('select', function(){
				var sel = frame.state().get('selection'), ids = [], prev = $('#at_album_preview').empty();
				sel.each(function(att){
					ids.push(att.id);
					var url = (att.attributes.sizes && att.attributes.sizes.thumbnail) ? att.attributes.sizes.thumbnail.url : att.attributes.url;
					prev.append(att.attributes.type === 'image' && url ? '<img src="'+url+'" style="width:80px;height:80px;object-fit:cover;border-radius:4px;" alt="">' : '<span style="padding:20px;background:#eee;border-radius:4px;">Видео</span>');
				});
				$('#at_album_ids').val(ids.join(','));
			});
			frame.open();
		});
	});
	</script>
	<?php
}

function at_save_album( $post_id ) {
	if ( ! isset( $_POST['at_album_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_album_nonce'] ) ), 'at_save_album' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	$raw = isset( $_POST['at_album_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['at_album_ids'] ) ) : '';
	$ids = array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	$ids ? update_post_meta( $post_id, '_at_album', $ids ) : delete_post_meta( $post_id, '_at_album' );
}
add_action( 'save_post_post', 'at_save_album' );

/**
 * Pin to top block on homepage.
 */
function at_add_pin_meta_box() {
	add_meta_box(
		'at_pin',
		'Закрепить на главной',
		'at_render_pin_meta_box',
		'post',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'at_add_pin_meta_box' );

function at_render_pin_meta_box( $post ) {
	wp_nonce_field( 'at_save_pin', 'at_pin_nonce' );
	$pinned = (bool) get_post_meta( $post->ID, '_at_pin_top', true );
	?>
	<label>
		<input type="checkbox" name="at_pin_top" value="1" <?php checked( $pinned ); ?>>
		Закрепить в верхнем блоке главной
	</label>
	<p class="description">Эта запись станет главным материалом в шапке. Если закреплено несколько — берётся самая свежая.</p>
	<?php
}

function at_save_pin( $post_id ) {
	if ( ! isset( $_POST['at_pin_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_pin_nonce'] ) ), 'at_save_pin' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['at_pin_top'] ) && '1' === $_POST['at_pin_top'] ) {
		update_post_meta( $post_id, '_at_pin_top', '1' );
	} else {
		delete_post_meta( $post_id, '_at_pin_top' );
	}
}
add_action( 'save_post_post', 'at_save_pin' );
