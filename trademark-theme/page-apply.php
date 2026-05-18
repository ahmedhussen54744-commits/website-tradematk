<?php
/**
 * Template Name: Trademark Apply Page
 * 
 * @package Trademark_3D
 */

get_header();

$success = false;
$error = '';

// Handle form submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tm_apply_submit'])) {
    if (!wp_verify_nonce($_POST['tm_apply_nonce'], 'tm_apply_action')) {
        $error = 'Security verification failed.';
    } else {
        // Rate limiting
        $ip = $_SERVER['REMOTE_ADDR'];
        $transient_key = 'tm_rate_' . md5($ip);
        $attempts = get_transient($transient_key);
        
        if ($attempts && $attempts >= 5) {
            $error = 'Too many applications. Please try again later.';
        } else {
            // Sanitize all inputs
            $brand_name = sanitize_text_field($_POST['tm_brand_name']);
            $owner_name = sanitize_text_field($_POST['tm_owner_name']);
            $owner_email = sanitize_email($_POST['tm_owner_email']);
            $owner_phone = sanitize_text_field($_POST['tm_owner_phone']);
            $owner_address = sanitize_textarea_field($_POST['tm_owner_address']);
            $trademark_class = sanitize_text_field($_POST['tm_class']);
            $goods_services = sanitize_textarea_field($_POST['tm_goods_services']);
            $description = sanitize_textarea_field($_POST['tm_description']);
            $owner_type = sanitize_text_field($_POST['tm_owner_type']);
            $country = sanitize_text_field($_POST['tm_country']);
            $state = sanitize_text_field($_POST['tm_state']);
            $city = sanitize_text_field($_POST['tm_city']);
            $postal_code = sanitize_text_field($_POST['tm_postal_code']);
            $company_name = sanitize_text_field($_POST['tm_company_name']);
            $registration_no = sanitize_text_field($_POST['tm_registration_no']);
            
            // Validate required fields
            if (empty($brand_name) || empty($owner_name) || empty($owner_email)) {
                $error = 'Please fill in all required fields.';
            } else {
                // Create certificate post
                $post_data = array(
                    'post_title'  => $brand_name . ' - ' . $owner_name,
                    'post_type'   => 'tm_certificate',
                    'post_status' => 'publish',
                );
                
                $post_id = wp_insert_post($post_data);
                
                if ($post_id && !is_wp_error($post_id)) {
                    // Generate unique 20-char code
                    $cert_code = tm3d_generate_code(20);
                    
                    // Save all meta
                    update_post_meta($post_id, '_tm_cert_code', $cert_code);
                    update_post_meta($post_id, '_tm_brand_name', $brand_name);
                    update_post_meta($post_id, '_tm_owner_name', $owner_name);
                    update_post_meta($post_id, '_tm_owner_email', $owner_email);
                    update_post_meta($post_id, '_tm_owner_phone', $owner_phone);
                    update_post_meta($post_id, '_tm_owner_address', $owner_address);
                    update_post_meta($post_id, '_tm_class', $trademark_class);
                    update_post_meta($post_id, '_tm_goods_services', $goods_services);
                    update_post_meta($post_id, '_tm_description', $description);
                    update_post_meta($post_id, '_tm_owner_type', $owner_type);
                    update_post_meta($post_id, '_tm_country', $country);
                    update_post_meta($post_id, '_tm_state', $state);
                    update_post_meta($post_id, '_tm_city', $city);
                    update_post_meta($post_id, '_tm_postal_code', $postal_code);
                    update_post_meta($post_id, '_tm_company_name', $company_name);
                    update_post_meta($post_id, '_tm_registration_no', $registration_no);
                    update_post_meta($post_id, '_tm_status', 'pending');
                    update_post_meta($post_id, '_tm_application_date', current_time('Y-m-d'));
                    
                    // Handle brand logo upload
                    if (!empty($_FILES['tm_brand_logo']['name'])) {
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        require_once(ABSPATH . 'wp-admin/includes/file.php');
                        require_once(ABSPATH . 'wp-admin/includes/media.php');
                        
                        $logo_id = media_handle_upload('tm_brand_logo', $post_id);
                        if (!is_wp_error($logo_id)) {
                            update_post_meta($post_id, '_tm_brand_logo', wp_get_attachment_url($logo_id));
                            update_post_meta($post_id, '_tm_brand_logo_id', $logo_id);
                        }
                    }
                    
                    // Rate limiting increment
                    $attempts = $attempts ? $attempts + 1 : 1;
                    set_transient($transient_key, $attempts, HOUR_IN_SECONDS);
                    
                    $success = true;
                } else {
                    $error = 'An error occurred. Please try again.';
                }
            }
        }
    }
}

/**
 * Generate secure random code
 */
function tm3d_generate_code($length = 20) {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    
    // Ensure unique
    $existing = get_posts(array(
        'post_type'  => 'tm_certificate',
        'meta_key'   => '_tm_cert_code',
        'meta_value' => $code,
        'posts_per_page' => 1,
    ));
    
    if (!empty($existing)) {
        return tm3d_generate_code($length);
    }
    
    return $code;
}
?>

<main>
    <section class="tm-section" style="padding-top: 120px;">
        <div class="tm-container" style="max-width: 800px;">
            
            <?php if ($success): ?>
            <!-- Success Message -->
            <div class="tm-card" style="text-align: center;">
                <div class="tm-verify-status verified" style="margin-bottom: 20px;">&#10004;</div>
                <h2 style="font-family: var(--font-heading); margin-bottom: 15px;">Application Submitted!</h2>
                <p style="color: var(--text-secondary); margin-bottom: 30px;">Your trademark application has been submitted successfully. Our admin team will review and approve your application soon.</p>
                <p style="color: var(--accent); font-family: var(--font-heading); font-size: 14px;">Application Code: <?php echo esc_html($cert_code); ?></p>
                <p style="color: var(--text-secondary); font-size: 12px; margin-top: 10px;">Please save this code for tracking your application.</p>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="tm-btn tm-btn-outline" style="margin-top: 30px;">Back to Home</a>
            </div>
            
            <?php else: ?>
            <!-- Apply Form -->
            <div class="tm-card">
                <h2 style="font-family: var(--font-heading); text-align: center; margin-bottom: 10px; font-size: 24px;">
                    Trademark Application
                </h2>
                <p style="text-align: center; color: var(--text-secondary); margin-bottom: 40px;">
                    Fill in the details below to apply for trademark certificate registration.
                </p>
                
                <?php if ($error): ?>
                <div style="background: rgba(255,23,68,0.1); border: 1px solid rgba(255,23,68,0.3); border-radius: 12px; padding: 15px; margin-bottom: 25px; text-align: center; color: #ff5252;">
                    <?php echo esc_html($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" id="tmApplyForm">
                    <?php wp_nonce_field('tm_apply_action', 'tm_apply_nonce'); ?>
                    
                    <!-- Owner Type -->
                    <div class="tm-form-group">
                        <label>Owner Type *</label>
                        <select name="tm_owner_type" class="tm-form-control" required>
                            <option value="">Select Owner Type</option>
                            <option value="individual">Individual</option>
                            <option value="company">Company / Organization</option>
                            <option value="partnership">Partnership</option>
                            <option value="trust">Trust / Society</option>
                        </select>
                    </div>
                    
                    <!-- Brand Name -->
                    <div class="tm-form-group">
                        <label>Brand / Trademark Name *</label>
                        <input type="text" name="tm_brand_name" class="tm-form-control" placeholder="Enter your brand name" required>
                    </div>
                    
                    <!-- Owner Name -->
                    <div class="tm-form-group">
                        <label>Owner / Applicant Name *</label>
                        <input type="text" name="tm_owner_name" class="tm-form-control" placeholder="Full legal name" required>
                    </div>
                    
                    <!-- Company Name -->
                    <div class="tm-form-group">
                        <label>Company / Organization Name</label>
                        <input type="text" name="tm_company_name" class="tm-form-control" placeholder="Company name (if applicable)">
                    </div>
                    
                    <!-- Email -->
                    <div class="tm-form-group">
                        <label>Email Address *</label>
                        <input type="email" name="tm_owner_email" class="tm-form-control" placeholder="email@example.com" required>
                    </div>
                    
                    <!-- Phone -->
                    <div class="tm-form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="tm_owner_phone" class="tm-form-control" placeholder="+880-XXXX-XXXXXX">
                    </div>
                    
                    <!-- Address -->
                    <div class="tm-form-group">
                        <label>Address *</label>
                        <textarea name="tm_owner_address" class="tm-form-control" rows="3" placeholder="Full address" required></textarea>
                    </div>
                    
                    <!-- Country, State, City Row -->
                    <div class="tm-grid-2">
                        <div class="tm-form-group">
                            <label>Country *</label>
                            <select name="tm_country" class="tm-form-control" required>
                                <option value="">Select Country</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="India">India</option>
                                <option value="USA">United States</option>
                                <option value="UK">United Kingdom</option>
                                <option value="Canada">Canada</option>
                                <option value="Australia">Australia</option>
                                <option value="UAE">UAE</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="tm-form-group">
                            <label>State / Division</label>
                            <input type="text" name="tm_state" class="tm-form-control" placeholder="State/Division">
                        </div>
                    </div>
                    
                    <div class="tm-grid-2">
                        <div class="tm-form-group">
                            <label>City</label>
                            <input type="text" name="tm_city" class="tm-form-control" placeholder="City">
                        </div>
                        <div class="tm-form-group">
                            <label>Postal Code</label>
                            <input type="text" name="tm_postal_code" class="tm-form-control" placeholder="Postal/ZIP code">
                        </div>
                    </div>
                    
                    <!-- Trademark Class -->
                    <div class="tm-form-group">
                        <label>Trademark Class *</label>
                        <select name="tm_class" class="tm-form-control" required>
                            <option value="">Select Class</option>
                            <option value="Class 1 - Chemicals">Class 1 - Chemicals</option>
                            <option value="Class 2 - Paints">Class 2 - Paints</option>
                            <option value="Class 3 - Cosmetics">Class 3 - Cosmetics & Cleaning</option>
                            <option value="Class 5 - Pharmaceuticals">Class 5 - Pharmaceuticals</option>
                            <option value="Class 9 - Electronics">Class 9 - Electronics & Software</option>
                            <option value="Class 16 - Paper Goods">Class 16 - Paper Goods & Printing</option>
                            <option value="Class 25 - Clothing">Class 25 - Clothing</option>
                            <option value="Class 28 - Games">Class 28 - Games & Sporting</option>
                            <option value="Class 30 - Food">Class 30 - Food Products</option>
                            <option value="Class 35 - Business Services">Class 35 - Advertising & Business</option>
                            <option value="Class 36 - Financial">Class 36 - Financial Services</option>
                            <option value="Class 38 - Telecommunications">Class 38 - Telecommunications</option>
                            <option value="Class 41 - Education">Class 41 - Education & Entertainment</option>
                            <option value="Class 42 - Technology">Class 42 - Technology & Computing</option>
                            <option value="Class 43 - Food Services">Class 43 - Food & Hospitality</option>
                            <option value="Class 45 - Legal">Class 45 - Legal & Security</option>
                        </select>
                    </div>
                    
                    <!-- Registration No (if any) -->
                    <div class="tm-form-group">
                        <label>Existing Registration Number (if any)</label>
                        <input type="text" name="tm_registration_no" class="tm-form-control" placeholder="Previous registration number">
                    </div>
                    
                    <!-- Goods/Services Description -->
                    <div class="tm-form-group">
                        <label>Goods / Services Description *</label>
                        <textarea name="tm_goods_services" class="tm-form-control" rows="4" placeholder="Describe the goods or services covered by this trademark" required></textarea>
                    </div>
                    
                    <!-- Additional Description -->
                    <div class="tm-form-group">
                        <label>Additional Description / Notes</label>
                        <textarea name="tm_description" class="tm-form-control" rows="3" placeholder="Any additional information"></textarea>
                    </div>
                    
                    <!-- Brand Logo Upload -->
                    <div class="tm-form-group">
                        <label>Brand Logo *</label>
                        <div class="tm-file-upload">
                            <input type="file" name="tm_brand_logo" accept="image/png,image/jpeg,image/svg+xml" required>
                            <div>
                                <div style="font-size: 36px; margin-bottom: 10px;">&#128247;</div>
                                <p style="color: var(--text-secondary); font-size: 14px;">Upload your brand logo</p>
                                <p style="color: rgba(255,255,255,0.3); font-size: 12px;">PNG, JPG or SVG (Max 5MB)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms -->
                    <div class="tm-form-group" style="display: flex; align-items: flex-start; gap: 12px;">
                        <input type="checkbox" id="tmTerms" required style="margin-top: 4px; accent-color: var(--accent);">
                        <label for="tmTerms" style="font-size: 13px; color: var(--text-secondary); text-transform: none; letter-spacing: 0;">
                            I confirm that all information provided is accurate and I have the right to register this trademark. I agree to the terms and conditions.
                        </label>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" name="tm_apply_submit" class="tm-btn tm-btn-primary" style="width: 100%; justify-content: center; margin-top: 20px;">
                        Submit Application
                    </button>
                </form>
            </div>
            <?php endif; ?>
            
        </div>
    </section>
</main>

<?php get_footer(); ?>
