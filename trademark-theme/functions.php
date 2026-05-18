<?php
/**
 * Trademark Certificate 3D Theme Functions
 * 
 * @package Trademark_3D
 * @since 1.0.0
 * Established 2009
 */

if (!defined('ABSPATH')) exit;

define('TM3D_THEME_VERSION', '2.0.0');
define('TM3D_THEME_DIR', get_template_directory());
define('TM3D_THEME_URI', get_template_directory_uri());

/**
 * Theme Setup
 */
function tm3d_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array(
        'height'      => 100,
        'width'       => 400,
        'flex-height' => true,
        'flex-width'  => true,
    ));
    add_theme_support('html5', array('search-form', 'comment-form', 'gallery', 'caption'));
    
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'trademark-3d'),
        'footer'  => __('Footer Menu', 'trademark-3d'),
    ));
}
add_action('after_setup_theme', 'tm3d_theme_setup');

/**
 * Enqueue Styles and Scripts
 */
function tm3d_enqueue_assets() {
    // Google Fonts - Inter + Orbitron + Noto Sans Bengali
    wp_enqueue_style('tm3d-google-fonts', 
        'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Orbitron:wght@400;500;600;700;800;900&family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap', 
        array(), null
    );
    
    // Main Theme Style
    wp_enqueue_style('tm3d-style', get_stylesheet_uri(), array(), TM3D_THEME_VERSION);
    
    // Three.js for 3D effects
    wp_enqueue_script('three-js', 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js', array(), 'r128', true);
    
    // GSAP for advanced animations
    wp_enqueue_script('gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true);
    wp_enqueue_script('gsap-scroll', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', array('gsap'), '3.12.2', true);
    
    // QRCode.js
    wp_enqueue_script('qrcode-js', 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js', array(), '1.0.0', true);
    
    // Theme main JS
    wp_enqueue_script('tm3d-main', TM3D_THEME_URI . '/assets/js/main.js', array('jquery', 'three-js', 'gsap'), TM3D_THEME_VERSION, true);
    
    wp_localize_script('tm3d-main', 'tm3dData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'homeUrl' => home_url('/'),
        'nonce'   => wp_create_nonce('tm3d_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'tm3d_enqueue_assets');

/**
 * Security Headers
 */
function tm3d_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:;");
    }
}
add_action('send_headers', 'tm3d_security_headers');

/**
 * Disable right-click and copy protection (configurable)
 */
function tm3d_copy_protection() {
    if (get_option('tm3d_copy_protection', '1') === '1' && !is_admin()) {
        ?>
        <script>
        document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && (e.key === 'u' || e.key === 's' || e.key === 'c')) {
                e.preventDefault();
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'tm3d_copy_protection');

/**
 * Custom page templates
 */
function tm3d_page_templates($templates) {
    $templates['page-verify.php'] = 'Trademark Verify Page';
    $templates['page-apply.php'] = 'Trademark Apply Page';
    return $templates;
}
add_filter('theme_page_templates', 'tm3d_page_templates');

/**
 * Customizer Settings
 */
function tm3d_customizer($wp_customize) {
    // Theme Section
    $wp_customize->add_section('tm3d_settings', array(
        'title'    => __('Trademark Theme Settings', 'trademark-3d'),
        'priority' => 30,
    ));
    
    // Site Established Year
    $wp_customize->add_setting('tm3d_established_year', array(
        'default'           => '2009',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tm3d_established_year', array(
        'label'   => __('Established Year', 'trademark-3d'),
        'section' => 'tm3d_settings',
        'type'    => 'text',
    ));
    
    // Copyright Text
    $wp_customize->add_setting('tm3d_copyright_text', array(
        'default'           => 'Trademark Certificate Authority. All Rights Reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tm3d_copyright_text', array(
        'label'   => __('Copyright Text', 'trademark-3d'),
        'section' => 'tm3d_settings',
        'type'    => 'text',
    ));

    // Copy Protection Toggle
    $wp_customize->add_setting('tm3d_copy_protection', array(
        'default'           => '1',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('tm3d_copy_protection', array(
        'label'   => __('Enable Copy Protection', 'trademark-3d'),
        'section' => 'tm3d_settings',
        'type'    => 'checkbox',
    ));
}
add_action('customize_register', 'tm3d_customizer');

/**
 * Widget Areas
 */
function tm3d_widgets_init() {
    register_sidebar(array(
        'name'          => __('Footer Widget Area', 'trademark-3d'),
        'id'            => 'footer-widgets',
        'before_widget' => '<div class="tm-footer-widget">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4 class="tm-widget-title">',
        'after_title'   => '</h4>',
    ));
}
add_action('widgets_init', 'tm3d_widgets_init');
