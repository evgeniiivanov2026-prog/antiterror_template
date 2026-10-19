<?php
/**
 * Interactive surfaces: live search, AJAX feed, contact REST, TOC, share.
 *
 * Hooks into the existing at_* helpers and design tokens. No third-party JS.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST + script payload for the front-end interactivity layer.
 */
function at_interact_boot() {
	add_action( 'rest_api_init', 'at_register_interact_routes' );
	add_filter( 'the_content', 'at_content_heading_ids', 8 );
}

at_interact_boot();

/**
 * Permission for every interactive REST request: wp_rest nonce required.
 *
 * @param WP_REST_Request $request Request.
 * @return true|WP_Error
 */
function at_rest_can_interact( $request ) {
	$nonce = $request->get_header( 'X-WP-Nonce' );
	if ( ! $nonce ) {
		$nonce = $request->get_param( '_wpnonce' );
	}
	$nonce = is_string( $nonce ) ? sanitize_text_field( wp_unslash( $nonce ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'at_forbidden', 'Invalid nonce.', array( 'status' => 403 ) );
	}
	return true;
}

function at_register_interact_routes() {
	$can = array(
		'permission_callback' => 'at_rest_can_interact',
	);

	register_rest_route(
		'at/v1',
		'/search',
		array_merge(
			$can,
			array(
				'methods'  => 'GET',
				'callback' => 'at_rest_live_search',
				'args'     => array(
					'q' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		)
	);

	register_rest_route(
		'at/v1',
		'/feed',
		array_merge(
			$can,
			array(
				'methods'  => 'GET',
				'callback' => 'at_rest_feed',
				'args'     => array(
					'category' => array(
						'type'              => 'integer',
						'default'           => 0,
						'sanitize_callback' => 'absint',
					),
					'page'     => array(
						'type'              => 'integer',
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					's'        => array(
						'type'              => 'string',
						'default'           => '',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		)
	);

	register_rest_route(
		'at/v1',
		'/contact',
		array_merge(
			$can,
			array(
				'methods'  => 'POST',
				'callback' => 'at_rest_contact',
			)
		)
	);
}

/**
 * Payload passed to theme.js. Called from at_assets().
 *
 * @return array<string, mixed>
 */
function at_interact_localize() {
	return array(
		'restUrl' => esc_url_raw( rest_url( 'at/v1/' ) ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
		'home'    => esc_url_raw( home_url( '/' ) ),
		'i18n'    => array(
			'searchEmpty'  => 'Ничего не нашли. Нажмите Enter, чтобы открыть полную выдачу.',
			'searchTyping' => 'Введите ещё символ…',
			'searchNone'   => 'Нет совпадений',
			'loading'      => 'Загрузка…',
			'loadMore'     => 'Ещё материалы',
			'loadEnd'      => 'Это все материалы',
			'sent'         => 'Письмо отправлено. Редакция ответит на указанную почту.',
			'error'        => 'Не удалось отправить. Проверьте имя, почту, текст и согласие.',
			'copied'       => 'Ссылка скопирована',
			'copyFail'     => 'Не удалось скопировать',
			'all'          => 'Все',
		),
	);
}

/**
 * Categories shown on the journal filter strip (staff dossiers excluded).
 *
 * @return WP_Term[]
 */
function at_journal_filter_terms() {
	$exclude = at_staff_term_ids();
	$terms   = get_categories(
		array(
			'hide_empty' => true,
			'exclude'    => $exclude,
			'number'     => 12,
			'orderby'    => 'count',
			'order'      => 'DESC',
		)
	);
	return is_array( $terms ) ? $terms : array();
}

/**
 * Compact card for live search.
 *
 * @param WP_Post $post Post.
 * @return array<string, string>
 */
function at_search_item( $post ) {
	$kicker = '';
	if ( 'at_milestone' === $post->post_type ) {
		$kicker = 'Вехи истории';
	} elseif ( 'page' === $post->post_type ) {
		$kicker = 'Страница';
	} else {
		$kicker = at_kicker( $post->ID );
	}

	return array(
		'id'      => (int) $post->ID,
		'title'   => get_the_title( $post ),
		'url'     => get_permalink( $post ),
		'kicker'  => $kicker,
		'date'    => get_the_date( 'j F Y', $post ),
		'excerpt' => wp_trim_words( get_the_excerpt( $post ), 18 ),
	);
}

/**
 * Live search across journal posts, pages and milestones.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function at_rest_live_search( $request ) {
	$q = trim( (string) $request->get_param( 'q' ) );
	if ( function_exists( 'mb_strlen' ) ? mb_strlen( $q, 'UTF-8' ) < 2 : strlen( $q ) < 2 ) {
		return rest_ensure_response(
			array(
				'ok'    => true,
				'items' => array(),
				'total' => 0,
			)
		);
	}

	$query = new WP_Query(
		array(
			's'                      => $q,
			'post_type'              => array( 'post', 'page', 'at_milestone' ),
			'post_status'            => 'publish',
			'posts_per_page'         => 8,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
		)
	);

	$items = array();
	foreach ( $query->posts as $post ) {
		$items[] = at_search_item( $post );
	}

	return rest_ensure_response(
		array(
			'ok'    => true,
			'items' => $items,
			'total' => (int) $query->found_posts,
		)
	);
}

/**
 * Render a journal row for AJAX insertion.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function at_render_post_row( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return '';
	}
	ob_start();
	get_template_part( 'template-parts/post', 'row', array( 'post_id' => $post_id ) );
	return (string) ob_get_clean();
}

/**
 * AJAX journal feed: filter by category / search, paginate.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function at_rest_feed( $request ) {
	$page     = max( 1, (int) $request->get_param( 'page' ) );
	$category = (int) $request->get_param( 'category' );
	$search   = trim( (string) $request->get_param( 's' ) );
	$staff    = at_staff_term_ids();

	if ( $category && in_array( $category, $staff, true ) ) {
		$category = 0;
	}

	$args = at_news_args(
		array(
			'posts_per_page' => 12,
			'paged'          => $page,
		)
	);

	if ( $category ) {
		$args['cat'] = $category;
	}
	if ( $search ) {
		$args['s'] = $search;
	}

	$query = new WP_Query( $args );
	$html  = '';
	foreach ( $query->posts as $post ) {
		$html .= at_render_post_row( $post->ID );
	}

	return rest_ensure_response(
		array(
			'ok'      => true,
			'html'    => $html,
			'page'    => $page,
			'max'     => (int) $query->max_num_pages,
			'total'   => (int) $query->found_posts,
			'count'   => count( $query->posts ),
			'hasMore' => $page < (int) $query->max_num_pages,
		)
	);
}

/**
 * Shared contact processor (classic form + REST).
 *
 * @param array<string, mixed> $data Posted fields.
 * @return array{ok:bool,code:string,message:string}
 */
function at_process_contact_payload( $data ) {
	$nonce = isset( $data['at_contact_nonce'] ) ? sanitize_text_field( (string) $data['at_contact_nonce'] ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'at_contact' ) ) {
		return array(
			'ok'      => false,
			'code'    => 'nonce',
			'message' => 'Сессия устарела. Обновите страницу и отправьте снова.',
		);
	}

	if ( ! empty( $data['at_company'] ) ) {
		return array(
			'ok'      => true,
			'code'    => 'honeypot',
			'message' => 'Письмо отправлено. Редакция ответит на указанную почту.',
		);
	}

	$name    = isset( $data['at_name'] ) ? sanitize_text_field( (string) $data['at_name'] ) : '';
	$email   = isset( $data['at_email'] ) ? sanitize_email( (string) $data['at_email'] ) : '';
	$message = isset( $data['at_message'] ) ? sanitize_textarea_field( (string) $data['at_message'] ) : '';
	$topic   = isset( $data['at_topic'] ) ? sanitize_text_field( (string) $data['at_topic'] ) : 'Письмо в редакцию';
	$consent = ! empty( $data['at_consent'] );

	if ( ! $name || ! is_email( $email ) || strlen( $message ) < 8 || ! $consent ) {
		return array(
			'ok'      => false,
			'code'    => 'invalid',
			'message' => 'Не удалось отправить. Проверьте имя, почту, текст и согласие.',
		);
	}

	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$stamp = 'at_mail_' . md5( $ip );
	if ( get_transient( $stamp ) ) {
		return array(
			'ok'      => false,
			'code'    => 'rate',
			'message' => 'Слишком часто. Подождите минуту и попробуйте снова.',
		);
	}

	$subject = 'Антитеррор: ' . $topic;
	$body    = "Имя: {$name}\nEmail: {$email}\nТема: {$topic}\n\n{$message}";
	$sent    = wp_mail( get_option( 'admin_email' ), $subject, $body, array( 'Reply-To: ' . $email ) );

	if ( ! $sent ) {
		return array(
			'ok'      => false,
			'code'    => 'mail',
			'message' => 'Не удалось отправить. Проверьте имя, почту, текст и согласие.',
		);
	}

	set_transient( $stamp, 1, MINUTE_IN_SECONDS );

	return array(
		'ok'      => true,
		'code'    => 'sent',
		'message' => 'Письмо отправлено. Редакция ответит на указанную почту.',
	);
}

/**
 * REST contact endpoint.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function at_rest_contact( $request ) {
	$params = $request->get_json_params();
	if ( ! is_array( $params ) ) {
		$params = $request->get_params();
	}
	$result = at_process_contact_payload( is_array( $params ) ? $params : array() );
	$status = $result['ok'] ? 200 : ( 'nonce' === $result['code'] ? 403 : 400 );
	return new WP_REST_Response( $result, $status );
}

/**
 * Related materials, JournalX idea, existing story-card markup.
 *
 * @param int $post_id Current post.
 * @param int $limit   Number of cards.
 * @return WP_Post[]
 */
function at_related_posts( $post_id, $limit = 3 ) {
	$post_id = (int) $post_id;
	$limit   = max( 1, min( 6, (int) $limit ) );
	$cat     = at_primary_category( $post_id );
	$staff   = at_staff_term_ids();

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $limit,
		'post__not_in'        => array( $post_id ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);

	if ( $staff ) {
		$args['category__not_in'] = $staff;
	}
	if ( $cat && ! in_array( (int) $cat->term_id, $staff, true ) ) {
		$args['cat'] = (int) $cat->term_id;
	}

	$query = new WP_Query( $args );
	if ( count( $query->posts ) >= $limit ) {
		return $query->posts;
	}

	unset( $args['cat'] );
	$fallback = new WP_Query( $args );
	$seen     = array();
	$posts    = array();
	foreach ( array_merge( $query->posts, $fallback->posts ) as $post ) {
		if ( isset( $seen[ $post->ID ] ) ) {
			continue;
		}
		$seen[ $post->ID ] = true;
		$posts[]           = $post;
		if ( count( $posts ) >= $limit ) {
			break;
		}
	}
	return $posts;
}

/**
 * Share targets used on single posts (VK / Telegram / copy).
 *
 * @param int $post_id Post ID.
 * @return array<int, array<string, string>>
 */
function at_share_links( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();
	$url     = get_permalink( $post_id );
	$title   = get_the_title( $post_id );
	$encoded = rawurlencode( $url );
	$text    = rawurlencode( $title );

	return array(
		array(
			'id'    => 'vk',
			'label' => 'ВКонтакте',
			'url'   => 'https://vk.com/share.php?url=' . $encoded . '&title=' . $text,
		),
		array(
			'id'    => 'tg',
			'label' => 'Telegram',
			'url'   => 'https://t.me/share/url?url=' . $encoded . '&text=' . $text,
		),
		array(
			'id'    => 'copy',
			'label' => 'Копировать ссылку',
			'url'   => $url,
		),
	);
}

/**
 * Headings extracted from post content for the in-article TOC.
 *
 * @param string $content HTML.
 * @return array<int, array{id:string,level:int,text:string}>
 */
function at_extract_headings( $content ) {
	$items = array();
	if ( ! $content || false === stripos( $content, '<h' ) ) {
		return $items;
	}
	if ( ! preg_match_all( '/<h([23])([^>]*)>(.*?)<\/h\1>/is', $content, $matches, PREG_SET_ORDER ) ) {
		return $items;
	}
	$index = 0;
	foreach ( $matches as $match ) {
		$text = trim( wp_strip_all_tags( $match[3] ) );
		if ( ! $text ) {
			continue;
		}
		$id = '';
		if ( preg_match( '/id=["\']([^"\']+)["\']/', $match[2], $id_match ) ) {
			$id = $id_match[1];
		} else {
			$id = 'at-h-' . ( ++$index );
		}
		$items[] = array(
			'id'    => $id,
			'level' => (int) $match[1],
			'text'  => $text,
		);
	}
	return $items;
}

/**
 * Add stable ids to h2/h3 so the TOC and scroll-spy can target them.
 *
 * @param string $content Content.
 * @return string
 */
function at_content_heading_ids( $content ) {
	if ( is_admin() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( ! $content || false === stripos( $content, '<h' ) ) {
		return $content;
	}
	$index = 0;
	return preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		static function ( $match ) use ( &$index ) {
			$attrs = $match[2];
			if ( preg_match( '/\sid=/i', $attrs ) ) {
				return $match[0];
			}
			++$index;
			$id = 'at-h-' . $index;
			return '<h' . $match[1] . $attrs . ' id="' . esc_attr( $id ) . '">' . $match[3] . '</h' . $match[1] . '>';
		},
		$content
	);
}

/**
 * TOC items for the current post (after ids would be assigned).
 *
 * @param int $post_id Post ID.
 * @return array<int, array{id:string,level:int,text:string}>
 */
function at_post_toc( $post_id ) {
	$content = (string) get_post_field( 'post_content', $post_id );
	if ( ! $content ) {
		return array();
	}
	$index   = 0;
	$content = preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		static function ( $match ) use ( &$index ) {
			$attrs = $match[2];
			if ( ! preg_match( '/\sid=/i', $attrs ) ) {
				++$index;
				$attrs .= ' id="' . esc_attr( 'at-h-' . $index ) . '"';
			}
			return '<h' . $match[1] . $attrs . '>' . $match[3] . '</h' . $match[1] . '>';
		},
		$content
	);
	return at_extract_headings( (string) $content );
}
