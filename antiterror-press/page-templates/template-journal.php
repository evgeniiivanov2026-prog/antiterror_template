<?php
/**
 * Лента всех материалов.
 *
 * Template Name: Журнал
 *
 * @package antiterror-press
 */

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$query = new WP_Query(
	at_news_args(
		array(
			'posts_per_page' => 12,
			'paged'          => $paged,
		)
	)
);
add_action(
	'wp_head',
	static function () use ( $query, $paged ) {
		$base = get_permalink();
		if ( $paged > 1 ) {
			$prev = 2 === $paged ? $base : trailingslashit( $base ) . 'page/' . ( $paged - 1 ) . '/';
			echo '<link rel="prev" href="' . esc_url( $prev ) . '">' . "\n";
		}
		if ( $paged < (int) $query->max_num_pages ) {
			$next = trailingslashit( $base ) . 'page/' . ( $paged + 1 ) . '/';
			echo '<link rel="next" href="' . esc_url( $next ) . '">' . "\n";
		}
	}
);
wp_reset_postdata();
$page_url = get_permalink();
get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Лента</p>
			<h1><?php the_title(); ?></h1>
			<p class="deck">Новости журнала, свежие сверху. Досье сотрудников собрано отдельно, в списке бойцов.</p>
		</header>
		<?php $feed_terms = at_journal_filter_terms(); ?>
		<div class="feed" data-feed data-feed-page="<?php echo (int) $paged; ?>" data-feed-max="<?php echo (int) $query->max_num_pages; ?>">
			<div class="feed-tools">
				<div class="filters" role="tablist" aria-label="Рубрики журнала" data-feed-tabs>
					<button class="filter is-on" type="button" role="tab" aria-selected="true" data-feed-cat="0">Все</button>
					<?php foreach ( $feed_terms as $term ) : ?>
						<button class="filter" type="button" role="tab" aria-selected="false" data-feed-cat="<?php echo (int) $term->term_id; ?>">
							<?php echo esc_html( $term->name ); ?>
							<span><?php echo (int) $term->count; ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<label class="finder">
					<span>Найти в ленте</span>
					<input type="search" data-feed-search placeholder="Заголовок или фрагмент" autocomplete="off">
				</label>
			</div>
			<p class="directory__count" data-feed-count><?php echo esc_html( at_ru_plural( (int) $query->found_posts, 'материал', 'материала', 'материалов' ) ); ?></p>
		<?php if ( $query->have_posts() ) : ?>
			<div class="list" data-feed-list>
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					get_template_part( 'template-parts/post', 'row', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
			<p class="empty-note" data-feed-empty hidden>В этой рубрике пока нет материалов.</p>
			<button class="btn btn--ghost feed-more" type="button" data-feed-more hidden>Ещё материалы</button>
			<?php
			$links = paginate_links(
				array(
					'base'      => trailingslashit( $page_url ) . '%_%',
					'format'    => 'page/%#%/',
					'total'     => (int) $query->max_num_pages,
					'current'   => $paged,
					'prev_text' => 'Назад',
					'next_text' => 'Далее',
				)
			);
			if ( $links ) {
				echo '<nav class="pagination" data-feed-pages aria-label="Страницы">' . wp_kses_post( $links ) . '</nav>';
			}
			?>
		<?php else : ?>
			<div class="list" data-feed-list></div>
			<p class="empty-note" data-feed-empty>Записей пока нет.</p>
			<button class="btn btn--ghost feed-more" type="button" data-feed-more hidden>Ещё материалы</button>
		<?php endif; ?>
		</div>
	</div>
</main>
<?php
wp_reset_postdata();
get_footer();
