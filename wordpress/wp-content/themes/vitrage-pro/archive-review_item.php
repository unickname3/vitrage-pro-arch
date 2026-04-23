<?php
declare(strict_types=1);

get_header();
?>
<main class="tt-wrap margin-top-80 margin-bottom-80">
    <h1 class="page-header-title">Отзывы</h1>
    <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('margin-bottom-40'); ?>>
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div><?php the_excerpt(); ?></div>
            </article>
        <?php endwhile; ?>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p>Отзывы пока не добавлены.</p>
    <?php endif; ?>
</main>
<?php
get_footer();
