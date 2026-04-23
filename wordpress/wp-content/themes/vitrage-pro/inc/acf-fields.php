<?php
declare(strict_types=1);

function vitrage_pro_register_settings(): void
{
    register_setting('general', 'vp_phone', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']);
    register_setting('general', 'vp_email', ['type' => 'string', 'sanitize_callback' => 'sanitize_email']);
    register_setting('general', 'vp_address', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']);
    register_setting('general', 'vp_hero_subtitle', ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field']);

    add_settings_field('vp_phone', 'Телефон сайта', 'vitrage_pro_phone_field', 'general');
    add_settings_field('vp_email', 'Email сайта', 'vitrage_pro_email_field', 'general');
    add_settings_field('vp_address', 'Адрес', 'vitrage_pro_address_field', 'general');
    add_settings_field('vp_hero_subtitle', 'Подзаголовок на главной', 'vitrage_pro_hero_subtitle_field', 'general');
}
add_action('admin_init', 'vitrage_pro_register_settings');

function vitrage_pro_phone_field(): void
{
    printf('<input type="text" name="vp_phone" value="%s" class="regular-text" />', esc_attr((string) get_option('vp_phone', '')));
}

function vitrage_pro_email_field(): void
{
    printf('<input type="email" name="vp_email" value="%s" class="regular-text" />', esc_attr((string) get_option('vp_email', '')));
}

function vitrage_pro_address_field(): void
{
    printf('<input type="text" name="vp_address" value="%s" class="regular-text" />', esc_attr((string) get_option('vp_address', '')));
}

function vitrage_pro_hero_subtitle_field(): void
{
    printf('<input type="text" name="vp_hero_subtitle" value="%s" class="regular-text" />', esc_attr((string) get_option('vp_hero_subtitle', '')));
}
