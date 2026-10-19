<?php
/**
 * Форма письма в редакцию.
 *
 * @package antiterror-press
 */

$topic = isset( $args['topic'] ) ? $args['topic'] : 'Письмо в редакцию';
$sent  = isset( $_GET['at_sent'] ) ? sanitize_text_field( wp_unslash( $_GET['at_sent'] ) ) : '';
?>
<?php if ( '1' === $sent ) : ?>
	<p class="note note--ok">Письмо отправлено. Редакция ответит на указанную почту.</p>
<?php elseif ( '0' === $sent ) : ?>
	<p class="note note--bad">Не удалось отправить. Проверьте имя, почту, текст и согласие.</p>
<?php endif; ?>
<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-contact-form>
	<p class="note" data-contact-status role="status" hidden></p>
	<input type="hidden" name="action" value="at_contact">
	<input type="hidden" name="at_topic" value="<?php echo esc_attr( $topic ); ?>">
	<?php wp_nonce_field( 'at_contact', 'at_contact_nonce' ); ?>
	<p class="hp" aria-hidden="true"><label>Компания<input type="text" name="at_company" tabindex="-1" autocomplete="off"></label></p>
	<label>Имя<input type="text" name="at_name" required maxlength="80"></label>
	<label>Почта<input type="email" name="at_email" required></label>
	<label>Сообщение<textarea name="at_message" rows="6" required minlength="8"></textarea></label>
	<label class="check"><input type="checkbox" name="at_consent" value="1" required> Согласен на обработку имени и почты, чтобы редакция могла ответить.</label>
	<button class="btn" type="submit">Отправить</button>
</form>
