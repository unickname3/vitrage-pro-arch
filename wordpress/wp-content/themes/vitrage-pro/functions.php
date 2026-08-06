<?php
/**
 * Vitrage Pro theme functions.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('VITRAGE_PRO_VERSION', '1.0.0');
define('VITRAGE_PRO_DIR', get_template_directory());
define('VITRAGE_PRO_URI', get_template_directory_uri());

require_once VITRAGE_PRO_DIR . '/inc/cpt.php';
require_once VITRAGE_PRO_DIR . '/inc/meta-boxes.php';
require_once VITRAGE_PRO_DIR . '/inc/settings-page.php';
require_once VITRAGE_PRO_DIR . '/inc/form.php';
require_once VITRAGE_PRO_DIR . '/inc/setup.php';
require_once VITRAGE_PRO_DIR . '/inc/importer.php';

/**
 * Theme setup.
 */
function vitrage_pro_setup(): void
{
    load_theme_textdomain('vitrage-pro', VITRAGE_PRO_DIR . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary' => __('Основное меню', 'vitrage-pro'),
        'footer'  => __('Меню в подвале', 'vitrage-pro'),
    ]);

    add_image_size('vp-grid', 600, 450, true);   // карточки галереи и команды.
    add_image_size('vp-wide', 1400, 700, true);  // слайды hero.
}
add_action('after_setup_theme', 'vitrage_pro_setup');

/**
 * Register widget area (если понадобится).
 */
function vitrage_pro_widgets_init(): void
{
    register_sidebar([
        'name'          => __('Сайдбар', 'vitrage-pro'),
        'id'            => 'sidebar-1',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="widget-title">',
        'after_title'   => '</h4>',
    ]);
}
add_action('widgets_init', 'vitrage_pro_widgets_init');

/**
 * Enqueue styles and scripts.
 */
function vitrage_pro_enqueue_assets(): void
{
    $assets = VITRAGE_PRO_URI . '/assets';

    // Styles.
    wp_enqueue_style('vp-google-fonts', 'https://fonts.googleapis.com/css?family=Montserrat:100,200,300,400,500,600,700,800,900&subset=cyrillic', [], null);
    wp_enqueue_style('vp-bootstrap', $assets . '/vendor/bootstrap/css/bootstrap.min.css', [], null);
    wp_enqueue_style('vp-animsition', $assets . '/vendor/animsition/css/animsition.min.css', [], null);
    wp_enqueue_style('vp-fontawesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', [], '4.7.0');
    wp_enqueue_style('vp-lightgallery', $assets . '/vendor/lightgallery/css/lightgallery.min.css', [], null);
    wp_enqueue_style('vp-owl', $assets . '/vendor/owl-carousel/css/owl.carousel.min.css', [], null);
    wp_enqueue_style('vp-owl-theme', $assets . '/vendor/owl-carousel/css/owl.theme.default.min.css', ['vp-owl'], null);
    wp_enqueue_style('vp-animate', $assets . '/vendor/animate.min.css', [], null);
    wp_enqueue_style('vp-ytplayer', $assets . '/vendor/ytplayer/css/jquery.mb.YTPlayer.min.css', [], null);
    wp_enqueue_style('vp-helper', $assets . '/css/helper.css', [], null);
    wp_enqueue_style('vp-theme', $assets . '/css/theme.css', ['vp-helper'], null);
    wp_enqueue_style('vp-dark', $assets . '/css/dark-style.css', ['vp-theme'], null);
    wp_enqueue_style('vp-custom', $assets . '/css/custom.css', ['vp-dark'], VITRAGE_PRO_VERSION);
    wp_enqueue_style('vitrage-pro', get_stylesheet_uri(), ['vp-custom'], VITRAGE_PRO_VERSION);

    // Scripts.
    wp_enqueue_script('jquery');
    // Шим jQuery.browser (удалён из jQuery 1.9+) — до theme.js.
    wp_enqueue_script('vp-browser-shim', false, ['jquery'], null, true);
    wp_add_inline_script('vp-browser-shim', "if (!window.jQuery.browser) { window.jQuery.browser = { mobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) }; }");
    wp_enqueue_script('vp-bootstrap', $assets . '/vendor/bootstrap/js/bootstrap.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-animsition', $assets . '/vendor/animsition/js/animsition.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-easing', $assets . '/vendor/jquery.easing.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-isotope', $assets . '/vendor/isotope.pkgd.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-imagesloaded', $assets . '/vendor/imagesloaded.pkgd.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-owl', $assets . '/vendor/owl-carousel/js/owl.carousel.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-mousewheel', $assets . '/vendor/jquery.mousewheel.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-ytplayer', $assets . '/vendor/ytplayer/js/jquery.mb.YTPlayer.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-lightgallery', $assets . '/vendor/lightgallery/js/lightgallery.min.js', ['jquery'], null, true);
    wp_enqueue_script('vp-lightgallery-plugins', $assets . '/vendor/lightgallery/js/lightgallery-plugins.js', ['vp-lightgallery'], null, true);
    wp_enqueue_script('vp-theme', $assets . '/js/theme.js', ['jquery', 'vp-owl', 'vp-animsition', 'vp-lightgallery'], null, true);
    wp_enqueue_script('vp-custom', $assets . '/js/custom.js', ['jquery'], VITRAGE_PRO_VERSION, true);

    // Скрипты админки (медиа-библиотека в мета-боксах и настройках).
    if (is_admin()) {
        wp_enqueue_media();
        wp_enqueue_script('vp-admin', VITRAGE_PRO_URI . '/assets/js/admin.js', ['jquery', 'wp-util'], VITRAGE_PRO_VERSION, true);
        wp_enqueue_style('vp-admin', VITRAGE_PRO_URI . '/assets/css/admin.css', [], VITRAGE_PRO_VERSION);
    }

    // Данные для JS: URL обработчика формы.
    wp_localize_script('vp-custom', 'vpForm', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'action'  => 'vp_send_message',
        'nonce'   => wp_create_nonce('vp_form_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'vitrage_pro_enqueue_assets');

/**
 * Body classes: тёмная тема, boxed layout, анимация переходов.
 */
function vitrage_pro_body_classes(array $classes): array
{
    $classes[] = 'animsition';
    $classes[] = 'tt-boxed';
    $classes[] = 'tt-dark-style';
    return $classes;
}
add_filter('body_class', 'vitrage_pro_body_classes');

/**
 * Кастомный walker меню: разметка под дизайн шаблона (tt-submenu и т.д.).
 */
class Vitrage_Pro_Walker_Nav_Menu extends Walker_Nav_Menu
{
    /**
     * @param string   $output
     * @param WP_Post  $item
     * @param int      $depth
     * @param stdClass $args
     */
    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $class = $depth === 0 ? 'tt-submenu' : 'tt-submenu tt-submenu-sub';
        $output .= '<ul class="' . $class . '">';
    }

    /**
     * @param string   $output
     * @param WP_Post  $item
     * @param int      $depth
     * @param stdClass $args
     */
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $has_children = in_array('menu-item-has-children', $classes, true);
        $li_class = 'tt-submenu-wrap tt-submenu-master';
        if (!$has_children) {
            $li_class = '';
        }

        $output .= '<li class="' . esc_attr($li_class) . '">';
        $output .= '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
    }

    /**
     * @param string   $output
     * @param WP_Post  $item
     * @param int      $depth
     * @param stdClass $args
     */
    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $output .= '</li>';
    }
}

/**
 * Меню-заглушка, если меню не создано.
 */
function vitrage_pro_menu_fallback(): void
{
    $pages = [
        'about'    => 'Мастерская',
        'gallery'  => 'Галерея',
        'komanda'  => 'Команда',
        'reviews'  => 'Отзывы',
        'news'     => 'Новости',
        'price'    => 'Цены',
        'contacts' => 'Контакты',
    ];

    echo '<ul class="tt-menu-nav">';
    foreach ($pages as $slug => $title) {
        $page = get_page_by_path($slug);
        $url = $page ? get_permalink($page) : home_url('/' . $slug . '/');
        echo '<li><a href="' . esc_url($url) . '">' . esc_html($title) . '</a></li>';
    }
    echo '</ul>';
}

/**
 * Вспомогательные функции для шаблонов.
 */
function vitrage_pro_opt(string $key, string $default = ''): string
{
    return (string) get_option($key, $default);
}

/**
 * Хлебные крошки для шапки страниц.
 *
 * @param string $current Текущий заголовок (без ссылки).
 */
function vitrage_pro_breadcrumbs(string $current): void
{
    $items = [
        '<a href="' . esc_url(home_url('/')) . '">Главная</a>',
    ];
    if (is_tax('gallery_category')) {
        $items[] = '<a href="' . esc_url(get_post_type_archive_link('gallery_item')) . '">Галерея</a>';
    } elseif (is_post_type_archive('gallery_item')) {
        // Остаёмся на «Галерея».
    } elseif (is_post_type_archive('team_member')) {
        // «Команда».
    } elseif (is_post_type_archive('review_item')) {
        // «Отзывы».
    } elseif (is_singular('team_member')) {
        $items[] = '<a href="' . esc_url(get_post_type_archive_link('team_member')) . '">Команда</a>';
    } elseif (is_singular('review_item')) {
        $items[] = '<a href="' . esc_url(get_post_type_archive_link('review_item')) . '">Отзывы</a>';
    } elseif (is_singular('gallery_item')) {
        $items[] = '<a href="' . esc_url(get_post_type_archive_link('gallery_item')) . '">Галерея</a>';
    } elseif (is_home()) {
        // «Новости».
    } elseif (is_singular('post')) {
        $items[] = '<a href="' . esc_url(get_permalink(get_option('page_for_posts'))) . '">Новости</a>';
    }

    $items[] = esc_html($current);
    echo '<ul class="bread">';
    $count = count($items);
    foreach ($items as $i => $item) {
        $is_last = ($i === $count - 1);
        echo '<li' . ($is_last ? ' class="active"' : '') . '>' . wp_kses_post($item) . '</li>';
        if (!$is_last) {
            echo '|';
        }
    }
    echo '</ul>';
}

/**
 * Фоновое изображение шапки страницы.
 */
function vitrage_pro_page_header_bg(): string
{
    $bg = vitrage_pro_opt('vp_page_header_bg', '');
    if ($bg) {
        return $bg;
    }
    return '';
}

/**
 * Страница «Контакты»: ссылка для кнопок CTA.
 */
function vitrage_pro_contacts_url(): string
{
    $contacts = get_page_by_path('contacts');
    return $contacts ? get_permalink($contacts) : home_url('/contacts/');
}

/**
 * Получить обложку категории галереи (первое фото).
 *
 * @param int $term_id ID термина.
 */
function vitrage_pro_category_cover(int $term_id): string
{
    $items = get_posts([
        'post_type'      => 'gallery_item',
        'posts_per_page' => 1,
        'tax_query'      => [[
            'taxonomy' => 'gallery_category',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ]],
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ]);
    if ($items && has_post_thumbnail($items[0]->ID)) {
        return (string) get_the_post_thumbnail_url($items[0]->ID, 'vp-grid');
    }
    return '';
}
