<?php
/**
 * Страница «Контакты»: контактная информация + форма.
 *
 * @package VitragePro
 */

declare(strict_types=1);

get_header();

$phone = vitrage_pro_opt('vp_phone');
$phone_2 = vitrage_pro_opt('vp_phone_2');
$email = vitrage_pro_opt('vp_email');
$address = vitrage_pro_opt('vp_address');
$work_hours = vitrage_pro_opt('vp_work_hours');
$vk = vitrage_pro_opt('vp_vk');
$whatsapp = vitrage_pro_opt('vp_whatsapp');
$telegram = vitrage_pro_opt('vp_telegram');
$bg = vitrage_pro_opt('vp_page_header_bg', VITRAGE_PRO_URI . '/assets/img/hero-default.jpg');
?>
<main id="main">

    <!-- Page header -->
    <section id="page-header">
        <div class="page-header-image parallax-bg-3 bg-image" style="background-image: url(<?php echo esc_url($bg); ?>); background-size:cover;">
            <div class="cover bg-transparent-5-dark"></div>
        </div>
        <div class="page-header-inner tt-wrap">
            <div class="page-header-caption ph-caption-lg parallax-4 fade-out-scroll-3">
                <h1 class="page-header-title"><?php the_title(); ?></h1>
                <hr class="hr-short">
                <div class="page-header-description" data-max-words="40">
                    <?php vitrage_pro_breadcrumbs(get_the_title()); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact section -->
    <section id="contact-section">
        <div class="contact-section-inner tt-wrap">
            <div class="split-box">
                <div class="container-fluid">
                    <div class="row">
                        <div class="row-lg-height full-height-vh">

                            <div class="col-lg-6 col-lg-height col-lg-middle bg-image" style="background-image: url(<?php echo esc_url($bg); ?>); background-position: 50% 50%;">
                                <div class="cover"></div>
                                <div class="split-box-content text-left no-padding-left no-padding-right">
                                    <div class="contact-info-wrap">
                                        <div class="contact-info">
                                            <?php if ($phone) : ?>
                                                <p><i class="fa fa-phone"></i> тел: <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></p>
                                            <?php endif; ?>
                                            <?php if ($phone_2) : ?>
                                                <p><i class="fa fa-phone"></i> тел: <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone_2)); ?>"><?php echo esc_html($phone_2); ?></a></p>
                                            <?php endif; ?>
                                            <?php if ($email) : ?>
                                                <p><i class="fa fa-envelope"></i> email: <a href="mailto:<?php echo esc_attr($email); ?>" target="_blank" rel="noopener"><?php echo esc_html($email); ?></a></p>
                                            <?php endif; ?>
                                            <?php if ($address) : ?>
                                                <p><i class="fa fa-home"></i> адрес: <?php echo esc_html($address); ?></p>
                                            <?php endif; ?>
                                            <?php if ($work_hours) : ?>
                                                <p><i class="fa fa-clock-o"></i> <?php echo esc_html($work_hours); ?></p>
                                            <?php endif; ?>
                                            <?php if ($whatsapp) : ?>
                                                <p><i class="fa fa-whatsapp"></i> WhatsApp: <?php echo esc_html($whatsapp); ?></p>
                                            <?php endif; ?>
                                            <?php if ($telegram) : ?>
                                                <p><i class="fa fa-telegram"></i> Telegram: <?php echo esc_html($telegram); ?></p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($vk) : ?>
                                            <div class="social-buttons margin-top-20">
                                                <ul>
                                                    <li><a href="<?php echo esc_url($vk); ?>" class="btn btn-social-min btn-default btn-rounded-full" title="Вконтакте" target="_blank" rel="noopener"><i class="fa fa-vk"></i></a></li>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-lg-height col-lg-middle no-padding">
                                <div class="split-box-content">
                                    <?php echo do_shortcode('[vp_contact_form]'); ?>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
<?php
get_footer();
