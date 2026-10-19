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
<button class="to-top" type="button" data-to-top hidden>Наверх</button>
<p class="toast" data-toast role="status" hidden></p>
<?php wp_footer(); ?>
</body>
</html>
