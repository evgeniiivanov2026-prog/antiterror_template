<?php
/**
 * Архив рубрики, метки, даты.
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Рубрика</p>
			<h1><?php the_archive_title(); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<div class="deck"><?php the_archive_description(); ?></div>
			<?php endif; ?>
		</header>
		<?php if ( have_posts() ) : ?>
			<?php
			$feed_page  = max( 1, (int) get_query_var( 'paged' ) );
			$feed_pages = isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 1;
			$feed_tax   = '';
			$feed_term  = 0;
			if ( is_category() ) {
				$feed_tax  = 'category';
				$feed_term = (int) get_queried_object_id();
			} elseif ( is_tag() ) {
				$feed_tax  = 'post_tag';
				$feed_term = (int) get_queried_object_id();
			}
			$feed_ready = ! is_post_type_archive() && ! is_tax();
			if ( $feed_ready ) {
				at_feed_open(
					array(
						'context'  => 'archive',
						'page'     => $feed_page,
						'pages'    => $feed_pages,
						'taxonomy' => $feed_tax,
						'term'     => $feed_term,
						'year'     => (int) get_query_var( 'year' ),
						'month'    => (int) get_query_var( 'monthnum' ),
						'day'      => (int) get_query_var( 'day' ),
						'author'   => is_author() ? (int) get_queried_object_id() : 0,
					)
				);
			}
			?>
			<div class="list" <?php echo $feed_ready ? 'id="at-feed-list" data-feed-list' : ''; ?>>
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/post', 'row', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
			<?php the_posts_pagination( array( 'prev_text' => 'Назад', 'next_text' => 'Далее' ) ); ?>
			<?php
			if ( $feed_ready ) {
				at_feed_close( $feed_page, $feed_pages );
			}
			?>
		<?php else : ?>
			<p class="empty-note">В этом разделе пока пусто.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
