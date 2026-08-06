<?php
/**
 * Архив галереи: сетка категорий (как оригинальная страница /gallery.html).
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

$categories = get_terms([
    'taxonomy'   => 'gallery_category',
    'hide_empty' => false,
    'orderby'    => 'slug',
    'order'      => 'ASC',
]);
?>
<main id="main">

    <!-- Page header -->
    <section id="page-header">
        <div class="page-header-image parallax-bg-3 bg-image" style="background-size:cover;">
            <div class="cover bg-transparent-5-dark"></div>
        </div>
        <div class="page-header-inner tt-wrap">
            <div class="page-header-caption ph-caption-lg parallax-4 fade-out-scroll-3">
                <h1 class="page-header-title">Галерея</h1>
                <hr class="hr-short">
                <div class="page-header-description" data-max-words="40">
                    <?php vitrage_pro_breadcrumbs('Галерея'); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery list -->
    <section id="gallery-list-section">
        <div class="isotope-wrap tt-wrap">
            <div class="isotope col-3 gutter-3">
                <div class="isotope-items-wrap gli-colored">

                    <div class="grid-sizer"></div>

                    <?php if (empty($categories) || is_wp_error($categories)) : ?>
                        <p>Категории галереи ещё не созданы. Добавьте их в админке: «Галерея → Категории галереи».</p>
                    <?php endif; ?>

                    <?php foreach ((array) $categories as $category) : ?>
                        <?php
                        $cover = vitrage_pro_category_cover((int) $category->term_id);
                        $link = get_term_link($category);
                        if (is_wp_error($link)) {
                            continue;
                        }
                        ?>
                        <div class="isotope-item custom-category">
                            <div class="gallery-list-item">
                                <div class="gl-item-image-wrap">
                                    <a href="<?php echo esc_url($link); ?>" class="gl-item-image-inner">
                                        <?php if ($cover) : ?>
                                            <div class="gl-item-image bg-image" style="background-image: url(<?php echo esc_url($cover); ?>); background-position: 50% 50%; background-size:contain;"></div>
                                        <?php else : ?>
                                            <div class="gl-item-image bg-gray-3"></div>
                                        <?php endif; ?>
                                        <span class="gl-item-image-zoom"></span>
                                    </a>
                                </div>
                                <div class="gl-item-info">
                                    <div class="gl-item-caption">
                                        <h2 class="gl-item-title"><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($category->name); ?></a></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </section>

</main>
<?php
get_footer();
