<?php
/**
 * Письмо в редакцию.
 *
 * Template Name: Написать нам
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap narrow">
		<header class="page-hero">
			<p class="kicker">Редакция</p>
			<h1><?php the_title(); ?></h1>
			<p class="deck">Коротко напишите, о чём материал или вопрос. Ответ придёт на почту, которую вы укажете.</p>
		</header>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( get_the_content() ) ) {
				echo '<div class="prose">';
				the_content();
				echo '</div>';
			}
		endwhile;
		get_template_part( 'template-parts/contact', 'form', array( 'topic' => 'Письмо в редакцию' ) );
		?>
	</div>
</main>
<?php
get_footer();
