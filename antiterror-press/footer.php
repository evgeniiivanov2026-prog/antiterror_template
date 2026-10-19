<?php
/**
 * Подвал.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<dialog class="lightbox" data-lightbox aria-label="Просмотр">
	<div class="lightbox__top">
		<button class="lightbox__nav" type="button" data-lightbox-prev>Назад</button>
		<button class="lightbox__close" type="button" data-lightbox-close>Закрыть</button>
		<button class="lightbox__nav" type="button" data-lightbox-next>Дальше</button>
	</div>
	<div class="lightbox__stage" data-lightbox-stage></div>
	<div class="lightbox__bar">
		<div>
			<p class="kicker" data-lightbox-kicker></p>
			<p class="lightbox__title" data-lightbox-title></p>
		</div>
		<a class="section__link" data-lightbox-link href="#">К материалу</a>
	</div>
</dialog>

<dialog class="contact-dialog" data-contact-dialog aria-labelledby="contact-dialog-title">
	<div class="contact-dialog__panel">
		<button class="search-close" type="button" data-contact-close>Закрыть</button>
		<p class="kicker">Редакция</p>
		<h2 id="contact-dialog-title">Написать нам</h2>
		<p class="deck">Коротко напишите, о чём материал или вопрос. Ответ придёт на почту, которую вы укажете.</p>
		<?php get_template_part( 'template-parts/contact', 'form', array( 'topic' => 'Письмо в редакцию' ) ); ?>
	</div>
</dialog>

<?php if ( ! is_page_template( 'page-templates/template-contact.php' ) ) : ?>
<section class="colophon" aria-labelledby="colophon-heading">
	<div class="wrap colophon__inner">
		<div>
			<p class="kicker">Редакция</p>
			<h2 id="colophon-heading">Написать в номер</h2>
			<p>Короткое письмо о материале, вопросе или инициативе. Ответ придёт на почту, которую вы укажете.</p>
		</div>
		<div class="colophon__actions">
			<button class="btn" type="button" data-contact-open data-contact-topic="Письмо в редакцию">Написать</button>
			<a class="btn btn--ghost" href="<?php echo esc_url( at_page_url( 'fond-iniciativ' ) ); ?>">Фонд инициатив</a>
		</div>
	</div>
</section>
<?php endif; ?>

<footer class="footer">
	<div class="wrap">
		<div class="footer__grid">
			<div>
				<p class="kicker">Журнал</p>
				<p class="footer__name"><?php bloginfo( 'name' ); ?></p>
				<p class="footer__lead"><?php bloginfo( 'description' ); ?></p>
				<?php at_social_links( 'footer__social' ); ?>
			</div>
			<div>
				<h2>Разделы</h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'fallback_cb'    => 'at_fallback_menu',
						'depth'          => 1,
					)
				);
				?>
			</div>
			<div>
				<h2>Рубрики</h2>
				<ul>
					<?php
					$cats = get_categories(
						array(
							'hide_empty' => true,
							'number'     => 6,
						)
					);
					if ( $cats ) {
						foreach ( $cats as $cat ) {
							echo '<li><a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a></li>';
						}
					} else {
						echo '<li><a href="' . esc_url( at_page_url( 'zhurnal' ) ) . '">Журнал</a></li>';
					}
					?>
				</ul>
			</div>
		</div>
		<div class="footer__base">
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
			<p><a href="<?php echo esc_url( at_page_url( 'sitemap' ) ); ?>">Карта сайта</a></p>
		</div>
	</div>
</footer>
<button class="desk-write" type="button" data-contact-open data-contact-topic="Письмо в редакцию" hidden>Написать</button>
<button class="to-top" type="button" data-to-top hidden>Наверх</button>
<nav class="dock" aria-label="Быстрые разделы">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"<?php echo is_front_page() ? ' aria-current="page"' : ''; ?>>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5V20H4v-8.5Z" stroke="currentColor" stroke-width="1.6"/></svg>
		Главная
	</a>
	<a href="<?php echo esc_url( at_page_url( 'zhurnal' ) ); ?>"<?php echo is_page( 'zhurnal' ) ? ' aria-current="page"' : ''; ?>>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 5h12v14H6V5Z" stroke="currentColor" stroke-width="1.6"/><path d="M9 9h6M9 13h6" stroke="currentColor" stroke-width="1.6"/></svg>
		Журнал
	</a>
	<button type="button" data-dock-search>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.6"/><path d="m16 16.5 4 4" stroke="currentColor" stroke-width="1.6"/></svg>
		Поиск
	</button>
	<a href="<?php echo esc_url( at_page_url( 'fond-iniciativ' ) ); ?>"<?php echo ( is_page( 'fond-iniciativ' ) || is_singular( 'at_initiative' ) ) ? ' aria-current="page"' : ''; ?>>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6 15.5V5.8L18 4v9.2" stroke="currentColor" stroke-width="1.6"/><path d="M6 15.5c0 1.8 2.7 3 6 3s6-1.2 6-3" stroke="currentColor" stroke-width="1.6"/></svg>
		Фонд
	</a>
	<button type="button" data-dock-menu>
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.6"/></svg>
		Меню
	</button>
</nav>
<p class="toast" data-toast role="status" hidden></p>
<?php wp_footer(); ?>
</body>
</html>
