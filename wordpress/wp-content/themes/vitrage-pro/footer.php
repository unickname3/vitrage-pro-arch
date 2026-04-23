<?php
declare(strict_types=1);
?>
</div>
<footer id="footer" class="footer-dark no-margin-top">
    <div class="footer-inner tt-wrap">
        <div class="row">
            <div class="col-md-8">
                <p class="no-margin-bottom">
                    <?php echo esc_html(get_bloginfo('name')); ?>, <?php echo esc_html(date('Y')); ?>
                </p>
            </div>
            <div class="col-md-4 text-right">
                <?php
                wp_nav_menu([
                    'theme_location' => 'footer',
                    'container' => false,
                    'menu_class' => 'list-inline',
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
