<?php
/**
 * Карточка вехи для ABC Pagination.
 * Если плагин не передал объект записи, берём текущий пост.
 *
 * @package antiterror-press
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = null;
if ( isset( $args['post'] ) ) {
	$post = $args['post'];
} elseif ( isset( $GLOBALS['post'] ) ) {
	$post = $GLOBALS['post'];
}

at_milestone_card( $post );
