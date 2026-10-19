<?php
/**
 * Фонд инициатив: записи, которые редактор заводит в админке.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Статусы, которые можно выбрать в карточке.
 *
 * @return array<string,string>
 */
function at_initiative_statuses() {
	return array(
		'open'    => 'Открыта',
		'review'  => 'На рассмотрении',
		'adopted' => 'Принята',
		'archive' => 'В архиве',
	);
}

/**
 * @param string $status Stored key.
 * @return string
 */
function at_initiative_status_label( $status ) {
	$statuses = at_initiative_statuses();
	return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $statuses['open'];
}

/**
 * @param int $number Position, starting at 1.
 * @return string
 */
function at_initiative_no( $number ) {
	return str_pad( (string) max( 1, (int) $number ), 2, '0', STR_PAD_LEFT );
}

function at_register_initiatives() {
	register_post_type(
		'at_initiative',
		array(
			'labels'              => array(
				'name'          => 'Фонд инициатив',
				'singular_name' => 'Инициатива',
				'add_new_item'  => 'Добавить инициативу',
				'edit_item'     => 'Редактировать инициативу',
				'menu_name'     => 'Фонд инициатив',
				'all_items'     => 'Все инициативы',
			),
			'public'              => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'menu_icon'           => 'dashicons-megaphone',
			'menu_position'       => 7,
			'rewrite'             => array( 'slug' => 'iniciativa' ),
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'exclude_from_search' => false,
		)
	);
}
add_action( 'init', 'at_register_initiatives' );

function at_initiative_meta_box() {
	add_meta_box(
		'at_initiative',
		'Карточка фонда',
		'at_render_initiative_meta_box',
		'at_initiative',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'at_initiative_meta_box' );

/**
 * @param WP_Post $post Initiative.
 */
function at_render_initiative_meta_box( $post ) {
	wp_nonce_field( 'at_save_initiative', 'at_initiative_nonce' );
	$status = get_post_meta( $post->ID, '_at_init_status', true );
	$place  = get_post_meta( $post->ID, '_at_init_place', true );
	$home   = get_post_meta( $post->ID, '_at_init_home', true );
	if ( ! $status ) {
		$status = 'open';
	}
	?>
	<p>
		<label for="at_init_status"><strong>Статус</strong></label><br>
		<select id="at_init_status" name="at_init_status" class="widefat">
			<?php foreach ( at_initiative_statuses() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label for="at_init_place"><strong>Место</strong></label><br>
		<input type="text" class="widefat" id="at_init_place" name="at_init_place" value="<?php echo esc_attr( $place ); ?>" placeholder="Лубянская площадь">
	</p>
	<p>
		<label>
			<input type="checkbox" name="at_init_home" value="1" <?php checked( '1', (string) $home ); ?>>
			Показывать на главной
		</label>
	</p>
	<p class="description">Заголовок, анонс и текст — в редакторе. Порядок на странице фонда задаётся полем «Порядок» в атрибутах страницы.</p>
	<?php
}

/**
 * @param int $post_id Post ID.
 */
function at_save_initiative_meta( $post_id ) {
	if ( ! isset( $_POST['at_initiative_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_initiative_nonce'] ) ), 'at_save_initiative' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$statuses = at_initiative_statuses();
	$status   = isset( $_POST['at_init_status'] ) ? sanitize_key( wp_unslash( $_POST['at_init_status'] ) ) : 'open';
	if ( ! isset( $statuses[ $status ] ) ) {
		$status = 'open';
	}
	update_post_meta( $post_id, '_at_init_status', $status );

	$place = isset( $_POST['at_init_place'] ) ? sanitize_text_field( wp_unslash( $_POST['at_init_place'] ) ) : '';
	if ( $place ) {
		update_post_meta( $post_id, '_at_init_place', $place );
	} else {
		delete_post_meta( $post_id, '_at_init_place' );
	}
	update_post_meta( $post_id, '_at_init_home', empty( $_POST['at_init_home'] ) ? '0' : '1' );
}
add_action( 'save_post_at_initiative', 'at_save_initiative_meta' );

/**
 * @param array<string,mixed> $args Query overrides.
 * @return WP_Post[]
 */
function at_initiatives( $args = array() ) {
	$query = new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'              => 'at_initiative',
				'post_status'            => 'publish',
				'posts_per_page'         => 24,
				'orderby'                => array(
					'menu_order' => 'ASC',
					'date'       => 'ASC',
				),
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
			)
		)
	);
	$posts = $query->posts;
	wp_reset_postdata();
	return $posts;
}

/**
 * Initiatives marked for the homepage. Falls back to the first published ones.
 *
 * @return WP_Post[]
 */
function at_home_initiatives() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$marked = at_initiatives(
		array(
			'posts_per_page' => 4,
			'meta_query'     => array(
				array(
					'key'   => '_at_init_home',
					'value' => '1',
				),
			),
		)
	);
	$cache = $marked ? $marked : at_initiatives( array( 'posts_per_page' => 3 ) );
	return $cache;
}

/**
 * @return int
 */
function at_initiative_lead_id() {
	$items = at_home_initiatives();
	return $items ? (int) $items[0]->ID : 0;
}

/**
 * @param int $post_id Initiative ID.
 * @return array{prev:?WP_Post,next:?WP_Post}
 */
function at_initiative_neighbors( $post_id ) {
	$items = at_initiatives( array( 'posts_per_page' => 50 ) );
	$prev  = null;
	$next  = null;
	foreach ( $items as $index => $item ) {
		if ( (int) $item->ID !== (int) $post_id ) {
			continue;
		}
		if ( $index > 0 ) {
			$prev = $items[ $index - 1 ];
		}
		if ( isset( $items[ $index + 1 ] ) ) {
			$next = $items[ $index + 1 ];
		}
		break;
	}
	return array(
		'prev' => $prev,
		'next' => $next,
	);
}

/**
 * @param string[] $columns Columns.
 * @return string[]
 */
function at_initiative_columns( $columns ) {
	$columns['at_status'] = 'Статус';
	$columns['at_place']  = 'Место';
	$columns['at_home']   = 'На главной';
	return $columns;
}
add_filter( 'manage_at_initiative_posts_columns', 'at_initiative_columns' );

/**
 * @param string $column Column key.
 * @param int    $post_id Post ID.
 */
function at_initiative_column( $column, $post_id ) {
	if ( 'at_status' === $column ) {
		echo esc_html( at_initiative_status_label( (string) get_post_meta( $post_id, '_at_init_status', true ) ) );
	}
	if ( 'at_place' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, '_at_init_place', true ) );
	}
	if ( 'at_home' === $column ) {
		echo '1' === (string) get_post_meta( $post_id, '_at_init_home', true ) ? 'Да' : '—';
	}
}
add_action( 'manage_at_initiative_posts_custom_column', 'at_initiative_column', 10, 2 );

/**
 * First initiative and the fund page. Existing edits are left alone.
 */
function at_ensure_initiatives() {
	if ( get_option( 'at_initiatives_ready' ) ) {
		return;
	}

	$page = get_page_by_path( 'fond-iniciativ' );
	if ( $page ) {
		$page_id = $page->ID;
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Фонд инициатив',
				'post_name'    => 'fond-iniciativ',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_excerpt' => 'Предложения журнала, которые можно обсуждать и дополнять. Первое — вернуть памятник Феликсу Дзержинскому на Лубянскую площадь.',
			)
		);
	}
	if ( ! $page_id || is_wp_error( $page_id ) ) {
		return;
	}
	update_post_meta( $page_id, '_wp_page_template', 'page-templates/template-initiatives.php' );

	$locations = get_nav_menu_locations();
	$done      = array();
	foreach ( array( 'primary', 'footer' ) as $location ) {
		if ( empty( $locations[ $location ] ) ) {
			continue;
		}
		$menu_id = (int) $locations[ $location ];
		if ( isset( $done[ $menu_id ] ) || at_menu_has_page( $menu_id, $page_id ) ) {
			$done[ $menu_id ] = true;
			continue;
		}
		$done[ $menu_id ] = true;
		wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => 'Фонд инициатив',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}

	$existing = get_page_by_path( 'pamyatnik-dzerzhinskomu', OBJECT, 'at_initiative' );
	if ( ! $existing ) {
		$body = '<p>Памятник Феликсу Эдмундовичу Дзержинскому работы скульптора Евгения Вучетича открыли на Лубянской площади 11 декабря 1958 года. Больше тридцати лет бронзовая фигура держала ось площади перед зданием органов государственной безопасности.</p>';
		$body .= '<p>В августе 1991 года монумент сняли. Площадь с тех пор меняла имя и рисунок мощения, а сам памятник стоит в парке «Музеон». У Лубянки не осталось вертикали, по которой её узнавали несколько поколений.</p>';
		$body .= '<p>Фонд предлагает вернуть памятник на историческое место. Это не вопрос переименования улиц и не пересмотр службы, которая работает в доме на площади. Это вопрос цельного облика Лубянки: фигура основателя ВЧК снова должна стоять перед зданием, с которым связана его работа.</p>';
		$body .= '<p>Инициатива открыта для уточнения. Городу предстоит решить точную точку установки, состояние бронзы и порядок согласований. Журнал публикует текст, чтобы это обсуждение было предметным, а не уличным.</p>';
		$id   = wp_insert_post(
			array(
				'post_type'    => 'at_initiative',
				'post_status'  => 'publish',
				'post_title'   => 'Вернуть памятник Феликсу Дзержинскому на Лубянскую площадь',
				'post_name'    => 'pamyatnik-dzerzhinskomu',
				'post_excerpt' => 'Монумент Вучетича стоял на площади с 1958 года и был снят в августе 1991-го. Фонд предлагает вернуть его на историческое место.',
				'post_content' => $body,
				'menu_order'   => 1,
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			return;
		}
		update_post_meta( $id, '_at_init_status', 'open' );
		update_post_meta( $id, '_at_init_place', 'Лубянская площадь, Москва' );
		update_post_meta( $id, '_at_init_home', '1' );
	}

	if ( ! get_option( 'at_initiatives_rewrite' ) ) {
		flush_rewrite_rules( false );
		update_option( 'at_initiatives_rewrite', 1 );
	}
	update_option( 'at_initiatives_ready', 1 );
}
add_action( 'init', 'at_ensure_initiatives', 20 );
