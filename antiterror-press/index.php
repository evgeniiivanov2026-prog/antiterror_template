<?php
/**
 * Запасной список записей.
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Журнал</p>
			<h1><?php echo is_search() ? 'Поиск' : 'Лента'; ?></h1>
		</header>
		<?php if ( have_posts() ) : ?>
			<?php
			$feed_page  = max( 1, (int) get_query_var( 'paged' ) );
			$feed_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
			at_feed_open(
				array(
					'context' => 'journal',
					'page'    => $feed_page,
					'pages'   => $feed_pages,
				)
			);
			at_feed_tabs( at_feed_terms() );
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
			<p class="empty-note">Здесь пока нет материалов.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
