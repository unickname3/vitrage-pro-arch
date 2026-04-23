<?php
declare(strict_types=1);

function vitrage_pro_register_cpt(): void
{
    register_post_type('gallery_item', [
        'labels' => [
            'name' => 'Галерея',
            'singular_name' => 'Работа',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'gallery'],
        'menu_icon' => 'dashicons-format-gallery',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'show_in_rest' => true,
    ]);

    register_post_type('team_member', [
        'labels' => [
            'name' => 'Команда',
            'singular_name' => 'Сотрудник',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'komanda'],
        'menu_icon' => 'dashicons-groups',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
        'show_in_rest' => true,
    ]);

    register_post_type('review_item', [
        'labels' => [
            'name' => 'Отзывы',
            'singular_name' => 'Отзыв',
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'reviews'],
        'menu_icon' => 'dashicons-testimonial',
        'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'vitrage_pro_register_cpt');

function vitrage_pro_register_gallery_taxonomy(): void
{
    register_taxonomy('gallery_category', ['gallery_item'], [
        'labels' => [
            'name' => 'Категории галереи',
            'singular_name' => 'Категория галереи',
        ],
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'gallery-category'],
    ]);
}
add_action('init', 'vitrage_pro_register_gallery_taxonomy');
