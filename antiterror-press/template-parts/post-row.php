<?php
/**
 * Строка ленты.
 *
 * @package antiterror-press
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
if ( ! $post_id ) {
	return;
}
?>
<?php $media = at_media_html( $post_id, 'at-thumb' ); ?>
<a class="row" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
	<?php if ( $media ) : ?>
		<div class="row__media"><?php echo $media; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
	<?php endif; ?>
	<div>
		<?php $kicker = at_kicker( $post_id ); ?>
		<?php if ( $kicker ) : ?><p class="kicker"><?php echo esc_html( $kicker ); ?></p><?php endif; ?>
		<h2 class="row__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
		<p class="row__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_id ), 32 ) ); ?></p>
		<?php at_meta_line( $post_id ); ?>
	</div>
</a>
