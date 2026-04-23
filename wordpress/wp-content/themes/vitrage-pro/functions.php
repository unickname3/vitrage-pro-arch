<?php
declare(strict_types=1);

require_once get_template_directory() . '/inc/cpt.php';
require_once get_template_directory() . '/inc/acf-fields.php';

function vitrage_pro_setup(): void
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus([
        'primary' => __('Primary menu', 'vitrage-pro'),
        'footer' => __('Footer menu', 'vitrage-pro'),
    ]);
}
add_action('after_setup_theme', 'vitrage_pro_setup');

function vitrage_pro_asset_base_uri(): string
{
    $theme_assets = get_template_directory_uri() . '/assets';
    return $theme_assets;
}

function vitrage_pro_enqueue_assets(): void
{
    $assets = vitrage_pro_asset_base_uri();

    wp_enqueue_style('vp-bootstrap', $assets . '/vendor/bootstrap/css/bootstrap.min.css', [], null);
    wp_enqueue_style('vp-animsition', $assets . '/vendor/animsition/css/animsition.min.css', [], null);
    wp_enqueue_style('vp-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', [], null);
    wp_enqueue_style('vp-lightgallery', $assets . '/vendor/lightgallery/css/lightgallery.min.css', [], null);
    wp_enqueue_style('vp-owl', $assets . '/vendor/owl-carousel/css/owl.carousel.min.css', [], null);
    wp_enqueue_style('vp-owl-theme', $assets . '/vendor/owl-carousel/css/owl.theme.default.min.css', ['vp-owl'], null);
    wp_enqueue_style('vp-helper', $assets . '/css/helper.css', [], null);
    wp_enqueue_style('vp-theme', $assets . '/css/theme.css', ['vp-helper'], null);
    wp_enqueue_style('vp-dark', $assets . '/css/dark-style.css', ['vp-theme'], null);
    wp_enqueue_style('vp-custom', get_template_directory_uri() . '/assets/css/custom.css', ['vp-dark'], '1.0.0');
    wp_enqueue_style('vitrage-pro', get_stylesheet_uri(), ['vp-dark'], '1.0.0');

    wp_enqueue_script('jquery');
    wp_enqueue_script('vp-bootstrap', $assets . '/vendor/bootstrap/js/bootstrap.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-animsition', $assets . '/vendor/animsition/js/animsition.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-isotope', $assets . '/vendor/isotope.pkgd.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-lightgallery', $assets . '/vendor/lightgallery/js/lightgallery.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-lightgallery-plugins', $assets . '/vendor/lightgallery/js/lightgallery-plugins.js', ['vp-lightgallery'], null, true);
    wp_enqueue_script('vp-owl', $assets . '/vendor/owl-carousel/js/owl.carousel.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-theme', $assets . '/js/theme.js', ['jquery', 'vp-owl'], null, true);
    wp_enqueue_script('vp-custom', get_template_directory_uri() . '/assets/js/custom.js', ['jquery'], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'vitrage_pro_enqueue_assets');

function vitrage_pro_body_classes(array $classes): array
{
    $classes[] = 'animsition';
    $classes[] = 'tt-boxed';
    $classes[] = 'tt-dark-style';
    return $classes;
}
add_filter('body_class', 'vitrage_pro_body_classes');

function vitrage_pro_contact_form_shortcode(): void
{
    add_shortcode('vp_contact_form', function (): string {
        if (shortcode_exists('contact-form-7')) {
            return do_shortcode('[contact-form-7 id="1" title="Контакты"]');
        }

        return '
            <form class="vp-contact-form" method="post" action="">
                <div class="form-group"><input class="form-control" type="text" name="name" placeholder="Имя" required></div>
                <div class="form-group"><input class="form-control" type="tel" name="phone" placeholder="Телефон" required></div>
                <div class="form-group"><input class="form-control" type="email" name="email" placeholder="Email"></div>
                <div class="form-group"><textarea class="form-control" name="message" rows="5" placeholder="Сообщение"></textarea></div>
                <button class="btn btn-primary" type="submit">Отправить</button>
            </form>';
    });
}
add_action('init', 'vitrage_pro_contact_form_shortcode');
