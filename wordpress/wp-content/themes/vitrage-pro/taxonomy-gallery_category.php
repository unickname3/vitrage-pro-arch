<?php
/**
 * Страница категории галереи: сетка фотографий с лайтбоксом.
 * (Аналог /gallery/okna.html в оригинале.)
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

$term = get_queried_object();
$term_name = $term && !is_wp_error($term) ? $term->name : 'Галерея';
$term_desc = $term && !is_wp_error($term) ? term_description($term) : '';

$items = get_posts([
    'post_type'      => 'gallery_item',
    'posts_per_page' => -1,
    'tax_query'      => $term && !is_wp_error($term) ? [[
        'taxonomy' => 'gallery_category',
        'field'    => 'term_id',
        'terms'    => (int) $term->term_id,
    ]] : [],
    'orderby'        => 'menu_order date',
    'order'          => 'ASC',
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
                <h1 class="page-header-title"><?php echo esc_html($term_name); ?></h1>
                <hr class="hr-short">
                <div class="page-header-description" data-max-words="40">
                    <?php vitrage_pro_breadcrumbs($term_name); ?>
                </div>
            </div>
        </div>
    </section>

    <?php if ($term_desc) : ?>
        <div class="blog-single-post-inner tt-heading padding-on">
            <div class="container">
                <div class="row">
                    <div class="post-content">
                        <?php echo wp_kses_post(wpautop($term_desc)); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Gallery single -->
    <section id="gallery-single-section">
        <div class="isotope-wrap tt-wrap">
            <div class="isotope col-4 gutter-3">
                <div id="gallery" class="isotope-items-wrap lightgallery">
                    <div class="grid-sizer"></div>

                    <?php if (!$items) : ?>
                        <p>В этой категории пока нет фотографий. Добавьте их в админке: «Галерея → Добавить».</p>
                    <?php endif; ?>

                    <?php foreach ($items as $item) : ?>
                        <?php
                        $full = (string) get_the_post_thumbnail_url($item->ID, 'large');
                        $thumb = (string) get_the_post_thumbnail_url($item->ID, 'medium');
                        $caption = esc_html(get_the_title($item->ID));
                        if (!$full) {
                            continue;
                        }
                        ?>
                        <div class="isotope-item">
                            <a href="<?php echo esc_url($full); ?>" class="gallery-single-item lg-trigger" data-sub-html="<p><?php echo esc_attr($caption); ?></p>">
                                <img src="<?php echo esc_url($thumb ?: $full); ?>" class="gs-item-image" alt="<?php echo esc_attr($caption); ?>" />
                                <div class="gsi-image-caption"><?php echo esc_html($caption); ?></div>
                                <div class="gs-item-icon"><i class="fa fa-search"></i></div>
                            </a>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </section>

</main>
<?php
get_footer();
