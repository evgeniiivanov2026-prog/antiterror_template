<?php
/**
 * Список бойцов по алфавиту.
 *
 * Template Name: Бойцы антитеррора
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Досье</p>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php else : ?>
				<p class="deck">Люди, о которых пишет журнал, по алфавиту. Поиск смотрит фамилию, звание и подразделение.</p>
			<?php endif; ?>
		</header>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( get_the_content() ) {
				echo '<div class="prose narrow">';
				the_content();
				echo '</div>';
			}
		endwhile;
		get_template_part( 'template-parts/directory', null, array( 'posts' => at_sorted_fighters() ) );
		?>
	</div>
</main>
<?php
get_footer();
