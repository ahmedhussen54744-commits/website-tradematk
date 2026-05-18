<?php
/**
 * Notifications Class - Email & Admin Notifications
 */

if (!defined('ABSPATH')) exit;

class TM_Cert_Notifications {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Notify admin on new application
        add_action('save_post_tm_certificate', array($this, 'notify_admin_new_application'), 20, 2);
        
        // Admin bar notification
        add_action('admin_bar_menu', array($this, 'admin_bar_pending'), 999);
        
        // Dashboard widget
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }
    
    /**
     * Notify admin when new application submitted
     */
    public function notify_admin_new_application($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_status !== 'publish') return;
        if (get_post_meta($post_id, '_tm_admin_notified', true)) return;
        
        $admin_email = get_option('tm_cert_admin_email', get_option('admin_email'));
        $brand = get_post_meta($post_id, '_tm_brand_name', true);
        $owner = get_post_meta($post_id, '_tm_owner_name', true);
        
        if (!$brand) return; // Not a proper certificate
        
        $subject = "New Trademark Application: {$brand}";
        $message = "A new trademark application has been submitted.\n\n";
        $message .= "Brand: {$brand}\n";
        $message .= "Owner: {$owner}\n";
        $message .= "Date: " . current_time('Y-m-d H:i:s') . "\n\n";
        $message .= "Review it: " . get_edit_post_link($post_id) . "\n\n";
        $message .= "Trademark Certificate Manager";
        
        wp_mail($admin_email, $subject, $message);
        update_post_meta($post_id, '_tm_admin_notified', '1');
    }
    
    /**
     * Show pending count in admin bar
     */
    public function admin_bar_pending($wp_admin_bar) {
        if (!current_user_can('manage_options')) return;
        
        $pending = new WP_Query(array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array('key' => '_tm_status', 'value' => 'pending'),
            ),
        ));
        
        $count = $pending->found_posts;
        
        if ($count > 0) {
            $wp_admin_bar->add_node(array(
                'id'    => 'tm-cert-pending',
                'title' => sprintf('TM Pending <span class="ab-label">%d</span>', $count),
                'href'  => admin_url('admin.php?page=tm-cert-pending'),
                'meta'  => array('class' => 'tm-admin-bar-pending'),
            ));
        }
    }
    
    /**
     * WordPress Dashboard Widget
     */
    public function add_dashboard_widget() {
        if (!current_user_can('manage_options')) return;
        
        wp_add_dashboard_widget(
            'tm_cert_widget',
            '&#127942; Trademark Certificates',
            array($this, 'render_dashboard_widget')
        );
    }
    
    public function render_dashboard_widget() {
        $pending = new WP_Query(array(
            'post_type' => 'tm_certificate',
            'fields' => 'ids',
            'meta_query' => array(array('key' => '_tm_status', 'value' => 'pending')),
        ));
        
        $approved = new WP_Query(array(
            'post_type' => 'tm_certificate',
            'fields' => 'ids',
            'meta_query' => array(array('key' => '_tm_status', 'value' => 'approved')),
        ));
        
        echo '<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">';
        echo '<div style="background:#fff3e0;padding:15px;border-radius:8px;text-align:center;">';
        echo '<div style="font-size:28px;font-weight:700;color:#ff8f00;">' . $pending->found_posts . '</div>';
        echo '<div style="font-size:12px;color:#666;">Pending</div>';
        echo '</div>';
        echo '<div style="background:#e8f5e9;padding:15px;border-radius:8px;text-align:center;">';
        echo '<div style="font-size:28px;font-weight:700;color:#00c853;">' . $approved->found_posts . '</div>';
        echo '<div style="font-size:12px;color:#666;">Approved</div>';
        echo '</div>';
        echo '</div>';
        echo '<p style="margin-top:15px;"><a href="' . admin_url('admin.php?page=tm-cert-dashboard') . '" class="button button-primary">View Dashboard</a></p>';
    }
}
