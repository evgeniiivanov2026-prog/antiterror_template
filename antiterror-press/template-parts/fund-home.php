<?php
/**
 * Блок фонда на главной. Первая инициатива — широкая, остальные — колонкой.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = at_home_initiatives();
if ( ! $items ) {
	return;
}
$lead     = array_shift( $items );
$place    = get_post_meta( $lead->ID, '_at_init_place', true );
$status   = at_initiative_status_label( (string) get_post_meta( $lead->ID, '_at_init_status', true ) );
$portrait = at_initiative_portrait( $lead );
?>
<section class="fund" id="fond" aria-labelledby="fund-heading">
	<div class="wrap">
		<div class="section__head">
			<h2 id="fund-heading">Фонд инициатив</h2>
			<a class="section__link" href="<?php echo esc_url( at_page_url( 'fond-iniciativ' ) ); ?>">Все инициативы</a>
		</div>
		<p class="fund__lead">Предложения, которые журнал выносит на обсуждение. Первое — вернуть памятнику Феликса Дзержинского его место на Лубянской площади.</p>
		<div class="fund__stage<?php echo $items ? '' : ' fund__stage--solo'; ?>">
			<article class="fund-lead">
				<?php if ( $portrait ) : ?>
					<a class="fund-lead__media" href="<?php echo esc_url( get_permalink( $lead ) ); ?>">
						<img src="<?php echo esc_url( $portrait['url'] ); ?>" width="<?php echo esc_attr( (string) $portrait['width'] ); ?>" height="<?php echo esc_attr( (string) $portrait['height'] ); ?>" alt="<?php echo esc_attr( $portrait['alt'] ); ?>">
						<?php if ( $portrait['credit'] ) : ?><span class="fund-lead__credit"><?php echo esc_html( $portrait['credit'] ); ?></span><?php endif; ?>
					</a>
				<?php endif; ?>
				<div class="fund-lead__body">
					<span class="fund-lead__index" aria-hidden="true"><?php echo esc_html( at_initiative_no( 1 ) ); ?></span>
					<span class="kicker"><?php echo esc_html( $status ); ?><?php echo $place ? ' · ' . esc_html( $place ) : ''; ?></span>
					<a class="fund-lead__title" href="<?php echo esc_url( get_permalink( $lead ) ); ?>" style="view-transition-name:at-fund-lead"><?php echo esc_html( get_the_title( $lead ) ); ?></a>
					<?php if ( has_excerpt( $lead ) ) : ?>
						<p class="fund-lead__deck"><?php echo esc_html( get_the_excerpt( $lead ) ); ?></p>
					<?php endif; ?>
					<a class="section__link fund-lead__go" href="<?php echo esc_url( get_permalink( $lead ) ); ?>">Читать инициативу</a>
					<?php at_vote_markup( $lead->ID ); ?>
				</div>
			</article>
			<?php if ( $items ) : ?>
				<div class="fund-side">
					<?php foreach ( $items as $index => $item ) : ?>
						<?php
						$item_place  = get_post_meta( $item->ID, '_at_init_place', true );
						$item_status = at_initiative_status_label( (string) get_post_meta( $item->ID, '_at_init_status', true ) );
						?>
						<article class="fund-row">
							<a href="<?php echo esc_url( get_permalink( $item ) ); ?>">
								<span class="kicker"><?php echo esc_html( at_initiative_no( $index + 2 ) ); ?> · <?php echo esc_html( $item_status ); ?></span>
								<span class="fund-row__title"><?php echo esc_html( get_the_title( $item ) ); ?></span>
								<?php if ( $item_place ) : ?><span class="fund-row__place"><?php echo esc_html( $item_place ); ?></span><?php endif; ?>
							</a>
							<?php at_vote_markup( $item->ID, true ); ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
