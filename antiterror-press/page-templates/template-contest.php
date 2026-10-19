<?php
/**
 * Конкурс «Герои среди нас».
 *
 * Template Name: Герои среди нас
 *
 * @package antiterror-press
 */

get_header();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Конкурс</p>
			<h1><?php the_title(); ?></h1>
			<p class="deck">Они не носят плащи. Бойцы, спасатели, врачи и просто решительные люди живут рядом. Три номинации, приём работ с 1 по 30 октября, возраст от 14 лет.</p>
		</header>
		<div class="at-tabs" data-tabs style="margin-top:22px;">
			<div class="at-tabs__list" role="tablist" aria-label="Номинации">
				<button class="at-tabs__tab is-on" type="button" role="tab" id="at-nom-tab-1" aria-controls="at-nom-panel-1" aria-selected="true">01 · Слово о герое</button>
				<button class="at-tabs__tab" type="button" role="tab" id="at-nom-tab-2" aria-controls="at-nom-panel-2" aria-selected="false" tabindex="-1">02 · Образ героя</button>
				<button class="at-tabs__tab" type="button" role="tab" id="at-nom-tab-3" aria-controls="at-nom-panel-3" aria-selected="false" tabindex="-1">03 · Герой рядом</button>
			</div>
			<article class="at-tabs__panel nom is-on" role="tabpanel" id="at-nom-panel-1" aria-labelledby="at-nom-tab-1">
				<p class="kicker">01</p>
				<strong>Слово о герое</strong>
				<p>Письмо, эссе или рассказ до 3000 знаков. Можно обратиться к конкретному человеку или к собирательному образу защитника.</p>
				<button class="btn" type="button" data-contact-open data-contact-topic="Конкурс: Слово о герое">Отправить в эту номинацию</button>
			</article>
			<article class="at-tabs__panel nom" role="tabpanel" id="at-nom-panel-2" aria-labelledby="at-nom-tab-2">
				<p class="kicker">02</p>
				<strong>Образ героя</strong>
				<p>Рисунок, плакат, фотография или видео до одной минуты. Важны авторство и уважение к людям на изображении.</p>
				<button class="btn" type="button" data-contact-open data-contact-topic="Конкурс: Образ героя">Отправить в эту номинацию</button>
			</article>
			<article class="at-tabs__panel nom" role="tabpanel" id="at-nom-panel-3" aria-labelledby="at-nom-tab-3">
				<p class="kicker">03</p>
				<strong>Герой рядом</strong>
				<p>Короткий рассказ о человеке, которого участник знает лично и который проявил мужество.</p>
				<button class="btn" type="button" data-contact-open data-contact-topic="Конкурс: Герой рядом">Отправить в эту номинацию</button>
			</article>
		</div>
		<?php
		while ( have_posts() ) :
			the_post();
			if ( trim( get_the_content() ) ) {
				echo '<div class="prose" style="margin-top:28px;">';
				the_content();
				echo '</div>';
			}
		endwhile;
		?>
		<section class="section narrow">
			<div class="section__head">
				<h2>Отправить работу</h2>
			</div>
			<p>Почта конкурса: <a href="mailto:geroisredinas@antiterror.press">geroisredinas@antiterror.press</a>. В теме письма укажите номинацию. Либо заполните форму.</p>
			<?php get_template_part( 'template-parts/contact', 'form', array( 'topic' => 'Конкурс «Герои среди нас»' ) ); ?>
		</section>
	</div>
</main>
<?php
get_footer();
