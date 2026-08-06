<?php
/**
 * Шапка сайта.
 *
 * @package VitragePro
 */

declare(strict_types=1);

$contacts_url = vitrage_pro_contacts_url();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> id="body">
<?php wp_body_open(); ?>

<!-- Begin header -->
<header id="header" class="header-show-hide-on-scroll menu-align-right">

    <div class="header-inner tt-wrap">

        <!-- Logo -->
        <div id="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-light logo"><?php echo esc_html(get_bloginfo('name')); ?></a>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-light-m logo"><?php echo esc_html(get_bloginfo('name')); ?></a>
        </div>

        <!-- Main menu -->
        <nav class="tt-main-menu">

            <div id="tt-m-menu-toggle-btn"><span></span></div>

            <div class="tt-menu-tools">
                <ul>
                    <li>
                        <a href="<?php echo esc_url($contacts_url); ?>" class="tt-tools-button">Задать вопрос</a>
                    </li>
                </ul>
            </div>

            <div class="tt-menu-collapse tt-submenu-dark">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'tt-menu-nav',
                    'fallback_cb'    => 'vitrage_pro_menu_fallback',
                    'walker'         => new Vitrage_Pro_Walker_Nav_Menu(),
                ]);
                ?>
            </div>

        </nav>

    </div>

</header>
<!-- End header -->

<div id="body-content">
