<?php
/**
 * Результаты поиска.
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Поиск</p>
			<h1><?php echo esc_html( get_search_query() ? get_search_query() : 'Поиск' ); ?></h1>
			<p class="deck"><?php echo esc_html( at_ru_plural( (int) $wp_query->found_posts, 'материал', 'материала', 'материалов' ) ); ?></p>
		</header>
		<?php if ( have_posts() ) : ?>
			<?php
			$feed_page  = max( 1, (int) get_query_var( 'paged' ) );
			$feed_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
			at_feed_open(
				array(
					'context' => 'search',
					'page'    => $feed_page,
					'pages'   => $feed_pages,
					'search'  => get_search_query(),
				)
			);
			?>
			<div class="list" id="at-feed-list" data-feed-list>
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/post', 'row', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
			<?php the_posts_pagination( array( 'prev_text' => 'Назад', 'next_text' => 'Далее' ) ); ?>
			<?php at_feed_close( $feed_page, $feed_pages ); ?>
		<?php else : ?>
			<p class="empty-note">Ничего не нашли. Проверьте написание или откройте список бойцов.</p>
			<p><a class="btn" href="<?php echo esc_url( at_page_url( 'geroi-antiterrora' ) ); ?>">К бойцам</a></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
