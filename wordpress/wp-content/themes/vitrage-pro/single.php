<?php
declare(strict_types=1);

get_header();
?>
<main class="tt-wrap margin-top-80 margin-bottom-80">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
            <h1 class="page-header-title"><?php the_title(); ?></h1>
            <?php if (has_post_thumbnail()) : ?>
                <div class="margin-bottom-30"><?php the_post_thumbnail('large', ['class' => 'img-responsive']); ?></div>
            <?php endif; ?>
            <div class="post-content"><?php the_content(); ?></div>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
