<?php
/**
 * Шапка журнала.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#f3efe6" data-theme-color>
	<script>
	(function () {
		var theme = "light";
		try {
			var saved = localStorage.getItem("at-theme");
			if (saved === "dark" || saved === "light") theme = saved;
			else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) theme = "dark";
		} catch (e) {}
		document.documentElement.setAttribute("data-theme", theme);
		var meta = document.querySelector("[data-theme-color]");
		if (meta) meta.setAttribute("content", theme === "dark" ? "#141311" : "#f3efe6");
	})();
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content">К содержанию</a>

<div class="utility">
	<div class="wrap utility__inner">
		<p class="utility__label"><?php echo esc_html( get_theme_mod( 'at_tagline_short', 'Борьба с терроризмом и экстремизмом. За межнациональную дружбу.' ) ); ?></p>
		<?php at_trending_markup(); ?>
		<?php at_social_links(); ?>
	</div>
</div>

<header class="mast" data-mast>
	<div class="wrap mast__inner">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<span class="brand__logo"><?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'thumbnail', false, array( 'alt' => '' ) ); ?></span>
			<?php else : ?>
				<span class="brand__mark" aria-hidden="true">А</span>
			<?php endif; ?>
			<span class="brand__text">
				<span class="brand__kicker">Журнал</span>
				<span class="brand__name"><?php bloginfo( 'name' ); ?></span>
			</span>
		</a>

		<nav class="nav" id="site-nav" data-nav aria-label="Разделы">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav__list',
					'fallback_cb'    => 'at_fallback_menu',
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<div class="mast__tools">
			<button class="icon-btn" type="button" data-contact-open data-contact-topic="Письмо в редакцию" aria-haspopup="dialog" aria-label="Написать в редакцию">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 6.5h16v11H4v-11Z" stroke="currentColor" stroke-width="1.6"/><path d="m5 7 7 6 7-6" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
			<button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-pressed="false">
				<span class="sr" data-theme-label>Тёмная тема</span>
				<svg class="theme-toggle__moon" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16.5 14.5A6.5 6.5 0 0 1 9.2 6.2 6.5 6.5 0 1 0 16.5 14.5Z" stroke="currentColor" stroke-width="1.6"/></svg>
				<svg class="theme-toggle__sun" width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M12 3.5v2.2M12 18.3v2.2M3.5 12h2.2M18.3 12h2.2M6 6l1.6 1.6M16.4 16.4 18 18M18 6l-1.6 1.6M7.6 16.4 6 18" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
			<button class="icon-btn" type="button" data-search-open aria-label="Открыть поиск">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.6"/><path d="M16 16.5L20 20.5" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
			<button class="icon-btn nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="site-nav">
				<span class="sr">Меню</span>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" stroke-width="1.6"/></svg>
			</button>
		</div>
	</div>
</header>
<?php at_breadcrumbs(); ?>

<div class="search-layer" data-search hidden>
	<div class="search-panel" role="dialog" aria-modal="true" aria-labelledby="search-title">
		<button class="search-close" type="button" data-search-close>Закрыть</button>
		<h2 id="search-title">Поиск по журналу</h2>
		<?php get_search_form(); ?>
		<div class="search-live" data-search-live hidden></div>
	</div>
</div>
