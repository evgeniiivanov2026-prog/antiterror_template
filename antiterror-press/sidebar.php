<?php
/**
 * Колонка записи.
 *
 * @package antiterror-press
 */

if ( is_active_sidebar( 'sidebar-1' ) ) {
	dynamic_sidebar( 'sidebar-1' );
	return;
}

$current = get_the_ID();
$cat     = at_primary_category( $current );
$shared  = array(
	'post_type'              => 'post',
	'posts_per_page'         => 5,
	'post__not_in'           => array( $current ),
	'ignore_sticky_posts'    => true,
	'no_found_rows'          => true,
	'update_post_meta_cache' => false,
	'update_post_term_cache' => false,
);
if ( $cat ) {
	$shared['cat'] = (int) $cat->term_id;
}
$related = new WP_Query( $shared );
if ( ! $related->have_posts() ) {
	unset( $shared['cat'] );
	$related = new WP_Query( $shared );
}
?>
<section class="rail__block">
	<h2>Ещё в номере</h2>
	<?php
	while ( $related->have_posts() ) :
		$related->the_post();
		?>
		<a class="rail-link" href="<?php the_permalink(); ?>">
			<?php the_title(); ?>
			<small><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></small>
		</a>
	<?php endwhile; ?>
	<?php wp_reset_postdata(); ?>
</section>
<section class="rail__block">
	<h2>Разделы</h2>
	<a class="rail-link" href="<?php echo esc_url( at_page_url( 'geroi-antiterrora' ) ); ?>">Бойцы антитеррора</a>
	<a class="rail-link" href="<?php echo esc_url( at_page_url( 'zhurnal' ) ); ?>">Журнал</a>
	<a class="rail-link" href="<?php echo esc_url( at_page_url( 'arhiv' ) ); ?>">Архив фото и видео</a>
	<a class="rail-link" href="<?php echo esc_url( at_page_url( 'geroi-sredi-nas' ) ); ?>">Герои среди нас</a>
</section>
