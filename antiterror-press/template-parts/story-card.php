<?php
/**
 * Карточка материала.
 *
 * @package antiterror-press
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}
?>
<?php $media = at_media_html( $post_id, 'at-card' ); ?>
<a class="story" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
	<?php if ( $media ) : ?>
		<div class="story__media"><?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
	<div class="story__body">
		<?php $kicker = at_kicker( $post_id ); ?>
		<?php if ( $kicker ) : ?><p class="kicker"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
		<h3 class="story__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
		<p class="story__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 22 ) ); ?></p>
		<?php at_meta_line( $post_id ); ?>
	</div>
</a>
