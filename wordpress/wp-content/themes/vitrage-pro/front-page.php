<?php
declare(strict_types=1);

get_header();
?>
<main>
    <section id="tt-intro" class="slideshow-intro">
        <div class="tt-intro-inner">
            <div class="tt-wrap">
                <div class="intro-caption intro-caption-xxlg center-left">
                    <h1 class="tt-heading-title"><?php echo esc_html(get_bloginfo('description')); ?></h1>
                    <p class="intro-description max-width-650">
                        <?php echo esc_html((string) get_option('vp_hero_subtitle', 'Профессиональное проектирование и изготовление художественных витражей')); ?>
                    </p>
                    <div class="margin-top-30">
                        <a href="<?php echo esc_url(home_url('/contacts/')); ?>" class="btn btn-primary">Оставить заявку</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tt-wrap margin-top-80">
        <h2 class="tt-heading-title">Наши работы</h2>
        <?php
        $gallery_items = new WP_Query([
            'post_type' => 'gallery_item',
            'posts_per_page' => 9,
        ]);
        if ($gallery_items->have_posts()) :
            echo '<div class="row">';
            while ($gallery_items->have_posts()) :
                $gallery_items->the_post();
                echo '<div class="col-sm-6 col-md-4 margin-bottom-30">';
                echo '<a href="' . esc_url(get_permalink()) . '">';
                if (has_post_thumbnail()) {
                    the_post_thumbnail('large', ['class' => 'img-responsive']);
                }
                echo '<h3 class="gl-item-title">' . esc_html(get_the_title()) . '</h3>';
                echo '</a></div>';
            endwhile;
            echo '</div>';
            wp_reset_postdata();
        endif;
        ?>
    </section>
</main>
<?php
get_footer();
