<?php
/**
 * Template Name: Trademark Verify Page
 * 
 * @package Trademark_3D
 */

get_header();

// Get verification code from POST only (secure)
$cert_code = '';
$certificate = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tm_verify_code'])) {
    if (!wp_verify_nonce($_POST['tm_verify_nonce'], 'tm_verify_action')) {
        $error = 'Security verification failed. Please try again.';
    } else {
        $cert_code = sanitize_text_field($_POST['tm_verify_code']);
        
        // Query certificate by code
        $args = array(
            'post_type'      => 'tm_certificate',
            'posts_per_page' => 1,
            'meta_query'     => array(
                array(
                    'key'   => '_tm_cert_code',
                    'value' => $cert_code,
                ),
            ),
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            $query->the_post();
            $certificate = array(
                'id'                => get_the_ID(),
                'brand_name'        => get_post_meta(get_the_ID(), '_tm_brand_name', true),
                'owner_name'        => get_post_meta(get_the_ID(), '_tm_owner_name', true),
                'status'            => get_post_meta(get_the_ID(), '_tm_status', true),
                'registration_date' => get_post_meta(get_the_ID(), '_tm_registration_date', true),
                'approved_date'     => get_post_meta(get_the_ID(), '_tm_approved_date', true),
                'expiry_date'       => get_post_meta(get_the_ID(), '_tm_expiry_date', true),
                'application_date'  => get_post_meta(get_the_ID(), '_tm_application_date', true),
                'cert_image'        => get_post_meta(get_the_ID(), '_tm_cert_image', true),
                'brand_logo'        => get_post_meta(get_the_ID(), '_tm_brand_logo', true),
                'trademark_class'   => get_post_meta(get_the_ID(), '_tm_class', true),
                'cert_code'         => $cert_code,
                'application_no'    => get_post_meta(get_the_ID(), '_tm_application_no', true),
            );
            wp_reset_postdata();
        } else {
            $error = 'No certificate found with this code.';
        }
    }
}
?>

<main>
    <div class="tm-verify-container">
        <?php if (!$certificate && !isset($error)): ?>
        <!-- Verify Search Form -->
        <div class="tm-verify-card">
            <div class="tm-verify-status verified">&#128270;</div>
            <h2 style="font-family: var(--font-heading); margin-bottom: 10px; font-size: 24px;">Verify Certificate</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Enter your 20-character certificate code to verify authenticity.</p>
            
            <form method="POST" action="" id="tmVerifyForm">
                <?php wp_nonce_field('tm_verify_action', 'tm_verify_nonce'); ?>
                <div class="tm-form-group">
                    <input type="text" name="tm_verify_code" class="tm-form-control" 
                           placeholder="Enter Certificate Code" 
                           maxlength="20" minlength="20" required
                           style="text-align: center; font-family: var(--font-heading); font-size: 18px; letter-spacing: 2px;">
                </div>
                <button type="submit" class="tm-btn tm-btn-primary" style="width: 100%;">
                    Verify Now
                </button>
            </form>
            
            <div id="tmQrScanner" style="margin-top: 30px;">
                <p style="color: var(--text-secondary); font-size: 13px;">Or scan QR code on your certificate</p>
            </div>
        </div>
        
        <?php elseif (isset($error)): ?>
        <!-- Error State -->
        <div class="tm-verify-card">
            <div class="tm-verify-status rejected">&#10008;</div>
            <h2 style="font-family: var(--font-heading); margin-bottom: 10px; font-size: 24px; color: #ff5252;">Verification Failed</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;"><?php echo esc_html($error); ?></p>
            <a href="<?php echo esc_url(get_permalink()); ?>" class="tm-btn tm-btn-outline">Try Again</a>
        </div>
        
        <?php else: ?>
        <!-- Certificate Found -->
        <div class="tm-verify-card">
            <?php 
            $status = $certificate['status'];
            $status_icon = '&#10004;';
            $status_class = 'verified';
            $status_text = 'Verified & Active';
            
            if ($status === 'pending') {
                $status_icon = '&#9203;';
                $status_class = 'pending';
                $status_text = 'Pending Approval';
            } elseif ($status === 'rejected') {
                $status_icon = '&#10008;';
                $status_class = 'rejected';
                $status_text = 'Rejected';
            } elseif ($status === 'expired') {
                $status_icon = '&#9888;';
                $status_class = 'rejected';
                $status_text = 'Expired';
            }
            ?>
            
            <div class="tm-verify-status <?php echo esc_attr($status_class); ?>"><?php echo $status_icon; ?></div>
            <h2 style="font-family: var(--font-heading); margin-bottom: 5px; font-size: 20px;"><?php echo esc_html($status_text); ?></h2>
            <p style="color: var(--text-secondary); font-size: 13px; margin-bottom: 25px;">Certificate Code: <?php echo esc_html($certificate['cert_code']); ?></p>
            
            <!-- Brand Logo Highlight -->
            <?php if (!empty($certificate['brand_logo'])): ?>
            <img src="<?php echo esc_url($certificate['brand_logo']); ?>" 
                 alt="Brand Logo" class="tm-verify-logo">
            <?php endif; ?>
            
            <!-- Brand Name & Owner -->
            <div class="tm-verify-brand"><?php echo esc_html($certificate['brand_name']); ?></div>
            <div class="tm-verify-owner">Owner: <?php echo esc_html($certificate['owner_name']); ?></div>
            
            <!-- Certificate Image (JPG uploaded by admin) -->
            <?php if (!empty($certificate['cert_image'])): ?>
            <img src="<?php echo esc_url($certificate['cert_image']); ?>" 
                 alt="Certificate" class="tm-verify-cert-img">
            <?php endif; ?>
            
            <!-- Certificate Details -->
            <div class="tm-verify-details">
                <div class="tm-verify-row">
                    <span class="tm-verify-label">Application No</span>
                    <span class="tm-verify-value"><?php echo esc_html($certificate['application_no']); ?></span>
                </div>
                <div class="tm-verify-row">
                    <span class="tm-verify-label">Trademark Class</span>
                    <span class="tm-verify-value"><?php echo esc_html($certificate['trademark_class']); ?></span>
                </div>
                <div class="tm-verify-row">
                    <span class="tm-verify-label">Application Date</span>
                    <span class="tm-verify-value"><?php echo esc_html($certificate['application_date']); ?></span>
                </div>
                <div class="tm-verify-row">
                    <span class="tm-verify-label">Registration Date</span>
                    <span class="tm-verify-value"><?php echo esc_html($certificate['registration_date']); ?></span>
                </div>
                <div class="tm-verify-row">
                    <span class="tm-verify-label">Approved Date</span>
                    <span class="tm-verify-value"><?php echo esc_html($certificate['approved_date']); ?></span>
                </div>
                <div class="tm-verify-row">
                    <span class="tm-verify-label">Expiry Date</span>
                    <span class="tm-verify-value"><?php echo esc_html($certificate['expiry_date']); ?></span>
                </div>
            </div>
            
            <!-- QR Code -->
            <div class="tm-qr-code" id="tmVerifyQR"></div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof QRCode !== 'undefined') {
                    new QRCode(document.getElementById('tmVerifyQR'), {
                        text: '<?php echo esc_js(get_permalink() . '?code=' . $certificate['cert_code']); ?>',
                        width: 150,
                        height: 150,
                        colorDark: '#1a237e',
                        colorLight: '#ffffff',
                    });
                }
            });
            </script>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?>
