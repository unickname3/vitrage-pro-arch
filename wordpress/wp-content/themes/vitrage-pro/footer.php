<?php
/**
 * Подвал сайта.
 *
 * @package VitragePro
 */

declare(strict_types=1);

$footer_text = vitrage_pro_opt('vp_footer_text', '');
if ($footer_text === '') {
    $footer_text = '- Профессиональное проектирование и изготовление художественных витражей, авторских светильников и мозаичных панно на заказ.';
}
$copyright = vitrage_pro_opt('vp_copyright', '');
if ($copyright === '') {
    $copyright = '© Витраж Про ' . esc_html(date('Y')) . ' / Все права защищены';
}
$vk = vitrage_pro_opt('vp_vk', '');
$email = vitrage_pro_opt('vp_email', '');
$show_subscribe = (int) get_option('vp_show_subscribe', 0);
?>
</div><!-- /#body-content -->

<!-- Footer -->
<section id="footer" class="footer-dark no-margin-top">
    <div class="footer-inner">
        <div class="footer-container tt-wrap">
            <div class="row">
                <div class="col-md-3">
                    <div id="footer-logo">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-light logo"><?php echo esc_html(strtoupper((string) get_bloginfo('name'))); ?></a>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-light-m logo"><?php echo esc_html(strtoupper((string) get_bloginfo('name'))); ?></a>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="footer-text">
                        <h4><?php echo esc_html($footer_text); ?></h4>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="social-buttons">
                        <ul>
                            <?php if ($vk) : ?>
                                <li><a href="<?php echo esc_url($vk); ?>" class="btn btn-social-min btn-default btn-rounded-full" title="Вконтакте" target="_blank" rel="noopener"><i class="fa fa-vk"></i></a></li>
                            <?php endif; ?>
                            <?php if ($email) : ?>
                                <li><a href="mailto:<?php echo esc_attr($email); ?>" class="btn btn-social-min btn-default btn-rounded-full" title="Email" target="_blank" rel="noopener"><i class="fa fa-envelope"></i></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <?php if ($show_subscribe) : ?>
                        <form id="footer-subscribe-form" class="form-btn-inside" action="#" method="post">
                            <div class="form-group">
                                <input type="email" class="form-control no-bg" name="subscribe" placeholder="Подписаться..." aria-label="Email для подписки">
                                <button type="submit" aria-label="Подписаться"><i class="fa fa-paper-plane"></i></button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-container tt-wrap">
                <div class="row">
                    <div class="col-md-6 col-md-push-6">
                        <?php
                        wp_nav_menu([
                            'theme_location' => 'footer',
                            'container'      => false,
                            'menu_class'     => 'footer-menu',
                            'fallback_cb'    => false,
                        ]);
                        ?>
                    </div>
                    <div class="col-md-6 col-md-pull-6">
                        <div class="footer-copyright">
                            <p><?php echo esc_html($copyright); ?></p>
                            <?php $privacy = get_page_by_path('privacy'); ?>
                            <?php if ($privacy) : ?>
                                <p><a href="<?php echo esc_url(get_permalink($privacy)); ?>">Политика конфиденциальности</a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="#body" class="scrolltotop sm-scroll" title="На верх"><i class="fa fa-chevron-up"></i></a>
</section>

<?php wp_footer(); ?>
</body>
</html>
