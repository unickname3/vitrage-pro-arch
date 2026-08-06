<?php
/**
 * Страница отдельной работы.
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();
    $full = (string) get_the_post_thumbnail_url(get_the_ID(), 'large');
    $terms = get_the_terms(get_the_ID(), 'gallery_category');
    ?>
    <main id="main">

        <!-- Page header -->
        <section id="page-header">
            <div class="page-header-image parallax-bg-3 bg-image" style="background-size:cover;">
                <div class="cover bg-transparent-5-dark"></div>
            </div>
            <div class="page-header-inner tt-wrap">
                <div class="page-header-caption ph-caption-lg parallax-4 fade-out-scroll-3">
                    <h1 class="page-header-title"><?php the_title(); ?></h1>
                    <hr class="hr-short">
                    <div class="page-header-description" data-max-words="40">
                        <?php vitrage_pro_breadcrumbs(get_the_title()); ?>
                    </div>
                </div>
            </div>
        </section>

        <div class="blog-single-post-inner tt-heading padding-on">
            <div class="container">
                <div class="row">
                    <div class="post-content">
                        <?php if ($full) : ?>
                            <p><img src="<?php echo esc_url($full); ?>" alt="<?php the_title_attribute(); ?>" class="img-responsive" /></p>
                        <?php endif; ?>
                        <?php the_content(); ?>

                        <?php if ($terms && !is_wp_error($terms)) : ?>
                            <p class="margin-top-20">
                                <strong>Категория: </strong>
                                <?php foreach ($terms as $i => $t) : ?>
                                    <?php $link = get_term_link($t); ?>
                                    <?php if (!is_wp_error($link)) : ?>
                                        <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($t->name); ?></a><?php echo $i < count($terms) - 1 ? ', ' : ''; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <?php
endwhile;

get_footer();
