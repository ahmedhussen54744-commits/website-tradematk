<?php
/**
 * Admin Metabox for Certificate Details
 */

if (!defined('ABSPATH')) exit;

class TM_Cert_Admin_Metabox {
    
    private static $instance = null;
    
    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('add_meta_boxes', array($this, 'add_metaboxes'));
        add_action('save_post_tm_certificate', array($this, 'save_metabox'), 10, 2);
    }
    
    public function add_metaboxes() {
        add_meta_box('tm_cert_details', 'Certificate Details', array($this, 'render_details'), 'tm_certificate', 'normal', 'high');
        add_meta_box('tm_cert_dates', 'Important Dates', array($this, 'render_dates'), 'tm_certificate', 'normal', 'high');
        add_meta_box('tm_cert_files', 'Certificate Files (PDF & JPG)', array($this, 'render_files'), 'tm_certificate', 'normal', 'high');
        add_meta_box('tm_cert_status_box', 'Status & Actions', array($this, 'render_status'), 'tm_certificate', 'side', 'high');
        add_meta_box('tm_cert_qr', 'QR Code & Verify Link', array($this, 'render_qr'), 'tm_certificate', 'side', 'default');
    }
    
    public function render_details($post) {
        wp_nonce_field('tm_cert_save', 'tm_cert_nonce');
        
        $fields = array(
            '_tm_cert_code'      => array('label' => 'Certificate Code', 'type' => 'text', 'readonly' => true),
            '_tm_brand_name'     => array('label' => 'Brand Name', 'type' => 'text'),
            '_tm_owner_name'     => array('label' => 'Owner Name', 'type' => 'text'),
            '_tm_owner_email'    => array('label' => 'Email', 'type' => 'email'),
            '_tm_owner_phone'    => array('label' => 'Phone', 'type' => 'text'),
            '_tm_owner_address'  => array('label' => 'Address', 'type' => 'textarea'),
            '_tm_owner_type'     => array('label' => 'Owner Type', 'type' => 'select', 'options' => array('individual' => 'Individual', 'company' => 'Company', 'partnership' => 'Partnership', 'trust' => 'Trust/Society')),
            '_tm_company_name'   => array('label' => 'Company Name', 'type' => 'text'),
            '_tm_class'          => array('label' => 'Trademark Class', 'type' => 'text'),
            '_tm_goods_services' => array('label' => 'Goods/Services', 'type' => 'textarea'),
            '_tm_description'    => array('label' => 'Description', 'type' => 'textarea'),
            '_tm_country'        => array('label' => 'Country', 'type' => 'text'),
            '_tm_state'          => array('label' => 'State', 'type' => 'text'),
            '_tm_city'           => array('label' => 'City', 'type' => 'text'),
            '_tm_postal_code'    => array('label' => 'Postal Code', 'type' => 'text'),
            '_tm_application_no' => array('label' => 'Application Number', 'type' => 'text'),
            '_tm_registration_no'=> array('label' => 'Registration Number', 'type' => 'text'),
        );
        
        echo '<table class="form-table tm-metabox-table">';
        foreach ($fields as $key => $field) {
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr><th><label>' . esc_html($field['label']) . '</label></th><td>';
            
            switch ($field['type']) {
                case 'textarea':
                    echo '<textarea name="' . esc_attr($key) . '" class="large-text" rows="3">' . esc_textarea($value) . '</textarea>';
                    break;
                case 'select':
                    echo '<select name="' . esc_attr($key) . '" class="regular-text">';
                    foreach ($field['options'] as $opt_val => $opt_label) {
                        echo '<option value="' . esc_attr($opt_val) . '" ' . selected($value, $opt_val, false) . '>' . esc_html($opt_label) . '</option>';
                    }
                    echo '</select>';
                    break;
                default:
                    $readonly = !empty($field['readonly']) ? 'readonly' : '';
                    echo '<input type="' . esc_attr($field['type']) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text" ' . $readonly . '>';
            }
            
            echo '</td></tr>';
        }
        echo '</table>';
    }
    
    public function render_dates($post) {
        $dates = array(
            '_tm_application_date'  => 'Application Date',
            '_tm_registration_date' => 'Registration Date',
            '_tm_approved_date'     => 'Approved Date',
            '_tm_expiry_date'       => 'Expiry Date',
        );
        
        echo '<table class="form-table">';
        foreach ($dates as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);
            echo '<tr><th><label>' . esc_html($label) . '</label></th>';
            echo '<td><input type="date" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" class="regular-text"></td></tr>';
        }
        echo '</table>';
        echo '<p class="description">All dates can be edited by admin. Format: YYYY-MM-DD</p>';
    }
    
    public function render_files($post) {
        $cert_image = get_post_meta($post->ID, '_tm_cert_image', true);
        $cert_pdf = get_post_meta($post->ID, '_tm_cert_pdf', true);
        $brand_logo = get_post_meta($post->ID, '_tm_brand_logo', true);
        ?>
        <div class="tm-files-section">
            <!-- Certificate Image (JPG) -->
            <div class="tm-file-field">
                <h4>Certificate Image (JPG)</h4>
                <div class="tm-file-preview">
                    <?php if ($cert_image): ?>
                    <img src="<?php echo esc_url($cert_image); ?>" style="max-width:300px;border:1px solid #ddd;padding:5px;margin-bottom:10px;display:block;">
                    <?php endif; ?>
                </div>
                <input type="hidden" name="_tm_cert_image" id="tm_cert_image" value="<?php echo esc_attr($cert_image); ?>">
                <button type="button" class="button tm-upload-btn" data-target="tm_cert_image" data-preview="1">Upload JPG Image</button>
                <?php if ($cert_image): ?>
                <button type="button" class="button tm-remove-btn" data-target="tm_cert_image">Remove</button>
                <?php endif; ?>
                <p class="description">Upload the certificate image (JPG/PNG) that will be shown on verify page.</p>
            </div>
            
            <hr>
            
            <!-- Certificate PDF -->
            <div class="tm-file-field" style="margin-top:20px;">
                <h4>Certificate PDF</h4>
                <?php if ($cert_pdf): ?>
                <p><a href="<?php echo esc_url($cert_pdf); ?>" target="_blank" class="button button-small">View PDF</a></p>
                <?php endif; ?>
                <input type="hidden" name="_tm_cert_pdf" id="tm_cert_pdf" value="<?php echo esc_attr($cert_pdf); ?>">
                <button type="button" class="button tm-upload-btn" data-target="tm_cert_pdf" data-preview="0">Upload PDF</button>
                <?php if ($cert_pdf): ?>
                <button type="button" class="button tm-remove-btn" data-target="tm_cert_pdf">Remove</button>
                <?php endif; ?>
                <p class="description">Upload the certificate PDF file.</p>
            </div>
            
            <hr>
            
            <!-- Brand Logo -->
            <div class="tm-file-field" style="margin-top:20px;">
                <h4>Brand Logo</h4>
                <div class="tm-file-preview">
                    <?php if ($brand_logo): ?>
                    <img src="<?php echo esc_url($brand_logo); ?>" style="max-width:150px;border:1px solid #ddd;padding:5px;margin-bottom:10px;display:block;">
                    <?php endif; ?>
                </div>
                <input type="hidden" name="_tm_brand_logo" id="tm_brand_logo" value="<?php echo esc_attr($brand_logo); ?>">
                <button type="button" class="button tm-upload-btn" data-target="tm_brand_logo" data-preview="1">Upload Logo</button>
                <?php if ($brand_logo): ?>
                <button type="button" class="button tm-remove-btn" data-target="tm_brand_logo">Remove</button>
                <?php endif; ?>
                <p class="description">Brand logo uploaded during application (or admin can change it).</p>
            </div>
        </div>
        <?php
    }
    
    public function render_status($post) {
        $status = get_post_meta($post->ID, '_tm_status', true) ?: 'pending';
        $statuses = array(
            'pending'  => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired'  => 'Expired',
        );
        ?>
        <div class="tm-status-section">
            <label><strong>Current Status:</strong></label>
            <select name="_tm_status" style="width:100%;margin-top:8px;padding:8px;">
                <?php foreach ($statuses as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($status, $key); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            
            <div style="margin-top:15px;padding:10px;background:#f9f9f9;border-radius:4px;">
                <small><strong>Code:</strong> <?php echo esc_html(get_post_meta($post->ID, '_tm_cert_code', true)); ?></small>
            </div>
        </div>
        <?php
    }
    
    public function render_qr($post) {
        $code = get_post_meta($post->ID, '_tm_cert_code', true);
        $verify_url = get_option('tm_cert_verify_url', home_url('/verify'));
        $qr_base = get_option('tm_cert_qr_base_url', home_url('/verify'));
        ?>
        <div class="tm-qr-section">
            <p><strong>Verify URL:</strong></p>
            <input type="text" value="<?php echo esc_attr($verify_url); ?>" class="widefat" readonly style="margin-bottom:10px;font-size:11px;">
            
            <p><strong>QR Code Link:</strong></p>
            <input type="text" name="_tm_qr_custom_url" value="<?php echo esc_attr(get_post_meta($post->ID, '_tm_qr_custom_url', true)); ?>" class="widefat" placeholder="Custom QR URL (leave empty for default)" style="margin-bottom:10px;font-size:11px;">
            <p class="description" style="font-size:11px;">Leave empty to use: <?php echo esc_html($qr_base . '?code=' . $code); ?></p>
            
            <?php if ($code): ?>
            <div id="tmAdminQR" style="text-align:center;margin-top:15px;padding:10px;background:#fff;border-radius:8px;"></div>
            <script>
            jQuery(document).ready(function() {
                if (typeof QRCode !== 'undefined') {
                    new QRCode(document.getElementById('tmAdminQR'), {
                        text: '<?php echo esc_js($qr_base . '?code=' . $code); ?>',
                        width: 120,
                        height: 120,
                    });
                }
            });
            </script>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function save_metabox($post_id, $post) {
        if (!isset($_POST['tm_cert_nonce']) || !wp_verify_nonce($_POST['tm_cert_nonce'], 'tm_cert_save')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        
        // Save all meta fields
        $text_fields = array(
            '_tm_brand_name', '_tm_owner_name', '_tm_owner_email', '_tm_owner_phone',
            '_tm_owner_type', '_tm_company_name', '_tm_class', '_tm_country',
            '_tm_state', '_tm_city', '_tm_postal_code', '_tm_application_no',
            '_tm_registration_no', '_tm_status', '_tm_cert_image', '_tm_cert_pdf',
            '_tm_brand_logo', '_tm_qr_custom_url',
        );
        
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        // Textarea fields
        $textarea_fields = array('_tm_owner_address', '_tm_goods_services', '_tm_description');
        foreach ($textarea_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_textarea_field($_POST[$field]));
            }
        }
        
        // Date fields
        $date_fields = array('_tm_application_date', '_tm_registration_date', '_tm_approved_date', '_tm_expiry_date');
        foreach ($date_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
}
