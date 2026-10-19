<?php
/**
 * Главная: обложка, лента, бойцы, конкурс.
 *
 * @package antiterror-press
 */

get_header();

// Prefer a pinned post for the top block
$pinned_query = new WP_Query(
	at_news_args(
		array(
			'posts_per_page' => 1,
			'meta_key'       => '_at_pin_top',
			'meta_value'     => '1',
			'no_found_rows'  => true,
		)
	)
);
$pinned = $pinned_query->have_posts() ? $pinned_query->posts[0] : null;
wp_reset_postdata();

$home = new WP_Query(
	at_news_args(
		array(
			'posts_per_page' => 10,
			'no_found_rows'  => true,
			'post__not_in'   => $pinned ? array( $pinned->ID ) : array(),
		)
	)
);
if ( ! $home->have_posts() && ! $pinned ) {
	$home = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}
$posts = $home->posts;
if ( $pinned ) {
	array_unshift( $posts, $pinned );
}
$feature = $posts ? array_shift( $posts ) : null;
$side    = array_splice( $posts, 0, 3 );
$rest    = $posts;
$fighters = array_slice( at_sorted_fighters( -1 ), 0, 6 );
?>
<main id="content" class="page">
	<div class="wrap">
		<?php if ( ! $feature ) : ?>
			<section class="notice">
				<p class="kicker">Журнал</p>
				<h1>Материалы появятся здесь</h1>
				<p class="deck" style="margin-top:14px;">Опубликуйте первую запись. Бойцов удобно собирать в рубрике с ярлыком heroes.</p>
			</section>
		<?php else : ?>
			<section class="lead" aria-label="Главные материалы">
				<article class="feature">
					<?php $feature_media = at_media_html( $feature->ID, 'at-portrait', array( 'fetchpriority' => 'high', 'loading' => 'eager' ) ); ?>
					<?php if ( $feature_media ) : ?>
					<a class="feature__media" href="<?php echo esc_url( get_permalink( $feature ) ); ?>">
						<?php echo $feature_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
					<?php endif; ?>
					<div class="feature__copy">
						<?php
						$kicker = at_kicker( $feature->ID );
						$is_pinned = (bool) get_post_meta( $feature->ID, '_at_pin_top', true );
						?>
						<?php if ( $is_pinned ) : ?><p class="kicker">Закреплено</p><?php elseif ( $kicker ) : ?><p class="kicker"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
						<h1 class="feature__title"><a href="<?php echo esc_url( get_permalink( $feature ) ); ?>"><?php echo esc_html( get_the_title( $feature ) ); ?></a></h1>
						<p class="feature__deck"><?php echo esc_html( wp_trim_words( get_the_excerpt( $feature ), 32 ) ); ?></p>
						<?php at_meta_line( $feature->ID ); ?>
					</div>
				</article>
				<?php if ( $side ) : ?>
				<div class="side-stack">
					<?php foreach ( $side as $item ) : ?>
						<a class="side-story" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
							<?php
							$side_media = at_media_html( $item->ID, 'at-thumb' );
							if ( $side_media ) {
								echo $side_media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
							<span>
								<?php $kicker = at_kicker( $item->ID ); ?>
								<?php if ( $kicker ) : ?><span class="kicker"><?php echo esc_html( $kicker ); ?></span><?php endif; ?>
								<span class="side-story__title"><?php echo esc_html( get_the_title( $item ) ); ?></span>
								<?php at_meta_line( $item->ID ); ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( $rest ) : ?>
			<section class="section" aria-labelledby="journal-heading">
				<div class="section__head">
					<h2 id="journal-heading">Лента</h2>
					<a class="section__link" href="<?php echo esc_url( at_page_url( 'zhurnal' ) ); ?>">Весь журнал</a>
				</div>
				<div class="cards">
					<?php
					foreach ( $rest as $item ) {
						get_template_part( 'template-parts/story', 'card', array( 'post_id' => $item->ID ) );
					}
					?>
				</div>
			</section>
		<?php endif; ?>
	</div>

	<?php
	// Блок событий БПЛА
	$bpla_query = new WP_Query(
		array(
			'post_type'      => 'bpla_event',
			'post_status'    => 'publish',
			'posts_per_page' => 15,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		)
	);
	$bpla_markers = array();
	$bpla_list    = array();
	if ( $bpla_query->have_posts() ) {
		while ( $bpla_query->have_posts() ) {
			$bpla_query->the_post();
			$pid    = get_the_ID();
			$lat    = get_post_meta( $pid, '_bpla_lat', true );
			$lng    = get_post_meta( $pid, '_bpla_lng', true );
			$status = get_post_meta( $pid, '_bpla_status', true );
			$type   = get_post_meta( $pid, '_bpla_type', true );
			$region = get_post_meta( $pid, '_bpla_region', true );
			$time   = get_post_meta( $pid, '_bpla_time', true );
			$status_labels = array(
				'in_flight'       => 'В полёте',
				'intercepted'     => 'Перехвачен',
				'destroyed'       => 'Уничтожен',
				'attack'          => 'Атака',
				'reconnaissance'  => 'Разведка',
				'alert'           => 'Тревога',
			);
			$status_label = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status;
			$item = array(
				'id'         => $pid,
				'title'      => get_the_title(),
				'url'        => get_permalink(),
				'lat'        => $lat ? (float) $lat : null,
				'lng'        => $lng ? (float) $lng : null,
				'status'     => $status_label,
				'status_key' => $status,
				'type'       => $type,
				'region'     => $region,
				'time'       => $time ? $time : get_the_date( 'c' ),
				'excerpt'    => wp_trim_words( get_the_excerpt(), 18 ),
			);
			$bpla_list[] = $item;
			if ( $item['lat'] && $item['lng'] ) {
				$bpla_markers[] = $item;
			}
		}
		wp_reset_postdata();
	}
	// Localize for JS (Leaflet already enqueued)
	if ( ! empty( $bpla_markers ) || ! empty( $bpla_list ) ) {
		wp_localize_script(
			'yandex-maps',
			'atBpla',
			array(
				'markers' => $bpla_markers,
				'list'    => $bpla_list,
			)
		);
	}
	?>
	<?php
	$bpla_total = count( $bpla_list );
	$bpla_destroyed = 0;
	$bpla_regions = array();
	foreach ( $bpla_list as $ev ) {
		if ( in_array( $ev['status_key'], array( 'destroyed', 'intercepted', 'attack' ), true ) ) {
			$bpla_destroyed++;
		}
		if ( ! empty( $ev['region'] ) ) {
			$bpla_regions[ $ev['region'] ] = true;
		}
	}
	$bpla_region_count = count( $bpla_regions );
	?>
	<section class="bpla-section" aria-labelledby="bpla-heading">
		<div class="wrap">
			<header class="bpla-header">
				<div>
					<p class="kicker">Мониторинг</p>
					<h2 id="bpla-heading">События БПЛА</h2>
				</div>
				<div class="bpla-meta">
					<span class="bpla-update">По сводкам Министерства обороны</span>
				</div>
			</header>

			<?php if ( $bpla_total ) : ?>
			<div class="bpla-stats" aria-label="Сводка">
				<div class="bpla-stat">
					<span class="bpla-stat__num"><?php echo esc_html( $bpla_total ); ?></span>
					<span class="bpla-stat__label">событий</span>
				</div>
				<div class="bpla-stat">
					<span class="bpla-stat__num"><?php echo esc_html( $bpla_destroyed ); ?></span>
					<span class="bpla-stat__label">перехватов</span>
				</div>
				<div class="bpla-stat">
					<span class="bpla-stat__num"><?php echo esc_html( $bpla_region_count ); ?></span>
					<span class="bpla-stat__label">регионов</span>
				</div>
			</div>
			<?php endif; ?>

			<div class="bpla-filters" role="tablist" aria-label="Фильтр событий">
				<button type="button" class="bpla-filter is-active" role="tab" aria-selected="true" data-filter="all">Все</button>
				<button type="button" class="bpla-filter" role="tab" aria-selected="false" data-filter="destroyed">Уничтожены</button>
				<button type="button" class="bpla-filter" role="tab" aria-selected="false" data-filter="intercepted">Перехвачены</button>
				<button type="button" class="bpla-filter" role="tab" aria-selected="false" data-filter="in_flight">В полёте</button>
				<button type="button" class="bpla-filter" role="tab" aria-selected="false" data-filter="alert">Тревога</button>
			</div>

			<?php if ( empty( $bpla_list ) ) : ?>
				<div class="bpla-empty">
					<p>Событий пока нет</p>
					<span>Данные появятся после автоматического парсинга сводок МО или ручного добавления.</span>
				</div>
			<?php else : ?>
				<ul class="bpla-list">
					<?php foreach ( array_slice( $bpla_list, 0, 12 ) as $ev ) : ?>
						<li class="bpla-item" data-status="<?php echo esc_attr( $ev['status_key'] ? $ev['status_key'] : 'unknown' ); ?>">
							<button type="button" class="bpla-item__toggle" aria-expanded="false">
								<span class="bpla-item__accent bpla-accent--<?php echo esc_attr( $ev['status_key'] ? $ev['status_key'] : 'unknown' ); ?>"></span>
								<span class="bpla-item__body">
									<span class="bpla-item__row">
										<strong class="bpla-item__title"><?php echo esc_html( $ev['title'] ); ?></strong>
										<time class="bpla-item__time"><?php echo esc_html( $ev['time'] ? date_i18n( 'd.m H:i', strtotime( $ev['time'] ) ) : '' ); ?></time>
									</span>
									<span class="bpla-item__meta">
										<?php if ( $ev['region'] ) : ?><span><?php echo esc_html( $ev['region'] ); ?></span><?php endif; ?>
										<?php if ( $ev['status'] ) : ?><span class="bpla-pill bpla-pill--<?php echo esc_attr( $ev['status_key'] ? $ev['status_key'] : 'unknown' ); ?>"><?php echo esc_html( $ev['status'] ); ?></span><?php endif; ?>
									</span>
								</span>
								<span class="bpla-item__chevron" aria-hidden="true">↓</span>
							</button>
							<div class="bpla-item__details" hidden>
								<?php if ( $ev['excerpt'] ) : ?>
									<p><?php echo esc_html( $ev['excerpt'] ); ?></p>
								<?php endif; ?>
								<?php if ( $ev['type'] ) : ?>
									<p class="bpla-item__type">Тип: <?php echo esc_html( $ev['type'] ); ?></p>
								<?php endif; ?>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$vehi_order = array( 'nord-ost', 'beslan', 'budyonnovsk', 'ordzhonikidze', 'kizlyar', 'krokus', 'alfa' );
	$vehi_by    = array();
	foreach ( at_milestones() as $vehi_item ) {
		$vehi_by[ $vehi_item->post_name ] = $vehi_item;
	}
	$vehi_home = array();
	foreach ( $vehi_order as $vehi_slug ) {
		if ( isset( $vehi_by[ $vehi_slug ] ) ) {
			$vehi_home[] = $vehi_by[ $vehi_slug ];
		}
	}
	?>
	<?php if ( $vehi_home ) : ?>
	<section class="vehi-home" aria-labelledby="vehi-heading">
		<div class="wrap">
			<div class="section__head">
				<h2 id="vehi-heading">Вехи истории</h2>
				<a class="section__link" href="<?php echo esc_url( at_page_url( 'vehi-istorii' ) ); ?>">Весь указатель</a>
			</div>
			<p class="vehi-home__lead">Имена, которые нужно знать: Дубровка и «Норд-Ост», Беслан, Будённовск. Хронология операций подразделений, которые освобождали заложников.</p>
			<div class="vehi-rail vehi-rail--home">
				<?php foreach ( $vehi_home as $vehi_item ) : ?>
					<a class="vehi-rail__item" href="<?php echo esc_url( get_permalink( $vehi_item ) ); ?>">
						<span class="vehi-rail__year"><?php echo esc_html( get_post_meta( $vehi_item->ID, '_at_year', true ) ); ?></span>
						<span class="vehi-rail__name"><?php echo esc_html( get_the_title( $vehi_item ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="band" aria-labelledby="fighters-heading">
		<div class="wrap band__inner">
			<div class="section__head">
				<h2 id="fighters-heading">Бойцы антитеррора</h2>
				<a class="section__link" href="<?php echo esc_url( at_page_url( 'geroi-antiterrora' ) ); ?>">Весь список</a>
			</div>
			<p class="band__lead">Алфавитный список людей, о которых пишет журнал. Звание и подразделение берутся из досье записи, буква — из фамилии.</p>
			<?php if ( $fighters ) : ?>
				<div class="roster">
					<?php foreach ( $fighters as $person ) : ?>
						<a class="person-card" href="<?php echo esc_url( get_permalink( $person ) ); ?>">
							<span class="person-card__photo">
								<?php
								if ( has_post_thumbnail( $person ) ) {
									echo get_the_post_thumbnail( $person, 'at-portrait', array( 'alt' => '' ) );
								}
								?>
							</span>
							<span class="person-card__name"><?php echo esc_html( get_the_title( $person ) ); ?></span>
							<?php $rank = get_post_meta( $person->ID, '_at_rank', true ); ?>
							<?php if ( $rank ) : ?><span class="person-card__rank"><?php echo esc_html( $rank ); ?></span><?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<p>Пока пусто. Добавьте записи в рубрику heroes.</p>
			<?php endif; ?>
		</div>
	</section>

	<?php
	$groups        = at_media_groups();
	$staff_preview = array_slice( $groups['staff'], 0, 4 );
	$story_preview = array();
	foreach ( $groups['topics'] as $topic ) {
		foreach ( $topic['items'] as $item ) {
			$story_preview[] = $item;
			if ( count( $story_preview ) >= 4 ) {
				break 2;
			}
		}
	}
	?>
	<?php if ( $staff_preview || $story_preview ) : ?>
	<div class="wrap">
		<section class="section" aria-labelledby="archive-heading">
			<div class="section__head">
				<h2 id="archive-heading">Архив фото и видео</h2>
				<a class="section__link" href="<?php echo esc_url( at_page_url( 'arhiv' ) ); ?>">Весь архив</a>
			</div>
			<div class="home-shelves">
				<?php if ( $staff_preview ) : ?>
					<div class="home-shelf">
						<div class="shelf__head">
							<h3>Сотрудники</h3>
							<a class="section__link" href="<?php echo esc_url( at_page_url( 'arhiv' ) . '#sotrudniki' ); ?>">Все портреты</a>
						</div>
						<div class="vault vault--home">
							<?php
							foreach ( $staff_preview as $item ) {
								get_template_part( 'template-parts/media', 'tile', array( 'item' => $item ) );
							}
							?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( $story_preview ) : ?>
					<div class="home-shelf">
						<div class="shelf__head">
							<h3>По темам</h3>
							<a class="section__link" href="<?php echo esc_url( at_page_url( 'arhiv' ) . '#materialy' ); ?>">Все материалы</a>
						</div>
						<div class="vault vault--home">
							<?php
							foreach ( $story_preview as $item ) {
								get_template_part( 'template-parts/media', 'tile', array( 'item' => $item ) );
							}
							?>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
	<?php endif; ?>

	<div class="wrap">
		<section class="contest" aria-labelledby="contest-heading">
			<div>
				<p class="kicker">Конкурс</p>
				<h2 id="contest-heading">Герои среди нас</h2>
				<p class="contest__text">Три номинации для тех, кто хочет сказать о мужестве рядом: слово, образ и рассказ о конкретном человеке.</p>
				<a class="btn" href="<?php echo esc_url( at_page_url( 'geroi-sredi-nas' ) ); ?>">Условия</a>
			</div>
			<div class="noms">
				<article class="nom"><p class="kicker">01</p><strong>Слово о герое</strong><p>Письмо или эссе до 3000 знаков.</p></article>
				<article class="nom"><p class="kicker">02</p><strong>Образ героя</strong><p>Рисунок, фото или видео до минуты.</p></article>
				<article class="nom"><p class="kicker">03</p><strong>Герой рядом</strong><p>Рассказ о человеке, которого знаете лично.</p></article>
			</div>
		</section>
	</div>
</main>
<?php
wp_reset_postdata();
get_footer();
