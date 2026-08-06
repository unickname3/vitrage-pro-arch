<?php
/**
 * Автоматическая настройка сайта при активации темы:
 * страницы, меню, категории галереи, постоянные ссылки.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Запуск при активации темы.
 */
function vitrage_pro_activate(): void
{
    vitrage_pro_create_pages();
    vitrage_pro_create_gallery_categories(); // до меню: подменю «Галерея» ссылается на категории.
    vitrage_pro_create_menus();
    vitrage_pro_set_reading_settings();
    vitrage_pro_flush_rewrite();
}
add_action('after_switch_theme', 'vitrage_pro_activate');

/**
 * Страницы по умолчанию.
 *
 * @return array Список ID созданных страниц.
 */
function vitrage_pro_create_pages(): array
{
    $defaults = [
        // Галерея/Команда/Отзывы — архивы CPT, страницы для них создавать не нужно
        // (иначе конфликт URL: /gallery/, /komanda/, /reviews/ отдают страницы).
        'about'    => ['Мастерская', ''],
        'ceny'     => ['Витражи тиффани', ''],
        'news'     => ['Новости', ''],
        'price'    => ['Цены', ''],
        'contacts' => ['Контакты', '[vp_contact_form]'],
        'privacy'  => ['Политика конфиденциальности', vitrage_pro_default_privacy_text()],
    ];

    $created = [];
    foreach ($defaults as $slug => [$title, $content]) {
        $existing = get_page_by_path($slug);
        if ($existing) {
            $created[$slug] = (int) $existing->ID;
            continue;
        }

        $parent_id = 0;
        if ($slug === 'ceny' && !empty($created['about'])) {
            $parent_id = (int) $created['about'];
        }

        $page_id = wp_insert_post([
            'post_title'   => $title,
            'post_name'    => $slug,
            'post_content' => $content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_parent'  => $parent_id,
        ]);

        if ($page_id && !is_wp_error($page_id)) {
            $created[$slug] = (int) $page_id;
        }
    }

    return $created;
}

/**
 * Меню (основное и футер).
 */
function vitrage_pro_create_menus(): void
{
    $menu_name = 'Основное меню';
    $menu = wp_get_nav_menu_object($menu_name);

    if (!$menu) {
        $menu_id = wp_create_nav_menu($menu_name);
        if (is_wp_error($menu_id)) {
            return;
        }
    } else {
        $menu_id = (int) $menu->term_id;
    }

    // Позиции: слаг страницы => пункт меню (может отсутствовать).
    $structure = [
        'about'    => null,
        'gallery'  => null,
        'komanda'  => null,
        'reviews'  => null,
        'news'     => null,
        'price'    => null,
        'contacts' => null,
    ];

    $pages = vitrage_pro_create_pages();
    foreach ($structure as $slug => $_) {
        if (empty($pages[$slug])) {
            continue;
        }
        // Пропускаем, если пункт уже есть в меню.
        if (!vitrage_pro_menu_has_item($menu_id, (int) $pages[$slug])) {
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => get_the_title($pages[$slug]),
                'menu-item-object'    => 'page',
                'menu-item-object-id' => (int) $pages[$slug],
                'menu-item-type'      => 'post_type',
                'menu-item-status'    => 'publish',
            ]);
        }
    }

    // Архивы CPT: галерея, команда, отзывы.
    $gallery_item_id = 0;
    foreach (['gallery_item' => 'Галерея', 'team_member' => 'Команда', 'review_item' => 'Отзывы'] as $cpt => $label) {
        $archive_url = get_post_type_archive_link($cpt);
        if (!$archive_url) {
            continue;
        }
        if (!vitrage_pro_menu_has_url($menu_id, $archive_url)) {
            $item_id = wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'  => $label,
                'menu-item-url'    => $archive_url,
                'menu-item-type'   => 'custom',
                'menu-item-status' => 'publish',
            ]);
            if ($cpt === 'gallery_item' && !is_wp_error($item_id)) {
                $gallery_item_id = (int) $item_id;
            }
        } else {
            // Найти существующий пункт «Галерея».
            $items = wp_get_nav_menu_items($menu_id);
            foreach ((array) $items as $item) {
                if ($item->type === 'custom' && rtrim((string) $item->url, '/') === rtrim($archive_url, '/')) {
                    $gallery_item_id = (int) $item->ID;
                    break;
                }
            }
        }
    }

    // Подменю «Галерея»: категории.
    if ($gallery_item_id) {
        $terms = get_terms([
            'taxonomy'   => 'gallery_category',
            'hide_empty' => false,
            'orderby'    => 'slug',
            'order'      => 'ASC',
        ]);
        foreach ((array) $terms as $term) {
            $term_link = get_term_link($term);
            if (is_wp_error($term_link)) {
                continue;
            }
            if (vitrage_pro_menu_has_url($menu_id, (string) $term_link)) {
                continue;
            }
            wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'     => $term->name,
                'menu-item-url'       => (string) $term_link,
                'menu-item-type'      => 'custom',
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $gallery_item_id,
            ]);
        }
    }

    // Назначаем локации.
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations['primary'] = $menu_id;
    $locations['footer'] = $menu_id;
    set_theme_mod('nav_menu_locations', $locations);
}

/**
 * Есть ли пункт в меню.
 *
 * @param int $menu_id  ID меню.
 * @param int $object_id ID страницы.
 */
function vitrage_pro_menu_has_item(int $menu_id, int $object_id): bool
{
    $items = wp_get_nav_menu_items($menu_id);
    if (!$items) {
        return false;
    }
    foreach ($items as $item) {
        if ((int) $item->object_id === $object_id && $item->object === 'page') {
            return true;
        }
    }
    return false;
}

/**
 * Есть ли пункт с URL в меню.
 *
 * @param int    $menu_id ID меню.
 * @param string $url     URL.
 */
function vitrage_pro_menu_has_url(int $menu_id, string $url): bool
{
    $items = wp_get_nav_menu_items($menu_id);
    if (!$items) {
        return false;
    }
    foreach ($items as $item) {
        if ($item->type === 'custom' && rtrim((string) $item->url, '/') === rtrim($url, '/')) {
            return true;
        }
    }
    return false;
}

/**
 * Категории галереи по умолчанию (как на оригинальном сайте).
 */
function vitrage_pro_create_gallery_categories(): void
{
    $categories = [
        'okna'       => 'Окна',
        'dveri'      => 'Двери',
        'mozaika'    => 'Мозаика',
        'interery'   => 'Интерьеры',
        'peregorodki' => 'Перегородки',
        'potolki'    => 'Потолки',
        'svetilniki' => 'Светильники',
        'fyuzing'    => 'Фьюзинг',
        'rospis'     => 'Роспись',
        'podarki'    => 'От эскиза к витражу',
    ];

    foreach ($categories as $slug => $name) {
        if (!term_exists($slug, 'gallery_category')) {
            wp_insert_term($name, 'gallery_category', ['slug' => $slug]);
        }
    }
}

/**
 * Настройки чтения: главная = страница «Главная», новости = «Новости».
 */
function vitrage_pro_set_reading_settings(): void
{
    // Страница «Главная» для статической главной.
    $front = get_page_by_path('home');
    if (!$front) {
        $front_id = wp_insert_post([
            'post_title'  => 'Главная',
            'post_name'   => 'home',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type'   => 'page',
        ]);
        $front = $front_id && !is_wp_error($front_id) ? get_post($front_id) : null;
    }

    if ($front) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', (int) $front->ID);
    }

    // Страница «Новости» как страница записей.
    $news = get_page_by_path('news');
    if ($news) {
        update_option('page_for_posts', (int) $news->ID);
    }

    // Человеко-понятные ссылки.
    if ((string) get_option('permalink_structure') !== '/%postname%/') {
        update_option('permalink_structure', '/%postname%/');
    }
}

/**
 * Текст политики конфиденциальности по умолчанию.
 */
function vitrage_pro_default_privacy_text(): string
{
    return '<h2>Политика конфиденциальности</h2>'
        . '<p>Настоящая Политика конфиденциальности определяет порядок обработки и защиты персональных данных '
        . 'посетителей сайта vitrage-pro.ru.</p>'
        . '<h3>Какие данные мы собираем</h3>'
        . '<p>При отправке формы обратной связи вы добровольно предоставляете: имя, номер телефона, адрес электронной почты '
        . 'и текст сообщения.</p>'
        . '<h3>Как мы используем данные</h3>'
        . '<p>Данные используются исключительно для ответа на ваше обращение. Мы не передаём персональные данные третьим лицам '
        . 'и не используем их для рассылок без вашего согласия.</p>'
        . '<h3>Хранение данных</h3>'
        . '<p>Письма с обращениями хранятся в почтовом ящике владельца сайта и удаляются по мере необходимости, '
        . 'но не позднее чем через 5 лет с момента обращения.</p>'
        . '<h3>Ваши права</h3>'
        . '<p>Вы можете запросить удаление своих персональных данных, отправив письмо на адрес, указанный в разделе «Контакты».</p>';
}

/**
 * Кнопка «Очистить» не нужна, но флеш реврайтов после смены слагов — да.
 */
add_action('after_switch_theme', 'vitrage_pro_flush_rewrite');
