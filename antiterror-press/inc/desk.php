<?php
/**
 * Редакционный «стол»: факты номера, рубрики, разделы, подсказки поиска.
 *
 * Приёмы собраны по открытой структуре корпоративных шаблонов вроде Aspro Allcorp
 * (тизеры под баннером, плитки разделов, подсказки в поиске) и переписаны
 * под бумажную сетку журнала. Чужой код тем не используется.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Слово после числа: «материал», «материала», «материалов».
 *
 * @param int    $number Count.
 * @param string $one    Singular.
 * @param string $few    Few.
 * @param string $many   Many.
 */
function at_desk_word( $number, $one, $few, $many ) {
	$phrase = at_ru_plural( (int) $number, $one, $few, $many );
	$parts  = explode( ' ', $phrase, 2 );
	return isset( $parts[1] ) ? $parts[1] : $many;
}

/**
 * Сколько опубликованных досье бойцов.
 */
function at_desk_fighter_count() {
	$query = new WP_Query(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => false,
			'ignore_sticky_posts'    => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => at_staff_slugs(),
				),
			),
		)
	);
	$count = (int) $query->found_posts;
	wp_reset_postdata();
	return $count;
}

/**
 * Короткие факты для полосы под обложкой. Только живые числа.
 *
 * @return array<int,array{value:string,count:int,label:string}>
 */
function at_desk_facts() {
	$facts = array();

	$posts = wp_count_posts( 'post' );
	$post_count = isset( $posts->publish ) ? (int) $posts->publish : 0;
	if ( $post_count > 0 ) {
		$facts[] = array(
			'value' => (string) $post_count,
			'count' => $post_count,
			'label' => at_desk_word( $post_count, 'материал', 'материала', 'материалов' ),
		);
	}

	$fighters = at_desk_fighter_count();
	if ( $fighters > 0 ) {
		$facts[] = array(
			'value' => (string) $fighters,
			'count' => $fighters,
			'label' => at_desk_word( $fighters, 'боец', 'бойца', 'бойцов' ),
		);
	}

	$initiatives = wp_count_posts( 'at_initiative' );
	$initiative_count = isset( $initiatives->publish ) ? (int) $initiatives->publish : 0;
	if ( $initiative_count > 0 ) {
		$facts[] = array(
			'value' => (string) $initiative_count,
			'count' => $initiative_count,
			'label' => at_desk_word( $initiative_count, 'инициатива', 'инициативы', 'инициатив' ),
		);
	}

	$latest = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( $latest ) {
		$facts[] = array(
			'value' => get_the_date( 'j M', $latest[0] ),
			'count' => 0,
			'label' => 'свежий материал',
		);
	}

	return $facts;
}

/**
 * Рубрики с материалами, самые наполненные первыми.
 *
 * @param int $limit Max terms.
 * @return WP_Term[]
 */
function at_desk_rubrics( $limit = 8 ) {
	$terms = get_categories(
		array(
			'hide_empty' => true,
			'number'     => $limit,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'exclude'    => array( (int) get_option( 'default_category' ) ),
		)
	);
	return is_array( $terms ) ? $terms : array();
}

/**
 * Разделы журнала. Тексты совпадают с тем, что уже сказано на страницах.
 *
 * @return array<int,array{n:string,title:string,text:string,url:string}>
 */
function at_desk_sections() {
	return array(
		array(
			'n'     => '01',
			'title' => 'Журнал',
			'text'  => 'Лента материалов: от закреплённого к свежим.',
			'url'   => at_page_url( 'zhurnal' ),
		),
		array(
			'n'     => '02',
			'title' => 'Фонд инициатив',
			'text'  => 'Предложения, которые журнал выносит на обсуждение.',
			'url'   => at_page_url( 'fond-iniciativ' ),
		),
		array(
			'n'     => '03',
			'title' => 'Бойцы антитеррора',
			'text'  => 'Алфавитный список людей, о которых пишет журнал.',
			'url'   => at_page_url( 'geroi-antiterrora' ),
		),
		array(
			'n'     => '04',
			'title' => 'Вехи истории',
			'text'  => 'Указатель событий, которые нужно помнить по именам.',
			'url'   => at_page_url( 'vehi-istorii' ),
		),
		array(
			'n'     => '05',
			'title' => 'Архив',
			'text'  => 'Фото и видео: портреты сотрудников и материалы по темам.',
			'url'   => at_page_url( 'arhiv' ),
		),
		array(
			'n'     => '06',
			'title' => 'Герои среди нас',
			'text'  => 'Три номинации: слово, образ и рассказ о человеке рядом.',
			'url'   => at_page_url( 'geroi-sredi-nas' ),
		),
	);
}

/**
 * Четыре свежих заголовка под полем поиска.
 *
 * @return array<int,array{title:string,url:string}>
 */
function at_desk_hints() {
	$posts = get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 4,
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$hints = array();
	foreach ( $posts as $post ) {
		$hints[] = array(
			'title' => get_the_title( $post ),
			'url'   => get_permalink( $post ),
		);
	}
	return $hints;
}
