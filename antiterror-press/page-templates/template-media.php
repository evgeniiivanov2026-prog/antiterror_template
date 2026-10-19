<?php
/**
 * Архив фото и видео: сотрудники отдельно, материалы по темам.
 *
 * Template Name: Архив фото и видео
 *
 * @package antiterror-press
 */

get_header();
$groups = at_media_groups();
$staff  = $groups['staff'];
$topics = $groups['topics'];
$photos = 0;
$videos = 0;
$stories = 0;
foreach ( $staff as $item ) {
	if ( 'video' === $item['type'] ) {
		$videos++;
	} else {
		$photos++;
	}
}
foreach ( $topics as $topic ) {
	foreach ( $topic['items'] as $item ) {
		$stories++;
		if ( 'video' === $item['type'] ) {
			$videos++;
		} else {
			$photos++;
		}
	}
}
$total = count( $staff ) + $stories;
?>
<main id="content" class="page">
	<div class="wrap" data-archive>
		<header class="page-hero">
			<p class="kicker">Архив</p>
			<h1><?php the_title(); ?></h1>
			<p class="deck">Сначала портреты сотрудников, ниже — кадры и ролики материалов, собранные по темам. Откройте карточку, чтобы посмотреть целиком, и перейдите к заметке, если нужен контекст.</p>
		</header>

		<?php if ( ! $total ) : ?>
			<p class="empty-note">Архив появится, когда в записях будут фотографии или видео.</p>
		<?php else : ?>
			<div class="vault-tools">
				<div class="filters" role="group" aria-label="Раздел архива">
					<button class="filter is-on" type="button" data-vault-scope="all" aria-pressed="true">Все <span><?php echo (int) $total; ?></span></button>
					<button class="filter" type="button" data-vault-scope="staff" aria-pressed="false">Сотрудники <span><?php echo (int) count( $staff ); ?></span></button>
					<button class="filter" type="button" data-vault-scope="story" aria-pressed="false">Материалы <span><?php echo (int) $stories; ?></span></button>
				</div>
				<div class="filters" role="group" aria-label="Тип файла">
					<button class="filter is-on" type="button" data-vault-type="all" aria-pressed="true">Фото и видео</button>
					<button class="filter" type="button" data-vault-type="photo" aria-pressed="false">Фото <span><?php echo (int) $photos; ?></span></button>
					<button class="filter" type="button" data-vault-type="video" aria-pressed="false">Видео <span><?php echo (int) $videos; ?></span></button>
				</div>
				<label class="finder">
					<span>Найти</span>
					<input type="search" data-vault-search placeholder="Имя, тема или название" autocomplete="off">
				</label>
				<p class="directory__count" data-vault-count></p>
			</div>

			<?php if ( $staff ) : ?>
				<section class="shelf" data-shelf="staff" id="sotrudniki" aria-labelledby="staff-heading">
					<header class="shelf__head">
						<div>
							<p class="kicker">Досье</p>
							<h2 id="staff-heading">Сотрудники</h2>
						</div>
						<p class="shelf__lead">Портреты и кадры из материалов о людях журнала.</p>
					</header>
					<div class="vault vault--staff">
						<?php
						foreach ( $staff as $item ) {
							get_template_part( 'template-parts/media', 'tile', array( 'item' => $item ) );
						}
						?>
					</div>
				</section>
			<?php endif; ?>

			<?php if ( $topics ) : ?>
				<section class="shelf" data-shelf="story" id="materialy" aria-labelledby="stories-heading">
					<header class="shelf__head">
						<div>
							<p class="kicker">Журнал</p>
							<h2 id="stories-heading">Материалы по темам</h2>
						</div>
						<p class="shelf__lead">Сводки, задержания и остальные заметки — каждая тема отдельно.</p>
					</header>
					<nav class="topic-nav" aria-label="Темы">
						<?php foreach ( $topics as $topic ) : ?>
							<a href="#tema-<?php echo esc_attr( $topic['slug'] ); ?>"><?php echo esc_html( $topic['name'] ); ?></a>
						<?php endforeach; ?>
					</nav>
					<?php foreach ( $topics as $topic ) : ?>
						<section class="chapter" data-chapter id="tema-<?php echo esc_attr( $topic['slug'] ); ?>">
							<header class="chapter__head">
								<h3><?php echo esc_html( $topic['name'] ); ?></h3>
								<span class="chapter__count"><?php echo esc_html( at_ru_plural( count( $topic['items'] ), 'кадр', 'кадра', 'кадров' ) ); ?></span>
							</header>
							<div class="vault">
								<?php
								foreach ( $topic['items'] as $item ) {
									get_template_part( 'template-parts/media', 'tile', array( 'item' => $item ) );
								}
								?>
							</div>
						</section>
					<?php endforeach; ?>
				</section>
			<?php endif; ?>
			<p class="empty-note" data-vault-empty hidden>Ничего не нашли. Смените раздел, тип или запрос.</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
