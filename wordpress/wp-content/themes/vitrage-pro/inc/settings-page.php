<?php
/**
 * Страница «Настройки сайта» в админке.
 * Все тексты и контакты, которые владелец может менять без программиста.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Добавить страницу настроек в меню.
 */
function vitrage_pro_settings_menu(): void
{
    add_menu_page(
        'Настройки сайта',
        'Настройки сайта',
        'manage_options',
        'vp-settings',
        'vitrage_pro_settings_page',
        'dashicons-admin-generic',
        3
    );
}
add_action('admin_menu', 'vitrage_pro_settings_menu');

/**
 * Регистрация настроек.
 */
function vitrage_pro_register_settings(): void
{
    $group = 'vp_settings';

    $text_fields = [
        // Контакты.
        'vp_phone',
        'vp_phone_2',
        'vp_email',
        'vp_address',
        'vp_work_hours',
        'vp_vk',
        'vp_whatsapp',
        'vp_telegram',
        // Главная: hero.
        'vp_hero_title',
        'vp_hero_subtitle',
        // Главная: о мастерской.
        'vp_about_title',
        'vp_about_text',
        'vp_about_button_text',
        'vp_about_button_url',
        'vp_about_button2_text',
        'vp_about_button2_url',
        // Главная: галерея.
        'vp_gallery_title',
        'vp_gallery_subtitle',
        // Главная: отзывы.
        'vp_testimonials_title',
        'vp_testimonials_subtitle',
        // Главная: CTA.
        'vp_cta_title',
        'vp_cta_subtitle',
        'vp_cta_button1_text',
        'vp_cta_button1_url',
        'vp_cta_button2_text',
        'vp_cta_button2_url',
        // Футер.
        'vp_footer_text',
        'vp_copyright',
        // Форма.
        'vp_form_recipient',
        'vp_form_subject',
        'vp_form_success',
        // SMTP.
        'vp_smtp_host',
        'vp_smtp_port',
        'vp_smtp_user',
        'vp_smtp_pass',
        'vp_smtp_secure',
        'vp_smtp_from_name',
    ];

    foreach ($text_fields as $field) {
        register_setting($group, $field, ['sanitize_callback' => 'sanitize_text_field']);
    }

    // URL-поля.
    foreach (['vp_page_header_bg'] as $field) {
        register_setting($group, $field, ['sanitize_callback' => 'esc_url_raw']);
    }

    // Текст (textarea) — без потери переносов строк.
    register_setting($group, 'vp_about_text', ['sanitize_callback' => 'sanitize_textarea_field']);
    register_setting($group, 'vp_footer_text', ['sanitize_callback' => 'sanitize_textarea_field']);

    // Включить/выключить секции.
    register_setting($group, 'vp_show_testimonials', ['sanitize_callback' => 'absint']);
    register_setting($group, 'vp_show_subscribe', ['sanitize_callback' => 'absint']);

    // Слайды на главной (сериализованный массив).
    register_setting($group, 'vp_hero_slides', ['sanitize_callback' => 'vitrage_pro_sanitize_slides']);
}
add_action('admin_init', 'vitrage_pro_register_settings');

/**
 * Санитизация слайдов hero.
 *
 * @param mixed $value Значение.
 * @return array
 */
function vitrage_pro_sanitize_slides($value): array
{
    $clean = [];
    if (!is_array($value)) {
        return $clean;
    }
    foreach ($value as $slide) {
        $clean[] = [
            'image'      => isset($slide['image']) ? esc_url_raw($slide['image']) : '',
            'title'      => isset($slide['title']) ? sanitize_text_field($slide['title']) : '',
            'subtitle'   => isset($slide['subtitle']) ? sanitize_text_field($slide['subtitle']) : '',
            'btn_text'   => isset($slide['btn_text']) ? sanitize_text_field($slide['btn_text']) : '',
            'btn_url'    => isset($slide['btn_url']) ? esc_url_raw($slide['btn_url']) : '',
        ];
    }
    return $clean;
}

/**
 * Вывод страницы настроек.
 */
function vitrage_pro_settings_page(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'contacts';
    $tabs = [
        'contacts'    => 'Контакты',
        'home'        => 'Главная страница',
        'footer'      => 'Футер',
        'form'        => 'Форма и почта',
    ];

    settings_errors();
    ?>
    <div class="wrap">
        <h1>Настройки сайта</h1>
        <p>Здесь можно изменить контакты, тексты главной страницы, футер и настройки почты. Изменения сохраняются кнопкой «Сохранить» внизу страницы.</p>

        <nav class="nav-tab-wrapper vp-nav-tab-wrapper">
            <?php foreach ($tabs as $key => $label) : ?>
                <a href="?page=vp-settings&tab=<?php echo esc_attr($key); ?>" class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html($label); ?></a>
            <?php endforeach; ?>
        </nav>

        <form method="post" action="options.php" class="vp-settings-form">
            <?php settings_fields('vp_settings'); ?>
            <?php vitrage_pro_render_tab($tab); ?>
            <?php submit_button('Сохранить настройки'); ?>
        </form>

        <?php if ($tab === 'home') : ?>
            <script type="text/html" id="tmpl-vp-repeater-row">
                <div class="vp-repeater-row">
                    <div class="row-title">Слайд <?php /* заполняется скриптом */ ?></div>
                    <div class="vp-single-media">
                        <input type="hidden" class="vp-single-input" name="vp_hero_slides[__INDEX__][image]" value="{{ data.image }}" />
                        <div class="vp-single-media-preview" <# if ( ! data.image ) { #>style="display:none;"<# } #>>
                            <img src="{{ data.image }}" alt="" />
                        </div>
                        <button type="button" class="button vp-set-image">Выбрать изображение</button>
                        <button type="button" class="button vp-remove-image">Удалить</button>
                    </div>
                    <input type="text" name="vp_hero_slides[__INDEX__][title]" value="{{ data.title }}" placeholder="Заголовок слайда" />
                    <input type="text" name="vp_hero_slides[__INDEX__][subtitle]" value="{{ data.subtitle }}" placeholder="Подзаголовок слайда" />
                    <input type="text" name="vp_hero_slides[__INDEX__][btn_text]" value="{{ data.btn_text }}" placeholder="Текст кнопки (необязательно)" />
                    <input type="text" name="vp_hero_slides[__INDEX__][btn_url]" value="{{ data.btn_url }}" placeholder="Ссылка кнопки (например, /contacts/)" />
                    <button type="button" class="button vp-remove-row">Удалить слайд</button>
                </div>
            </script>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Рендер вкладки.
 *
 * @param string $tab Ключ вкладки.
 */
function vitrage_pro_render_tab(string $tab): void
{
    switch ($tab) {
        case 'home':
            vitrage_pro_render_tab_home();
            break;
        case 'footer':
            vitrage_pro_render_tab_footer();
            break;
        case 'form':
            vitrage_pro_render_tab_form();
            break;
        case 'contacts':
        default:
            vitrage_pro_render_tab_contacts();
            break;
    }
}

/**
 * Вкладка «Контакты».
 */
function vitrage_pro_render_tab_contacts(): void
{
    echo '<h2>Контакты</h2>';
    echo '<table class="form-table">';

    vitrage_pro_text_field('vp_phone', 'Телефон (основной)', 'Например: +7 (905) 771-08-25');
    vitrage_pro_text_field('vp_phone_2', 'Телефон (дополнительный)', 'Необязательно');
    vitrage_pro_text_field('vp_email', 'Email', 'Например: info@vitrage-pro.ru');
    vitrage_pro_text_field('vp_address', 'Адрес', 'Например: г. Москва, Малый Демидовский переулок, д.3');
    vitrage_pro_text_field('vp_work_hours', 'Часы работы', 'Необязательно. Например: Ежедневно 10:00–20:00');
    vitrage_pro_text_field('vp_vk', 'ВКонтакте', 'Полная ссылка, например https://vk.com/vitragepro');
    vitrage_pro_text_field('vp_whatsapp', 'WhatsApp', 'Необязательно. Номер в международном формате');
    vitrage_pro_text_field('vp_telegram', 'Telegram', 'Необязательно. Ссылка или @ник');

    echo '</table>';
}

/**
 * Вкладка «Главная страница».
 */
function vitrage_pro_render_tab_home(): void
{
    echo '<h2>Слайды в начале главной страницы</h2>';
    echo '<p>Каждый слайд — это большое изображение с заголовком. Можно добавить несколько слайдов, они будут листаться автоматически.</p>';

    $slides = (array) get_option('vp_hero_slides', []);
    ?>
    <div class="vp-repeater-wrap">
        <div class="vp-repeater-rows">
            <?php foreach ($slides as $i => $slide) : ?>
                <?php vitrage_pro_render_slide_row($i, (array) $slide); ?>
            <?php endforeach; ?>
        </div>
        <button type="button" class="button vp-add-row">+ Добавить слайд</button>
    </div>
    <?php

    echo '<h2>Секция «О мастерской»</h2>';
    echo '<table class="form-table">';
    vitrage_pro_text_field('vp_about_title', 'Заголовок секции', 'По умолчанию: Мы создаем уникальные витражи…');
    vitrage_pro_textarea_field('vp_about_text', 'Текст секции', 'Основной текст о мастерской. Можно несколько абзацев.');
    vitrage_pro_text_field('vp_about_button_text', 'Кнопка 1 (текст)', 'Например: Подробнее');
    vitrage_pro_text_field('vp_about_button_url', 'Кнопка 1 (ссылка)', 'Например: /about/ или https://…');
    vitrage_pro_text_field('vp_about_button2_text', 'Кнопка 2 (текст)', 'Например: Задать вопрос');
    vitrage_pro_text_field('vp_about_button2_url', 'Кнопка 2 (ссылка)', 'Например: /contacts/');
    echo '</table>';

    echo '<h2>Секция «Наши работы»</h2>';
    echo '<table class="form-table">';
    vitrage_pro_text_field('vp_gallery_title', 'Заголовок', 'Например: Наши работы');
    vitrage_pro_text_field('vp_gallery_subtitle', 'Подзаголовок', 'Например: Свежие проекты и работы');
    echo '</table>';

    echo '<h2>Секция «Отзывы»</h2>';
    echo '<table class="form-table">';
    vitrage_pro_checkbox_field('vp_show_testimonials', 'Показывать отзывы на главной', 'Включено');
    vitrage_pro_text_field('vp_testimonials_title', 'Заголовок (необязательно)', '');
    vitrage_pro_text_field('vp_testimonials_subtitle', 'Подзаголовок (необязательно)', '');
    echo '</table>';

    echo '<h2>Секция «Задать вопрос» (CTA)</h2>';
    echo '<table class="form-table">';
    vitrage_pro_text_field('vp_cta_title', 'Заголовок', 'Например: Задать вопрос');
    vitrage_pro_text_field('vp_cta_subtitle', 'Подзаголовок', 'Например: Интересны наши работы');
    vitrage_pro_text_field('vp_cta_button1_text', 'Кнопка 1 (текст)', 'Например: Подробнее о нас');
    vitrage_pro_text_field('vp_cta_button1_url', 'Кнопка 1 (ссылка)', 'Например: /about/');
    vitrage_pro_text_field('vp_cta_button2_text', 'Кнопка 2 (текст)', 'Например: Оставить заявку');
    vitrage_pro_text_field('vp_cta_button2_url', 'Кнопка 2 (ссылка)', 'Например: /contacts/');
    echo '</table>';
}

/**
 * Вкладка «Футер».
 */
function vitrage_pro_render_tab_footer(): void
{
    echo '<h2>Футер</h2>';
    echo '<table class="form-table">';
    vitrage_pro_textarea_field('vp_footer_text', 'Текст в футере', 'Короткое описание мастерской.');
    vitrage_pro_text_field('vp_copyright', 'Текст копирайта', 'По умолчанию: © Витраж Про 2018 / Все права защищены');
    vitrage_pro_checkbox_field('vp_show_subscribe', 'Форма подписки', 'Показывать форму «Подписаться…» в футере');
    echo '</table>';
}

/**
 * Вкладка «Форма и почта».
 */
function vitrage_pro_render_tab_form(): void
{
    echo '<h2>Форма обратной связи</h2>';
    echo '<p>Письма с заявками приходят на указанный ниже адрес. Если письма не доходят, настройте SMTP (см. ниже) или обратитесь в поддержку хостинга.</p>';
    echo '<table class="form-table">';
    vitrage_pro_text_field('vp_form_recipient', 'Куда присылать заявки', 'Email владельца, например info@vitrage-pro.ru');
    vitrage_pro_text_field('vp_form_subject', 'Тема письма', 'Например: Заявка с сайта vitrage-pro.ru');
    vitrage_pro_text_field('vp_form_success', 'Сообщение после отправки', 'Например: Спасибо! Мы свяжемся с вами в ближайшее время.');
    echo '</table>';

    echo '<h2>SMTP (необязательно)</h2>';
    echo '<p>Если форма работает, эти поля можно не заполнять. SMTP нужен, когда письма не доходят (например, на общих хостингах). Настройки можно взять у хостинга или у бесплатной Яндекс.Почты для домена.</p>';
    echo '<table class="form-table">';
    vitrage_pro_text_field('vp_smtp_host', 'SMTP-сервер', 'Например: smtp.yandex.ru');
    vitrage_pro_text_field('vp_smtp_port', 'Порт', 'Например: 465');
    vitrage_pro_text_field('vp_smtp_user', 'Логин', 'Например: info@vitrage-pro.ru');
    vitrage_pro_text_field('vp_smtp_pass', 'Пароль', 'Пароль приложения/почтового ящика');
    vitrage_pro_text_field('vp_smtp_secure', 'Шифрование', 'ssl или tls');
    vitrage_pro_text_field('vp_smtp_from_name', 'Имя отправителя', 'Например: Витраж Про');
    echo '</table>';
}

/**
 * Поле ввода.
 */
function vitrage_pro_text_field(string $key, string $label, string $description = ''): void
{
    $value = (string) get_option($key, '');
    ?>
    <tr>
        <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
        <td>
            <input type="text" class="regular-text vp-setting-field" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>" />
            <?php if ($description) : ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Поле textarea.
 */
function vitrage_pro_textarea_field(string $key, string $label, string $description = ''): void
{
    $value = (string) get_option($key, '');
    ?>
    <tr>
        <th scope="row"><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label></th>
        <td>
            <textarea class="large-text vp-setting-field" id="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($key); ?>" rows="5"><?php echo esc_textarea($value); ?></textarea>
            <?php if ($description) : ?>
                <p class="description"><?php echo esc_html($description); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Поле-чекбокс.
 */
function vitrage_pro_checkbox_field(string $key, string $label, string $on_text = 'Включено'): void
{
    $value = (int) get_option($key, 0);
    ?>
    <tr>
        <th scope="row"><?php echo esc_html($label); ?></th>
        <td>
            <label>
                <input type="checkbox" name="<?php echo esc_attr($key); ?>" value="1" <?php checked($value, 1); ?> />
                <?php echo esc_html($on_text); ?>
            </label>
        </td>
    </tr>
    <?php
}

/**
 * Строка слайда (PHP-рендер, для существующих слайдов).
 *
 * @param int   $index Индекс.
 * @param array $slide Данные слайда.
 */
function vitrage_pro_render_slide_row(int $index, array $slide): void
{
    $image    = isset($slide['image']) ? (string) $slide['image'] : '';
    $title    = isset($slide['title']) ? (string) $slide['title'] : '';
    $subtitle = isset($slide['subtitle']) ? (string) $slide['subtitle'] : '';
    $btn_text = isset($slide['btn_text']) ? (string) $slide['btn_text'] : '';
    $btn_url  = isset($slide['btn_url']) ? (string) $slide['btn_url'] : '';
    ?>
    <div class="vp-repeater-row">
        <div class="row-title">Слайд <?php echo esc_html((string) ($index + 1)); ?></div>
        <div class="vp-single-media">
            <input type="hidden" class="vp-single-input" name="vp_hero_slides[<?php echo esc_attr((string) $index); ?>][image]" value="<?php echo esc_url($image); ?>" />
            <div class="vp-single-media-preview" <?php echo $image ? '' : 'style="display:none;"'; ?>>
                <img src="<?php echo esc_url($image); ?>" alt="" />
            </div>
            <button type="button" class="button vp-set-image">Выбрать изображение</button>
            <button type="button" class="button vp-remove-image">Удалить</button>
        </div>
        <input type="text" name="vp_hero_slides[<?php echo esc_attr((string) $index); ?>][title]" value="<?php echo esc_attr($title); ?>" placeholder="Заголовок слайда" />
        <input type="text" name="vp_hero_slides[<?php echo esc_attr((string) $index); ?>][subtitle]" value="<?php echo esc_attr($subtitle); ?>" placeholder="Подзаголовок слайда" />
        <input type="text" name="vp_hero_slides[<?php echo esc_attr((string) $index); ?>][btn_text]" value="<?php echo esc_attr($btn_text); ?>" placeholder="Текст кнопки (необязательно)" />
        <input type="text" name="vp_hero_slides[<?php echo esc_attr((string) $index); ?>][btn_url]" value="<?php echo esc_attr($btn_url); ?>" placeholder="Ссылка кнопки (например, /contacts/)" />
        <button type="button" class="button vp-remove-row">Удалить слайд</button>
    </div>
    <?php
}
