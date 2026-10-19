<?php
/**
 * Список инициатив фонда.
 *
 * Template Name: Фонд инициатив
 *
 * @package antiterror-press
 */

get_header();

$items = at_initiatives();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Фонд</p>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php else : ?>
				<p class="deck">Предложения журнала к открытому обсуждению. У каждой инициативы есть статус и место.</p>
			<?php endif; ?>
		</header>

		<?php if ( $items ) : ?>
			<div class="fund-index">
				<?php foreach ( $items as $index => $item ) : ?>
					<?php
					$place  = get_post_meta( $item->ID, '_at_init_place', true );
					$status = at_initiative_status_label( (string) get_post_meta( $item->ID, '_at_init_status', true ) );
					$lead   = (int) $item->ID === at_initiative_lead_id();
					?>
					<a class="fund-line" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
						<span class="fund-line__no" aria-hidden="true"><?php echo esc_html( at_initiative_no( $index + 1 ) ); ?></span>
						<span class="fund-line__body">
							<span class="kicker"><?php echo esc_html( $status ); ?><?php echo $place ? ' · ' . esc_html( $place ) : ''; ?></span>
							<span class="fund-line__title"<?php echo $lead ? ' style="view-transition-name:at-fund-lead"' : ''; ?>><?php echo esc_html( get_the_title( $item ) ); ?></span>
							<?php if ( has_excerpt( $item ) ) : ?>
								<span class="fund-line__deck"><?php echo esc_html( get_the_excerpt( $item ) ); ?></span>
							<?php endif; ?>
						</span>
						<span class="section__link">Открыть</span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="empty-note">Инициатив пока нет. Первую можно создать в админке, в разделе «Фонд инициатив».</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
