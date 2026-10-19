<?php
/**
 * Форма письма в редакцию.
 *
 * @package antiterror-press
 */

$topic  = isset( $args['topic'] ) ? $args['topic'] : 'Письмо в редакцию';
$dialog = ! empty( $args['dialog'] );
$sent   = ( ! $dialog && isset( $_GET['at_sent'] ) ) ? sanitize_text_field( wp_unslash( $_GET['at_sent'] ) ) : '';

static $at_form_n = 0;
++$at_form_n;
$uid = 'at-c-' . $at_form_n;
?>
<?php if ( '1' === $sent ) : ?>
	<p class="note note--ok" data-form-note>Письмо отправлено. Редакция ответит на указанную почту.</p>
<?php elseif ( '0' === $sent ) : ?>
	<p class="note note--bad" data-form-note>Не удалось отправить. Проверьте имя, почту, текст и согласие.</p>
<?php else : ?>
	<p class="note" data-form-note hidden></p>
<?php endif; ?>
<form class="form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-ajax-form>
	<input type="hidden" name="action" value="at_contact">
	<input type="hidden" name="at_topic" value="<?php echo esc_attr( $topic ); ?>" data-topic-field>
	<?php wp_nonce_field( 'at_contact', 'at_contact_nonce' ); ?>
	<p class="hp" aria-hidden="true"><label>Компания<input type="text" name="at_company" tabindex="-1" autocomplete="off"></label></p>
	<label for="<?php echo esc_attr( $uid ); ?>-name">Имя<input id="<?php echo esc_attr( $uid ); ?>-name" type="text" name="at_name" required maxlength="80" autocomplete="name"></label>
	<label for="<?php echo esc_attr( $uid ); ?>-email">Почта<input id="<?php echo esc_attr( $uid ); ?>-email" type="email" name="at_email" required autocomplete="email"></label>
	<label for="<?php echo esc_attr( $uid ); ?>-message">Сообщение<textarea id="<?php echo esc_attr( $uid ); ?>-message" name="at_message" rows="6" required minlength="8"></textarea></label>
	<label class="check"><input type="checkbox" name="at_consent" value="1" required> Согласен на обработку имени и почты, чтобы редакция могла ответить.</label>
	<button class="btn" type="submit" data-submit>Отправить</button>
</form>
