<?php
/**
 * Страница сотрудника.
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

while (have_posts()) :
    the_post();
    $photo = (string) get_the_post_thumbnail_url(get_the_ID(), 'medium_large');
    $position = (string) get_post_meta(get_the_ID(), 'vp_team_position', true);
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
                    <?php if ($position) : ?>
                        <div class="page-header-subtitle"><?php echo esc_html($position); ?></div>
                    <?php endif; ?>
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
                    <div class="col-md-4">
                        <?php if ($photo) : ?>
                            <img src="<?php echo esc_url($photo); ?>" alt="<?php the_title_attribute(); ?>" class="img-responsive margin-bottom-30" />
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <div class="post-content">
                            <?php the_content(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
    <?php
endwhile;

get_footer();
