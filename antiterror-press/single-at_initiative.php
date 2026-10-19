<?php
/**
 * Одна инициатива фонда.
 *
 * @package antiterror-press
 */

get_header();

while ( have_posts() ) :
	the_post();
	$place    = get_post_meta( get_the_ID(), '_at_init_place', true );
	$status   = at_initiative_status_label( (string) get_post_meta( get_the_ID(), '_at_init_status', true ) );
	$is_lead  = (int) get_the_ID() === at_initiative_lead_id();
	$neighbors = at_initiative_neighbors( get_the_ID() );
	?>
<main id="content">
	<article <?php post_class( 'article-layout wrap' ); ?>>
		<div class="article">
			<header>
				<p class="kicker">Фонд инициатив</p>
				<p class="fund-single__status"><?php echo esc_html( $status ); ?></p>
				<h1 class="article__title"<?php echo $is_lead ? ' style="view-transition-name:at-fund-lead"' : ''; ?>><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?>
					<p class="article__deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php endif; ?>
				<?php if ( $place ) : ?>
					<ul class="vehi-facts">
						<li><span>Место</span><?php echo esc_html( $place ); ?></li>
						<li><span>Статус</span><?php echo esc_html( $status ); ?></li>
					</ul>
				<?php endif; ?>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article__figure">
					<?php the_post_thumbnail( 'large', array( 'alt' => esc_attr( get_the_title() ) ) ); ?>
				</figure>
			<?php endif; ?>
			<div class="prose">
				<?php the_content(); ?>
			</div>
			<?php at_share_markup(); ?>
			<?php if ( $neighbors['prev'] || $neighbors['next'] ) : ?>
				<nav class="siblings" aria-label="Другие инициативы">
					<?php if ( $neighbors['prev'] instanceof WP_Post ) : ?>
						<a class="siblings__link siblings__link--prev" href="<?php echo esc_url( get_permalink( $neighbors['prev'] ) ); ?>">
							<span class="kicker">Ранее</span>
							<span><?php echo esc_html( get_the_title( $neighbors['prev'] ) ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $neighbors['next'] instanceof WP_Post ) : ?>
						<a class="siblings__link siblings__link--next" href="<?php echo esc_url( get_permalink( $neighbors['next'] ) ); ?>">
							<span class="kicker">Дальше</span>
							<span><?php echo esc_html( get_the_title( $neighbors['next'] ) ); ?></span>
						</a>
					<?php endif; ?>
				</nav>
			<?php endif; ?>
			<p class="vehi-back"><a class="section__link" href="<?php echo esc_url( at_page_url( 'fond-iniciativ' ) ); ?>">Все инициативы</a></p>
		</div>
		<aside class="rail">
			<?php get_sidebar(); ?>
		</aside>
	</article>
</main>
	<?php
endwhile;
get_footer();
