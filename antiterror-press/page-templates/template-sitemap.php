<?php
/**
 * Карта сайта.
 *
 * Template Name: Карта сайта
 *
 * @package antiterror-press
 */

get_header();
$pages = get_pages( array( 'sort_column' => 'menu_order,post_title' ) );
$cats  = get_categories( array( 'hide_empty' => false ) );
$posts = get_posts( array( 'numberposts' => 30 ) );
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Навигация</p>
			<h1><?php the_title(); ?></h1>
		</header>
		<div class="sitemap-grid">
			<section>
				<h2>Страницы</h2>
				<?php foreach ( $pages as $page ) : ?>
					<a href="<?php echo esc_url( get_permalink( $page ) ); ?>"><?php echo esc_html( $page->post_title ); ?></a>
				<?php endforeach; ?>
			</section>
			<section>
				<h2>Рубрики</h2>
				<?php foreach ( $cats as $cat ) : ?>
					<a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a>
				<?php endforeach; ?>
			</section>
			<section>
				<h2>Свежие материалы</h2>
				<?php foreach ( $posts as $item ) : ?>
					<a href="<?php echo esc_url( get_permalink( $item ) ); ?>"><?php echo esc_html( get_the_title( $item ) ); ?></a>
				<?php endforeach; ?>
			</section>
		</div>
	</div>
</main>
<?php
get_footer();
