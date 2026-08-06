<?php
/**
 * Архив «Команда»: сетка сотрудников (как оригинальная /komanda.html).
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
                <h1 class="page-header-title">Команда</h1>
                <hr class="hr-short">
                <div class="page-header-description" data-max-words="40">
                    <?php vitrage_pro_breadcrumbs('Команда'); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Team grid -->
    <section id="gallery-list-section">
        <div class="isotope-wrap tt-wrap">
            <div class="isotope col-3 gutter-3">
                <div class="isotope-items-wrap gli-colored">

                    <div class="grid-sizer"></div>

                    <?php if (!have_posts()) : ?>
                        <p>Карточки сотрудников пока не добавлены. Добавьте их в админке: «Команда → Добавить сотрудника».</p>
                    <?php endif; ?>

                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        $photo = (string) get_the_post_thumbnail_url(get_the_ID(), 'vp-grid');
                        $position = (string) get_post_meta(get_the_ID(), 'vp_team_position', true);
                        ?>
                        <div class="isotope-item custom-category">
                            <div class="gallery-list-item">
                                <div class="gl-item-image-wrap">
                                    <a href="<?php the_permalink(); ?>" class="gl-item-image-inner">
                                        <?php if ($photo) : ?>
                                            <div class="gl-item-image bg-image" style="background-image: url(<?php echo esc_url($photo); ?>); background-position: 50% 50%; background-size:contain;"></div>
                                        <?php else : ?>
                                            <div class="gl-item-image bg-gray-3"></div>
                                        <?php endif; ?>
                                        <span class="gl-item-image-zoom"></span>
                                    </a>
                                </div>
                                <div class="gl-item-info">
                                    <div class="gl-item-caption">
                                        <h2 class="gl-item-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                        <?php if ($position) : ?>
                                            <p class="gl-item-subtitle"><?php echo esc_html($position); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                </div>
            </div>
        </div>
    </section>

</main>
<?php
get_footer();
