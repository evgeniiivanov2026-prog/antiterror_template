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
		<?php if ( $query->have_posts() ) : ?>
			<?php
			at_feed_open(
				array(
					'context' => 'journal',
					'page'    => $paged,
					'pages'   => (int) $query->max_num_pages,
				)
			);
			at_feed_tabs( at_feed_terms() );
			?>
			<div class="list" id="at-feed-list" data-feed-list>
				<?php
				while ( $query->have_posts() ) {
					$query->the_post();
					get_template_part( 'template-parts/post', 'row', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
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
				echo '<nav class="pagination" aria-label="Страницы">' . wp_kses_post( $links ) . '</nav>';
			}
			at_feed_close( $paged, (int) $query->max_num_pages );
			?>
		<?php else : ?>
			<p class="empty-note">Записей пока нет.</p>
		<?php endif; ?>
	</div>
</main>
<?php
wp_reset_postdata();
get_footer();
