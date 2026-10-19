<?php
/**
 * Одна веха истории.
 *
 * @package antiterror-press
 */

get_header();

while ( have_posts() ) :
	the_post();
	$year  = get_post_meta( get_the_ID(), '_at_year', true );
	$date  = get_post_meta( get_the_ID(), '_at_date', true );
	$place = get_post_meta( get_the_ID(), '_at_place', true );
	$units = get_post_meta( get_the_ID(), '_at_units', true );
	?>
<main id="content">
	<article <?php post_class( 'article-layout wrap' ); ?> data-article>
		<div class="article">
			<header>
				<p class="kicker">Веха истории</p>
				<?php if ( $year ) : ?><p class="vehi-single__year"><?php echo esc_html( $year ); ?></p><?php endif; ?>
				<h1 class="article__title"><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="article__deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<ul class="vehi-facts">
					<?php if ( $date ) : ?><li><span>Дата</span><?php echo esc_html( $date ); ?></li><?php endif; ?>
					<?php if ( $place ) : ?><li><span>Место</span><?php echo esc_html( $place ); ?></li><?php endif; ?>
					<?php if ( $units ) : ?><li><span>Кто работал</span><?php echo esc_html( $units ); ?></li><?php endif; ?>
				</ul>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article__figure">
					<?php the_post_thumbnail( 'large', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
				</figure>
			<?php endif; ?>
			<div class="prose">
				<?php the_content(); ?>
			</div>
			<p class="vehi-back"><a class="section__link" href="<?php echo esc_url( at_page_url( 'vehi-istorii' ) ); ?>">Все вехи</a></p>
		</div>
		<aside class="rail">
			<?php get_sidebar(); ?>
		</aside>
	</article>
</main>
	<?php
endwhile;
get_footer();
