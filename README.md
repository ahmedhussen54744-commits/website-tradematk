# Trademark Certificate Website

Advanced 3D Trademark Certificate Registration & Verification System built for WordPress.

**Established: 2009**

---

## Features

### Theme (trademark-theme.zip)
- **Advanced 3D Design** - Three.js animated background with floating geometric shapes
- **GSAP Scroll Animations** - Smooth scroll-triggered animations
- **Particle Effects** - Dynamic floating particles
- **Glass Morphism UI** - Modern glassmorphism card design
- **Holographic Text** - Animated gradient text effects
- **Responsive** - Fully optimized for Mobile + Desktop
- **Google Fonts** - Inter, Orbitron, Noto Sans Bengali (supports Bangla)
- **Copyright Protection** - Right-click disabled, keyboard shortcuts blocked
- **Security Headers** - X-Frame-Options, CSP, XSS Protection
- **Page Templates** - Verify page, Apply page

### Plugin (trademark-plugin.zip)
- **Application System** - Full trademark application form with all fields
- **Admin Approval** - Approve/Reject applications from dashboard
- **PDF + JPG Upload** - Admin can upload certificate files (both formats)
- **Brand Logo** - Applicants upload logo, displayed on verify page
- **Date Management** - Registration Date, Approved Date, Expiry Date, Application Date (all editable by admin)
- **20-Character Codes** - Secure random POST-based certificate codes
- **QR Code** - Auto-generated QR code for each certificate (customizable URL)
- **Changeable Verify URL** - Admin can change the verification page link anytime
- **Email Notifications** - Auto emails on approval/rejection + admin notifications
- **Dashboard** - Stats cards, recent applications, quick actions
- **Bulk Actions** - Approve/reject multiple applications at once
- **CSV Export** - Export all certificate data
- **Admin Bar** - Pending count shown in WordPress admin bar
- **Dashboard Widget** - Quick stats on WP Dashboard
- **Rate Limiting** - Prevents spam applications (configurable per hour)
- **Brute Force Protection** - Login attempt limiting
- **REST API Restriction** - User endpoints hidden for non-logged users
- **No GET Requests** - All verification uses POST method only

---

## Installation

### Theme
1. Go to WordPress Admin → Appearance → Themes → Add New → Upload Theme
2. Upload `trademark-theme.zip`
3. Activate the theme

### Plugin
1. Go to WordPress Admin → Plugins → Add New → Upload Plugin
2. Upload `trademark-plugin.zip`
3. Activate the plugin
4. Pages "Verify" and "Apply" will be auto-created

---

## Admin Usage

### Settings (TM Certificates → Settings)
- **Verify Page URL** - Change the verification page link
- **QR Code Base URL** - Customize QR code destination
- **Code Length** - Default 20 characters
- **Email Notifications** - Enable/disable auto emails
- **Rate Limit** - Max applications per IP per hour
- **Copy Protection** - Toggle right-click protection

### Managing Certificates
1. New applications appear as "Pending"
2. Click "Edit" to view full details
3. Upload certificate JPG image + PDF file
4. Set all dates (Registration, Approved, Expiry)
5. Change status to "Approved"
6. Applicant receives email notification

### Verify Page
- Users enter 20-character code via POST form
- Shows: Brand logo (highlighted), Brand name, Owner name, Certificate image, all dates
- QR code displayed for sharing

---

## Security Features
- POST-only verification (no GET parameter exposure)
- WordPress nonce verification on all forms
- Rate limiting on applications and verification
- Login brute force protection
- File upload validation (malicious content check)
- REST API user endpoint restriction
- Username enumeration prevention
- XML-RPC disabled
- Security headers (CSP, X-Frame-Options, XSS Protection)
- Input sanitization on all fields
- Copy protection (configurable)

---

## Tech Stack
- WordPress 5.0+
- PHP 7.4+
- Three.js (3D background)
- GSAP (animations)
- QRCode.js (QR generation)
- Google Fonts (Inter, Orbitron, Noto Sans Bengali)

---

© 2009 - 2026 Trademark Certificate Authority. All Rights Reserved.
