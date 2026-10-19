<?php
/**
 * Антитеррор — тема журнала.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/meta.php';
require get_template_directory() . '/inc/seo.php';
require get_template_directory() . '/inc/milestones.php';
require get_template_directory() . '/inc/initiatives.php';
require get_template_directory() . '/inc/interact.php';
require get_template_directory() . '/inc/polish.php';

function at_setup() {
	load_theme_textdomain( 'antiterror-press', get_template_directory() . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 88, 'width' => 88, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'at-portrait', 800, 1060, false );
	add_image_size( 'at-card', 960, 600, true );
	add_image_size( 'at-thumb', 480, 360, true );

	register_nav_menus(
		array(
			'primary' => 'Главное меню',
			'footer'  => 'Меню в подвале',
		)
	);
}
add_action( 'after_setup_theme', 'at_setup' );

function at_assets() {
	wp_enqueue_style(
		'at-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Manrope:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	$css_path = get_template_directory() . '/assets/css/theme.css';
	$js_path  = get_template_directory() . '/assets/js/theme.js';
	wp_enqueue_style( 'at-theme', get_template_directory_uri() . '/assets/css/theme.css', array( 'at-fonts' ), filemtime( $css_path ) );
	$theme_deps = array();
	if ( is_front_page() ) {
		// no map deps
	}
	wp_enqueue_script( 'at-theme', get_template_directory_uri() . '/assets/js/theme.js', $theme_deps, filemtime( $js_path ), true );
	$motion_css   = get_template_directory() . '/assets/css/motion.css';
	$motion_js    = get_template_directory() . '/assets/js/motion.js';
	$interact_css = get_template_directory() . '/assets/css/interact.css';
	$interact_js  = get_template_directory() . '/assets/js/interact.js';
	wp_enqueue_style( 'at-motion', get_template_directory_uri() . '/assets/css/motion.css', array( 'at-theme' ), filemtime( $motion_css ) );
	wp_enqueue_script( 'at-motion', get_template_directory_uri() . '/assets/js/motion.js', array(), filemtime( $motion_js ), true );
	wp_enqueue_style( 'at-interact', get_template_directory_uri() . '/assets/css/interact.css', array( 'at-motion' ), filemtime( $interact_css ) );
	wp_enqueue_script( 'at-interact', get_template_directory_uri() . '/assets/js/interact.js', array( 'at-motion' ), filemtime( $interact_js ), true );
	wp_localize_script(
		'at-interact',
		'atInteract',
		array(
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'search'  => esc_url_raw( rest_url( 'at/v1/search' ) ),
			'feed'    => esc_url_raw( rest_url( 'at/v1/feed' ) ),
			'contact' => esc_url_raw( rest_url( 'at/v1/contact' ) ),
			'home'    => esc_url_raw( home_url( '/' ) ),
		)
	);
	if ( is_singular( 'post' ) && ! is_preview() ) {
		wp_localize_script(
			'at-theme',
			'atView',
			array(
				'endpoint' => esc_url_raw( rest_url( 'at/v1/view' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
				'id'       => get_queried_object_id(),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'at_assets' );

function at_async_fonts( $html, $handle ) {
	if ( 'at-fonts' !== $handle ) {
		return $html;
	}
	$html = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $html );
	return str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $html );
}
add_filter( 'style_loader_tag', 'at_async_fonts', 10, 2 );

function at_resource_hints( $urls, $relation ) {
	if ( 'dns-prefetch' === $relation ) {
		$urls = array_values(
			array_filter(
				$urls,
				static function ( $url ) {
					$href = is_array( $url ) ? ( $url['href'] ?? '' ) : (string) $url;
					return false === strpos( $href, 's.w.org' );
				}
			)
		);
	}
	if ( 'preconnect' === $relation ) {
		$urls[] = array(
			'href' => 'https://fonts.googleapis.com',
		);
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'at_resource_hints', 10, 2 );

function at_trim_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
}
add_action( 'init', 'at_trim_head' );

function at_trim_assets() {
	wp_dequeue_script( 'wp-embed' );
	wp_deregister_script( 'wp-embed' );
	if ( ! is_singular() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'at_trim_assets', 100 );

function at_image_sizes( $sizes, $size ) {
	$name = is_array( $size ) ? '' : (string) $size;
	if ( 'at-thumb' === $name ) {
		return '(max-width: 680px) 100vw, 220px';
	}
	if ( 'at-card' === $name ) {
		return '(max-width: 680px) 100vw, (max-width: 980px) 50vw, 360px';
	}
	if ( 'at-portrait' === $name ) {
		return '(max-width: 680px) 92vw, 640px';
	}
	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'at_image_sizes', 10, 2 );

function at_speculation_rules() {
	if ( is_admin() || is_customize_preview() ) {
		return;
	}
	$rules = array(
		'prefetch' => array(
			array(
				'where'     => array(
					'and' => array(
						array( 'href_matches' => '/*' ),
						array( 'not' => array( 'href_matches' => '/wp-admin/*' ) ),
						array( 'not' => array( 'href_matches' => '/wp-login.php*' ) ),
						array( 'not' => array( 'href_matches' => '/*\\?*(^|&)s=*' ) ),
					),
				),
				'eagerness' => 'conservative',
			),
		),
	);
	echo '<script type="speculationrules">' . wp_json_encode( $rules ) . '</script>' . "\n";
}
add_action( 'wp_head', 'at_speculation_rules', 2 );

function at_register_view_route() {
	register_rest_route(
		'at/v1',
		'/view',
		array(
			'methods'             => 'POST',
			'permission_callback' => 'at_rest_nonce_permission',
			'callback'            => 'at_count_view',
			'args'                => array(
				'id' => array(
					'type'     => 'integer',
					'required' => true,
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'at_register_view_route' );

function at_count_view( $request ) {
	$post_id = (int) $request->get_param( 'id' );
	$post    = get_post( $post_id );
	if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status ) {
		return new WP_REST_Response( array( 'ok' => false ), 404 );
	}
	if ( is_user_logged_in() || at_view_is_limited() ) {
		return array( 'ok' => true, 'counted' => false );
	}
	$cookie = 'at_seen_' . $post_id;
	if ( isset( $_COOKIE[ $cookie ] ) ) {
		return array( 'ok' => true, 'counted' => false );
	}
	$count = (int) get_post_meta( $post_id, '_at_views', true );
	update_post_meta( $post_id, '_at_views', $count + 1 );
	setcookie( $cookie, '1', time() + DAY_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true );
	return array( 'ok' => true, 'counted' => true );
}

function at_widgets() {
	register_sidebar(
		array(
			'name'          => 'Колонка записи',
			'id'            => 'sidebar-1',
			'before_widget' => '<section class="widget rail__block">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2>',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'at_widgets' );

function at_customize( $wp_customize ) {
	$wp_customize->add_section(
		'at_journal',
		array(
			'title'    => 'Журнал',
			'priority' => 30,
		)
	);
	$wp_customize->add_setting(
		'at_tagline_short',
		array(
			'default'           => 'Борьба с терроризмом и экстремизмом. За межнациональную дружбу.',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'at_tagline_short',
		array(
			'label'   => 'Короткая строка в шапке',
			'section' => 'at_journal',
			'type'    => 'text',
		)
	);
	$wp_customize->add_setting(
		'at_vk',
		array(
			'default'           => 'https://vk.com/antiterror_rossii',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'at_vk',
		array(
			'label'   => 'ВКонтакте',
			'section' => 'at_journal',
			'type'    => 'url',
		)
	);
	$wp_customize->add_setting(
		'at_tg',
		array(
			'default'           => 'https://t.me/terrorismunet',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'at_tg',
		array(
			'label'   => 'Telegram',
			'section' => 'at_journal',
			'type'    => 'url',
		)
	);
	$wp_customize->add_setting(
		'at_yandex_maps_key',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_setting(
		'at_home_milestones',
		array(
			'default'           => false,
			'sanitize_callback' => 'wp_validate_boolean',
		)
	);
	$wp_customize->add_control(
		'at_home_milestones',
		array(
			'label'   => 'Показывать блок «Вехи истории» на главной',
			'section' => 'at_journal',
			'type'    => 'checkbox',
		)
	);
	$wp_customize->add_control(
		'at_yandex_maps_key',
		array(
			'label'       => 'API-ключ Яндекс.Карт',
			'description' => 'Для отображения карты. Получить: developer.tech.yandex.ru → JavaScript API и HTTP Геокодер',
			'section'     => 'at_journal',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'at_customize' );

function at_seed_pages() {
	if ( get_option( 'at_pages_seeded' ) ) {
		return;
	}
	$pages = array(
		'geroi-antiterrora' => array( 'Герои Антитеррора', 'page-templates/template-fighters.php' ),
		'zhurnal'           => array( 'Журнал', 'page-templates/template-journal.php' ),
		'arhiv'             => array( 'Архив фото и видео', 'page-templates/template-media.php' ),
		'geroi-sredi-nas'   => array( 'Герои среди нас', 'page-templates/template-contest.php' ),
		'contacts'          => array( 'Написать нам', 'page-templates/template-contact.php' ),
		'sitemap'           => array( 'Карта сайта', 'page-templates/template-sitemap.php' ),
	);
	$ids = array();
	foreach ( $pages as $slug => $data ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			if ( ! get_page_template_slug( $existing->ID ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', $data[1] );
			}
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_title'  => $data[0],
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_wp_page_template', $data[1] );
			$ids[ $slug ] = $id;
		}
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	if ( empty( $locations['primary'] ) ) {
		$menu_id = wp_create_nav_menu( 'Журнал' );
		if ( ! is_wp_error( $menu_id ) ) {
			$order = array( 'geroi-antiterrora', 'zhurnal', 'arhiv', 'geroi-sredi-nas', 'contacts' );
			$pos   = 1;
			foreach ( $order as $slug ) {
				if ( empty( $ids[ $slug ] ) ) {
					continue;
				}
				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $ids[ $slug ] ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $ids[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
						'menu-item-position'  => $pos,
					)
				);
				$pos++;
			}
			$locations['primary'] = $menu_id;
			if ( empty( $locations['footer'] ) ) {
				$locations['footer'] = $menu_id;
			}
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}
	update_option( 'at_pages_seeded', 1 );
}
add_action( 'after_switch_theme', 'at_seed_pages' );

function at_menu_has_page( $menu_id, $page_id ) {
	$items = wp_get_nav_menu_items( $menu_id );
	if ( ! $items ) {
		return false;
	}
	foreach ( $items as $item ) {
		if ( 'page' === $item->object && (int) $item->object_id === (int) $page_id ) {
			return true;
		}
	}
	return false;
}

function at_ensure_archive_page() {
	if ( get_option( 'at_archive_ready' ) ) {
		return;
	}
	$page = get_page_by_path( 'arhiv' );
	if ( $page ) {
		$page_id = $page->ID;
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'  => 'Архив фото и видео',
				'post_name'   => 'arhiv',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);
	}
	if ( ! $page_id || is_wp_error( $page_id ) ) {
		return;
	}
	update_post_meta( $page_id, '_wp_page_template', 'page-templates/template-media.php' );

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
				'menu-item-title'     => 'Архив',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
	}
	update_option( 'at_archive_ready', 1 );
}
add_action( 'init', 'at_ensure_archive_page' );

function at_handle_contact() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	if ( ! isset( $_POST['at_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_contact_nonce'] ) ), 'at_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'at_sent', '0', $redirect ) );
		exit;
	}
	$result = at_contact_send(
		isset( $_POST['at_name'] ) ? wp_unslash( $_POST['at_name'] ) : '',
		isset( $_POST['at_email'] ) ? wp_unslash( $_POST['at_email'] ) : '',
		isset( $_POST['at_message'] ) ? wp_unslash( $_POST['at_message'] ) : '',
		isset( $_POST['at_topic'] ) ? wp_unslash( $_POST['at_topic'] ) : '',
		! empty( $_POST['at_consent'] ),
		isset( $_POST['at_company'] ) ? wp_unslash( $_POST['at_company'] ) : ''
	);
	wp_safe_redirect( add_query_arg( 'at_sent', true === $result ? '1' : '0', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_at_contact', 'at_handle_contact' );
add_action( 'admin_post_at_contact', 'at_handle_contact' );

function at_news_main_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() ) {
		return;
	}
	$ids = at_staff_term_ids();
	if ( $ids ) {
		$query->set( 'category__not_in', $ids );
		$query->set( 'ignore_sticky_posts', true );
	}
}
add_action( 'pre_get_posts', 'at_news_main_query' );

function at_excerpt_length( $length ) {
	return 28;
}
add_filter( 'excerpt_length', 'at_excerpt_length' );

function at_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'at_excerpt_more' );

function at_archive_title( $title ) {
	if ( is_category() ) {
		return single_cat_title( '', false );
	}
	if ( is_tag() ) {
		return single_tag_title( '', false );
	}
	if ( is_author() ) {
		return get_the_author();
	}
	if ( is_post_type_archive() ) {
		return post_type_archive_title( '', false );
	}
	return $title;
}
add_filter( 'get_the_archive_title', 'at_archive_title' );

/**
 * Custom Post Type: События БПЛА
 */
function at_register_bpla_cpt() {
	$labels = array(
		'name'               => 'События БПЛА',
		'singular_name'      => 'Событие БПЛА',
		'menu_name'          => 'БПЛА',
		'add_new'            => 'Добавить событие',
		'add_new_item'       => 'Добавить событие БПЛА',
		'edit_item'          => 'Редактировать событие',
		'new_item'           => 'Новое событие',
		'view_item'          => 'Просмотреть',
		'search_items'       => 'Искать события',
		'not_found'          => 'Событий не найдено',
		'not_found_in_trash' => 'В корзине пусто',
	);
	register_post_type(
		'bpla_event',
		array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'bpla' ),
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
			'menu_icon'           => 'dashicons-airplane',
			'show_in_rest'        => true,
			'menu_position'       => 5,
		)
	);
}
add_action( 'init', 'at_register_bpla_cpt' );

/**
 * Meta box for BPLA events: coordinates, status, type.
 */
function at_bpla_meta_boxes() {
	add_meta_box(
		'at_bpla_details',
		'Детали события БПЛА',
		'at_bpla_meta_box_render',
		'bpla_event',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'at_bpla_meta_boxes' );

function at_bpla_meta_box_render( $post ) {
	wp_nonce_field( 'at_bpla_save', 'at_bpla_nonce' );
	$lat    = get_post_meta( $post->ID, '_bpla_lat', true );
	$lng    = get_post_meta( $post->ID, '_bpla_lng', true );
	$status = get_post_meta( $post->ID, '_bpla_status', true );
	$type   = get_post_meta( $post->ID, '_bpla_type', true );
	$time   = get_post_meta( $post->ID, '_bpla_time', true );
	$region = get_post_meta( $post->ID, '_bpla_region', true );
	?>
	<p>
		<label>Широта (lat): <input type="text" name="bpla_lat" value="<?php echo esc_attr( $lat ); ?>" style="width:120px" placeholder="55.7558"></label>
		&nbsp;&nbsp;
		<label>Долгота (lng): <input type="text" name="bpla_lng" value="<?php echo esc_attr( $lng ); ?>" style="width:120px" placeholder="37.6173"></label>
	</p>
	<p>
		<label>Регион / город: <input type="text" name="bpla_region" value="<?php echo esc_attr( $region ); ?>" style="width:240px" placeholder="Московская область"></label>
	</p>
	<p>
		<label>Статус:
			<select name="bpla_status">
				<option value="" <?php selected( $status, '' ); ?>>—</option>
				<option value="in_flight" <?php selected( $status, 'in_flight' ); ?>>В полёте</option>
				<option value="intercepted" <?php selected( $status, 'intercepted' ); ?>>Перехвачен</option>
				<option value="destroyed" <?php selected( $status, 'destroyed' ); ?>>Уничтожен</option>
				<option value="attack" <?php selected( $status, 'attack' ); ?>>Атака</option>
				<option value="reconnaissance" <?php selected( $status, 'reconnaissance' ); ?>>Разведка</option>
				<option value="alert" <?php selected( $status, 'alert' ); ?>>Тревога</option>
			</select>
		</label>
	</p>
	<p>
		<label>Тип БПЛА: <input type="text" name="bpla_type" value="<?php echo esc_attr( $type ); ?>" style="width:240px" placeholder="FPV, Lancet, Geran..."></label>
	</p>
	<p>
		<label>Время события: <input type="datetime-local" name="bpla_time" value="<?php echo esc_attr( $time ); ?>"></label>
	</p>
	<p class="description">Координаты обязательны для отображения на карте. Используйте десятичный формат.</p>
	<?php
}

function at_bpla_save_meta( $post_id ) {
	if ( ! isset( $_POST['at_bpla_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_bpla_nonce'] ) ), 'at_bpla_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$fields = array( 'bpla_lat', 'bpla_lng', 'bpla_status', 'bpla_type', 'bpla_time', 'bpla_region' );
	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			update_post_meta( $post_id, '_' . $field, $value );
		}
	}
}
add_action( 'save_post_bpla_event', 'at_bpla_save_meta' );

/**
 * Enqueue Leaflet on front page for BPLA map.
 */
// Yandex Maps removed — block is now a pure event feed.


/**
 * Approximate centers of regions frequently mentioned in MoD reports.
 */
function at_bpla_region_coords() {
	return array(
		'Белгородская область'   => array( 50.595, 36.587 ),
		'Брянская область'       => array( 53.252, 34.371 ),
		'Курская область'        => array( 51.737, 36.187 ),
		'Воронежская область'    => array( 51.672, 39.184 ),
		'Орловская область'      => array( 52.970, 36.064 ),
		'Тульская область'       => array( 54.204, 37.619 ),
		'Калужская область'      => array( 54.529, 36.275 ),
		'Липецкая область'       => array( 52.608, 39.599 ),
		'Тамбовская область'     => array( 52.721, 41.452 ),
		'Рязанская область'      => array( 54.630, 39.742 ),
		'Смоленская область'     => array( 54.782, 32.045 ),
		'Тверская область'       => array( 56.858, 35.917 ),
		'Московская область'     => array( 55.504, 37.577 ),
		'Москва'                 => array( 55.755, 37.617 ),
		'Московский регион'      => array( 55.755, 37.617 ),
		'Ростовская область'     => array( 47.236, 39.714 ),
		'Краснодарский край'     => array( 45.035, 38.975 ),
		'Республика Крым'        => array( 44.952, 34.102 ),
		'Крым'                   => array( 44.952, 34.102 ),
		'Севастополь'            => array( 44.616, 33.525 ),
		'Волгоградская область'  => array( 48.708, 44.513 ),
		'Саратовская область'    => array( 51.533, 46.034 ),
		'Самарская область'      => array( 53.195, 50.101 ),
		'Пензенская область'     => array( 53.200, 45.000 ),
		'Нижегородская область'  => array( 56.327, 44.002 ),
		'Владимирская область'   => array( 56.129, 40.407 ),
		'Ярославская область'    => array( 57.626, 39.894 ),
		'Ленинградская область'  => array( 59.939, 30.316 ),
		'Санкт-Петербург'        => array( 59.939, 30.316 ),
		'Астраханская область'   => array( 46.349, 48.040 ),
		'Черное море'            => array( 44.000, 34.000 ),
		'Азовское море'          => array( 46.000, 37.000 ),
		'Каспийское море'        => array( 42.000, 50.000 ),
	);
}

/**
 * Parse recent MoD Telegram summaries and create BPLA events.
 */
function at_parse_mod_bpla() {
	$url = 'https://t.me/s/mod_russia';
	$response = wp_remote_get( $url, array(
		'timeout' => 20,
		'user-agent' => 'Mozilla/5.0 (compatible; AntiterrorPress/1.0)',
	) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return;
	}
	$html = wp_remote_retrieve_body( $response );
	if ( empty( $html ) ) {
		return;
	}

	libxml_use_internal_errors( true );
	$dom = new DOMDocument();
	@$dom->loadHTML( '<?xml encoding="UTF-8">' . $html );
	$xpath = new DOMXPath( $dom );
	$nodes = $xpath->query( '//div[contains(@class,"tgme_widget_message_text")]' );

	$regions = at_bpla_region_coords();
	$created = 0;

	foreach ( $nodes as $node ) {
		$text = trim( $node->textContent );
		if ( ! $text || ( false === stripos( $text, 'БПЛА' ) && false === stripos( $text, 'беспилотн' ) ) ) {
			continue;
		}
		if ( false === stripos( $text, 'перехвачен' ) && false === stripos( $text, 'уничтожен' ) && false === stripos( $text, 'сбит' ) ) {
			continue;
		}

		// Extract number
		$num = 0;
		if ( preg_match( '/(\d+)\s*(?:украинских\s+)?(?:беспилотных|БПЛА)/ui', $text, $m ) ) {
			$num = (int) $m[1];
		}

		// Extract regions after "над территориями" or similar
		$region_list = array();
		if ( preg_match( '/над\s+(?:территориями\s+)?(.+?)(?:\.|$)/ui', $text, $m ) ) {
			$raw = $m[1];
			$parts = preg_split( '/[,;]|и\s+/u', $raw );
			foreach ( $parts as $part ) {
				$part = trim( preg_replace( '/\s+/u', ' ', $part ) );
				$part = preg_replace( '/^(областей|области|края|республики)\s+/ui', '', $part );
				if ( $part && mb_strlen( $part ) > 3 ) {
					$region_list[] = $part;
				}
			}
		}

		// Create a unique key for this summary
		$hash = md5( substr( $text, 0, 200 ) );
		$existing = get_posts( array(
			'post_type'      => 'bpla_event',
			'meta_key'       => '_bpla_source_hash',
			'meta_value'     => $hash,
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( $existing ) {
			continue;
		}

		$title = $num ? sprintf( 'Сводка МО: уничтожено %d БПЛА', $num ) : 'Сводка МО по БПЛА';
		$post_id = wp_insert_post( array(
			'post_type'    => 'bpla_event',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_content' => wp_kses_post( nl2br( esc_html( $text ) ) ),
			'post_excerpt' => wp_trim_words( $text, 30 ),
		) );
		if ( ! $post_id || is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, '_bpla_source_hash', $hash );
		update_post_meta( $post_id, '_bpla_status', 'destroyed' );
		update_post_meta( $post_id, '_bpla_type', 'самолётного типа (сводка МО)' );
		update_post_meta( $post_id, '_bpla_time', current_time( 'Y-m-d\TH:i' ) );
		update_post_meta( $post_id, '_bpla_count', $num );

		// Attach first matching region coordinates (or multiple later)
		$lat = $lng = null;
		$matched_region = '';
		foreach ( $region_list as $rname ) {
			foreach ( $regions as $key => $coords ) {
				if ( false !== stripos( $rname, $key ) || false !== stripos( $key, $rname ) ) {
					$lat = $coords[0];
					$lng = $coords[1];
					$matched_region = $key;
					break 2;
				}
			}
		}
		if ( $lat && $lng ) {
			update_post_meta( $post_id, '_bpla_lat', $lat );
			update_post_meta( $post_id, '_bpla_lng', $lng );
			update_post_meta( $post_id, '_bpla_region', $matched_region );
		} else {
			update_post_meta( $post_id, '_bpla_region', implode( ', ', array_slice( $region_list, 0, 3 ) ) );
		}

		$created++;
		if ( $created >= 5 ) {
			break; // limit per run
		}
	}
}

/**
 * Schedule the parser.
 */
function at_schedule_bpla_parser() {
	if ( ! wp_next_scheduled( 'at_bpla_parse_event' ) ) {
		wp_schedule_event( time(), 'twicedaily', 'at_bpla_parse_event' );
	}
}
add_action( 'init', 'at_schedule_bpla_parser' );
add_action( 'at_bpla_parse_event', 'at_parse_mod_bpla' );

// Manual trigger for testing (admin only)
function at_bpla_manual_parse() {
	if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['at_parse_bpla'] ) ) {
		return;
	}
	$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'at_parse_bpla' ) ) {
		return;
	}
	at_parse_mod_bpla();
	wp_safe_redirect( admin_url( 'edit.php?post_type=bpla_event&at_parsed=1' ) );
	exit;
}
add_action( 'admin_init', 'at_bpla_manual_parse' );

function at_bpla_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! current_user_can( 'manage_options' ) || ! $screen || 'bpla_event' !== $screen->post_type ) {
		return;
	}
	$url = wp_nonce_url( admin_url( 'edit.php?post_type=bpla_event&at_parse_bpla=1' ), 'at_parse_bpla' );
	echo '<div class="notice notice-info"><p>Автопарсинг сводок МО из Telegram @mod_russia запускается 2 раза в сутки. <a href="' . esc_url( $url ) . '">Запустить сейчас</a>.</p></div>';
	if ( isset( $_GET['at_parsed'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Парсинг выполнен.</p></div>';
	}
}
add_action( 'admin_notices', 'at_bpla_admin_notice' );


function at_enqueue_album_media( $hook ) {
	if ( "post.php" !== $hook && "post-new.php" !== $hook ) return;
	wp_enqueue_media();
}
add_action( "admin_enqueue_scripts", "at_enqueue_album_media" );

