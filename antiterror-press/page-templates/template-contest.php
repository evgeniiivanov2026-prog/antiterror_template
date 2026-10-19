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
		<div class="tabs" data-tabs>
			<div class="filters" role="tablist" aria-label="Номинации">
				<button class="filter is-on" type="button" role="tab" aria-selected="true" aria-controls="nom-word" id="tab-word" data-tab>Слово о герое</button>
				<button class="filter" type="button" role="tab" aria-selected="false" aria-controls="nom-image" id="tab-image" data-tab>Образ героя</button>
				<button class="filter" type="button" role="tab" aria-selected="false" aria-controls="nom-near" id="tab-near" data-tab>Герой рядом</button>
			</div>
			<div class="noms tabs__panels" style="margin-top:22px;">
				<article class="nom" role="tabpanel" id="nom-word" aria-labelledby="tab-word" data-tab-panel data-topic="Конкурс «Герои среди нас»: Слово о герое">
					<p class="kicker">01</p>
					<strong>Слово о герое</strong>
					<p>Письмо, эссе или рассказ до 3000 знаков. Можно обратиться к конкретному человеку или к собирательному образу защитника.</p>
					<button class="btn" type="button" data-contact-open data-topic="Конкурс «Герои среди нас»: Слово о герое">Отправить работу</button>
				</article>
				<article class="nom" role="tabpanel" id="nom-image" aria-labelledby="tab-image" data-tab-panel hidden data-topic="Конкурс «Герои среди нас»: Образ героя">
					<p class="kicker">02</p>
					<strong>Образ героя</strong>
					<p>Рисунок, плакат, фотография или видео до одной минуты. Важны авторство и уважение к людям на изображении.</p>
					<button class="btn" type="button" data-contact-open data-topic="Конкурс «Герои среди нас»: Образ героя">Отправить работу</button>
				</article>
				<article class="nom" role="tabpanel" id="nom-near" aria-labelledby="tab-near" data-tab-panel hidden data-topic="Конкурс «Герои среди нас»: Герой рядом">
					<p class="kicker">03</p>
					<strong>Герой рядом</strong>
					<p>Короткий рассказ о человеке, которого участник знает лично и который проявил мужество.</p>
					<button class="btn" type="button" data-contact-open data-topic="Конкурс «Герои среди нас»: Герой рядом">Отправить работу</button>
				</article>
			</div>
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
