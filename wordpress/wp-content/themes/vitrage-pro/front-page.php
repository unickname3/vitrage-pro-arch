<?php
/**
 * Главная страница (полный порт оригинального дизайна).
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

$slides = (array) get_option('vp_hero_slides', []);
if (empty($slides)) {
    // Слайд по умолчанию, пока владелец не добавил свои.
    $default_image = VITRAGE_PRO_URI . '/assets/img/hero-default.jpg';
    $slides = [[
        'image'    => $default_image,
        'title'    => (string) get_option('vp_hero_title', 'Профессиональное проектирование и изготовление художественных витражей'),
        'subtitle' => (string) get_option('vp_hero_subtitle', 'Авторских светильников и мозаичных панно на заказ'),
        'btn_text' => '',
        'btn_url'  => '',
    ]];
}

$about_title = vitrage_pro_opt('vp_about_title', 'Мы создаем уникальные витражи, предметы интерьера и мозаичные панно для вашего интерьера.');
$about_text = vitrage_pro_opt('vp_about_text', '');
if ($about_text === '') {
    $about_text = 'Мы создаем уникальные витражи, предметы интерьера и мозаичные панно для вашего интерьера. Приоритетное направление нашей деятельности – сложные высокохудожественные работы. Мы работаем только с настоящим витражным стеклом, окрашенным в массе в заводских условиях. Оно не теряет своего цвета и качества, устойчиво к различным воздействиям среды. Художественные изделия из такого стекла со временем приобретают ценность как антиквариат. Преимущественно используемая нами технология сборки витража – тиффани. При необходимости применяются и другие техники: спекание (фьюзинг), обжиговая роспись, сборка на латунный профиль.';
}

$gallery_title = vitrage_pro_opt('vp_gallery_title', 'Наши работы');
$gallery_subtitle = vitrage_pro_opt('vp_gallery_subtitle', 'Свежие проекты и работы');
$gallery_archive = get_post_type_archive_link('gallery_item');

$show_testimonials = (int) get_option('vp_show_testimonials', 1);
$testimonials_title = vitrage_pro_opt('vp_testimonials_title', '');
$testimonials_subtitle = vitrage_pro_opt('vp_testimonials_subtitle', '');

$cta_title = vitrage_pro_opt('vp_cta_title', 'Задать вопрос');
$cta_subtitle = vitrage_pro_opt('vp_cta_subtitle', 'Интересны наши работы');
$cta_btn1_text = vitrage_pro_opt('vp_cta_button1_text', 'Подробнее о нас');
$cta_btn1_url = vitrage_pro_opt('vp_cta_button1_url', '');
$cta_btn2_text = vitrage_pro_opt('vp_cta_button2_text', 'Оставить заявку');
$cta_btn2_url = vitrage_pro_opt('vp_cta_button2_url', '');

if ($cta_btn1_url === '') {
    $about_page = get_page_by_path('about');
    $cta_btn1_url = $about_page ? get_permalink($about_page) : home_url('/about/');
}
if ($cta_btn2_url === '') {
    $cta_btn2_url = vitrage_pro_contacts_url();
}
?>

<main id="main">

    <!-- ===== Begin intro (hero) ===== -->
    <section id="tt-intro" class="slideshow-intro">
        <div class="tt-intro-inner">
            <div class="gl-carousel-wrap no-padding">
                <div class="owl-carousel cc-height-5 cursor-grab dots-right bg-dark"
                     data-items="1" data-loop="true" data-nav="true" data-nav-speed="500"
                     data-dots-speed="500" data-autoplay="true" data-autoplay-timeout="8000"
                     data-autoplay-speed="500" data-autoplay-hover-pause="true">

                    <?php foreach ($slides as $slide) : ?>
                        <?php
                        $slide = (array) $slide;
                        $img = isset($slide['image']) ? (string) $slide['image'] : '';
                        $title = isset($slide['title']) ? (string) $slide['title'] : '';
                        $subtitle = isset($slide['subtitle']) ? (string) $slide['subtitle'] : '';
                        $btn_text = isset($slide['btn_text']) ? (string) $slide['btn_text'] : '';
                        $btn_url = isset($slide['btn_url']) ? (string) $slide['btn_url'] : '';
                        ?>
                        <div class="cc-item">
                            <span class="cover bg-transparent-3-dark"></span>
                            <div class="cc-image bg-image" style="background-image: url(<?php echo esc_url($img); ?>); background-position: 50% 50%;"></div>
                            <div class="intro-caption caption-animate intro-caption-xxlg center-left">
                                <?php if ($title) : ?>
                                    <h2 class="intro-subtitle"><?php echo esc_html($title); ?></h2>
                                <?php endif; ?>
                                <?php if ($subtitle) : ?>
                                    <p class="intro-description max-width-650"><?php echo esc_html($subtitle); ?></p>
                                <?php endif; ?>
                                <?php if ($btn_text && $btn_url) : ?>
                                    <div class="margin-top-30">
                                        <a href="<?php echo esc_url($btn_url); ?>" class="btn btn-primary"><?php echo esc_html($btn_text); ?></a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                </div>
            </div>
        </div>
    </section>
    <!-- End intro -->

    <!-- ===== Begin about section ===== -->
    <section id="about-me-section">
        <div class="about-me-inner">
            <div class="split-box about-me">
                <div class="container-fluid">
                    <div class="row">
                        <div class="row-lg-height">

                            <div class="col-lg-5 col-lg-height split-box-image no-padding bg-image"
                                 style="background-image: url(<?php echo esc_url(VITRAGE_PRO_URI . '/assets/img/about-default.jpg'); ?>); background-position: 50% 50%;">
                                <div class="sbi-height padding-height-80"></div>
                            </div>

                            <div class="col-lg-7 col-lg-height col-lg-middle no-padding">
                                <div class="full-cover for-light-style bg-gray-3 bg-image" style="background-image: url(<?php echo esc_url(VITRAGE_PRO_URI . '/assets/img/pattern/bg-pattern-1-light.png'); ?>); background-position: 50% 50%;"></div>
                                <div class="full-cover for-dark-style bg-gray-3 bg-image" style="background-image: url(<?php echo esc_url(VITRAGE_PRO_URI . '/assets/img/pattern/bg-pattern-1-dark.png'); ?>); background-position: 50% 50%;"></div>

                                <div class="split-box-content sb-content-right">
                                    <div class="tt-heading">
                                        <div class="tt-heading-inner">
                                            <h1 class="tt-heading-title"><?php echo esc_html($about_title); ?></h1>
                                            <div class="tt-heading-subtitle"></div>
                                            <hr class="hr-short">
                                        </div>
                                    </div>

                                    <div class="margin-top-30">
                                        <?php echo wpautop(wp_kses_post($about_text)); ?>
                                    </div>

                                    <?php
                                    $about_btn1 = vitrage_pro_opt('vp_about_button_text', 'Подробнее');
                                    $about_btn1_url = vitrage_pro_opt('vp_about_button_url', '');
                                    if ($about_btn1_url === '') {
                                        $about_page = get_page_by_path('about');
                                        $about_btn1_url = $about_page ? get_permalink($about_page) : home_url('/about/');
                                    }
                                    $about_btn2 = vitrage_pro_opt('vp_about_button2_text', 'Задать вопрос');
                                    $about_btn2_url = vitrage_pro_opt('vp_about_button2_url', vitrage_pro_contacts_url());
                                    ?>
                                    <?php if ($about_btn1) : ?>
                                        <a href="<?php echo esc_url($about_btn1_url); ?>" class="btn btn-primary margin-top-20"><?php echo esc_html($about_btn1); ?></a>
                                    <?php endif; ?>
                                    <?php if ($about_btn2) : ?>
                                        <a href="<?php echo esc_url($about_btn2_url); ?>" class="btn btn-dark margin-top-20"><?php echo esc_html($about_btn2); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End about section -->

    <!-- ===== Begin gallery list section ===== -->
    <section id="gallery-list-section">
        <div class="tt-heading tt-heading-lg padding-on text-center">
            <div class="tt-heading-inner tt-wrap">
                <h1 class="tt-heading-title"><?php echo esc_html($gallery_title); ?></h1>
                <div class="tt-heading-subtitle"><?php echo esc_html($gallery_subtitle); ?>
                    <?php if ($gallery_archive) : ?>
                        / <a href="<?php echo esc_url($gallery_archive); ?>">Посмотреть все</a>
                    <?php endif; ?>
                </div>
                <hr class="hr-short">
            </div>
        </div>

        <div class="isotope-wrap">
            <div class="isotope col-4 gutter-3 custom-item">
                <div class="isotope-items-wrap gli-colored gli-alter-5">
                    <div class="grid-sizer"></div>

                    <?php
                    $categories = get_terms([
                        'taxonomy'   => 'gallery_category',
                        'hide_empty' => false,
                        'orderby'    => 'slug',
                        'order'      => 'ASC',
                        'number'     => 8,
                    ]);

                    foreach ($categories as $category) :
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
    <!-- End gallery list section -->

    <?php if ($show_testimonials) : ?>
        <?php
        $reviews = get_posts([
            'post_type'      => 'review_item',
            'posts_per_page' => 5,
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ]);
        ?>
        <?php if ($reviews) : ?>
            <!-- ===== Begin testimonials ===== -->
            <section id="testimonials-section" class="bg-dark bg-image-fixed">
                <span class="cover bg-transparent-7-dark"></span>

                <div class="testimonials-section-inner tt-wrap">
                    <div class="testimonials-carousel tm-center">

                        <?php if ($testimonials_title) : ?>
                            <div class="tt-heading tt-heading-lg text-center">
                                <div class="tt-heading-inner tt-wrap">
                                    <h1 class="tt-heading-title text-white"><?php echo esc_html($testimonials_title); ?></h1>
                                    <?php if ($testimonials_subtitle) : ?>
                                        <div class="tt-heading-subtitle text-gray-3"><?php echo esc_html($testimonials_subtitle); ?></div>
                                    <?php endif; ?>
                                    <hr class="hr-short">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="owl-carousel cursor-grab nav-outside dots-outside"
                             data-items="1" data-loop="true" data-autoheight="true" data-nav="true"
                             data-nav-speed="500" data-dots-speed="500" data-autoplay="true"
                             data-autoplay-timeout="8000" data-autoplay-speed="500" data-autoplay-hover-pause="true">

                            <?php foreach ($reviews as $review) : ?>
                                <?php
                                $city = (string) get_post_meta($review->ID, 'vp_review_city', true);
                                $avatar = get_the_post_thumbnail_url($review->ID, 'thumbnail');
                                $author = get_the_title($review->ID);
                                if ($city) {
                                    $author = $author . ', ' . $city;
                                }
                                ?>
                                <div class="cc-item">
                                    <div class="testimonial-item text-white">
                                        <div class="tm-image bg-image" style="background-image: url(<?php echo esc_url($avatar ?: VITRAGE_PRO_URI . '/assets/img/noimage.png'); ?>); background-position: 50% 50%;"></div>
                                        <blockquote>
                                            <p><?php echo wp_kses_post(get_the_excerpt($review)); ?></p>
                                            <small><?php echo esc_html($author); ?></small>
                                        </blockquote>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                        </div>
                    </div>
                </div>
            </section>
            <!-- End testimonials -->
        <?php endif; ?>
    <?php endif; ?>

    <!-- ===== Begin CTA ===== -->
    <section class="call-to-action-section bg-gray-3">
        <div class="full-cover for-light-style bg-image" style="background-image: url(<?php echo esc_url(VITRAGE_PRO_URI . '/assets/img/pattern/bg-pattern-2-light.png'); ?>); background-position: 50% 50%;"></div>
        <div class="full-cover for-dark-style bg-image" style="background-image: url(<?php echo esc_url(VITRAGE_PRO_URI . '/assets/img/pattern/bg-pattern-2-dark.png'); ?>); background-position: 50% 50%;"></div>

        <div class="call-to-action-inner tt-wrap">
            <div class="row">
                <div class="col-md-12 text-center">
                    <div class="tt-heading tt-heading-lg text-center">
                        <div class="tt-heading-inner tt-wrap">
                            <h2 class="tt-heading-title"><?php echo esc_html($cta_title); ?></h2>
                            <?php if ($cta_subtitle) : ?>
                                <div class="tt-heading-subtitle"><?php echo esc_html($cta_subtitle); ?></div>
                            <?php endif; ?>
                            <hr class="hr-short">
                        </div>
                    </div>

                    <div class="margin-top-30 max-width-1000 margin-auto">
                        <div class="margin-top-30">
                            <a href="<?php echo esc_url($cta_btn1_url); ?>" class="btn btn-dark margin-top-5 margin-right-5"><?php echo esc_html($cta_btn1_text); ?></a>
                            <a href="<?php echo esc_url($cta_btn2_url); ?>" class="btn btn-primary margin-top-5"><?php echo esc_html($cta_btn2_text); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End CTA -->

</main>

<?php
get_footer();
