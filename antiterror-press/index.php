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
			<p class="empty-note">Здесь пока нет материалов.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
