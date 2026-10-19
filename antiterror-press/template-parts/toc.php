<?php
/**
 * In-article table of contents.
 *
 * @package antiterror-press
 */

$items = isset( $args['items'] ) ? $args['items'] : array();
if ( count( $items ) < 2 ) {
	return;
}
?>
<nav class="toc" data-toc aria-labelledby="at-toc-title">
	<p class="kicker" id="at-toc-title">Содержание</p>
	<ol class="toc__list">
		<?php foreach ( $items as $item ) : ?>
			<li class="toc__item toc__item--h<?php echo (int) $item['level']; ?>">
				<a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
