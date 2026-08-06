<?php
/**
 * Архив «Отзывы»: карусель отзывов (как оригинальная /reviews.html).
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
                <h1 class="page-header-title">Отзывы</h1>
                <hr class="hr-short">
                <div class="page-header-description" data-max-words="40">
                    <?php vitrage_pro_breadcrumbs('Отзывы'); ?>
                </div>
            </div>
        </div>
    </section>

    <?php if (!have_posts()) : ?>
        <div class="tt-wrap margin-top-80">
            <p>Отзывы пока не добавлены. Добавьте их в админке: «Отзывы → Добавить отзыв».</p>
        </div>
    <?php endif; ?>

    <?php if (have_posts()) : ?>
        <section id="testimonials-section" class="margin-top-40">
            <div class="testimonials-section-inner tt-wrap">
                <div class="testimonials-carousel tm-center">

                    <div class="owl-carousel cursor-grab nav-outside dots-outside"
                         data-items="1" data-loop="true" data-autoheight="true" data-nav="true"
                         data-nav-speed="500" data-dots-speed="500" data-autoplay="true"
                         data-autoplay-timeout="8000" data-autoplay-speed="500" data-autoplay-hover-pause="true">

                        <?php while (have_posts()) : the_post(); ?>
                            <?php
                            $city = (string) get_post_meta(get_the_ID(), 'vp_review_city', true);
                            $avatar = (string) get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                            $author = get_the_title();
                            if ($city) {
                                $author = $author . ', ' . $city;
                            }
                            $review_link = get_permalink();
                            ?>
                            <div class="cc-item">
                                <div class="testimonial-item text-white">
                                    <div class="tm-image bg-image" style="background-image: url(<?php echo esc_url($avatar ?: VITRAGE_PRO_URI . '/assets/img/noimage.png'); ?>); background-position: 50% 50%;"></div>
                                    <blockquote>
                                        <p><?php echo esc_html(wp_trim_words((string) get_the_content(), 60, '…')); ?></p>
                                        <small><a href="<?php echo esc_url($review_link); ?>"><?php echo esc_html($author); ?></a></small>
                                    </blockquote>
                                </div>
                            </div>
                        <?php endwhile; ?>

                    </div>
                </div>
            </div>
        </section>

        <!-- Список отзывов -->
        <section class="tt-wrap margin-top-80 margin-bottom-80">
            <div class="row">
                <?php rewind_posts(); ?>
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $city = (string) get_post_meta(get_the_ID(), 'vp_review_city', true);
                    ?>
                    <div class="col-sm-6 col-md-4 margin-bottom-30">
                        <article <?php post_class('vp-review-card'); ?>>
                            <blockquote class="post-content">
                                <?php the_excerpt(); ?>
                            </blockquote>
                            <p class="margin-bottom-0">
                                <strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
                                <?php if ($city) : ?><br><?php echo esc_html($city); ?><?php endif; ?>
                            </p>
                        </article>
                    </div>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        </section>
    <?php endif; ?>

</main>
<?php
get_footer();
