<?php
/**
 * Страница не найдена.
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<section class="notice">
			<p class="kicker">404</p>
			<h1>Этой страницы нет</h1>
			<p class="deck">Ссылка устарела или адрес набран с ошибкой. Поиск и список бойцов остаются на месте.</p>
			<div class="notice__actions">
				<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">На главную</a>
				<a class="btn btn--ghost" href="<?php echo esc_url( at_page_url( 'geroi-antiterrora' ) ); ?>">Бойцы</a>
			</div>
		</section>
	</div>
</main>
<?php
get_footer();
