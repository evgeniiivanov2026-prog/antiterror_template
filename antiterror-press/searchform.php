<?php
/**
 * Форма поиска.
 *
 * @package antiterror-press
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-live-search>
	<label class="sr" for="at-search">Искать</label>
	<input id="at-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Имя, рубрика, событие" autocomplete="off" enterkeyhint="search" aria-autocomplete="list" aria-controls="at-search-live" data-live-input>
	<button type="submit">Найти</button>
</form>
