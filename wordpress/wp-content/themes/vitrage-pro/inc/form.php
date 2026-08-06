<?php
/**
 * Форма обратной связи: шорткод + AJAX-обработчик + SMTP.
 *
 * @package VitragePro
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Шорткод [vp_contact_form].
 */
function vitrage_pro_contact_form_shortcode(): string
{
    $success = (string) get_option('vp_form_success', 'Спасибо! Мы свяжемся с вами в ближайшее время.');
    ob_start();
    ?>
    <form class="ajax_form vp-contact-form" id="contact-form" method="post">
        <div class="contact-form-inner text-left">
            <div class="contact-form-info">
                <div class="tt-heading">
                    <div class="tt-heading-inner">
                        <h2 class="tt-heading-title">Напишите нам</h2>
                        <hr class="hr-short">
                    </div>
                </div>
                <div class="margin-top-30">
                    <p>Заполните форму — мы свяжемся с вами в удобное время.</p>
                </div>
            </div>

            <!-- Honeypot: поле скрыто от людей, но видно ботам. -->
            <div style="position:absolute; left:-9999px;" aria-hidden="true">
                <label for="vp_website">Не заполняйте это поле</label>
                <input type="text" id="vp_website" name="vp_website" tabindex="-1" autocomplete="off" />
            </div>

            <input type="hidden" name="vp_action" value="send" />

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <input type="text" name="name" value="" placeholder="Ваше имя" class="form-control" required />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <input type="tel" name="phone" value="" placeholder="Телефон" class="form-control" required />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <input type="email" name="email" value="" placeholder="Email" class="form-control" />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <textarea class="form-control" name="message" rows="4" placeholder="Сообщение" required></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="agreement form-group">
                    <div class="col-sm-8">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="agreement" class="check" checked required />
                                Я даю согласие на обработку персональных данных
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="form-group">
                    <div class="col-sm-12">
                        <button type="submit" class="btn btn-primary">Отправить</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="vp-form-result" role="status"></div>
        <input type="hidden" name="vp_success_message" value="<?php echo esc_attr($success); ?>" />
    </form>
    <?php
    return (string) ob_get_clean();
}
add_shortcode('vp_contact_form', 'vitrage_pro_contact_form_shortcode');

/**
 * AJAX-обработчик отправки формы.
 */
function vitrage_pro_handle_form(): void
{
    check_ajax_referer('vp_form_nonce', 'nonce');

    // Honeypot: боты заполняют скрытое поле — молча «успех».
    if (!empty($_POST['vp_website'])) {
        wp_send_json_success(['message' => 'Спасибо!']);
    }

    $name     = isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '';
    $phone    = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $email    = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $message  = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
    $success  = isset($_POST['vp_success_message']) ? sanitize_text_field(wp_unslash($_POST['vp_success_message'])) : 'Спасибо!';

    if ($name === '' || $phone === '' || $message === '') {
        wp_send_json_error(['message' => 'Пожалуйста, заполните обязательные поля: имя, телефон и сообщение.']);
    }

    $recipient = (string) get_option('vp_form_recipient', '');
    if ($recipient === '') {
        $recipient = (string) get_option('admin_email', '');
    }
    $subject = (string) get_option('vp_form_subject', 'Заявка с сайта vitrage-pro.ru');

    $body = "Имя: {$name}\n";
    $body .= "Телефон: {$phone}\n";
    if ($email) {
        $body .= "Email: {$email}\n";
    }
    $body .= "Сообщение:\n{$message}\n";

    $headers = ['Reply-To: ' . ($email ?: $recipient)];

    $sent = wp_mail($recipient, $subject, $body, $headers);

    if ($sent) {
        wp_send_json_success(['message' => $success]);
    }

    wp_send_json_error(['message' => 'Не удалось отправить письмо. Пожалуйста, попробуйте позже или позвоните нам.']);
}
add_action('wp_ajax_vp_send_message', 'vitrage_pro_handle_form');
add_action('wp_ajax_nopriv_vp_send_message', 'vitrage_pro_handle_form');

/**
 * Подключение SMTP к wp_mail(), если настройки заполнены.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer Объект почтовика.
 */
function vitrage_pro_smtp(/** @noinspection PhpUndefinedClassInspection */ $phpmailer): void
{
    $host = (string) get_option('vp_smtp_host', '');
    if ($host === '') {
        return;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host = $host;
    $phpmailer->Port = (int) get_option('vp_smtp_port', 465);
    $phpmailer->SMTPSecure = (string) get_option('vp_smtp_secure', 'ssl');

    $user = (string) get_option('vp_smtp_user', '');
    $pass = (string) get_option('vp_smtp_pass', '');
    if ($user !== '') {
        $phpmailer->SMTPAuth = true;
        $phpmailer->Username = $user;
        $phpmailer->Password = $pass;
    }

    $from_name = (string) get_option('vp_smtp_from_name', '');
    if ($from_name !== '') {
        $phpmailer->FromName = $from_name;
    }
}
add_action('phpmailer_init', 'vitrage_pro_smtp');
