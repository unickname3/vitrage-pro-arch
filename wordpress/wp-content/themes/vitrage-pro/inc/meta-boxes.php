<?php
/**
 * Meta boxes для удобного редактирования контента владельцем.
 * Без плагинов: только ядро WordPress + медиа-библиотека.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Регистрация мета-боксов.
 */
function vitrage_pro_register_meta_boxes(): void
{
    // Работа: фото (дублирует миниатюру, но удобнее для владельца).
    add_meta_box(
        'vp_gallery_photo',
        'Фото работы',
        'vitrage_pro_render_gallery_photo_box',
        'gallery_item',
        'normal',
        'high'
    );

    // Работа: подсказка.
    add_meta_box(
        'vp_gallery_hint',
        'Как это работает',
        'vitrage_pro_render_gallery_hint_box',
        'gallery_item',
        'side',
        'default'
    );

    // Сотрудник: должность.
    add_meta_box(
        'vp_team_position',
        'Должность',
        'vitrage_pro_render_team_position_box',
        'team_member',
        'normal',
        'high'
    );

    // Сотрудник: подсказка.
    add_meta_box(
        'vp_team_hint',
        'Как это работает',
        'vitrage_pro_render_team_hint_box',
        'team_member',
        'side',
        'default'
    );

    // Отзыв: город/компания.
    add_meta_box(
        'vp_review_author',
        'Автор отзыва',
        'vitrage_pro_render_review_author_box',
        'review_item',
        'normal',
        'high'
    );

    // Отзыв: подсказка.
    add_meta_box(
        'vp_review_hint',
        'Как это работает',
        'vitrage_pro_render_review_hint_box',
        'review_item',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'vitrage_pro_register_meta_boxes');

/**
 * Сохранение мета-полей.
 *
 * @param int $post_id ID записи.
 */
function vitrage_pro_save_meta_boxes(int $post_id): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (!isset($_POST['vp_meta_nonce']) || !wp_verify_nonce(sanitize_key($_POST['vp_meta_nonce']), 'vp_meta_save')) {
        return;
    }

    $fields = [
        'vp_team_position' => 'sanitize_text_field',
        'vp_review_city'   => 'sanitize_text_field',
    ];

    foreach ($fields as $key => $sanitize) {
        if (isset($_POST[$key])) {
            $value = call_user_func($sanitize, wp_unslash($_POST[$key])); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
            update_post_meta($post_id, $key, $value);
        } else {
            delete_post_meta($post_id, $key);
        }
    }

    // Фото работы = миниатюра записи.
    if (isset($_POST['vp_gallery_photo_id'])) {
        $photo_id = absint($_POST['vp_gallery_photo_id']);
        if ($photo_id && get_post_type($post_id) === 'gallery_item') {
            set_post_thumbnail($post_id, $photo_id);
        } elseif (!$photo_id) {
            delete_post_thumbnail($post_id);
        }
    }
}
add_action('save_post', 'vitrage_pro_save_meta_boxes');

/**
 * Общий nonce для мета-боксов.
 */
function vitrage_pro_meta_nonce(): void
{
    wp_nonce_field('vp_meta_save', 'vp_meta_nonce');
}
add_action('edit_form_after_title', 'vitrage_pro_meta_nonce');

/**
 * Рендер: фото работы.
 *
 * @param WP_Post $post Запись.
 */
function vitrage_pro_render_gallery_photo_box(WP_Post $post): void
{
    $thumb_id = (int) get_post_thumbnail_id($post->ID);
    $url = $thumb_id ? (string) wp_get_attachment_image_url($thumb_id, 'medium') : '';
    ?>
    <p class="description">Загрузите фотографию работы. Она появится в категории, выбранной ниже.</p>
    <div class="vp-single-media">
        <input type="hidden" class="vp-single-input" name="vp_gallery_photo_id" value="<?php echo esc_attr((string) $thumb_id); ?>" />
        <div class="vp-single-media-preview" <?php echo $url ? '' : 'style="display:none;"'; ?>>
            <img src="<?php echo esc_url($url); ?>" alt="" />
        </div>
        <button type="button" class="button vp-set-image"><?php echo $thumb_id ? 'Заменить фото' : 'Выбрать фото'; ?></button>
        <?php if ($thumb_id) : ?>
            <button type="button" class="button vp-remove-image">Удалить</button>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Рендер: подсказка для галереи.
 */
function vitrage_pro_render_gallery_hint_box(): void
{
    echo '<p><strong>Что делать:</strong></p>';
    echo '<ol>';
    echo '<li>Введите название работы (например, «Окно в прихожей»).</li>';
    echo '<li>Выберите фото выше (кнопка «Выбрать фото»).</li>';
    echo '<li>Отметьте категорию в блоке «Категории галереи» (например, «Окна»).</li>';
    echo '<li>При желании добавьте описание в текстовый редактор.</li>';
    echo '<li>Нажмите «Опубликовать» — фото появится на странице категории.</li>';
    echo '</ol>';
    echo '<p>Порядок фото задаётся в блоке «Атрибуты страницы → Порядок» (меньше — раньше).</p>';
}

/**
 * Рендер: должность сотрудника.
 *
 * @param WP_Post $post Запись.
 */
function vitrage_pro_render_team_position_box(WP_Post $post): void
{
    $position = (string) get_post_meta($post->ID, 'vp_team_position', true);
    ?>
    <p class="description">Например: «Художник по стеклу», «Мастер витража».</p>
    <input type="text" class="widefat" name="vp_team_position" value="<?php echo esc_attr($position); ?>" placeholder="Должность" />
    <?php
}

/**
 * Рендер: подсказка для команды.
 */
function vitrage_pro_render_team_hint_box(): void
{
    echo '<p><strong>Что делать:</strong></p>';
    echo '<ol>';
    echo '<li>Введите ФИО сотрудника.</li>';
    echo '<li>Заполните должность.</li>';
    echo '<li>Загрузите фото в блоке «Миниатюра записи» (справа).</li>';
    echo '<li>Напишите краткую биографию в редакторе.</li>';
    echo '<li>Нажмите «Опубликовать».</li>';
    echo '</ol>';
}

/**
 * Рендер: автор отзыва.
 *
 * @param WP_Post $post Запись.
 */
function vitrage_pro_render_review_author_box(WP_Post $post): void
{
    $city = (string) get_post_meta($post->ID, 'vp_review_city', true);
    ?>
    <p class="description">В заголовке записи укажите имя автора. Здесь — город или компания.</p>
    <input type="text" class="widefat" name="vp_review_city" value="<?php echo esc_attr($city); ?>" placeholder="Город / компания" />
    <?php
}

/**
 * Рендер: подсказка для отзывов.
 */
function vitrage_pro_render_review_hint_box(): void
{
    echo '<p><strong>Что делать:</strong></p>';
    echo '<ol>';
    echo '<li>В заголовке записи укажите имя автора.</li>';
    echo '<li>Заполните город/компанию слева.</li>';
    echo '<li>Вставьте текст отзыва в редактор.</li>';
    echo '<li>Нажмите «Опубликовать».</li>';
    echo '</ol>';
}
