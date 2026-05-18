<?php
/**
 * Admin Dashboard
 */

if (!defined('ABSPATH')) exit;

class TM_Cert_Admin_Dashboard {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('wp_ajax_tm_cert_quick_action', array($this, 'handle_quick_action'));
        add_action('wp_ajax_tm_cert_bulk_action', array($this, 'handle_bulk_action'));
        add_action('wp_ajax_tm_cert_export', array($this, 'handle_export'));
    }
    
    public function add_menu_pages() {
        // Main Menu
        add_menu_page(
            'Trademark Certificates',
            'TM Certificates',
            'manage_options',
            'tm-cert-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-awards',
            25
        );
        
        // Submenu pages
        add_submenu_page('tm-cert-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'tm-cert-dashboard', array($this, 'render_dashboard'));
        add_submenu_page('tm-cert-dashboard', 'All Certificates', 'All Certificates', 'manage_options', 'edit.php?post_type=tm_certificate');
        add_submenu_page('tm-cert-dashboard', 'Pending Applications', 'Pending', 'manage_options', 'tm-cert-pending', array($this, 'render_pending'));
        add_submenu_page('tm-cert-dashboard', 'Settings', 'Settings', 'manage_options', 'tm-cert-settings', array($this, 'render_settings'));
    }
    
    public function render_dashboard() {
        // Get stats
        $total = wp_count_posts('tm_certificate');
        $pending_count = $this->count_by_status('pending');
        $approved_count = $this->count_by_status('approved');
        $rejected_count = $this->count_by_status('rejected');
        $expired_count = $this->count_by_status('expired');
        
        // Recent applications
        $recent = get_posts(array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ));
        
        ?>
        <div class="wrap tm-admin-wrap">
            <h1 class="tm-admin-title">&#127942; Trademark Certificate Dashboard</h1>
            
            <!-- Stats Cards -->
            <div class="tm-admin-stats">
                <div class="tm-stat-card tm-stat-total">
                    <div class="tm-stat-number"><?php echo intval($total->publish); ?></div>
                    <div class="tm-stat-label">Total Applications</div>
                </div>
                <div class="tm-stat-card tm-stat-pending">
                    <div class="tm-stat-number"><?php echo intval($pending_count); ?></div>
                    <div class="tm-stat-label">Pending Review</div>
                </div>
                <div class="tm-stat-card tm-stat-approved">
                    <div class="tm-stat-number"><?php echo intval($approved_count); ?></div>
                    <div class="tm-stat-label">Approved</div>
                </div>
                <div class="tm-stat-card tm-stat-rejected">
                    <div class="tm-stat-number"><?php echo intval($rejected_count); ?></div>
                    <div class="tm-stat-label">Rejected</div>
                </div>
                <div class="tm-stat-card tm-stat-expired">
                    <div class="tm-stat-number"><?php echo intval($expired_count); ?></div>
                    <div class="tm-stat-label">Expired</div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="tm-admin-section">
                <h2>Quick Actions</h2>
                <div class="tm-quick-actions">
                    <a href="<?php echo admin_url('post-new.php?post_type=tm_certificate'); ?>" class="button button-primary button-large">+ Add New Certificate</a>
                    <a href="<?php echo admin_url('admin.php?page=tm-cert-pending'); ?>" class="button button-large">Review Pending (<?php echo intval($pending_count); ?>)</a>
                    <button class="button button-large" id="tmExportBtn">Export CSV</button>
                </div>
            </div>
            
            <!-- Recent Applications Table -->
            <div class="tm-admin-section">
                <h2>Recent Applications</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Brand Name</th>
                            <th>Owner</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $cert): 
                            $status = get_post_meta($cert->ID, '_tm_status', true);
                            $brand = get_post_meta($cert->ID, '_tm_brand_name', true);
                            $owner = get_post_meta($cert->ID, '_tm_owner_name', true);
                            $code = get_post_meta($cert->ID, '_tm_cert_code', true);
                        ?>
                        <tr>
                            <td><strong><?php echo esc_html($brand); ?></strong></td>
                            <td><?php echo esc_html($owner); ?></td>
                            <td><code><?php echo esc_html($code); ?></code></td>
                            <td>
                                <span class="tm-status-badge tm-status-<?php echo esc_attr($status); ?>">
                                    <?php echo esc_html(ucfirst($status)); ?>
                                </span>
                            </td>
                            <td><?php echo get_the_date('Y-m-d', $cert); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($cert->ID); ?>" class="button button-small">Edit</a>
                                <?php if ($status === 'pending'): ?>
                                <button class="button button-small button-primary tm-quick-approve" data-id="<?php echo $cert->ID; ?>">Approve</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($recent)): ?>
                        <tr><td colspan="6" style="text-align:center;padding:30px;">No applications yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    public function render_pending() {
        $pending = get_posts(array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_tm_status',
                    'value' => 'pending',
                ),
            ),
            'orderby' => 'date',
            'order'   => 'ASC',
        ));
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>&#9203; Pending Applications (<?php echo count($pending); ?>)</h1>
            
            <?php if (empty($pending)): ?>
            <div class="tm-admin-empty">
                <p>No pending applications at this time.</p>
            </div>
            <?php else: ?>
            
            <form id="tmBulkForm">
                <div class="tm-bulk-actions" style="margin: 20px 0;">
                    <select id="tmBulkSelect">
                        <option value="">Bulk Action</option>
                        <option value="approve">Approve Selected</option>
                        <option value="reject">Reject Selected</option>
                    </select>
                    <button type="button" class="button" id="tmBulkApply">Apply</button>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" id="tmSelectAll"></th>
                            <th>Brand</th>
                            <th>Owner</th>
                            <th>Email</th>
                            <th>Class</th>
                            <th>Applied</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending as $cert): 
                            $brand = get_post_meta($cert->ID, '_tm_brand_name', true);
                            $owner = get_post_meta($cert->ID, '_tm_owner_name', true);
                            $email = get_post_meta($cert->ID, '_tm_owner_email', true);
                            $class = get_post_meta($cert->ID, '_tm_class', true);
                            $app_date = get_post_meta($cert->ID, '_tm_application_date', true);
                        ?>
                        <tr>
                            <td><input type="checkbox" class="tm-bulk-check" value="<?php echo $cert->ID; ?>"></td>
                            <td><strong><?php echo esc_html($brand); ?></strong></td>
                            <td><?php echo esc_html($owner); ?></td>
                            <td><?php echo esc_html($email); ?></td>
                            <td><?php echo esc_html($class); ?></td>
                            <td><?php echo esc_html($app_date); ?></td>
                            <td>
                                <a href="<?php echo get_edit_post_link($cert->ID); ?>" class="button button-small">View/Edit</a>
                                <button type="button" class="button button-small button-primary tm-quick-approve" data-id="<?php echo $cert->ID; ?>">Approve</button>
                                <button type="button" class="button button-small tm-quick-reject" data-id="<?php echo $cert->ID; ?>">Reject</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function render_settings() {
        // Save settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tm_settings_nonce'])) {
            if (wp_verify_nonce($_POST['tm_settings_nonce'], 'tm_save_settings')) {
                update_option('tm_cert_verify_url', sanitize_url($_POST['tm_verify_url']));
                update_option('tm_cert_qr_base_url', sanitize_url($_POST['tm_qr_base_url']));
                update_option('tm_cert_code_length', intval($_POST['tm_code_length']));
                update_option('tm_cert_auto_email', sanitize_text_field($_POST['tm_auto_email']));
                update_option('tm_cert_rate_limit', intval($_POST['tm_rate_limit']));
                update_option('tm_cert_admin_email', sanitize_email($_POST['tm_admin_email']));
                update_option('tm_cert_copy_protection', sanitize_text_field($_POST['tm_copy_protection']));
                
                echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
            }
        }
        
        $verify_url = get_option('tm_cert_verify_url', home_url('/verify'));
        $qr_base_url = get_option('tm_cert_qr_base_url', home_url('/verify'));
        $code_length = get_option('tm_cert_code_length', 20);
        $auto_email = get_option('tm_cert_auto_email', '1');
        $rate_limit = get_option('tm_cert_rate_limit', 5);
        $admin_email = get_option('tm_cert_admin_email', get_option('admin_email'));
        $copy_protection = get_option('tm_cert_copy_protection', '1');
        
        ?>
        <div class="wrap tm-admin-wrap">
            <h1>&#9881; Certificate Settings</h1>
            
            <form method="POST" class="tm-settings-form">
                <?php wp_nonce_field('tm_save_settings', 'tm_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th>Verify Page URL</th>
                        <td>
                            <input type="url" name="tm_verify_url" value="<?php echo esc_attr($verify_url); ?>" class="regular-text">
                            <p class="description">Full URL of the verification page. You can change this anytime.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>QR Code Base URL</th>
                        <td>
                            <input type="url" name="tm_qr_base_url" value="<?php echo esc_attr($qr_base_url); ?>" class="regular-text">
                            <p class="description">Base URL used in QR codes. Change this to customize QR verification links.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Certificate Code Length</th>
                        <td>
                            <input type="number" name="tm_code_length" value="<?php echo esc_attr($code_length); ?>" min="10" max="30" class="small-text">
                            <p class="description">Length of auto-generated certificate codes (default: 20 characters).</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Admin Notification Email</th>
                        <td>
                            <input type="email" name="tm_admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text">
                            <p class="description">Email to receive new application notifications.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Auto Email Notifications</th>
                        <td>
                            <select name="tm_auto_email">
                                <option value="1" <?php selected($auto_email, '1'); ?>>Enabled</option>
                                <option value="0" <?php selected($auto_email, '0'); ?>>Disabled</option>
                            </select>
                            <p class="description">Send email notifications on status changes.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Rate Limit (per hour)</th>
                        <td>
                            <input type="number" name="tm_rate_limit" value="<?php echo esc_attr($rate_limit); ?>" min="1" max="100" class="small-text">
                            <p class="description">Max applications per IP per hour.</p>
                        </td>
                    </tr>
                    <tr>
                        <th>Copy Protection</th>
                        <td>
                            <select name="tm_copy_protection">
                                <option value="1" <?php selected($copy_protection, '1'); ?>>Enabled</option>
                                <option value="0" <?php selected($copy_protection, '0'); ?>>Disabled</option>
                            </select>
                            <p class="description">Disable right-click and keyboard shortcuts on frontend.</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" class="button button-primary button-large" value="Save Settings">
                </p>
            </form>
        </div>
        <?php
    }
    
    // AJAX: Quick approve/reject
    public function handle_quick_action() {
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
        
        // Send notification email
        if (get_option('tm_cert_auto_email', '1') === '1') {
            $email = get_post_meta($post_id, '_tm_owner_email', true);
            $brand = get_post_meta($post_id, '_tm_brand_name', true);
            
            if ($email) {
                $subject = ($action === 'approve') 
                    ? "Your Trademark Application for '{$brand}' has been Approved!"
                    : "Update on your Trademark Application for '{$brand}'";
                    
                $message = ($action === 'approve')
                    ? "Congratulations! Your trademark application for '{$brand}' has been approved.\n\nYou can verify your certificate at: " . get_option('tm_cert_verify_url')
                    : "We regret to inform you that your trademark application for '{$brand}' has been rejected. Please contact us for more information.";
                    
                wp_mail($email, $subject, $message);
            }
        }
        
        wp_send_json_success(array('status' => $status));
    }
    
    // AJAX: Bulk action
    public function handle_bulk_action() {
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $ids = array_map('intval', $_POST['ids']);
        $action = sanitize_text_field($_POST['tm_action']);
        
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        foreach ($ids as $id) {
            update_post_meta($id, '_tm_status', $status);
            if ($action === 'approve') {
                update_post_meta($id, '_tm_approved_date', current_time('Y-m-d'));
            }
        }
        
        wp_send_json_success(array('count' => count($ids)));
    }
    
    // AJAX: Export CSV
    public function handle_export() {
        check_ajax_referer('tm_cert_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
        }
        
        $certs = get_posts(array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => -1,
        ));
        
        $data = array();
        $data[] = array('Code', 'Brand', 'Owner', 'Email', 'Status', 'Class', 'Application Date', 'Approved Date', 'Expiry Date');
        
        foreach ($certs as $cert) {
            $data[] = array(
                get_post_meta($cert->ID, '_tm_cert_code', true),
                get_post_meta($cert->ID, '_tm_brand_name', true),
                get_post_meta($cert->ID, '_tm_owner_name', true),
                get_post_meta($cert->ID, '_tm_owner_email', true),
                get_post_meta($cert->ID, '_tm_status', true),
                get_post_meta($cert->ID, '_tm_class', true),
                get_post_meta($cert->ID, '_tm_application_date', true),
                get_post_meta($cert->ID, '_tm_approved_date', true),
                get_post_meta($cert->ID, '_tm_expiry_date', true),
            );
        }
        
        wp_send_json_success(array('csv' => $data));
    }
    
    private function count_by_status($status) {
        $args = array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => array(
                array(
                    'key'   => '_tm_status',
                    'value' => $status,
                ),
            ),
        );
        $query = new WP_Query($args);
        return $query->found_posts;
    }
}
