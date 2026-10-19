<?php
/**
 * Related stories under a single post — JournalX pattern, theme cards.
 *
 * @package antiterror-press
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$posts   = at_related_posts( $post_id, 3 );
if ( ! $posts ) {
	return;
}
?>
<section class="related wrap" aria-labelledby="related-heading">
	<div class="section__head">
		<h2 id="related-heading">Ещё по теме</h2>
		<a class="section__link" href="<?php echo esc_url( at_page_url( 'zhurnal' ) ); ?>">Весь журнал</a>
	</div>
	<div class="cards">
		<?php
		foreach ( $posts as $item ) {
			get_template_part( 'template-parts/story', 'card', array( 'post_id' => $item->ID ) );
		}
		?>
	</div>
</section>
