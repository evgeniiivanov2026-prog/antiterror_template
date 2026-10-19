<?php
/**
 * Плитка разделов журнала.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sections = at_desk_sections();
?>
<section class="desk-sections" id="razdely" aria-labelledby="sections-heading">
	<div class="section__head">
		<h2 id="sections-heading">Разделы</h2>
		<a class="section__link" href="<?php echo esc_url( at_page_url( 'sitemap' ) ); ?>">Карта сайта</a>
	</div>
	<div class="section-grid">
		<?php foreach ( $sections as $section ) : ?>
			<a class="section-card" href="<?php echo esc_url( $section['url'] ); ?>">
				<span class="section-card__n"><?php echo esc_html( $section['n'] ); ?></span>
				<span class="section-card__name"><?php echo esc_html( $section['title'] ); ?></span>
				<span class="section-card__text"><?php echo esc_html( $section['text'] ); ?></span>
				<span class="section-card__go">Открыть</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
