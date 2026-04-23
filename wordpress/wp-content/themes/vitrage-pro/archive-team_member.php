<?php
declare(strict_types=1);

get_header();
?>
<main class="tt-wrap margin-top-80 margin-bottom-80">
    <h1 class="page-header-title">Команда</h1>
    <?php if (have_posts()) : ?>
        <div class="row">
            <?php while (have_posts()) : the_post(); ?>
                <article <?php post_class('col-sm-6 col-md-4 margin-bottom-30'); ?>>
                    <a href="<?php the_permalink(); ?>">
                        <?php if (has_post_thumbnail()) { the_post_thumbnail('medium_large', ['class' => 'img-responsive']); } ?>
                        <h2 class="gl-item-title"><?php the_title(); ?></h2>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <p>Карточки сотрудников пока не добавлены.</p>
    <?php endif; ?>
</main>
<?php
get_footer();
