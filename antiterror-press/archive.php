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
			<div class="list">
				<?php
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/post', 'row', array( 'post_id' => get_the_ID() ) );
				}
				?>
			</div>
			<?php the_posts_pagination( array( 'prev_text' => 'Назад', 'next_text' => 'Далее' ) ); ?>
		<?php else : ?>
			<p class="empty-note">В этом разделе пока пусто.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
