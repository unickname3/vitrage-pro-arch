<?php
declare(strict_types=1);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?> id="body">
<?php wp_body_open(); ?>
<header id="header" class="header-show-hide-on-scroll menu-align-right">
    <div class="header-inner tt-wrap">
        <div id="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-light logo"><?php bloginfo('name'); ?></a>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="logo-light-m logo"><?php bloginfo('name'); ?></a>
        </div>
        <nav class="tt-main-menu">
            <div id="tt-m-menu-toggle-btn"><span></span></div>
            <div class="tt-menu-tools">
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/contacts/')); ?>" class="tt-tools-button">Задать вопрос</a></li>
                </ul>
            </div>
            <div class="tt-menu-collapse tt-submenu-dark">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'tt-menu-nav',
                    'fallback_cb' => false,
                ]);
                ?>
            </div>
        </nav>
    </div>
</header>
<div id="body-content">
