<?php
/**
 * Custom post types and taxonomies.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * CPT: Работы (галерея).
 */
function vitrage_pro_register_cpt(): void
{
    register_post_type('gallery_item', [
        'labels' => [
            'name'          => 'Галерея',
            'singular_name' => 'Работа',
            'add_new'       => 'Добавить фото',
            'add_new_item'  => 'Добавить работу/фото',
            'edit_item'     => 'Редактировать работу',
            'new_item'      => 'Новая работа',
            'view_item'     => 'Просмотреть работу',
            'search_items'  => 'Найти работы',
            'not_found'     => 'Работы не найдены',
            'not_found_in_trash' => 'В корзине работ нет',
            'menu_name'     => 'Галерея',
        ],
        'public'       => true,
        // Архив /gallery/ = страница категорий. Одиночные работы — /work/<слаг>/,
        // чтобы не конфликтовать с категориями /gallery/<категория>/.
        'has_archive'  => 'gallery',
        'rewrite'      => ['slug' => 'work', 'with_front' => false],
        'menu_icon'    => 'dashicons-format-gallery',
        'menu_position' => 20,
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes', 'revisions'],
        'show_in_rest' => true,
    ]);

    register_post_type('team_member', [
        'labels' => [
            'name'          => 'Команда',
            'singular_name' => 'Сотрудник',
            'add_new'       => 'Добавить сотрудника',
            'add_new_item'  => 'Добавить сотрудника',
            'edit_item'     => 'Редактировать сотрудника',
            'new_item'      => 'Новый сотрудник',
            'view_item'     => 'Просмотреть сотрудника',
            'search_items'  => 'Найти сотрудников',
            'not_found'     => 'Сотрудники не найдены',
            'menu_name'     => 'Команда',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'komanda'],
        'menu_icon'    => 'dashicons-groups',
        'menu_position' => 21,
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes', 'revisions'],
        'show_in_rest' => true,
    ]);

    register_post_type('review_item', [
        'labels' => [
            'name'          => 'Отзывы',
            'singular_name' => 'Отзыв',
            'add_new'       => 'Добавить отзыв',
            'add_new_item'  => 'Добавить отзыв',
            'edit_item'     => 'Редактировать отзыв',
            'new_item'      => 'Новый отзыв',
            'view_item'     => 'Просмотреть отзыв',
            'search_items'  => 'Найти отзывы',
            'not_found'     => 'Отзывы не найдены',
            'menu_name'     => 'Отзывы',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'reviews'],
        'menu_icon'    => 'dashicons-testimonial',
        'menu_position' => 22,
        'supports'     => ['title', 'editor', 'thumbnail', 'page-attributes', 'revisions'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'vitrage_pro_register_cpt');

/**
 * Таксономия «Категории галереи».
 */
function vitrage_pro_register_gallery_taxonomy(): void
{
    register_taxonomy('gallery_category', ['gallery_item'], [
        'labels' => [
            'name'          => 'Категории галереи',
            'singular_name' => 'Категория',
            'search_items'  => 'Найти категории',
            'all_items'     => 'Все категории',
            'edit_item'     => 'Редактировать категорию',
            'update_item'   => 'Обновить категорию',
            'add_new_item'  => 'Добавить категорию',
            'new_item_name' => 'Название категории',
            'menu_name'     => 'Категории галереи',
        ],
        'public'            => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => 'gallery'],
    ]);
}
add_action('init', 'vitrage_pro_register_gallery_taxonomy');

/**
 * Флеш перезаписи после регистрации (при активации темы).
 */
function vitrage_pro_flush_rewrite(): void
{
    vitrage_pro_register_cpt();
    vitrage_pro_register_gallery_taxonomy();
    flush_rewrite_rules();
}
