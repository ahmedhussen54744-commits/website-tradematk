<?php
/**
 * Internal API & AJAX Handlers (POST only, no GET)
 */

if (!defined('ABSPATH')) exit;

class TM_Cert_API {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // All AJAX handlers use POST method only
        add_action('wp_ajax_tm_verify_certificate', array($this, 'verify_certificate'));
        add_action('wp_ajax_nopriv_tm_verify_certificate', array($this, 'verify_certificate'));
        add_action('wp_ajax_tm_admin_update_status', array($this, 'admin_update_status'));
        add_action('wp_ajax_tm_admin_regenerate_code', array($this, 'admin_regenerate_code'));
        add_action('wp_ajax_tm_cert_quick_action', array($this, 'quick_action'));
        add_action('wp_ajax_tm_cert_bulk_action', array($this, 'bulk_action'));
        add_action('wp_ajax_tm_cert_export', array($this, 'export_data'));
    }
    
    /**
     * Verify certificate via AJAX (POST only)
     */
    public function verify_certificate() {
        // Only accept POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Method not allowed', 405);
        }
        
        check_ajax_referer('tm3d_nonce', 'nonce');
        
        $code = sanitize_text_field($_POST['code']);
        
        if (strlen($code) !== 20) {
            wp_send_json_error('Invalid code format. Must be exactly 20 characters.');
        }
        
        // Rate limit verification attempts
        $ip = $_SERVER['REMOTE_ADDR'];
        $rate_key = 'tm_verify_' . md5($ip);
        $attempts = get_transient($rate_key) ?: 0;
        
        if ($attempts >= 20) {
            wp_send_json_error('Too many verification attempts. Please wait.');
        }
        
        set_transient($rate_key, $attempts + 1, HOUR_IN_SECONDS);
        
        // Query certificate
        $args = array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => '_tm_cert_code',
                    'value' => $code,
                ),
            ),
        );
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            wp_send_json_error('Certificate not found.');
        }
        
        $post = $query->posts[0];
        $data = array(
            'brand_name'        => get_post_meta($post->ID, '_tm_brand_name', true),
            'owner_name'        => get_post_meta($post->ID, '_tm_owner_name', true),
            'status'            => get_post_meta($post->ID, '_tm_status', true),
            'class'             => get_post_meta($post->ID, '_tm_class', true),
            'application_date'  => get_post_meta($post->ID, '_tm_application_date', true),
            'registration_date' => get_post_meta($post->ID, '_tm_registration_date', true),
            'approved_date'     => get_post_meta($post->ID, '_tm_approved_date', true),
            'expiry_date'       => get_post_meta($post->ID, '_tm_expiry_date', true),
            'cert_image'        => get_post_meta($post->ID, '_tm_cert_image', true),
            'brand_logo'        => get_post_meta($post->ID, '_tm_brand_logo', true),
        );
        
        wp_send_json_success($data);
    }
    
    /**
     * Admin: Update certificate status
     */
    public function admin_update_status() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Method not allowed');
        }
        
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id']);
        $status = sanitize_text_field($_POST['status']);
        
        $valid_statuses = array('pending', 'approved', 'rejected', 'expired');
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error('Invalid status');
        }
        
        update_post_meta($post_id, '_tm_status', $status);
        
        if ($status === 'approved') {
            update_post_meta($post_id, '_tm_approved_date', current_time('Y-m-d'));
        }
        
        wp_send_json_success(array('new_status' => $status));
    }
    
    /**
     * Admin: Regenerate certificate code
     */
    public function admin_regenerate_code() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Method not allowed');
        }
        
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id']);
        $length = intval(get_option('tm_cert_code_length', 20));
        
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        update_post_meta($post_id, '_tm_cert_code', $code);
        
        wp_send_json_success(array('new_code' => $code));
    }
    
    /**
     * Quick approve/reject
     */
    public function quick_action() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Method not allowed');
        }
        
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $post_id = intval($_POST['post_id']);
        $action = sanitize_text_field($_POST['tm_action']);
        
        if (!in_array($action, array('approve', 'reject'))) {
            wp_send_json_error('Invalid action');
        }
        
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        update_post_meta($post_id, '_tm_status', $status);
        
        if ($action === 'approve') {
            update_post_meta($post_id, '_tm_approved_date', current_time('Y-m-d'));
        }
        
        // Email notification
        $this->send_status_notification($post_id, $status);
        
        wp_send_json_success(array('status' => $status));
    }
    
    /**
     * Bulk action handler
     */
    public function bulk_action() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Method not allowed');
        }
        
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $ids = isset($_POST['ids']) ? array_map('intval', (array) $_POST['ids']) : array();
        $action = sanitize_text_field($_POST['tm_action']);
        
        if (empty($ids)) {
            wp_send_json_error('No items selected');
        }
        
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        foreach ($ids as $id) {
            update_post_meta($id, '_tm_status', $status);
            if ($action === 'approve') {
                update_post_meta($id, '_tm_approved_date', current_time('Y-m-d'));
            }
            $this->send_status_notification($id, $status);
        }
        
        wp_send_json_success(array('count' => count($ids), 'status' => $status));
    }
    
    /**
     * Export data
     */
    public function export_data() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            wp_send_json_error('Method not allowed');
        }
        
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $certs = get_posts(array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => -1,
        ));
        
        $rows = array();
        $rows[] = array('Code', 'Brand', 'Owner', 'Email', 'Phone', 'Status', 'Class', 'Country', 'App Date', 'Reg Date', 'Approved', 'Expiry');
        
        foreach ($certs as $cert) {
            $rows[] = array(
                get_post_meta($cert->ID, '_tm_cert_code', true),
                get_post_meta($cert->ID, '_tm_brand_name', true),
                get_post_meta($cert->ID, '_tm_owner_name', true),
                get_post_meta($cert->ID, '_tm_owner_email', true),
                get_post_meta($cert->ID, '_tm_owner_phone', true),
                get_post_meta($cert->ID, '_tm_status', true),
                get_post_meta($cert->ID, '_tm_class', true),
                get_post_meta($cert->ID, '_tm_country', true),
                get_post_meta($cert->ID, '_tm_application_date', true),
                get_post_meta($cert->ID, '_tm_registration_date', true),
                get_post_meta($cert->ID, '_tm_approved_date', true),
                get_post_meta($cert->ID, '_tm_expiry_date', true),
            );
        }
        
        wp_send_json_success(array('data' => $rows));
    }
    
    /**
     * Send status notification email
     */
    private function send_status_notification($post_id, $status) {
        if (get_option('tm_cert_auto_email', '1') !== '1') return;
        
        $email = get_post_meta($post_id, '_tm_owner_email', true);
        $brand = get_post_meta($post_id, '_tm_brand_name', true);
        $code = get_post_meta($post_id, '_tm_cert_code', true);
        
        if (!$email) return;
        
        $verify_url = get_option('tm_cert_verify_url', home_url('/verify'));
        
        if ($status === 'approved') {
            $subject = "Trademark Application Approved - {$brand}";
            $message = "Dear Applicant,\n\n";
            $message .= "Your trademark application for '{$brand}' has been APPROVED.\n\n";
            $message .= "Certificate Code: {$code}\n";
            $message .= "Verify at: {$verify_url}\n\n";
            $message .= "Congratulations!\n";
            $message .= "Trademark Certificate Authority";
        } else {
            $subject = "Trademark Application Update - {$brand}";
            $message = "Dear Applicant,\n\n";
            $message .= "Your trademark application for '{$brand}' status has been updated to: " . strtoupper($status) . ".\n\n";
            $message .= "If you have questions, please contact our support team.\n\n";
            $message .= "Trademark Certificate Authority";
        }
        
        wp_mail($email, $subject, $message);
    }
}
