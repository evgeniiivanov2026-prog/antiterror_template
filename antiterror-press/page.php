<?php
/**
 * Обычная страница.
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<header class="page-hero">
					<p class="kicker">Журнал</p>
					<h1><?php the_title(); ?></h1>
				</header>
				<div class="prose">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</div>
</main>
<?php
get_footer();
