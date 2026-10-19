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
$lead  = array_shift( $items );
$place = get_post_meta( $lead->ID, '_at_init_place', true );
$status = at_initiative_status_label( (string) get_post_meta( $lead->ID, '_at_init_status', true ) );
?>
<section class="fund" aria-labelledby="fund-heading">
	<div class="wrap">
		<div class="section__head">
			<h2 id="fund-heading">Фонд инициатив</h2>
			<a class="section__link" href="<?php echo esc_url( at_page_url( 'fond-iniciativ' ) ); ?>">Все инициативы</a>
		</div>
		<p class="fund__lead">Предложения, которые журнал выносит на обсуждение. Первое — вернуть памятнику Феликса Дзержинского его место на Лубянской площади.</p>
		<div class="fund__stage<?php echo $items ? '' : ' fund__stage--solo'; ?>">
			<a class="fund-lead" href="<?php echo esc_url( get_permalink( $lead ) ); ?>">
				<span class="fund-lead__index" aria-hidden="true"><?php echo esc_html( at_initiative_no( 1 ) ); ?></span>
				<span class="kicker"><?php echo esc_html( $status ); ?><?php echo $place ? ' · ' . esc_html( $place ) : ''; ?></span>
				<span class="fund-lead__title" style="view-transition-name:at-fund-lead"><?php echo esc_html( get_the_title( $lead ) ); ?></span>
				<?php if ( has_excerpt( $lead ) ) : ?>
					<span class="fund-lead__deck"><?php echo esc_html( get_the_excerpt( $lead ) ); ?></span>
				<?php endif; ?>
				<span class="section__link fund-lead__go">Читать инициативу</span>
			</a>
			<?php if ( $items ) : ?>
				<div class="fund-side">
					<?php foreach ( $items as $index => $item ) : ?>
						<?php
						$item_place  = get_post_meta( $item->ID, '_at_init_place', true );
						$item_status = at_initiative_status_label( (string) get_post_meta( $item->ID, '_at_init_status', true ) );
						?>
						<a class="fund-row" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
							<span class="kicker"><?php echo esc_html( at_initiative_no( $index + 2 ) ); ?> · <?php echo esc_html( $item_status ); ?></span>
							<span class="fund-row__title"><?php echo esc_html( get_the_title( $item ) ); ?></span>
							<?php if ( $item_place ) : ?><span class="fund-row__place"><?php echo esc_html( $item_place ); ?></span><?php endif; ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
