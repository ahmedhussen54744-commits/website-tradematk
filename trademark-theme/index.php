<?php get_header(); ?>

<main>
    <!-- Hero Section -->
    <section class="tm-hero">
        <div class="tm-hero-content">
            <div class="tm-3d-badge">&#9733; Trusted Since 2009</div>
            <h1>Trademark Certificate Authority</h1>
            <p>Secure your brand identity with our official trademark registration and verification system. Advanced 3D-secured digital certificates with instant verification.</p>
            <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo esc_url(home_url('/apply')); ?>" class="tm-btn tm-btn-primary">Apply Now</a>
                <a href="<?php echo esc_url(home_url('/verify')); ?>" class="tm-btn tm-btn-outline">Verify Certificate</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="tm-section">
        <div class="tm-container">
            <h2 class="tm-section-title">Why Choose Us</h2>
            <div class="tm-grid-3">
                <div class="tm-card">
                    <div style="font-size: 48px; margin-bottom: 20px;">&#128274;</div>
                    <h3 style="font-family: var(--font-heading); margin-bottom: 15px; font-size: 18px;">Maximum Security</h3>
                    <p style="color: var(--text-secondary); font-size: 14px;">256-bit encryption with advanced security protocols protecting every certificate issued.</p>
                </div>
                <div class="tm-card">
                    <div style="font-size: 48px; margin-bottom: 20px;">&#9889;</div>
                    <h3 style="font-family: var(--font-heading); margin-bottom: 15px; font-size: 18px;">Instant Verification</h3>
                    <p style="color: var(--text-secondary); font-size: 14px;">QR code and unique link-based instant verification system for your certificates.</p>
                </div>
                <div class="tm-card">
                    <div style="font-size: 48px; margin-bottom: 20px;">&#127760;</div>
                    <h3 style="font-family: var(--font-heading); margin-bottom: 15px; font-size: 18px;">Global Recognition</h3>
                    <p style="color: var(--text-secondary); font-size: 14px;">Internationally recognized trademark certificates accepted worldwide.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="tm-section" style="background: rgba(255,215,0,0.02);">
        <div class="tm-container">
            <div class="tm-grid-3" style="text-align: center;">
                <div class="tm-card">
                    <div style="font-family: var(--font-heading); font-size: 48px; color: var(--accent); margin-bottom: 10px;">15K+</div>
                    <p style="color: var(--text-secondary);">Certificates Issued</p>
                </div>
                <div class="tm-card">
                    <div style="font-family: var(--font-heading); font-size: 48px; color: var(--accent); margin-bottom: 10px;">50+</div>
                    <p style="color: var(--text-secondary);">Countries Covered</p>
                </div>
                <div class="tm-card">
                    <div style="font-family: var(--font-heading); font-size: 48px; color: var(--accent); margin-bottom: 10px;">99.9%</div>
                    <p style="color: var(--text-secondary);">Uptime Guaranteed</p>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
