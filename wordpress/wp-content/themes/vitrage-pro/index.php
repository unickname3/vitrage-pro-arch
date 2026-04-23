<?php
declare(strict_types=1);

get_header();
?>
<main class="tt-wrap">
    <section class="margin-top-80 margin-bottom-80">
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('margin-bottom-40'); ?>>
                    <h1 class="tt-heading-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                    <div><?php the_excerpt(); ?></div>
                </article>
            <?php endwhile; ?>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p>Контент пока не добавлен.</p>
        <?php endif; ?>
    </section>
</main>
<?php
get_footer();
