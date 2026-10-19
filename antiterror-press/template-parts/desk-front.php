<?php
/**
 * Полоса фактов, якоря номера и плитка рубрик на главной.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args     = isset( $args ) && is_array( $args ) ? $args : array();
$has_feed = ! empty( $args['has_feed'] );
$facts    = at_desk_facts();
$rubrics  = at_desk_rubrics();
$has_fund = (bool) at_home_initiatives();

$jumps = array();
if ( ! empty( $args['has_feature'] ) ) {
	$jumps[] = array( 'Главное', '#glavnoe' );
}
if ( $rubrics ) {
	$jumps[] = array( 'Рубрики', '#rubriki' );
}
if ( $has_feed ) {
	$jumps[] = array( 'Лента', '#lenta' );
}
if ( get_theme_mod( 'at_home_bpla', false ) ) {
	$jumps[] = array( 'Сводка', '#svodka' );
}
if ( $has_fund ) {
	$jumps[] = array( 'Фонд', '#fond' );
}
$jumps[] = array( 'Люди', '#ljudi' );
$jumps[] = array( 'Разделы', '#razdely' );
?>
<?php if ( count( $facts ) >= 2 ) : ?>
	<div class="issue-strip" aria-label="Сводка номера">
		<?php foreach ( $facts as $fact ) : ?>
			<p class="issue-cell">
				<?php if ( $fact['count'] > 0 ) : ?>
					<strong data-count="<?php echo esc_attr( (string) $fact['count'] ); ?>"><?php echo esc_html( $fact['value'] ); ?></strong>
				<?php else : ?>
					<strong><?php echo esc_html( $fact['value'] ); ?></strong>
				<?php endif; ?>
				<span><?php echo esc_html( $fact['label'] ); ?></span>
			</p>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<?php if ( count( $jumps ) > 1 ) : ?>
	<nav class="issue-jump" aria-label="По странице">
		<?php foreach ( $jumps as $jump ) : ?>
			<a href="<?php echo esc_url( $jump[1] ); ?>"><?php echo esc_html( $jump[0] ); ?></a>
		<?php endforeach; ?>
	</nav>
<?php endif; ?>

<?php if ( $rubrics ) : ?>
	<section class="rubrics" id="rubriki" aria-labelledby="rubrics-heading">
		<div class="section__head">
			<h2 id="rubrics-heading">Рубрики</h2>
			<a class="section__link" href="<?php echo esc_url( at_page_url( 'zhurnal' ) ); ?>">Весь журнал</a>
		</div>
		<div class="rubric-grid">
			<?php foreach ( $rubrics as $index => $term ) : ?>
				<a class="rubric" href="<?php echo esc_url( get_category_link( $term ) ); ?>">
					<span class="rubric__n"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
					<span class="rubric__name"><?php echo esc_html( $term->name ); ?></span>
					<span class="rubric__count"><?php echo esc_html( at_ru_plural( (int) $term->count, 'материал', 'материала', 'материалов' ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>
