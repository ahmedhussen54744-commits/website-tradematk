<?php
/**
 * Security Class - Advanced Protection
 */

if (!defined('ABSPATH')) exit;

class TM_Cert_Security {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'security_init'));
        add_action('wp_login_failed', array($this, 'log_failed_login'));
        add_filter('authenticate', array($this, 'check_login_attempts'), 30, 3);
        add_action('template_redirect', array($this, 'block_direct_access'));
    }
    
    public function security_init() {
        // Remove WordPress version from head
        remove_action('wp_head', 'wp_generator');
        
        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Disable file editing from dashboard
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
        
        // Remove REST API user endpoints for non-logged users
        add_filter('rest_endpoints', array($this, 'restrict_rest_endpoints'));
        
        // Prevent username enumeration
        if (!is_admin()) {
            if (isset($_REQUEST['author']) && !is_user_logged_in()) {
                wp_redirect(home_url('/'), 301);
                exit;
            }
        }
    }
    
    /**
     * Restrict REST API endpoints
     */
    public function restrict_rest_endpoints($endpoints) {
        if (!is_user_logged_in()) {
            if (isset($endpoints['/wp/v2/users'])) {
                unset($endpoints['/wp/v2/users']);
            }
            if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
                unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
            }
        }
        return $endpoints;
    }
    
    /**
     * Log failed login attempts
     */
    public function log_failed_login($username) {
        $ip = $this->get_client_ip();
        $key = 'tm_failed_login_' . md5($ip);
        $attempts = get_transient($key) ?: 0;
        set_transient($key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    }
    
    /**
     * Block brute force login attempts
     */
    public function check_login_attempts($user, $username, $password) {
        if (empty($username)) return $user;
        
        $ip = $this->get_client_ip();
        $key = 'tm_failed_login_' . md5($ip);
        $attempts = get_transient($key) ?: 0;
        
        if ($attempts >= 5) {
            return new WP_Error('too_many_attempts', 
                'Too many failed login attempts. Please try again in 15 minutes.'
            );
        }
        
        return $user;
    }
    
    /**
     * Block direct file access to certificate uploads
     */
    public function block_direct_access() {
        // Prevent accessing certificate files without proper authentication
        $request_uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($request_uri, '/tm-certificates/') !== false) {
            if (!is_user_logged_in() && !isset($_POST['tm_verify_code'])) {
                // Allow only through verify page
                $referer = wp_get_referer();
                if (!$referer || strpos($referer, 'verify') === false) {
                    status_header(403);
                    exit('Access denied.');
                }
            }
        }
    }
    
    /**
     * Get real client IP
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key]);
                return trim($ip[0]);
            }
        }
        return '0.0.0.0';
    }
    
    /**
     * Sanitize file uploads - check for malicious content
     */
    public static function validate_upload($file) {
        // Check file extension
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'svg', 'pdf');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, $allowed_ext)) {
            return new WP_Error('invalid_type', 'Invalid file type.');
        }
        
        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return new WP_Error('too_large', 'File too large. Maximum 10MB.');
        }
        
        // Check for PHP in file content
        $content = file_get_contents($file['tmp_name']);
        if (preg_match('/<\?php|<\?=|eval\s*\(/i', $content)) {
            return new WP_Error('malicious', 'File appears to contain malicious content.');
        }
        
        return true;
    }
    
    /**
     * Generate secure hash for certificate verification
     */
    public static function generate_verify_hash($cert_code, $post_id) {
        return hash('sha256', $cert_code . $post_id . wp_salt('auth'));
    }
}
