<?php
/**
 * Запись: материал или досье.
 *
 * @package antiterror-press
 */

get_header();
?>
<div class="progress" aria-hidden="true"><span data-progress></span></div>
<main id="content">
	<?php
	while ( have_posts() ) :
		the_post();
		$rank = get_post_meta( get_the_ID(), '_at_rank', true );
		$unit = get_post_meta( get_the_ID(), '_at_unit', true );
		?>
		<article <?php post_class( 'article-layout wrap' ); ?> data-article>
			<div class="article">
				<header>
					<?php $kicker = at_kicker(); ?>
					<?php if ( $kicker ) : ?><p class="kicker"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
					<h1 class="article__title"><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?>
						<p class="article__deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<?php at_meta_line( get_the_ID() ); ?>
					<?php if ( $rank || $unit ) : ?>
						<div class="dossier">
							<?php if ( $rank ) : ?><span class="chip"><?php echo esc_html( $rank ); ?></span><?php endif; ?>
							<?php if ( $unit ) : ?><span class="chip"><?php echo esc_html( $unit ); ?></span><?php endif; ?>
						</div>
					<?php endif; ?>
				</header>
				<?php
				$is_hero = has_category( array( 'heroes', 'geroi-antiterrora' ) );
				$album_ids = get_post_meta( get_the_ID(), '_at_album', true );
				$album_ids = is_array( $album_ids ) ? $album_ids : array();
				?>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="article__figure<?php echo $is_hero ? ' article__figure--hero' : ''; ?>">
						<?php the_post_thumbnail( $is_hero ? 'at-portrait' : 'large', array( 'alt' => esc_attr( get_the_title() ), 'loading' => 'eager' ) ); ?>
						<?php if ( $is_hero ) : ?>
							<div class="hero-qr" title="QR-код страницы героя">
								<img src="<?php echo esc_url( 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&margin=8&data=' . rawurlencode( get_permalink() ) ); ?>" width="120" height="120" alt="QR-код: <?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
							</div>
						<?php endif; ?>
						<?php if ( get_the_post_thumbnail_caption() ) : ?>
							<figcaption><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<?php if ( $is_hero && $album_ids ) : ?>
					<section class="hero-album" aria-labelledby="album-heading">
						<h2 id="album-heading" class="kicker">Альбом</h2>
						<div class="album-grid">
							<?php foreach ( $album_ids as $att_id ) :
								$att = get_post( $att_id );
								if ( ! $att || 'attachment' !== $att->post_type ) continue;
								$mime = get_post_mime_type( $att_id );
								$is_video = $mime && 0 === strpos( $mime, 'video/' );
								$full = wp_get_attachment_url( $att_id );
								$thumb = wp_get_attachment_image_url( $att_id, 'medium' );
								?>
								<button type="button" class="album-item" data-full="<?php echo esc_url( $full ); ?>" data-type="<?php echo $is_video ? 'video' : 'image'; ?>" aria-label="Открыть">
									<?php if ( $is_video ) : ?>
										<span class="album-item__play" aria-hidden="true">▶</span>
										<?php if ( $thumb ) : ?><img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy"><?php else : ?><span class="album-item__ph">Видео</span><?php endif; ?>
									<?php else : ?>
										<img src="<?php echo esc_url( $thumb ?: $full ); ?>" alt="<?php echo esc_attr( get_the_title( $att_id ) ); ?>" loading="lazy">
									<?php endif; ?>
								</button>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endif; ?>

				<div class="prose">
					<?php the_content(); ?>
				</div>
				<?php
				wp_link_pages( array( 'before' => '<nav class="pagination">', 'after' => '</nav>' ) );
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
			<aside class="rail">
				<?php get_sidebar(); ?>
			</aside>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
