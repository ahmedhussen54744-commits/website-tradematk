<?php
/**
 * Plugin Name: Trademark Certificate Manager
 * Plugin URI: https://trademark-certificate.com
 * Description: Advanced Trademark Certificate Management System with admin approval, PDF/JPG upload, QR verification, and secure POST-based architecture.
 * Version: 2.0.0
 * Author: Trademark Team
 * Author URI: https://trademark-certificate.com
 * Text Domain: tm-cert
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

define('TM_CERT_VERSION', '2.0.0');
define('TM_CERT_PATH', plugin_dir_path(__FILE__));
define('TM_CERT_URL', plugin_dir_url(__FILE__));
define('TM_CERT_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
final class TM_Certificate_Manager {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        require_once TM_CERT_PATH . 'includes/class-post-type.php';
        require_once TM_CERT_PATH . 'includes/class-admin-dashboard.php';
        require_once TM_CERT_PATH . 'includes/class-admin-metabox.php';
        require_once TM_CERT_PATH . 'includes/class-security.php';
        require_once TM_CERT_PATH . 'includes/class-api.php';
        require_once TM_CERT_PATH . 'includes/class-notifications.php';
    }
    
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'));
        add_action('admin_enqueue_scripts', array($this, 'admin_assets'));
    }
    
    public function init() {
        // Initialize all components
        TM_Cert_Post_Type::instance();
        TM_Cert_Admin_Dashboard::instance();
        TM_Cert_Admin_Metabox::instance();
        TM_Cert_Security::instance();
        TM_Cert_API::instance();
        TM_Cert_Notifications::instance();
    }
    
    public function activate() {
        // Set default options
        add_option('tm_cert_verify_url', home_url('/verify'));
        add_option('tm_cert_qr_base_url', home_url('/verify'));
        add_option('tm_cert_code_length', 20);
        add_option('tm_cert_auto_email', '1');
        add_option('tm_cert_rate_limit', 5);
        add_option('tm_cert_copy_protection', '1');
        
        // Create pages
        $this->create_pages();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_pages() {
        // Create Verify page
        if (!get_page_by_path('verify')) {
            wp_insert_post(array(
                'post_title'   => 'Verify Certificate',
                'post_name'    => 'verify',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'page_template' => 'page-verify.php',
            ));
        }
        
        // Create Apply page
        if (!get_page_by_path('apply')) {
            wp_insert_post(array(
                'post_title'   => 'Apply for Trademark',
                'post_name'    => 'apply',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'page_template' => 'page-apply.php',
            ));
        }
    }
    
    public function admin_assets($hook) {
        if (strpos($hook, 'tm-cert') === false && get_post_type() !== 'tm_certificate') {
            return;
        }
        
        wp_enqueue_style('tm-cert-admin', TM_CERT_URL . 'assets/css/admin.css', array(), TM_CERT_VERSION);
        wp_enqueue_script('tm-cert-admin', TM_CERT_URL . 'assets/js/admin.js', array('jquery'), TM_CERT_VERSION, true);
        wp_enqueue_media();
        
        wp_localize_script('tm-cert-admin', 'tmCertAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('tm_cert_admin_nonce'),
        ));
    }
}

// Initialize plugin
TM_Certificate_Manager::instance();
