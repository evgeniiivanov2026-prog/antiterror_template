<?php
/**
 * Карточка архива.
 *
 * @package antiterror-press
 */

$item = isset( $args['item'] ) ? $args['item'] : array();
if ( empty( $item['src'] ) ) {
	return;
}
$is_video = isset( $item['type'] ) && 'video' === $item['type'];
$is_staff = isset( $item['shelf'] ) && 'staff' === $item['shelf'];
$kind     = $is_video ? 'Видео' : 'Фото';
$topic    = ! empty( $item['topic'] ) ? $item['topic'] : ( $is_staff ? 'Сотрудники' : 'Журнал' );
$kicker   = ( $is_staff && ! empty( $item['rank'] ) ) ? $item['rank'] : $topic;
$preview  = ! empty( $item['preview'] ) ? $item['preview'] : $item['src'];
$hay = $item['title'] . ' ' . $topic . ' ' . $kind . ' ' . ( isset( $item['rank'] ) ? $item['rank'] : '' );
if ( function_exists( 'mb_strtolower' ) ) {
	$hay = mb_strtolower( $hay, 'UTF-8' );
}
?>
<button
	class="tile"
	type="button"
	data-vault-item
	data-type="<?php echo esc_attr( $is_video ? 'video' : 'photo' ); ?>"
	data-shelf="<?php echo esc_attr( $is_staff ? 'staff' : 'story' ); ?>"
	data-src="<?php echo esc_url( $item['src'] ); ?>"
	data-title="<?php echo esc_attr( $item['title'] ); ?>"
	data-kicker="<?php echo esc_attr( $topic . ' · ' . $kind ); ?>"
	data-href="<?php echo esc_url( $item['permalink'] ); ?>"
	data-hay="<?php echo esc_attr( $hay ); ?>"
>
	<span class="tile__frame">
		<?php if ( $is_video ) : ?>
			<video muted playsinline preload="none" tabindex="-1" data-src="<?php echo esc_url( $preview ); ?>#t=0.4"></video>
			<span class="tile__play" aria-hidden="true"></span>
		<?php else : ?>
			<img src="<?php echo esc_url( $preview ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" decoding="async">
		<?php endif; ?>
	</span>
	<span class="tile__body">
		<span class="kicker"><?php echo esc_html( $kicker ); ?></span>
		<span class="tile__title"><?php echo esc_html( $item['title'] ); ?></span>
		<span class="tile__date"><?php echo esc_html( $kind . ' · ' . $item['date'] ); ?></span>
	</span>
</button>
