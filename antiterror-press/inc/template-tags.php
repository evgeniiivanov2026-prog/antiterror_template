<?php
/**
 * Разметка и выборки журнала.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function at_ru_plural( $number, $one, $few, $many ) {
	$number = abs( (int) $number );
	$n10    = $number % 10;
	$n100   = $number % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		$word = $one;
	} elseif ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 12 || $n100 > 14 ) ) {
		$word = $few;
	} else {
		$word = $many;
	}
	return $number . ' ' . $word;
}

function at_page_url( $slug ) {
	static $cache = array();
	if ( isset( $cache[ $slug ] ) ) {
		return $cache[ $slug ];
	}
	$page = get_page_by_path( $slug );
	if ( $page ) {
		$cache[ $slug ] = get_permalink( $page );
		return $cache[ $slug ];
	}
	$term = get_category_by_slug( $slug );
	if ( $term ) {
		$link = get_category_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$cache[ $slug ] = $link;
			return $link;
		}
	}
	$cache[ $slug ] = home_url( '/' );
	return $cache[ $slug ];
}

function at_staff_slugs() {
	return array( 'heroes', 'geroi-antiterrora' );
}

function at_staff_term_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}
	$ids = array();
	foreach ( at_staff_slugs() as $slug ) {
		$term = get_category_by_slug( $slug );
		if ( $term ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return $ids;
}

function at_news_args( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'orderby'             => 'date',
			'order'               => 'DESC',
		)
	);
	$ids = at_staff_term_ids();
	if ( $ids ) {
		$args['category__not_in'] = $ids;
	}
	return $args;
}

function at_is_staff_post( $post_id ) {
	$post_id = (int) $post_id;
	if ( get_post_meta( $post_id, '_at_rank', true ) ) {
		return true;
	}
	foreach ( get_the_category( $post_id ) as $cat ) {
		if ( in_array( $cat->slug, at_staff_slugs(), true ) ) {
			return true;
		}
	}
	return false;
}

function at_post_topic( $post_id ) {
	$cats = array();
	foreach ( get_the_category( $post_id ) as $cat ) {
		if ( ! in_array( $cat->slug, at_staff_slugs(), true ) ) {
			$cats[] = $cat;
		}
	}
	if ( ! $cats ) {
		return array(
			'name' => 'Журнал',
			'slug' => 'zhurnal',
		);
	}
	usort(
		$cats,
		function ( $a, $b ) {
			return (int) ( $b->parent > 0 ) - (int) ( $a->parent > 0 );
		}
	);
	return array(
		'name' => $cats[0]->name,
		'slug' => $cats[0]->slug,
	);
}

function at_first_content_image( $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	if ( $content && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches ) ) {
		return $matches[1];
	}
	return '';
}

function at_first_content_video( $post_id ) {
	$content = get_post_field( 'post_content', $post_id );
	if ( ! $content ) {
		return '';
	}
	if ( preg_match( '/<source[^>]+src=["\']([^"\']+)["\']/i', $content, $matches ) ) {
		return $matches[1];
	}
	if ( preg_match( '/<video[^>]+src=["\']([^"\']+)["\']/i', $content, $matches ) ) {
		return $matches[1];
	}
	return '';
}

function at_lead_sources( $post_id ) {
	static $cache = array();
	$post_id = (int) $post_id;
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}
	if ( '1' === (string) get_post_meta( $post_id, '_at_lead_ready', true ) ) {
		$cache[ $post_id ] = array(
			'image' => (string) get_post_meta( $post_id, '_at_lead_image', true ),
			'video' => (string) get_post_meta( $post_id, '_at_lead_video', true ),
		);
		return $cache[ $post_id ];
	}
	$image = at_first_content_image( $post_id );
	$video = at_first_content_video( $post_id );
	update_post_meta( $post_id, '_at_lead_image', $image );
	update_post_meta( $post_id, '_at_lead_video', $video );
	update_post_meta( $post_id, '_at_lead_ready', '1' );
	$cache[ $post_id ] = array(
		'image' => $image,
		'video' => $video,
	);
	return $cache[ $post_id ];
}

function at_refresh_lead_media( $post_id ) {
	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) || 'post' !== get_post_type( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_at_lead_image', at_first_content_image( $post_id ) );
	update_post_meta( $post_id, '_at_lead_video', at_first_content_video( $post_id ) );
	update_post_meta( $post_id, '_at_lead_ready', '1' );
	at_flush_media_archive();
}
add_action( 'save_post', 'at_refresh_lead_media' );

function at_media_html( $post_id, $size = 'at-thumb', $attrs = array() ) {
	$post_id = (int) $post_id;
	$attrs   = wp_parse_args(
		$attrs,
		array(
			'alt'      => wp_strip_all_tags( get_the_title( $post_id ) ),
			'decoding' => 'async',
		)
	);
	if ( empty( $attrs['loading'] ) && empty( $attrs['fetchpriority'] ) ) {
		$attrs['loading'] = 'lazy';
	}
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail( $post_id, $size, $attrs );
	}

	$sources = at_lead_sources( $post_id );
	if ( $sources['image'] ) {
		$html = '<img src="' . esc_url( $sources['image'] ) . '"';
		foreach ( array( 'alt', 'loading', 'decoding', 'fetchpriority' ) as $key ) {
			if ( ! empty( $attrs[ $key ] ) ) {
				$html .= ' ' . $key . '="' . esc_attr( $attrs[ $key ] ) . '"';
			}
		}
		return $html . '>';
	}

	if ( $sources['video'] ) {
		$video = strtok( $sources['video'], '#' );
		return '<video muted playsinline preload="none" tabindex="-1" data-src="' . esc_url( $video ) . '#t=0.1"></video>';
	}

	return '';
}

function at_media_key( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	$path = preg_replace( '/-\d+x\d+(?=\.[a-z0-9]+$)/i', '', $path );
	return strtolower( $path );
}

function at_is_archive_image( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( ! preg_match( '/\.(jpe?g|png|webp|gif)$/i', $path ) ) {
		return false;
	}
	$lower = strtolower( $url );
	foreach ( array( 'emoji', 'gravatar', 'smiley', 'sprite', 'favicon', 'data:' ) as $skip ) {
		if ( false !== strpos( $lower, $skip ) ) {
			return false;
		}
	}
	return true;
}

function at_is_archive_video( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	return (bool) preg_match( '/\.(mp4|webm|ogg)$/i', $path );
}

function at_media_context( $post ) {
	$staff = at_is_staff_post( $post->ID );
	$topic = at_post_topic( $post->ID );
	return array(
		'shelf'      => $staff ? 'staff' : 'story',
		'topic'      => $staff ? 'Сотрудники' : $topic['name'],
		'topic_slug' => $staff ? 'sotrudniki' : $topic['slug'],
		'rank'       => (string) get_post_meta( $post->ID, '_at_rank', true ),
		'title'      => get_the_title( $post ),
		'date'       => get_the_date( 'j F Y', $post ),
		'permalink'  => get_permalink( $post ),
	);
}

function at_push_media_item( &$items, &$seen, $url, $type, $context, $preview = '' ) {
	$url = trim( (string) $url );
	if ( ! $url ) {
		return;
	}
	if ( 'photo' === $type && ! at_is_archive_image( $url ) ) {
		return;
	}
	if ( 'video' === $type && ! at_is_archive_video( $url ) ) {
		return;
	}
	$key = at_media_key( $url );
	if ( ! $key || isset( $seen[ $key ] ) ) {
		return;
	}
	$seen[ $key ] = true;
	$clean        = strtok( $url, '#' );
	$items[]      = array_merge(
		$context,
		array(
			'type'    => $type,
			'src'     => $clean,
			'preview' => $preview ? $preview : $clean,
		)
	);
}

function at_collect_archive_posts() {
	$common   = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	$term_ids = at_staff_term_ids();
	$staff    = $common;
	$story    = $common;
	$staff['posts_per_page'] = 80;
	$story['posts_per_page'] = 40;
	if ( $term_ids ) {
		$staff['tax_query']      = array(
			array(
				'taxonomy' => 'category',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			),
		);
		$story['category__not_in'] = $term_ids;
	} else {
		$staff['meta_query'] = array(
			array(
				'key'     => '_at_rank',
				'compare' => 'EXISTS',
			),
		);
	}
	$posts = array();
	$seen  = array();
	foreach ( array( new WP_Query( $staff ), new WP_Query( $story ) ) as $query ) {
		update_post_thumbnail_cache( $query );
		foreach ( $query->posts as $post ) {
			if ( isset( $seen[ $post->ID ] ) ) {
				continue;
			}
			$seen[ $post->ID ] = true;
			$posts[]           = $post;
		}
	}
	return $posts;
}

function at_build_media_archive() {
	$items = array();
	$seen  = array();
	foreach ( at_collect_archive_posts() as $post ) {
		$context  = at_media_context( $post );
		$thumb_id = get_post_thumbnail_id( $post );
		if ( $thumb_id ) {
			$meta = wp_get_attachment_metadata( $thumb_id );
			if ( empty( $meta['width'] ) || (int) $meta['width'] >= 240 ) {
				$size = 'staff' === $context['shelf'] ? 'at-portrait' : 'at-card';
				at_push_media_item(
					$items,
					$seen,
					wp_get_attachment_image_url( $thumb_id, 'full' ),
					'photo',
					$context,
					wp_get_attachment_image_url( $thumb_id, $size )
				);
			}
		}
		$content = $post->post_content;
		if ( ! $content ) {
			continue;
		}
		if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $images ) ) {
			foreach ( $images[1] as $src ) {
				at_push_media_item( $items, $seen, $src, 'photo', $context );
			}
		}
		if ( preg_match_all( '/<(?:source|video)[^>]+src=["\']([^"\']+)["\']/i', $content, $videos ) ) {
			foreach ( $videos[1] as $src ) {
				at_push_media_item( $items, $seen, $src, 'video', $context );
			}
		}
	}
	return $items;
}

function at_media_archive() {
	$cached = get_transient( 'at_media_v2' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$items = at_build_media_archive();
	set_transient( 'at_media_v2', $items, 15 * MINUTE_IN_SECONDS );
	return $items;
}

function at_media_groups() {
	$staff  = array();
	$topics = array();
	foreach ( at_media_archive() as $item ) {
		if ( empty( $item['src'] ) ) {
			continue;
		}
		if ( isset( $item['shelf'] ) && 'staff' === $item['shelf'] ) {
			$staff[] = $item;
			continue;
		}
		$slug = ! empty( $item['topic_slug'] ) ? $item['topic_slug'] : 'zhurnal';
		if ( ! isset( $topics[ $slug ] ) ) {
			$topics[ $slug ] = array(
				'name'  => ! empty( $item['topic'] ) ? $item['topic'] : 'Журнал',
				'slug'  => $slug,
				'items' => array(),
			);
		}
		$topics[ $slug ]['items'][] = $item;
	}
	$order = array( 'main', 'zaderzhaniya', '2026-svodki', 'history' );
	uksort(
		$topics,
		function ( $a, $b ) use ( $order ) {
			$ia = array_search( $a, $order, true );
			$ib = array_search( $b, $order, true );
			$ia = false === $ia ? 99 : $ia;
			$ib = false === $ib ? 99 : $ib;
			if ( $ia === $ib ) {
				return strcmp( $a, $b );
			}
			return $ia - $ib;
		}
	);
	return array(
		'staff'  => $staff,
		'topics' => array_values( $topics ),
	);
}

function at_flush_media_archive() {
	delete_transient( 'at_media_archive' );
	delete_transient( 'at_media_v2' );
}
add_action( 'deleted_post', 'at_flush_media_archive' );

function at_sort_name( $post ) {
	$name = get_post_meta( $post->ID, '_at_sort_name', true );
	if ( ! $name ) {
		$name = get_the_title( $post );
	}
	$name = trim( wp_strip_all_tags( $name ) );
	if ( function_exists( 'mb_strtoupper' ) ) {
		$name = mb_strtoupper( $name, 'UTF-8' );
	}
	return str_replace( 'Ё', 'Е', $name );
}

function at_letter( $name ) {
	$name = trim( (string) $name );
	if ( function_exists( 'mb_substr' ) ) {
		$letter = mb_substr( $name, 0, 1, 'UTF-8' );
	} else {
		$letter = substr( $name, 0, 1 );
	}
	return 'Ё' === $letter ? 'Е' : $letter;
}

function at_fighter_query( $limit = -1 ) {
	$slugs = array( 'heroes', 'geroi-antiterrora' );
	$args  = array(
		'post_type'           => 'post',
		'posts_per_page'      => $limit,
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'tax_query'              => array(
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $slugs,
			),
		),
	);
	$query = new WP_Query( $args );
	if ( $query->have_posts() ) {
		return $query;
	}

	return new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $limit,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array(
					'key'     => '_at_rank',
					'compare' => 'EXISTS',
				),
			),
		)
	);
}

function at_sorted_fighters( $limit = -1 ) {
	$query = at_fighter_query( $limit );
	update_post_thumbnail_cache( $query );
	$posts = $query->posts;
	usort(
		$posts,
		function ( $a, $b ) {
			return strcmp( at_sort_name( $a ), at_sort_name( $b ) );
		}
	);
	return $posts;
}

function at_fighter_groups( $posts ) {
	$groups = array();
	foreach ( $posts as $post ) {
		$key = at_letter( at_sort_name( $post ) );
		if ( ! isset( $groups[ $key ] ) ) {
			$groups[ $key ] = array();
		}
		$groups[ $key ][] = $post;
	}
	return $groups;
}

function at_alphabet() {
	return array( 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Э', 'Ю', 'Я' );
}

function at_primary_category( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$cats    = get_the_category( $post_id );
	if ( empty( $cats ) ) {
		return null;
	}
	return $cats[0];
}

function at_kicker( $post_id = 0 ) {
	$cat = at_primary_category( $post_id );
	if ( ! $cat ) {
		return '';
	}
	return $cat->name;
}

function at_views_html( $post_id ) {
	$views = (int) get_post_meta( $post_id, '_at_views', true );
	if ( $views < 1 ) {
		return '';
	}
	return esc_html( at_ru_plural( $views, 'просмотр', 'просмотра', 'просмотров' ) );
}

function at_meta_line( $post_id ) {
	$parts   = array();
	$parts[] = '<time datetime="' . esc_attr( get_the_date( 'c', $post_id ) ) . '">' . esc_html( get_the_date( 'j F Y', $post_id ) ) . '</time>';
	$rank    = get_post_meta( $post_id, '_at_rank', true );
	$unit    = get_post_meta( $post_id, '_at_unit', true );
	if ( $rank ) {
		$parts[] = '<span>' . esc_html( $rank ) . '</span>';
	}
	if ( $unit ) {
		$parts[] = '<span>' . esc_html( $unit ) . '</span>';
	}
	$views = at_views_html( $post_id );
	if ( $views ) {
		$parts[] = '<span>' . $views . '</span>';
	}
	echo '<div class="meta">' . implode( '', $parts ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function at_crumb_trail() {
	if ( is_front_page() ) {
		return array();
	}
	$trail = array(
		array( 'Главная', home_url( '/' ) ),
	);
	if ( is_search() ) {
		$trail[] = array( 'Поиск', '' );
	} elseif ( is_404() ) {
		$trail[] = array( 'Страница не найдена', '' );
	} elseif ( is_singular( 'post' ) ) {
		$cat = at_primary_category();
		if ( $cat && ! is_wp_error( $cat ) ) {
			$link = get_category_link( $cat );
			$trail[] = array( $cat->name, is_wp_error( $link ) ? '' : $link );
		}
		$trail[] = array( get_the_title(), '' );
	} elseif ( is_singular( 'at_milestone' ) ) {
		$trail[] = array( 'Вехи истории', at_page_url( 'vehi-istorii' ) );
		$trail[] = array( get_the_title(), '' );
	} elseif ( is_page() ) {
		$trail[] = array( get_the_title(), '' );
	} elseif ( is_category() || is_tag() || is_tax() || is_archive() ) {
		$trail[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
	}
	return $trail;
}

function at_breadcrumbs() {
	$trail = at_crumb_trail();
	if ( count( $trail ) < 2 ) {
		return;
	}
	echo '<nav class="crumbs" aria-label="Хлебные крошки"><ol>';
	$last = count( $trail ) - 1;
	foreach ( $trail as $index => $crumb ) {
		echo '<li>';
		if ( $index !== $last && ! empty( $crumb[1] ) ) {
			echo '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
		} else {
			echo '<span aria-current="page">' . esc_html( $crumb[0] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

function at_fallback_menu( $args = array() ) {
	$items = array(
		array( 'Бойцы', at_page_url( 'geroi-antiterrora' ) ),
		array( 'Вехи истории', at_page_url( 'vehi-istorii' ) ),
		array( 'Журнал', at_page_url( 'zhurnal' ) ),
		array( 'Архив', at_page_url( 'arhiv' ) ),
		array( 'Задержания', at_page_url( 'zaderzhaniya' ) ),
		array( 'Сводки', at_page_url( '2026-svodki' ) ),
		array( 'Герои среди нас', at_page_url( 'geroi-sredi-nas' ) ),
	);
	$class = ( isset( $args['theme_location'] ) && 'footer' === $args['theme_location'] ) ? 'footer-menu' : 'nav__list';
	echo '<ul class="' . esc_attr( $class ) . '">';
	foreach ( $items as $item ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $item[1] ),
			esc_html( $item[0] )
		);
	}
	echo '</ul>';
}

function at_social_links( $class = 'utility__links' ) {
	$links = array(
		'ВКонтакте' => get_theme_mod( 'at_vk', 'https://vk.com/antiterror_rossii' ),
		'Telegram'  => get_theme_mod( 'at_tg', 'https://t.me/terrorismunet' ),
	);
	echo '<div class="' . esc_attr( $class ) . '">';
	foreach ( $links as $label => $url ) {
		if ( ! $url ) {
			continue;
		}
		printf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);
	}
	echo '</div>';
}
