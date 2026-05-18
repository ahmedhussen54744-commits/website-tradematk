<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noarchive, noimageindex">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Loading Screen -->
<div class="tm-loader" id="tmLoader">
    <div class="tm-loader-ring"></div>
</div>

<!-- 3D Background -->
<div class="tm-bg-canvas" id="tmBgCanvas"></div>
<div class="tm-particles" id="tmParticles"></div>

<!-- Header -->
<header class="tm-header" id="tmHeader">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="tm-logo">
        <div class="tm-logo-icon">TM</div>
        <span class="tm-logo-text"><?php bloginfo('name'); ?></span>
    </a>
    
    <nav class="tm-nav" id="tmNav">
        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <a href="<?php echo esc_url(home_url('/apply')); ?>">Apply</a>
        <a href="<?php echo esc_url(home_url('/verify')); ?>">Verify</a>
        <?php if (is_user_logged_in() && current_user_can('manage_options')): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=tm-cert-dashboard')); ?>">Dashboard</a>
        <?php endif; ?>
    </nav>
    
    <button class="tm-mobile-menu-btn" id="tmMobileMenu" aria-label="Menu">&#9776;</button>
</header>
