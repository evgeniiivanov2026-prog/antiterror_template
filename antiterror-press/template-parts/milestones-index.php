<?php
/**
 * Алфавитный указатель вех: буква и карточки событий под ней.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts  = isset( $args['posts'] ) && is_array( $args['posts'] ) ? $args['posts'] : at_milestones();
$groups = array();
foreach ( $posts as $item ) {
	if ( ! $item instanceof WP_Post ) {
		continue;
	}
	$letter = at_milestone_letter( $item );
	if ( ! $letter ) {
		continue;
	}
	$groups[ $letter ][] = $item;
}
$groups = at_milestone_sort_letters( $groups );
foreach ( $groups as $letter => $items ) {
	usort(
		$items,
		function ( $a, $b ) {
			$ya = (int) get_post_meta( $a->ID, '_at_year', true );
			$yb = (int) get_post_meta( $b->ID, '_at_year', true );
			if ( $ya === $yb ) {
				return strcmp( $a->post_title, $b->post_title );
			}
			return $ya - $yb;
		}
	);
	$groups[ $letter ] = $items;
}
?>
<div class="vehi-native" data-vehi>
	<div class="vehi-tools">
		<label class="finder">
			<span>Найти событие</span>
			<input type="search" data-vehi-search placeholder="Норд-Ост, Дубровка, Беслан" autocomplete="off">
		</label>
		<p class="directory__count" data-vehi-count></p>
	</div>
	<?php if ( $groups ) : ?>
		<nav class="alpha" aria-label="Алфавит вех">
			<?php foreach ( $groups as $letter => $items ) : ?>
				<a href="#vehi-<?php echo esc_attr( $letter ); ?>" data-vehi-letter="<?php echo esc_attr( $letter ); ?>">
					<?php echo esc_html( $letter ); ?>
					<small><?php echo esc_html( (string) count( $items ) ); ?></small>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>
	<p class="empty-note" data-vehi-empty hidden>Такого события в указателе нет. Попробуйте другое имя: Дубровка, Беслан, Будённовск.</p>
	<?php if ( ! $groups ) : ?>
		<p class="empty-note">Вехи появятся, когда в разделе «Вехи истории» будут опубликованы записи.</p>
	<?php endif; ?>
	<?php foreach ( $groups as $letter => $items ) : ?>
		<section class="vehi-letter" id="vehi-<?php echo esc_attr( $letter ); ?>" data-vehi-group="<?php echo esc_attr( $letter ); ?>">
			<h2 class="letter__mark"><?php echo esc_html( $letter ); ?><small><?php echo esc_html( (string) count( $items ) ); ?></small></h2>
			<div class="vehi-grid">
				<?php
				foreach ( $items as $item ) {
					at_milestone_card( $item );
				}
				?>
			</div>
		</section>
	<?php endforeach; ?>
</div>
