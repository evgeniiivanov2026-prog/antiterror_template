<?php
/**
 * Compact share row: VK, Telegram, copy link.
 *
 * @package antiterror-press
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$links   = at_share_links( $post_id );
if ( ! $links ) {
	return;
}
?>
<div class="share" data-share>
	<p class="kicker">Поделиться</p>
	<div class="share__row">
		<?php foreach ( $links as $link ) : ?>
			<?php if ( 'copy' === $link['id'] ) : ?>
				<button class="share__btn" type="button" data-share-copy="<?php echo esc_url( $link['url'] ); ?>">
					<?php echo esc_html( $link['label'] ); ?>
				</button>
			<?php else : ?>
				<a class="share__btn" href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $link['label'] ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<p class="share__note" data-share-note hidden></p>
</div>
