<?php
/**
 * Вехи истории: хронология и алфавитный указатель.
 *
 * Template Name: Вехи истории
 *
 * @package antiterror-press
 */

get_header();

$milestones = at_milestones();
?>
<main id="content" class="page">
	<div class="wrap">
		<header class="page-hero">
			<p class="kicker">Память</p>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="deck"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php else : ?>
				<p class="deck">События подразделений, которые боролись с захватами и терактами. Сначала годы, затем алфавит: Норд-Ост, Дубровка, Беслан и остальные имена, которые нельзя забывать.</p>
			<?php endif; ?>
		</header>

		<?php if ( $milestones ) : ?>
			<div class="vehi-rail" data-vehi-rail>
				<?php foreach ( $milestones as $item ) : ?>
					<?php $year = get_post_meta( $item->ID, '_at_year', true ); ?>
					<a class="vehi-rail__item" href="<?php echo esc_url( get_permalink( $item ) ); ?>">
						<span class="vehi-rail__year"><?php echo esc_html( $year ); ?></span>
						<span class="vehi-rail__name"><?php echo esc_html( get_the_title( $item ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<section class="vehi" aria-labelledby="vehi-index">
			<div class="section__head">
				<h2 id="vehi-index">По алфавиту</h2>
				<p class="vehi-note"><?php echo esc_html( sprintf( '%d %s', count( $milestones ), at_milestone_word( count( $milestones ) ) ) ); ?></p>
			</div>
			<p class="vehi-home__lead">Под каждой буквой — сами события. Нажмите имя, чтобы открыть карточку.</p>
			<?php get_template_part( 'template-parts/milestones', 'index', array( 'posts' => $milestones ) ); ?>
		</section>
	</div>
</main>
<?php
get_footer();
