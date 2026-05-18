<?php
/**
 * Custom Post Type for Certificates
 */

if (!defined('ABSPATH')) exit;

class TM_Cert_Post_Type {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_filter('manage_tm_certificate_posts_columns', array($this, 'custom_columns'));
        add_action('manage_tm_certificate_posts_custom_column', array($this, 'column_content'), 10, 2);
        add_filter('manage_edit-tm_certificate_sortable_columns', array($this, 'sortable_columns'));
    }
    
    public function register_post_type() {
        $labels = array(
            'name'               => 'Certificates',
            'singular_name'      => 'Certificate',
            'add_new'            => 'Add New Certificate',
            'add_new_item'       => 'Add New Certificate',
            'edit_item'          => 'Edit Certificate',
            'new_item'           => 'New Certificate',
            'view_item'          => 'View Certificate',
            'search_items'       => 'Search Certificates',
            'not_found'          => 'No certificates found',
            'not_found_in_trash' => 'No certificates found in trash',
            'menu_name'          => 'Certificates',
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => false, // We'll add it to our custom menu
            'query_var'          => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'supports'           => array('title'),
            'menu_icon'          => 'dashicons-awards',
        );
        
        register_post_type('tm_certificate', $args);
    }
    
    public function custom_columns($columns) {
        $new_columns = array(
            'cb'               => $columns['cb'],
            'title'            => 'Brand / Owner',
            'tm_cert_code'     => 'Certificate Code',
            'tm_status'        => 'Status',
            'tm_class'         => 'Class',
            'tm_app_date'      => 'Application Date',
            'tm_expiry'        => 'Expiry Date',
        );
        return $new_columns;
    }
    
    public function column_content($column, $post_id) {
        switch ($column) {
            case 'tm_cert_code':
                $code = get_post_meta($post_id, '_tm_cert_code', true);
                echo '<code style="background:#f0f0f1;padding:3px 8px;border-radius:4px;font-size:12px;">' . esc_html($code) . '</code>';
                break;
                
            case 'tm_status':
                $status = get_post_meta($post_id, '_tm_status', true);
                $colors = array(
                    'pending'  => '#ff8f00',
                    'approved' => '#00c853',
                    'rejected' => '#ff1744',
                    'expired'  => '#9e9e9e',
                );
                $color = isset($colors[$status]) ? $colors[$status] : '#666';
                echo '<span style="background:' . esc_attr($color) . ';color:#fff;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;text-transform:uppercase;">' . esc_html($status) . '</span>';
                break;
                
            case 'tm_class':
                echo esc_html(get_post_meta($post_id, '_tm_class', true));
                break;
                
            case 'tm_app_date':
                echo esc_html(get_post_meta($post_id, '_tm_application_date', true));
                break;
                
            case 'tm_expiry':
                echo esc_html(get_post_meta($post_id, '_tm_expiry_date', true));
                break;
        }
    }
    
    public function sortable_columns($columns) {
        $columns['tm_status'] = 'tm_status';
        $columns['tm_app_date'] = 'tm_app_date';
        return $columns;
    }
}
