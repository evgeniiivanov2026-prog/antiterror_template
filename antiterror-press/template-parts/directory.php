<?php
/**
 * Алфавитный список бойцов.
 *
 * @package antiterror-press
 */

$posts  = isset( $args['posts'] ) ? $args['posts'] : at_sorted_fighters();
$groups = at_fighter_groups( $posts );
$active = array_keys( $groups );
?>
<div class="directory">
	<div class="finder-row">
		<label class="finder">
			<span>Найти бойца</span>
			<input type="search" data-fighter-search placeholder="Фамилия, звание, подразделение" autocomplete="off">
		</label>
		<p class="directory__count" data-fighter-count></p>
	</div>
	<nav class="alpha" aria-label="Алфавит">
		<?php foreach ( at_alphabet() as $letter ) : ?>
			<?php if ( in_array( $letter, $active, true ) ) : ?>
				<a href="#letter-<?php echo esc_attr( $letter ); ?>"><?php echo esc_html( $letter ); ?></a>
			<?php else : ?>
				<span class="is-empty"><?php echo esc_html( $letter ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<p class="empty-note" data-fighter-empty hidden>Никого не нашли. Попробуйте другую фамилию.</p>
	<?php if ( ! $groups ) : ?>
		<p class="empty-note">Список появится, когда в рубрике «Герои Антитеррора» будут материалы. В досье записи можно указать звание и имя для алфавита.</p>
	<?php endif; ?>
	<?php foreach ( $groups as $letter => $people ) : ?>
		<section class="letter" id="letter-<?php echo esc_attr( $letter ); ?>" data-letter="<?php echo esc_attr( $letter ); ?>">
			<h2 class="letter__mark"><?php echo esc_html( $letter ); ?></h2>
			<div class="letter__list">
				<?php foreach ( $people as $person ) : ?>
					<?php
					$rank = get_post_meta( $person->ID, '_at_rank', true );
					$unit = get_post_meta( $person->ID, '_at_unit', true );
					$hay  = trim( get_the_title( $person ) . ' ' . $rank . ' ' . $unit . ' ' . at_sort_name( $person ) );
					?>
					<a class="person" data-fighter data-hay="<?php echo esc_attr( $hay ); ?>" href="<?php echo esc_url( get_permalink( $person ) ); ?>">
						<?php if ( has_post_thumbnail( $person ) ) : ?>
							<?php echo get_the_post_thumbnail( $person, 'at-portrait', array( 'alt' => '' ) ); ?>
						<?php else : ?>
							<span class="person__ph" aria-hidden="true"></span>
						<?php endif; ?>
						<span>
							<?php if ( $rank ) : ?><span class="person__rank"><?php echo esc_html( $rank ); ?></span><?php endif; ?>
							<span class="person__name"><?php echo esc_html( get_the_title( $person ) ); ?></span>
							<?php if ( $unit ) : ?><span class="person__unit"><?php echo esc_html( $unit ); ?></span><?php endif; ?>
						</span>
						<span class="person__go">Досье</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>
</div>
