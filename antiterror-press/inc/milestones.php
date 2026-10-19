<?php
/**
 * Вехи истории: события подразделений, алфавитный указатель.
 *
 * Если активен ABC Pagination (WPShop), страница выводит его шорткод.
 * Иначе — собственный указатель с тем же смыслом: буква, поиск, карточка.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Catalog of events everyone should be able to find by name.
 *
 * @return array<int, array<string, string|int>>
 */
function at_milestone_catalog() {
	return array(
		array(
			'slug'    => 'alfa',
			'title'   => 'Альфа',
			'year'    => 1974,
			'date'    => '29 июля 1974',
			'place'   => 'Москва',
			'units'   => 'КГБ СССР',
			'aliases' => 'группа А, группа «А»',
			'excerpt' => 'Создано подразделение по борьбе с терроризмом. С этой даты начинается история группы, которую позже узнают по операциям освобождения заложников.',
			'body'    => 'Группа «А» (позже «Альфа») создана по решению председателя КГБ СССР Юрия Андропова. Задача подразделения — противодействие террористическим актам и освобождение заложников. В дальнейшем «Альфа» участвовала в ключевых операциях внутри страны: от Орджоникидзе до «Норд-Оста» и Беслана.',
		),
		array(
			'slug'    => 'dvorets-amina',
			'title'   => 'Дворец Амина',
			'year'    => 1979,
			'date'    => '27 декабря 1979',
			'place'   => 'Кабул',
			'units'   => '«Гром», «Зенит»',
			'aliases' => 'Тадж-Бек, Шторм-333',
			'excerpt' => 'Штурм дворца Тадж-Бек в Кабуле. Операция стала боевым крещением спецподразделений КГБ, из которых выросли поздние антитеррористические группы.',
			'body'    => 'В ночь на 27 декабря 1979 года группы «Гром» и «Зенит» КГБ штурмовали дворец Амина. Для истории российских специальных подразделений это исходная точка: опыт Кабула лег в подготовку «Альфы» и «Вымпела».',
		),
		array(
			'slug'    => 'vympel',
			'title'   => 'Вымпел',
			'year'    => 1981,
			'date'    => '19 августа 1981',
			'place'   => 'Москва',
			'units'   => 'КГБ СССР',
			'excerpt' => 'Создано подразделение специального назначения «Вымпел». Вместе с «Альфой» оно станет основой сил освобождения заложников.',
			'body'    => '«Вымпел» сформирован в 1981 году как подразделение специального назначения КГБ. В 1990-е и 2000-е его бойцы работали рядом с «Альфой» в Будённовске, на Дубровке и в Беслане.',
		),
		array(
			'slug'    => 'ordzhonikidze',
			'title'   => 'Орджоникидзе',
			'year'    => 1988,
			'date'    => '1–2 декабря 1988',
			'place'   => 'Орджоникидзе',
			'units'   => 'Группа «Альфа»',
			'aliases' => 'Владикавказ, Минеральные Воды, автобус',
			'excerpt' => 'Захват автобуса со школьниками. Одна из первых операций «Альфы» по освобождению детей: автобус ушёл в сторону Минеральных Вод, заложников удалось вернуть.',
			'body'    => '1 декабря 1988 года вооружённая группа захватила автобус с учениками в Орджоникидзе (ныне Владикавказ) и потребовала вылет. Переговоры вели при участии группы «Альфа». На следующий день дети были освобождены. Для страны это один из первых широко известных случаев, когда специальное подразделение работало ради спасения школьников.',
		),
		array(
			'slug'    => 'budyonnovsk',
			'title'   => 'Будённовск',
			'year'    => 1995,
			'date'    => '14–19 июня 1995',
			'place'   => 'Будённовск',
			'units'   => '«Альфа», «Вымпел»',
			'excerpt' => 'Захват городской больницы. «Альфа» и «Вымпел» были введены, чтобы вывести заложников. Операция осталась в памяти как тяжёлое испытание для спецназа и города.',
			'body'    => '14 июня 1995 года террористы захватили больницу Будённовска вместе с пациентами и персоналом. Для освобождения заложников привлекли «Альфу» и «Вымпел». Штурм не завершил кризис: заложников отпустили после переговоров. Будённовск помнят как событие, после которого антитеррор перестал быть далёкой темой.',
		),
		array(
			'slug'    => 'kizlyar',
			'title'   => 'Кизляр',
			'year'    => 1996,
			'date'    => '9–18 января 1996',
			'place'   => 'Кизляр и Первомайское',
			'units'   => '«Альфа», «Вымпел», внутренние войска',
			'aliases' => 'Первомайское',
			'excerpt' => 'Захват больницы в Кизляре и последующая операция у села Первомайское. Спецподразделения несколько суток удерживали оцепление и пытались освободить заложников.',
			'body'    => 'В январе 1996 года была захвачена больница в Кизляре. Колонна с заложниками вышла к селу Первомайское, где развернулась многодневная операция. В ней участвовали «Альфа», «Вымпел» и внутренние войска. Событие стоит рядом с Будённовском в истории подразделений 1990-х.',
		),
		array(
			'slug'    => 'nord-ost',
			'title'   => 'Норд-Ост',
			'year'    => 2002,
			'date'    => '23–26 октября 2002',
			'place'   => 'Дубровка, Москва',
			'units'   => '«Альфа», «Вымпел»',
			'aliases' => 'Дубровка, театральный центр, мюзикл',
			'excerpt' => 'Захват мюзикла «Норд-Ост» в театральном центре на Дубровке. На четвёртые сутки «Альфа» и «Вымпел» провели штурм и вывели заложников из зала.',
			'body'    => '23 октября 2002 года во время спектакля «Норд-Ост» террористы захватили зрительный зал театрального центра на Дубровке. Более пятидесяти часов зрители и артисты оставались в зале. 26 октября подразделения «Альфа» и «Вымпел» вошли в здание. Имя «Норд-Ост» и слово «Дубровка» с тех пор означают одно событие: его нужно знать, чтобы понимать цену работы специальных подразделений и цену ошибки.',
		),
		array(
			'slug'    => 'beslan',
			'title'   => 'Беслан',
			'year'    => 2004,
			'date'    => '1–3 сентября 2004',
			'place'   => 'Беслан',
			'units'   => '«Альфа», «Вымпел»',
			'excerpt' => 'Захват школы №1 в день знаний. На третьи сутки специальные подразделения пошли выводить оставшихся заложников.',
			'body'    => '1 сентября 2004 года террористы захватили школу №1 в Беслане. В спортзале оказались дети, родители и учителя. 3 сентября, после взрывов в здании, «Альфа», «Вымпел» и другие подразделения начали эвакуацию. Беслан остаётся главной вехой, которую обязан помнить каждый, кто говорит об антитерроре.',
		),
		array(
			'slug'    => 'nalchik',
			'title'   => 'Нальчик',
			'year'    => 2005,
			'date'    => '13–14 октября 2005',
			'place'   => 'Нальчик',
			'units'   => 'Спецподразделения и внутренние войска',
			'excerpt' => 'Нападение на город. Сутки подразделения специального назначения и внутренние войска отражали атаки на учреждения и жилые кварталы.',
			'body'    => '13 октября 2005 года вооружённые группы одновременно атаковали силовые объекты Нальчика. Бои в городе шли больше суток. Спецподразделения и внутренние войска удерживали учреждения и выводили людей. Нальчик — пример того, как антитеррористические силы работают не в одном здании, а по всему городу.',
		),
		array(
			'slug'    => 'krokus',
			'title'   => 'Крокус Сити Холл',
			'year'    => 2024,
			'date'    => '22 марта 2024',
			'place'   => 'Красногорск',
			'units'   => 'Спецслужбы и экстренные службы',
			'aliases' => 'Красногорск, концертный зал',
			'excerpt' => 'Нападение на концертный зал. Спецслужбы и экстренные службы работали на месте и по горячим следам.',
			'body'    => '22 марта 2024 года вооружённые люди напали на концертный зал «Крокус Сити Холл» в Красногорске. Экстренные службы эвакуировали людей, специальные подразделения искали нападавших. Это веха уже нового времени: зал, город и службы, которые обязаны быть готовы.',
		),
		array(
			'slug'    => 'nazran',
			'title'   => 'Назрань',
			'year'    => 2004,
			'date'    => '21–22 июня 2004',
			'place'   => 'Назрань',
			'units'   => 'Спецподразделения и внутренние войска',
			'aliases' => 'Ингушетия',
			'excerpt' => 'Нападение на город. Подразделения специального назначения и внутренние войска отражали атаки на учреждения.',
			'body'    => '21 июня 2004 года вооружённые группы атаковали силовые объекты Назрани. Бои шли больше суток. Спецподразделения и внутренние войска удерживали учреждения и выводили людей. Назрань стоит в одном ряду с Нальчиком: антитеррор здесь работал по всему городу, а не в одном здании.',
		),
		array(
			'slug'    => 'metro-moskva',
			'title'   => 'Метро Москвы',
			'year'    => 2010,
			'date'    => '29 марта 2010',
			'place'   => 'Лубянка и Парк культуры',
			'units'   => 'Спецслужбы и экстренные службы',
			'aliases' => 'Лубянка, Парк культуры',
			'excerpt' => 'Взрывы в московском метро. Экстренные службы и спецслужбы работали на станциях и по горячим следам.',
			'body'    => '29 марта 2010 года в московском метро произошли взрывы на станциях «Лубянка» и «Парк культуры». Станции закрыли, людей выводили на поверхность. Это веха, которую знают по именам станций: городская транспортная система стала целью, и службы обязаны были быть готовы.',
		),
		array(
			'slug'    => 'domodedovo',
			'title'   => 'Домодедово',
			'year'    => 2011,
			'date'    => '24 января 2011',
			'place'   => 'Аэропорт Домодедово',
			'units'   => 'Службы аэропорта и спецслужбы',
			'aliases' => 'аэропорт',
			'excerpt' => 'Взрыв в зоне прилёта международного аэропорта. Службы аэропорта и спецслужбы работали на месте.',
			'body'    => '24 января 2011 года взрыв произошёл в зоне прилёта аэропорта Домодедово. Экстренные службы эвакуировали людей, спецслужбы работали на месте и по следам. Домодедово — веха о том, что удар может прийтись на транспортный узел, куда каждый день приезжают тысячи людей.',
		),
		array(
			'slug'    => 'volgograd',
			'title'   => 'Волгоград',
			'year'    => 2013,
			'date'    => '29–30 декабря 2013',
			'place'   => 'Волгоград',
			'units'   => 'Спецслужбы и экстренные службы',
			'aliases' => 'вокзал, троллейбус',
			'excerpt' => 'Два взрыва в городе за сутки. Экстренные службы эвакуировали людей, спецслужбы искали исполнителей.',
			'body'    => '29 и 30 декабря 2013 года в Волгограде произошли взрывы на вокзале и в троллейбусе. Город работал в режиме эвакуации и поиска. Волгоград помнят как веху, где удар пришёлся не на одно здание, а на обычные городские места.',
		),
		array(
			'slug'    => 'metro-peterburg',
			'title'   => 'Метро Санкт-Петербурга',
			'year'    => 2017,
			'date'    => '3 апреля 2017',
			'place'   => 'Санкт-Петербург',
			'units'   => 'Спецслужбы и экстренные службы',
			'aliases' => 'Сенная площадь, Технологический институт',
			'excerpt' => 'Взрыв в вагоне петербургского метро. Станции закрыли, людей выводили, спецслужбы работали на месте.',
			'body'    => '3 апреля 2017 года взрыв произошёл в вагоне метро между станциями «Сенная площадь» и «Технологический институт». Людей выводили, станции закрывали. Для Петербурга это веха того же ряда, что московское метро: транспорт, город и службы, которые должны ответить сразу.',
		),

	);
}

function at_register_milestones() {
	register_post_type(
		'at_milestone',
		array(
			'labels'              => array(
				'name'          => 'Вехи истории',
				'singular_name' => 'Веха',
				'add_new_item'  => 'Добавить веху',
				'edit_item'     => 'Редактировать веху',
				'menu_name'     => 'Вехи истории',
			),
			'public'              => true,
			'show_in_rest'        => true,
			'has_archive'         => false,
			'menu_icon'           => 'dashicons-flag',
			'menu_position'       => 6,
			'rewrite'             => array( 'slug' => 'vehi' ),
			'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
			'exclude_from_search' => false,
		)
	);
}
add_action( 'init', 'at_register_milestones' );

function at_milestone_meta_box() {
	add_meta_box(
		'at_milestone',
		'Карточка вехи',
		'at_render_milestone_meta_box',
		'at_milestone',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'at_milestone_meta_box' );

function at_render_milestone_meta_box( $post ) {
	wp_nonce_field( 'at_save_milestone', 'at_milestone_nonce' );
	$year    = get_post_meta( $post->ID, '_at_year', true );
	$date    = get_post_meta( $post->ID, '_at_date', true );
	$place   = get_post_meta( $post->ID, '_at_place', true );
	$units   = get_post_meta( $post->ID, '_at_units', true );
	$aliases = get_post_meta( $post->ID, '_at_aliases', true );
	$letter  = get_post_meta( $post->ID, '_at_letter', true );
	?>
	<p class="description">Эти поля попадают в карточку указателя и в поиск. «Также» нужно, чтобы «Дубровка» находила «Норд-Ост».</p>
	<p>
		<label for="at_year"><strong>Год</strong></label><br>
		<input type="number" class="widefat" id="at_year" name="at_year" min="1900" max="2100" value="<?php echo esc_attr( $year ); ?>">
	</p>
	<p>
		<label for="at_date"><strong>Дата</strong></label><br>
		<input type="text" class="widefat" id="at_date" name="at_date" value="<?php echo esc_attr( $date ); ?>" placeholder="23–26 октября 2002">
	</p>
	<p>
		<label for="at_place"><strong>Место</strong></label><br>
		<input type="text" class="widefat" id="at_place" name="at_place" value="<?php echo esc_attr( $place ); ?>">
	</p>
	<p>
		<label for="at_units"><strong>Подразделения</strong></label><br>
		<input type="text" class="widefat" id="at_units" name="at_units" value="<?php echo esc_attr( $units ); ?>">
	</p>
	<p>
		<label for="at_aliases"><strong>Также ищут как</strong></label><br>
		<input type="text" class="widefat" id="at_aliases" name="at_aliases" value="<?php echo esc_attr( $aliases ); ?>" placeholder="Дубровка, театральный центр">
	</p>
	<p>
		<label for="at_letter"><strong>Буква в указателе</strong></label><br>
		<input type="text" class="widefat" id="at_letter" name="at_letter" maxlength="1" value="<?php echo esc_attr( $letter ); ?>" placeholder="Н">
		<span class="description">Пусто — берётся первая буква названия.</span>
	</p>
	<?php
}

function at_save_milestone_meta( $post_id ) {
	if ( ! isset( $_POST['at_milestone_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['at_milestone_nonce'] ) ), 'at_save_milestone' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$year = isset( $_POST['at_year'] ) ? absint( wp_unslash( $_POST['at_year'] ) ) : 0;
	if ( $year >= 1900 && $year <= 2100 ) {
		update_post_meta( $post_id, '_at_year', $year );
	} else {
		delete_post_meta( $post_id, '_at_year' );
	}
	foreach ( array( 'at_date' => '_at_date', 'at_place' => '_at_place', 'at_units' => '_at_units', 'at_aliases' => '_at_aliases' ) as $field => $key ) {
		$value = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		if ( $value ) {
			update_post_meta( $post_id, $key, $value );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
	$letter = isset( $_POST['at_letter'] ) ? sanitize_text_field( wp_unslash( $_POST['at_letter'] ) ) : '';
	$letter = function_exists( 'mb_substr' ) ? mb_substr( $letter, 0, 1, 'UTF-8' ) : substr( $letter, 0, 1 );
	if ( $letter ) {
		update_post_meta( $post_id, '_at_letter', function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $letter, 'UTF-8' ) : strtoupper( $letter ) );
	} else {
		delete_post_meta( $post_id, '_at_letter' );
	}
}
add_action( 'save_post_at_milestone', 'at_save_milestone_meta' );

/**
 * @return WP_Post[]
 */
function at_milestones( $limit = -1 ) {
	$query = new WP_Query(
		array(
			'post_type'              => 'at_milestone',
			'post_status'            => 'publish',
			'posts_per_page'         => $limit > 0 ? $limit : 100,
			'meta_key'               => '_at_year',
			'orderby'                => array(
				'meta_value_num' => 'ASC',
				'title'          => 'ASC',
			),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
		)
	);
	return $query->posts;
}

function at_milestone_letter( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$custom = get_post_meta( $post->ID, '_at_letter', true );
	if ( $custom ) {
		$custom = function_exists( 'mb_substr' ) ? mb_substr( $custom, 0, 1, 'UTF-8' ) : substr( $custom, 0, 1 );
		return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $custom, 'UTF-8' ) : strtoupper( $custom );
	}
	$title = wp_strip_all_tags( get_the_title( $post ) );
	$title = preg_replace( '/^\s+/u', '', $title );
	$letter = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1, 'UTF-8' ) : substr( $title, 0, 1 );
	return function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $letter, 'UTF-8' ) : strtoupper( $letter );
}

function at_milestone_sort_letters( $groups ) {
	$order = 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
	uksort(
		$groups,
		function ( $a, $b ) use ( $order ) {
			$pa = function_exists( 'mb_strpos' ) ? mb_strpos( $order, $a, 0, 'UTF-8' ) : strpos( $order, $a );
			$pb = function_exists( 'mb_strpos' ) ? mb_strpos( $order, $b, 0, 'UTF-8' ) : strpos( $order, $b );
			$pa = false === $pa ? 99 : $pa;
			$pb = false === $pb ? 99 : $pb;
			return $pa - $pb;
		}
	);
	return $groups;
}

function at_milestone_word( $n ) {
	$n   = absint( $n );
	$n10 = $n % 10;
	$n100 = $n % 100;
	if ( 1 === $n10 && 11 !== $n100 ) {
		return 'событие';
	}
	if ( $n10 >= 2 && $n10 <= 4 && ( $n100 < 12 || $n100 > 14 ) ) {
		return 'события';
	}
	return 'событий';
}

function at_milestone_card( $post ) {
	$post = get_post( $post );
	if ( ! $post || 'at_milestone' !== $post->post_type || 'publish' !== $post->post_status ) {
		return;
	}
	$year    = get_post_meta( $post->ID, '_at_year', true );
	$date    = get_post_meta( $post->ID, '_at_date', true );
	$place   = get_post_meta( $post->ID, '_at_place', true );
	$units   = get_post_meta( $post->ID, '_at_units', true );
	$aliases = get_post_meta( $post->ID, '_at_aliases', true );
	$hay     = trim( get_the_title( $post ) . ' ' . $place . ' ' . $units . ' ' . $date . ' ' . $aliases . ' ' . get_the_excerpt( $post ) );
	?>
	<article class="vehi-card abc-pagination-post js-abc-pagination-post" data-vehi-card data-hay="<?php echo esc_attr( $hay ); ?>">
		<?php if ( $year ) : ?>
			<p class="vehi-card__year"><?php echo esc_html( $year ); ?></p>
		<?php endif; ?>
		<h3 class="vehi-card__title abc-pagination-post__title js-abc-pagination-post-title">
			<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
				<?php echo esc_html( get_the_title( $post ) ); ?>
				<?php
				$also = trim( $place . ' ' . $units . ' ' . $date . ' ' . $aliases );
				if ( $also ) :
					?>
					<span class="sr"><?php echo esc_html( $also ); ?></span>
				<?php endif; ?>
			</a>
		</h3>
		<p class="vehi-card__meta">
			<?php if ( $date ) : ?><span><?php echo esc_html( $date ); ?></span><?php endif; ?>
			<?php if ( $place ) : ?><span><?php echo esc_html( $place ); ?></span><?php endif; ?>
			<?php if ( $units ) : ?><span><?php echo esc_html( $units ); ?></span><?php endif; ?>
		</p>
		<?php if ( $aliases ) : ?>
			<p class="vehi-card__aliases">Также: <?php echo esc_html( $aliases ); ?></p>
		<?php endif; ?>
		<?php if ( has_excerpt( $post ) ) : ?>
			<p class="vehi-card__excerpt abc-pagination-post__excerpt"><?php echo esc_html( get_the_excerpt( $post ) ); ?></p>
		<?php endif; ?>
	</article>
	<?php
}

function at_ensure_milestones() {
	if ( get_option( 'at_milestones_ready' ) ) {
		at_sync_milestone_catalog();
		return;
	}

	$page = get_page_by_path( 'vehi-istorii' );
	if ( $page ) {
		$page_id = $page->ID;
	} else {
		$page_id = wp_insert_post(
			array(
				'post_title'   => 'Вехи истории',
				'post_name'    => 'vehi-istorii',
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_excerpt' => 'События, которые обязан знать каждый: операции подразделений, освобождение заложников и дни, после которых страна стала другой.',
			)
		);
	}
	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-templates/template-milestones.php' );
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
					'menu-item-title'     => 'Вехи истории',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $page_id,
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
				)
			);
		}
	}

	foreach ( at_milestone_catalog() as $item ) {
		$existing = get_page_by_path( $item['slug'], OBJECT, 'at_milestone' );
		if ( $existing ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'at_milestone',
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_name'    => $item['slug'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => '<p>' . esc_html( $item['body'] ) . '</p>',
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_at_year', (int) $item['year'] );
		update_post_meta( $id, '_at_date', $item['date'] );
		update_post_meta( $id, '_at_place', $item['place'] );
		update_post_meta( $id, '_at_units', $item['units'] );
		if ( ! empty( $item['aliases'] ) ) {
			update_post_meta( $id, '_at_aliases', $item['aliases'] );
		}
	}

	if ( ! get_option( 'at_milestones_rewrite' ) ) {
		flush_rewrite_rules( false );
		update_option( 'at_milestones_rewrite', 1 );
	}
	update_option( 'at_milestones_ready', 1 );
	update_option( 'at_milestones_catalog', 2 );
}
add_action( 'init', 'at_ensure_milestones', 20 );

/**
 * Подпись поиска ABC Pagination на странице вех.
 *
 * @param string $translated Translated text.
 * @param string $text       Original text.
 * @param string $domain     Text domain.
 * @return string
 */
function at_milestone_search_label( $translated, $text, $domain ) {
	if ( 'abc-pagination' === $domain && 'Search...' === $text ) {
		return 'Норд-Ост, Дубровка, Беслан';
	}
	return $translated;
}

/**
 * Insert catalog items that are not on the site yet. Does not overwrite edits.
 *
 * @return int
 */
function at_install_missing_milestones() {
	$added = 0;
	foreach ( at_milestone_catalog() as $item ) {
		$existing = get_page_by_path( $item['slug'], OBJECT, 'at_milestone' );
		if ( $existing ) {
			if ( ! empty( $item['aliases'] ) && ! get_post_meta( $existing->ID, '_at_aliases', true ) ) {
				update_post_meta( $existing->ID, '_at_aliases', $item['aliases'] );
			}
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'at_milestone',
				'post_status'  => 'publish',
				'post_title'   => $item['title'],
				'post_name'    => $item['slug'],
				'post_excerpt' => $item['excerpt'],
				'post_content' => '<p>' . esc_html( $item['body'] ) . '</p>',
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_at_year', (int) $item['year'] );
		update_post_meta( $id, '_at_date', $item['date'] );
		update_post_meta( $id, '_at_place', $item['place'] );
		update_post_meta( $id, '_at_units', $item['units'] );
		if ( ! empty( $item['aliases'] ) ) {
			update_post_meta( $id, '_at_aliases', $item['aliases'] );
		}
		$added++;
	}
	return $added;
}

function at_sync_milestone_catalog() {
	if ( (int) get_option( 'at_milestones_catalog' ) >= 2 ) {
		return;
	}
	at_install_missing_milestones();
	update_option( 'at_milestones_catalog', 2 );
}

function at_milestone_columns( $columns ) {
	$next = array();
	foreach ( $columns as $key => $label ) {
		$next[ $key ] = $label;
		if ( 'title' === $key ) {
			$next['at_year']  = 'Год';
			$next['at_place'] = 'Место';
			$next['at_units'] = 'Подразделения';
		}
	}
	return $next;
}
add_filter( 'manage_at_milestone_posts_columns', 'at_milestone_columns' );

function at_milestone_column( $column, $post_id ) {
	if ( 'at_year' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_at_year', true ) );
	} elseif ( 'at_place' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_at_place', true ) );
	} elseif ( 'at_units' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_at_units', true ) );
	}
}
add_action( 'manage_at_milestone_posts_custom_column', 'at_milestone_column', 10, 2 );

function at_milestone_sortable( $columns ) {
	$columns['at_year'] = 'year';
	return $columns;
}
add_filter( 'manage_edit-at_milestone_sortable_columns', 'at_milestone_sortable' );

function at_milestone_admin_order( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || 'at_milestone' !== $query->get( 'post_type' ) ) {
		return;
	}
	if ( 'year' === $query->get( 'orderby' ) ) {
		$query->set( 'meta_key', '_at_year' );
		$query->set( 'orderby', 'meta_value_num' );
	}
}
add_action( 'pre_get_posts', 'at_milestone_admin_order' );

function at_milestone_catalog_menu() {
	add_submenu_page(
		'edit.php?post_type=at_milestone',
		'Каталог вех',
		'Каталог и установка',
		'edit_posts',
		'at-milestone-catalog',
		'at_render_milestone_catalog_page'
	);
}
add_action( 'admin_menu', 'at_milestone_catalog_menu' );

function at_handle_milestone_install() {
	if ( empty( $_POST['at_milestone_install'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	check_admin_referer( 'at_milestone_install' );
	$action = sanitize_key( wp_unslash( $_POST['at_milestone_install'] ) );
	if ( 'missing' === $action ) {
		$n   = at_install_missing_milestones();
		$msg = $n ? sprintf( 'Установлено событий: %d', $n ) : 'Все события каталога уже установлены.';
	} else {
		$msg = 'Неизвестное действие.';
	}
	set_transient( 'at_milestone_install_notice', $msg, 30 );
	wp_safe_redirect( admin_url( 'edit.php?post_type=at_milestone&page=at-milestone-catalog' ) );
	exit;
}
add_action( 'admin_init', 'at_handle_milestone_install' );

function at_milestone_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'edit-at_milestone' !== $screen->id ) {
		return;
	}
	$url = admin_url( 'edit.php?post_type=at_milestone&page=at-milestone-catalog' );
	echo '<div class="notice notice-info"><p>Карточка события правится здесь: год, место, подразделения и имена для поиска. Готовый набор — в <a href="' . esc_url( $url ) . '">каталоге</a>.</p></div>';
}
add_action( 'admin_notices', 'at_milestone_admin_notice' );

function at_render_milestone_catalog_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}
	$notice = get_transient( 'at_milestone_install_notice' );
	if ( $notice ) {
		delete_transient( 'at_milestone_install_notice' );
	}
	$installed = 0;
	$rows      = array();
	foreach ( at_milestone_catalog() as $item ) {
		$existing = get_page_by_path( $item['slug'], OBJECT, 'at_milestone' );
		if ( $existing ) {
			$installed++;
		}
		$rows[] = array( $item, $existing );
	}
	?>
	<div class="wrap">
		<h1>Каталог вех</h1>
		<p>Установка добавляет только недостающие события и не затирает уже отредактированные. Новую веху можно создать вручную.</p>
		<?php if ( $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
		<?php endif; ?>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=at_milestone' ) ); ?>">Добавить свою веху</a>
			<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=at_milestone' ) ); ?>">Все события</a>
		</p>
		<form method="post">
			<?php wp_nonce_field( 'at_milestone_install' ); ?>
			<input type="hidden" name="at_milestone_install" value="missing">
			<?php submit_button( 'Установить недостающие (' . ( count( $rows ) - $installed ) . ')', 'secondary', 'submit', false ); ?>
		</form>
		<table class="widefat striped" style="margin-top:16px;max-width:980px">
			<thead>
				<tr><th>Событие</th><th>Год</th><th>Место</th><th>Статус</th></tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $row ) : ?>
					<?php list( $item, $existing ) = $row; ?>
					<tr>
						<td><strong><?php echo esc_html( $item['title'] ); ?></strong></td>
						<td><?php echo esc_html( (string) $item['year'] ); ?></td>
						<td><?php echo esc_html( $item['place'] ); ?></td>
						<td>
							<?php if ( $existing ) : ?>
								<a href="<?php echo esc_url( get_edit_post_link( $existing->ID ) ); ?>">Открыть</a>
							<?php else : ?>
								Не установлено
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}
