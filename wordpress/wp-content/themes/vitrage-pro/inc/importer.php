<?php
/**
 * Разовый импорт контента из статической версии сайта.
 *
 * Данные: content/data.json + изображения в content/images/.
 * После импорта сайт наполнен реальным контентом, владелец может
 * редактировать всё в админке.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Пункт меню «Импорт контента» (в подменю «Настройки сайта»).
 */
function vitrage_pro_importer_menu(): void
{
    add_submenu_page(
        'vp-settings',
        'Импорт контента',
        'Импорт контента',
        'manage_options',
        'vp-importer',
        'vitrage_pro_importer_page'
    );
}
add_action('admin_menu', 'vitrage_pro_importer_menu');

/**
 * Страница импорта.
 */
function vitrage_pro_importer_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $done = get_option('vp_content_imported', 0);
    ?>
    <div class="wrap">
        <h1>Импорт контента</h1>

        <?php if ($done) : ?>
            <div class="notice notice-success"><p>Контент уже импортирован. Повторный импорт добавит записи повторно — обычно он не нужен. Если нужно, удалите записи вручную и импортируйте снова.</p></div>
        <?php endif; ?>

        <p>Импортирует из папки темы <code>content/</code>:</p>
        <ul>
            <li>настройки сайта (контакты, тексты, слайды главной);</li>
            <li>категории галереи и фотографии работ;</li>
            <li>команду;</li>
            <li>отзывы;</li>
            <li>новости.</li>
        </ul>

        <?php
        $data_file = VITRAGE_PRO_DIR . '/content/data.json';
        if (!file_exists($data_file)) {
            echo '<div class="notice notice-error"><p>Файл <code>content/data.json</code> не найден. Убедитесь, что папка <code>content</code> скопирована в тему целиком.</p></div>';
            return;
        }
        ?>

        <form method="post" action="">
            <?php wp_nonce_field('vp_import', 'vp_import_nonce'); ?>
            <p>
                <button type="submit" class="button button-primary button-hero" name="vp_do_import" value="1">Запустить импорт</button>
            </p>
        </form>

        <?php
        if (isset($_POST['vp_do_import']) && check_admin_referer('vp_import', 'vp_import_nonce')) {
            vitrage_pro_run_import();
        }
        ?>
    </div>
    <?php
}

/**
 * Запуск импорта.
 */
function vitrage_pro_run_import(): void
{
    $data_file = VITRAGE_PRO_DIR . '/content/data.json';
    $raw = file_get_contents($data_file);
    if ($raw === false) {
        echo '<div class="notice notice-error"><p>Не удалось прочитать content/data.json.</p></div>';
        return;
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        echo '<div class="notice notice-error"><p>Ошибка в content/data.json (не JSON).</p></div>';
        return;
    }

    echo '<div class="notice notice-info"><p>Импорт запущен…</p></div>';

    $stats = [
        'settings'    => 0,
        'categories'  => 0,
        'gallery'     => 0,
        'team'        => 0,
        'reviews'     => 0,
        'news'        => 0,
        'pages'       => 0,
    ];

    // 1. Настройки сайта.
    if (!empty($data['settings']) && is_array($data['settings'])) {
        foreach ($data['settings'] as $key => $value) {
            // Слайды hero: локальные файлы images/ -> URL медиатеки.
            if ($key === 'vp_hero_slides' && is_array($value)) {
                $slides = [];
                foreach ($value as $slide) {
                    $slide = (array) $slide;
                    if (!empty($slide['image']) && strpos((string) $slide['image'], 'http') !== 0) {
                        $attach_id = vitrage_pro_import_image((string) $slide['image'], (string) ($slide['title'] ?? 'Слайд'));
                        if ($attach_id) {
                            $slide['image'] = (string) wp_get_attachment_url($attach_id);
                        } else {
                            $slide['image'] = '';
                        }
                    }
                    $slides[] = $slide;
                }
                update_option($key, $slides);
                $stats['settings']++;
                continue;
            }
            update_option($key, $value);
            $stats['settings']++;
        }
    }

    // 2. Категории галереи.
    if (!empty($data['categories']) && is_array($data['categories'])) {
        foreach ($data['categories'] as $cat) {
            $slug = sanitize_title($cat['slug']);
            if (term_exists($slug, 'gallery_category')) {
                continue;
            }
            $args = ['slug' => $slug];
            if (!empty($cat['description'])) {
                $args['description'] = $cat['description'];
            }
            $res = wp_insert_term($cat['name'], 'gallery_category', $args);
            if (!is_wp_error($res)) {
                $stats['categories']++;
            }
        }
    }

    // 3. Галерея.
    if (!empty($data['gallery']) && is_array($data['gallery'])) {
        foreach ($data['gallery'] as $item) {
            $stats['gallery'] += vitrage_pro_import_gallery_item($item);
        }
    }

    // 4. Команда.
    if (!empty($data['team']) && is_array($data['team'])) {
        foreach ($data['team'] as $member) {
            $stats['team'] += vitrage_pro_import_team_member($member);
        }
    }

    // 5. Отзывы.
    if (!empty($data['reviews']) && is_array($data['reviews'])) {
        foreach ($data['reviews'] as $review) {
            $stats['reviews'] += vitrage_pro_import_review($review);
        }
    }

    // 6. Новости.
    if (!empty($data['news']) && is_array($data['news'])) {
        foreach ($data['news'] as $news) {
            $stats['news'] += vitrage_pro_import_news($news);
        }
    }

    // 7. Дополнительные страницы (например, «Витражи тиффани»).
    if (!empty($data['pages']) && is_array($data['pages'])) {
        foreach ($data['pages'] as $page) {
            $stats['pages'] += vitrage_pro_import_page($page);
        }
    }

    update_option('vp_content_imported', 1);

    echo '<div class="notice notice-success"><p><strong>Импорт завершён:</strong></p><ul>';
    echo '<li>Настройки: ' . esc_html((string) $stats['settings']) . '</li>';
    echo '<li>Категории: ' . esc_html((string) $stats['categories']) . '</li>';
    echo '<li>Фото работ: ' . esc_html((string) $stats['gallery']) . '</li>';
    echo '<li>Команда: ' . esc_html((string) $stats['team']) . '</li>';
    echo '<li>Отзывы: ' . esc_html((string) $stats['reviews']) . '</li>';
    echo '<li>Новости: ' . esc_html((string) $stats['news']) . '</li>';
    echo '<li>Страницы: ' . esc_html((string) $stats['pages']) . '</li>';
    echo '</ul></div>';
}

/**
 * Импорт одного изображения в медиатеку.
 *
 * @param string $filename Имя файла в content/images/.
 * @param string $alt      Alt-текст.
 * @return int ID вложения или 0.
 */
function vitrage_pro_import_image(string $filename, string $alt = ''): int
{
    $source = VITRAGE_PRO_DIR . '/content/images/' . $filename;
    if (!file_exists($source)) {
        return 0;
    }

    $uploads = wp_upload_dir();
    $dest_dir = $uploads['path'];
    if (!file_exists($dest_dir)) {
        wp_mkdir_p($dest_dir);
    }

    $safe_name = sanitize_file_name($filename);
    $dest = $dest_dir . '/' . $safe_name;
    if (!copy($source, $dest)) {
        return 0;
    }

    $file_type = wp_check_filetype($safe_name);
    $attachment = [
        'post_mime_type' => $file_type['type'] ?: 'image/jpeg',
        'post_title'     => $alt ?: pathinfo($safe_name, PATHINFO_FILENAME),
        'post_content'   => '',
        'post_status'    => 'inherit',
    ];

    $attach_id = wp_insert_attachment($attachment, $dest);
    if (is_wp_error($attach_id)) {
        return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata((int) $attach_id, $dest);
    wp_update_attachment_metadata((int) $attach_id, $attach_data);

    if ($alt) {
        update_post_meta((int) $attach_id, '_wp_attachment_image_alt', $alt);
    }

    return (int) $attach_id;
}

/**
 * Импорт работы галереи.
 *
 * @param array $item Данные.
 * @return int 0/1.
 */
function vitrage_pro_import_gallery_item(array $item): int
{
    if (empty($item['image'])) {
        return 0;
    }

    $title = !empty($item['title']) ? $item['title'] : pathinfo($item['image'], PATHINFO_FILENAME);

    // Пропускаем дубликаты по слагу.
    $slug = sanitize_title($item['slug'] ?? $title);
    if (get_page_by_path($slug, OBJECT, 'gallery_item')) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type'    => 'gallery_item',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => !empty($item['description']) ? $item['description'] : '',
        'menu_order'   => (int) ($item['order'] ?? 0),
    ]);

    if (is_wp_error($post_id)) {
        return 0;
    }

    $attach_id = vitrage_pro_import_image($item['image'], $title);
    if ($attach_id) {
        set_post_thumbnail((int) $post_id, $attach_id);
    }

    // Категория.
    if (!empty($item['category'])) {
        $term = term_exists($item['category'], 'gallery_category');
        if ($term) {
            wp_set_object_terms((int) $post_id, [(int) $term['term_id']], 'gallery_category');
        }
    }

    return 1;
}

/**
 * Импорт сотрудника.
 *
 * @param array $member Данные.
 * @return int 0/1.
 */
function vitrage_pro_import_team_member(array $member): int
{
    if (empty($member['name'])) {
        return 0;
    }

    $slug = sanitize_title($member['slug'] ?? $member['name']);
    if (get_page_by_path($slug, OBJECT, 'team_member')) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type'    => 'team_member',
        'post_status'  => 'publish',
        'post_title'   => $member['name'],
        'post_name'    => $slug,
        'post_content' => !empty($member['bio']) ? $member['bio'] : '',
        'menu_order'   => (int) ($member['order'] ?? 0),
    ]);

    if (is_wp_error($post_id)) {
        return 0;
    }

    if (!empty($member['position'])) {
        update_post_meta((int) $post_id, 'vp_team_position', $member['position']);
    }

    if (!empty($member['photo'])) {
        $attach_id = vitrage_pro_import_image($member['photo'], $member['name']);
        if ($attach_id) {
            set_post_thumbnail((int) $post_id, $attach_id);
        }
    }

    return 1;
}

/**
 * Импорт отзыва.
 *
 * @param array $review Данные.
 * @return int 0/1.
 */
function vitrage_pro_import_review(array $review): int
{
    if (empty($review['author']) || empty($review['text'])) {
        return 0;
    }

    $slug = sanitize_title($review['slug'] ?? $review['author']);
    if (get_page_by_path($slug, OBJECT, 'review_item')) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type'    => 'review_item',
        'post_status'  => 'publish',
        'post_title'   => $review['author'],
        'post_name'    => $slug,
        'post_content' => $review['text'],
        'menu_order'   => (int) ($review['order'] ?? 0),
    ]);

    if (is_wp_error($post_id)) {
        return 0;
    }

    if (!empty($review['city'])) {
        update_post_meta((int) $post_id, 'vp_review_city', $review['city']);
    }

    return 1;
}

/**
 * Импорт страницы с реальным контентом.
 *
 * @param array $page Данные: slug, title, parent, content.
 * @return int 0/1.
 */
function vitrage_pro_import_page(array $page): int
{
    if (empty($page['slug']) || empty($page['title'])) {
        return 0;
    }

    $existing = get_page_by_path((string) $page['slug']);
    if ($existing) {
        // Обновляем контент (страница уже создана setup-ом).
        $post_id = wp_update_post([
            'ID'           => (int) $existing->ID,
            'post_content' => !empty($page['content']) ? $page['content'] : $existing->post_content,
        ]);
        return is_wp_error($post_id) ? 0 : 1;
    }

    $parent_id = 0;
    if (!empty($page['parent'])) {
        $parent = get_page_by_path((string) $page['parent']);
        if ($parent) {
            $parent_id = (int) $parent->ID;
        }
    }

    $post_id = wp_insert_post([
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => $page['title'],
        'post_name'    => $page['slug'],
        'post_parent'  => $parent_id,
        'post_content' => !empty($page['content']) ? $page['content'] : '',
    ]);

    return is_wp_error($post_id) ? 0 : 1;
}

/**
 * Импорт новости.
 *
 * @param array $news Данные.
 * @return int 0/1.
 */
function vitrage_pro_import_news(array $news): int
{
    if (empty($news['title']) || empty($news['text'])) {
        return 0;
    }

    $slug = sanitize_title($news['slug'] ?? $news['title']);
    if (get_page_by_path($slug, OBJECT, 'post')) {
        return 0;
    }

    $post_id = wp_insert_post([
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => $news['title'],
        'post_name'    => $slug,
        'post_content' => $news['text'],
        'post_date'    => !empty($news['date']) ? $news['date'] : current_time('mysql'),
    ]);

    if (is_wp_error($post_id)) {
        return 0;
    }

    if (!empty($news['image'])) {
        $attach_id = vitrage_pro_import_image($news['image'], $news['title']);
        if ($attach_id) {
            set_post_thumbnail((int) $post_id, $attach_id);
        }
    }

    return 1;
}
