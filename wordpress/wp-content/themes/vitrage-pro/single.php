<?php
/**
 * Страница отдельной новости.
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();
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
                        <p class="text-muted"><?php echo esc_html(get_the_date()); ?></p>
                        <?php if (has_post_thumbnail()) : ?>
                            <p><?php the_post_thumbnail('large', ['class' => 'img-responsive']); ?></p>
                        <?php endif; ?>
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <?php
endwhile;

get_footer();
