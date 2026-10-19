<?php
/**
 * Media patterns adapted from the Redsign Media news template,
 * plus hardening and front-end performance.
 *
 * The Bitrix theme ships jQuery tickers, carousels, and lightbox plugins.
 * This file keeps the ideas (trending rail, share, siblings, reading time,
 * enclosure RSS) and implements them with the theme's own markup.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Keep only http(s) URLs that will be printed into meta tags.
 *
 * @param string $url Raw URL.
 * @return string
 */
function at_public_url( $url ) {
	$url = esc_url_raw( (string) $url );
	if ( ! $url ) {
		return '';
	}
	$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
	if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
		return '';
	}
	return $url;
}

/**
 * Minutes and word count for a published story.
 *
 * @param int $post_id Post ID.
 * @return array{minutes:int,words:int}
 */
function at_reading_stats( $post_id ) {
	$post_id = (int) $post_id;
	$minutes = get_post_meta( $post_id, '_at_reading_minutes', true );
	$words   = get_post_meta( $post_id, '_at_word_count', true );
	if ( '' !== $minutes && '' !== $words ) {
		return array(
			'minutes' => max( 1, (int) $minutes ),
			'words'   => max( 0, (int) $words ),
		);
	}
	return at_reading_stats_from_content( $post_id );
}

/**
 * Store reading time when a story is saved. Front-end requests never write it.
 *
 * @param int $post_id Post ID.
 */
function at_store_reading_time( $post_id ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( 'post' !== get_post_type( $post_id ) ) {
		return;
	}
	$stats = at_reading_stats_from_content( $post_id );
	update_post_meta( $post_id, '_at_reading_minutes', $stats['minutes'] );
	update_post_meta( $post_id, '_at_word_count', $stats['words'] );
	delete_transient( 'at_trending_v1' );
}
add_action( 'save_post', 'at_store_reading_time' );

/**
 * Count words from stored content, ignoring cached meta.
 *
 * @param int $post_id Post ID.
 * @return array{minutes:int,words:int}
 */
function at_reading_stats_from_content( $post_id ) {
	$text  = wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) );
	$count = preg_match_all( '/[\p{L}\p{N}]+/u', $text, $matches );
	$count = false === $count ? 0 : (int) $count;
	return array(
		'minutes' => max( 1, (int) ceil( max( 1, $count ) / 180 ) ),
		'words'   => $count,
	);
}

/**
 * Latest headlines for the utility rail. Cached so the header stays cheap.
 *
 * @return array<int,array{title:string,url:string}>
 */
function at_trending_items() {
	$cached = get_transient( 'at_trending_v1' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$query = new WP_Query(
		at_news_args(
			array(
				'posts_per_page'         => 8,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
			)
		)
	);
	$items = array();
	foreach ( $query->posts as $post ) {
		$url = at_public_url( get_permalink( $post ) );
		if ( ! $url ) {
			continue;
		}
		$items[] = array(
			'title' => get_the_title( $post ),
			'url'   => $url,
		);
	}
	wp_reset_postdata();
	set_transient( 'at_trending_v1', $items, 10 * MINUTE_IN_SECONDS );
	return $items;
}

/**
 * Trending rail. The track is duplicated so a CSS loop has no jump.
 */
function at_trending_markup() {
	$items = at_trending_items();
	if ( count( $items ) < 2 ) {
		return;
	}
	echo '<div class="ticker" data-ticker>';
	echo '<p class="sr">Сейчас в журнале</p>';
	echo '<div class="ticker__track">';
	foreach ( array( false, true ) as $clone ) {
		foreach ( $items as $item ) {
			printf(
				'<a href="%s"%s>%s</a>',
				esc_url( $item['url'] ),
				$clone ? ' tabindex="-1" aria-hidden="true"' : '',
				esc_html( $item['title'] )
			);
		}
	}
	echo '</div></div>';
}

/**
 * Share row for the open story. Networks are plain links, not widgets.
 */
function at_share_markup() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$url   = at_public_url( get_permalink() );
	$title = get_the_title();
	if ( ! $url ) {
		return;
	}
	$vk = 'https://vk.com/share.php?url=' . rawurlencode( $url ) . '&title=' . rawurlencode( $title );
	$tg = 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title );
	echo '<div class="share">';
	echo '<p class="kicker">Поделиться</p>';
	echo '<div class="share__row">';
	printf( '<a class="share__link" href="%s" target="_blank" rel="noopener noreferrer">ВКонтакте</a>', esc_url( $vk ) );
	printf( '<a class="share__link" href="%s" target="_blank" rel="noopener noreferrer">Telegram</a>', esc_url( $tg ) );
	printf(
		'<button class="share__link" type="button" data-copy-link data-copy-value="%s">Скопировать ссылку</button>',
		esc_attr( $url )
	);
	echo '</div></div>';
}

/**
 * Previous and next story in the same rubric.
 */
function at_sibling_markup() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}
	$previous = get_adjacent_post( true, '', true, 'category' );
	$next     = get_adjacent_post( true, '', false, 'category' );
	if ( ! $previous && ! $next ) {
		$previous = get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );
	}
	if ( ! $previous && ! $next ) {
		return;
	}
	echo '<nav class="siblings" aria-label="Соседние материалы">';
	if ( $previous instanceof WP_Post ) {
		printf(
			'<a class="siblings__link siblings__link--prev" href="%s"><span class="kicker">Ранее</span><span>%s</span></a>',
			esc_url( get_permalink( $previous ) ),
			esc_html( get_the_title( $previous ) )
		);
	}
	if ( $next instanceof WP_Post ) {
		printf(
			'<a class="siblings__link siblings__link--next" href="%s"><span class="kicker">Дальше</span><span>%s</span></a>',
			esc_url( get_permalink( $next ) ),
			esc_html( get_the_title( $next ) )
		);
	}
	echo '</nav>';
}

/**
 * Optional organization details. Empty values stay out of the graph.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function at_polish_customize( $wp_customize ) {
	$wp_customize->add_setting(
		'at_org_phone',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'at_org_phone',
		array(
			'label'   => 'Телефон редакции',
			'section' => 'at_journal',
			'type'    => 'text',
		)
	);
	$wp_customize->add_setting(
		'at_org_address',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'at_org_address',
		array(
			'label'   => 'Адрес редакции',
			'section' => 'at_journal',
			'type'    => 'text',
		)
	);
}
add_action( 'customize_register', 'at_polish_customize' );

/**
 * Response headers that do not depend on inline scripts or Google fonts.
 *
 * @param string[] $headers Headers.
 * @return string[]
 */
function at_security_headers( $headers ) {
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';
	$headers['Permissions-Policy']     = 'camera=(), microphone=(), geolocation=(), payment=()';
	unset( $headers['X-Pingback'] );
	if ( is_ssl() ) {
		$headers['Strict-Transport-Security'] = 'max-age=15552000';
	}
	return $headers;
}
add_filter( 'wp_headers', 'at_security_headers' );

/**
 * Drop the pingback XML-RPC method. The journal does not use remote pingbacks.
 *
 * @param array<string,string> $methods Methods.
 * @return array<string,string>
 */
function at_disable_pingback( $methods ) {
	unset( $methods['pingback.ping'] );
	return $methods;
}
add_filter( 'xmlrpc_methods', 'at_disable_pingback' );
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Guests do not need the public user directory.
 *
 * @param mixed           $result  Dispatch result.
 * @param WP_REST_Server  $server  Server.
 * @param WP_REST_Request $request Request.
 * @return mixed
 */
function at_rest_hide_users( $result, $server, $request ) {
	unset( $server );
	if ( $result instanceof WP_Error || is_user_logged_in() ) {
		return $result;
	}
	$route = (string) $request->get_route();
	if ( '/wp/v2/users' === $route || str_starts_with( $route, '/wp/v2/users/' ) ) {
		return new WP_Error(
			'at_rest_forbidden',
			'Authentication required.',
			array( 'status' => 401 )
		);
	}
	return $result;
}
add_filter( 'rest_pre_dispatch', 'at_rest_hide_users', 10, 3 );

/**
 * Cap anonymous view increments. The cookie still stops a repeat on one story.
 *
 * @return bool
 */
function at_view_is_limited() {
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'at_views_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit >= 40 ) {
		return true;
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );
	return false;
}

/**
 * Prefetch in-site stories on hover, and skip the admin.
 */
function at_script_strategy() {
	foreach ( array( 'at-theme', 'at-interact' ) as $handle ) {
		wp_script_add_data( $handle, 'strategy', 'defer' );
	}
}
add_action( 'wp_enqueue_scripts', 'at_script_strategy', 20 );

/**
 * Yandex and feed readers use enclosures and a non-adult rating.
 */
function at_rss_namespaces() {
	echo 'xmlns:media="http://search.yahoo.com/mrss/"' . "\n";
	echo 'xmlns:content="http://purl.org/rss/1.0/modules/content/"' . "\n";
}
add_action( 'rss2_ns', 'at_rss_namespaces' );

/**
 * Featured image plus the story body inside the existing RSS item.
 */
function at_rss_item() {
	echo '<media:rating scheme="urn:simple">nonadult</media:rating>' . "\n";
	$thumb_id = get_post_thumbnail_id();
	if ( $thumb_id ) {
		$src = wp_get_attachment_image_src( $thumb_id, 'large' );
		if ( $src ) {
			$file = get_attached_file( $thumb_id );
			$type = $file ? wp_check_filetype( $file ) : array();
			$mime = ! empty( $type['type'] ) ? $type['type'] : 'image/jpeg';
			printf(
				'<enclosure url="%s" length="0" type="%s" />' . "\n",
				esc_url( $src[0] ),
				esc_attr( $mime )
			);
		}
	}
	$content = get_the_content_feed( 'rss2' );
	$content = str_replace( ']]>', ']]]]><![CDATA[>', $content );
	echo '<content:encoded><![CDATA[' . $content . ']]></content:encoded>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
add_action( 'rss2_item', 'at_rss_item' );

/**
 * Rel prev/next for the main archive query. The journal template keeps its own.
 */
function at_archive_paging_links() {
	if ( is_singular() || is_404() || is_front_page() ) {
		return;
	}
	global $wp_query;
	$pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 0;
	if ( $pages < 2 ) {
		return;
	}
	$paged = max( 1, (int) get_query_var( 'paged' ) );
	if ( $paged > 1 ) {
		echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '">' . "\n";
	}
	if ( $paged < $pages ) {
		echo '<link rel="next" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'at_archive_paging_links', 3 );
add_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 4 );
