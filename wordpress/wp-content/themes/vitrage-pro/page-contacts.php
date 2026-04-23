<?php
declare(strict_types=1);

get_header();
?>
<main class="tt-wrap margin-top-80 margin-bottom-80">
    <h1 class="page-header-title"><?php the_title(); ?></h1>
    <div class="row">
        <div class="col-md-5 margin-bottom-30">
            <p><strong>Телефон:</strong> <?php echo esc_html((string) get_option('vp_phone', '')); ?></p>
            <p><strong>Email:</strong> <?php echo esc_html((string) get_option('vp_email', '')); ?></p>
            <p><strong>Адрес:</strong> <?php echo esc_html((string) get_option('vp_address', '')); ?></p>
        </div>
        <div class="col-md-7">
            <?php echo do_shortcode('[vp_contact_form]'); ?>
        </div>
    </div>
</main>
<?php
get_footer();
