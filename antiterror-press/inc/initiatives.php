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

/**
 * @param int $post_id Initiative ID.
 * @return int
 */
function at_initiative_votes( $post_id ) {
	return max( 0, (int) get_post_meta( $post_id, '_at_init_votes', true ) );
}

/**
 * @param int $number Vote count.
 * @return string
 */
function at_vote_word( $number ) {
	$label = at_ru_plural( $number, 'голос', 'голоса', 'голосов' );
	$parts = explode( ' ', $label, 2 );
	return isset( $parts[1] ) ? $parts[1] : 'голосов';
}

/**
 * Voting stays open while the initiative is still being discussed.
 *
 * @param int $post_id Initiative ID.
 * @return bool
 */
function at_initiative_voting_open( $post_id ) {
	$status = (string) get_post_meta( $post_id, '_at_init_status', true );
	if ( ! $status ) {
		$status = 'open';
	}
	return in_array( $status, array( 'open', 'review' ), true );
}

/**
 * @param int $post_id Initiative ID.
 * @return bool
 */
function at_initiative_voted( $post_id ) {
	$key = 'at_init_vote_' . (int) $post_id;
	if ( isset( $_COOKIE[ $key ] ) && '1' === $_COOKIE[ $key ] ) {
		return true;
	}
	if ( is_user_logged_in() ) {
		$voted = get_user_meta( get_current_user_id(), '_at_init_voted', true );
		if ( is_array( $voted ) && in_array( (int) $post_id, array_map( 'intval', $voted ), true ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Photograph for a card. A featured image wins. The first initiative
 * falls back to the CC0 file shipped with the theme.
 *
 * @param int|WP_Post $post Initiative.
 * @return array{url:string,width:int,height:int,alt:string,credit:string}|null
 */
function at_initiative_portrait( $post ) {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return null;
	}
	$thumb = get_post_thumbnail_id( $post );
	if ( $thumb ) {
		$src = wp_get_attachment_image_src( $thumb, 'large' );
		if ( $src ) {
			$alt = (string) get_post_meta( $thumb, '_wp_attachment_image_alt', true );
			return array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => $alt ? $alt : get_the_title( $post ),
				'credit' => (string) wp_get_attachment_caption( $thumb ),
			);
		}
	}
	if ( 'pamyatnik-dzerzhinskomu' !== $post->post_name ) {
		return null;
	}
	$file = get_template_directory() . '/assets/images/dzerzhinsky.jpg';
	if ( ! file_exists( $file ) ) {
		return null;
	}
	return array(
		'url'    => get_template_directory_uri() . '/assets/images/dzerzhinsky.jpg',
		'width'  => 989,
		'height' => 1280,
		'alt'    => 'Памятник Ф. Э. Дзержинскому в парке «Музеон». До 1991 года он стоял на Лубянской площади.',
		'credit' => 'Кирилл (KirillQQ), 2022. CC0.',
	);
}

/**
 * @param int  $post_id Initiative ID.
 * @param bool $compact Shorter control for a list row.
 */
function at_vote_markup( $post_id, $compact = false ) {
	$post_id = (int) $post_id;
	$votes   = at_initiative_votes( $post_id );
	$voted   = at_initiative_voted( $post_id );
	$open    = at_initiative_voting_open( $post_id );
	$label   = $voted ? 'Вы поддержали' : 'Поддержать';
	if ( ! $open ) {
		$label = 'Голосование закрыто';
	}
	echo '<div class="vote' . ( $compact ? ' vote--compact' : '' ) . '" data-vote-root="' . esc_attr( (string) $post_id ) . '">';
	echo '<p class="vote__count"><span data-vote-count>' . esc_html( (string) $votes ) . '</span> <span data-vote-word>' . esc_html( at_vote_word( $votes ) ) . '</span></p>';
	printf(
		'<button class="vote__btn" type="button" data-vote="%1$d" data-label-on="Вы поддержали" data-label-off="Поддержать" aria-pressed="%2$s"%3$s>%4$s</button>',
		$post_id,
		$voted ? 'true' : 'false',
		( ! $open || $voted ) ? ' disabled' : '',
		esc_html( $label )
	);
	echo '</div>';
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
	<p>Голосов сейчас: <strong><?php echo esc_html( (string) at_initiative_votes( $post->ID ) ); ?></strong></p>
	<p>
		<label>
			<input type="checkbox" name="at_init_votes_edit" value="1">
			Заменить число голосов
		</label><br>
		<input type="number" class="small-text" name="at_init_votes" min="0" value="<?php echo esc_attr( (string) at_initiative_votes( $post->ID ) ); ?>">
	</p>
	<p class="description">Заголовок, анонс, текст и снимок — в редакторе. Миниатюра записи заменяет фотографию на карточке. Порядок задаётся полем «Порядок».</p>
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
	if ( ! empty( $_POST['at_init_votes_edit'] ) ) {
		$votes = isset( $_POST['at_init_votes'] ) ? absint( wp_unslash( $_POST['at_init_votes'] ) ) : 0;
		update_post_meta( $post_id, '_at_init_votes', $votes );
	}
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
	$columns['at_votes']  = 'Голоса';
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
	if ( 'at_votes' === $column ) {
		echo esc_html( (string) at_initiative_votes( $post_id ) );
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
		update_post_meta( $id, '_at_init_votes', 0 );
		at_initiative_attach_portrait( $id );
	}

	if ( ! get_option( 'at_initiatives_rewrite' ) ) {
		flush_rewrite_rules( false );
		update_option( 'at_initiatives_rewrite', 1 );
	}
	update_option( 'at_initiatives_ready', 1 );
}
add_action( 'init', 'at_ensure_initiatives', 20 );

/**
 * Copy the bundled CC0 photograph into the media library once.
 *
 * @param int $post_id Initiative ID.
 * @return int Attachment ID, or 0.
 */
function at_initiative_attach_portrait( $post_id ) {
	$post_id = (int) $post_id;
	if ( $post_id < 1 || has_post_thumbnail( $post_id ) ) {
		return (int) get_post_thumbnail_id( $post_id );
	}
	$file = get_template_directory() . '/assets/images/dzerzhinsky.jpg';
	if ( ! file_exists( $file ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( 'dzerzhinsky.jpg' );
	if ( ! $tmp || ! copy( $file, $tmp ) ) {
		return 0;
	}
	$attachment = media_handle_sideload(
		array(
			'name'     => 'dzerzhinsky.jpg',
			'tmp_name' => $tmp,
		),
		$post_id,
		'Памятник Ф. Э. Дзержинскому в парке «Музеон», 2022. Кирилл (KirillQQ), CC0.'
	);
	if ( is_wp_error( $attachment ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		return 0;
	}
	set_post_thumbnail( $post_id, $attachment );
	update_post_meta( $attachment, '_wp_attachment_image_alt', 'Памятник Феликсу Дзержинскому. Парк «Музеон», Москва.' );
	wp_update_post(
		array(
			'ID'           => $attachment,
			'post_excerpt' => 'Кирилл (KirillQQ), 2022. Снимок опубликован на условиях CC0.',
		)
	);
	return (int) $attachment;
}

/**
 * Sites that already had the initiative get the photograph on a later load.
 */
function at_initiative_maybe_portrait() {
	if ( get_option( 'at_initiative_portrait' ) || get_transient( 'at_initiative_portrait_try' ) ) {
		return;
	}
	$post = get_page_by_path( 'pamyatnik-dzerzhinskomu', OBJECT, 'at_initiative' );
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	if ( has_post_thumbnail( $post ) ) {
		update_option( 'at_initiative_portrait', 1 );
		return;
	}
	set_transient( 'at_initiative_portrait_try', 1, HOUR_IN_SECONDS );
	$attachment = at_initiative_attach_portrait( $post->ID );
	if ( $attachment ) {
		update_option( 'at_initiative_portrait', 1 );
		delete_transient( 'at_initiative_portrait_try' );
	}
}
add_action( 'init', 'at_initiative_maybe_portrait', 30 );

/**
 * @param int $post_id Initiative ID.
 * @return int
 */
function at_initiative_add_vote( $post_id ) {
	$key = '_at_init_votes';
	if ( '' === get_post_meta( $post_id, $key, true ) && ! metadata_exists( 'post', $post_id, $key ) ) {
		add_post_meta( $post_id, $key, 1, true );
		return 1;
	}
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s",
			$post_id,
			$key
		)
	);
	wp_cache_delete( $post_id, 'post_meta' );
	return at_initiative_votes( $post_id );
}

/**
 * @return bool
 */
function at_vote_is_limited() {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'at_vote_ip_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 30 ) {
		return true;
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );
	return false;
}

/**
 * @param string $name Cookie name.
 */
function at_vote_cookie( $name ) {
	$args = array(
		'expires'  => time() + YEAR_IN_SECONDS,
		'path'     => ( defined( 'COOKIEPATH' ) && COOKIEPATH ) ? COOKIEPATH : '/',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	);
	if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
		$args['domain'] = COOKIE_DOMAIN;
	}
	setcookie( $name, '1', $args );
	$_COOKIE[ $name ] = '1';
}

function at_register_vote_route() {
	register_rest_route(
		'at/v1',
		'/vote',
		array(
			'methods'             => 'POST',
			'permission_callback' => 'at_rest_nonce_permission',
			'callback'            => 'at_cast_vote',
			'args'                => array(
				'id' => array(
					'type'     => 'integer',
					'required' => true,
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'at_register_vote_route' );

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function at_cast_vote( $request ) {
	$post_id = (int) $request->get_param( 'id' );
	$post    = get_post( $post_id );
	if ( ! $post || 'at_initiative' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_Error( 'at_vote_missing', 'Initiative not found.', array( 'status' => 404 ) );
	}
	if ( ! at_initiative_voting_open( $post_id ) ) {
		return new WP_Error( 'at_vote_closed', 'Voting is closed.', array( 'status' => 403 ) );
	}
	if ( at_initiative_voted( $post_id ) ) {
		return rest_ensure_response(
			array(
				'ok'    => true,
				'voted' => true,
				'votes' => at_initiative_votes( $post_id ),
			)
		);
	}
	if ( at_vote_is_limited() ) {
		return new WP_Error( 'at_vote_limit', 'Too many votes.', array( 'status' => 429 ) );
	}
	$votes = at_initiative_add_vote( $post_id );
	at_vote_cookie( 'at_init_vote_' . $post_id );
	if ( is_user_logged_in() ) {
		$voted   = get_user_meta( get_current_user_id(), '_at_init_voted', true );
		$voted   = is_array( $voted ) ? $voted : array();
		$voted[] = $post_id;
		update_user_meta( get_current_user_id(), '_at_init_voted', array_values( array_unique( array_map( 'intval', $voted ) ) ) );
	}
	return rest_ensure_response(
		array(
			'ok'    => true,
			'voted' => true,
			'votes' => $votes,
		)
	);
}
