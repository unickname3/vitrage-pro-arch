<?php
/**
 * Запасной шаблон (search, 404 и т.п.).
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();
?>
<main id="main">

    <!-- Page header -->
    <section id="page-header">
        <div class="page-header-image parallax-bg-3 bg-image" style="background-size:cover;">
            <div class="cover bg-transparent-5-dark"></div>
        </div>
        <div class="page-header-inner tt-wrap">
            <div class="page-header-caption ph-caption-lg parallax-4 fade-out-scroll-3">
                <h1 class="page-header-title">
                    <?php
                    if (is_search()) {
                        echo 'Поиск: ' . esc_html(get_search_query());
                    } elseif (is_404()) {
                        echo 'Страница не найдена';
                    } else {
                        the_archive_title();
                    }
                    ?>
                </h1>
                <hr class="hr-short">
                <div class="page-header-description" data-max-words="40">
                    <?php
                    if (is_search()) {
                        vitrage_pro_breadcrumbs('Поиск');
                    } elseif (is_404()) {
                        vitrage_pro_breadcrumbs('Страница не найдена');
                    } else {
                        vitrage_pro_breadcrumbs((string) get_the_archive_title());
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <section class="tt-wrap margin-top-80 margin-bottom-80">
        <?php if (have_posts()) : ?>
            <div class="row">
                <?php while (have_posts()) : the_post(); ?>
                    <div class="col-sm-6 col-md-4 margin-bottom-40">
                        <article <?php post_class('vp-news-card'); ?>>
                            <?php if (has_post_thumbnail()) : ?>
                                <div class="post-thumb"><a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('medium_large'); ?></a></div>
                            <?php endif; ?>
                            <h3 class="gl-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="post-content"><?php the_excerpt(); ?></div>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p>Ничего не найдено. Попробуйте изменить запрос.</p>
            <?php get_search_form(); ?>
        <?php endif; ?>
    </section>

</main>
<?php
get_footer();
