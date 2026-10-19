<?php
/**
 * Комментарии.
 *
 * @package antiterror-press
 */

if ( post_password_required() ) {
	return;
}
?>
<section class="comments" id="comments">
	<h2><?php echo esc_html( at_ru_plural( (int) get_comments_number(), 'комментарий', 'комментария', 'комментариев' ) ); ?></h2>
	<?php if ( have_comments() ) : ?>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'       => 'ol',
					'short_ping'  => true,
					'avatar_size' => 0,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'title_reply'          => 'Комментарий',
			'label_submit'         => 'Отправить',
			'comment_notes_before' => '',
		)
	);
	?>
</section>
