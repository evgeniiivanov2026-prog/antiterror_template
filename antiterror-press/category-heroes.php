<?php
/**
 * Рубрика героев открывается как список бойцов.
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Досье</p>
			<h1>Бойцы антитеррора</h1>
			<p class="deck">Тот же раздел, что на сайте: люди по алфавиту. Буква берётся из фамилии в досье, а если её нет — из заголовка.</p>
		</header>
		<?php get_template_part( 'template-parts/directory', null, array( 'posts' => at_sorted_fighters() ) ); ?>
	</div>
</main>
<?php
get_footer();
