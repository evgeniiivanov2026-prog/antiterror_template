<?php
/**
 * Форма поиска.
 *
 * @package antiterror-press
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="sr" for="at-search">Искать</label>
	<input id="at-search" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Имя, рубрика, событие">
	<button type="submit">Найти</button>
</form>
