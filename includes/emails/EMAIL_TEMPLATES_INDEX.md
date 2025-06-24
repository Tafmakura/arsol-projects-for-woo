# Email Templates Index

Quick reference for all email templates in the Arsol Projects for Woo plugin.

## 📁 Template Locations

All templates are located in: `includes/emails/templates/`

## 📧 Email Class → Template Mapping

| Email Class | HTML Template | Plain Text Template | Recipients |
|-------------|---------------|-------------------|------------|
| `New_Request_Email` | `arsol_new_request.php` | `plain/arsol_new_request.php` | Customer + Admin |
| `Request_Status_Email` | `arsol_request_status.php` | `plain/arsol_request_status.php` | Customer + Admin |
| `Proposal_Processing_Email` | `arsol_proposal_processing.php` | `plain/arsol_proposal_processing.php` | Customer + Lead + Admin |
| `Proposal_Ready_Email` | `arsol_proposal_ready.php` | `plain/arsol_proposal_ready.php` | Customer + Admin |
| `Proposal_Decision_Email` | `admin-proposal-decision.php` | N/A | Lead + Admin |
| `Project_Creation_Email` | `arsol_project_creation.php` | `plain/arsol_project_creation.php` | Customer + Lead + Admin |
| `Project_Status_Email` | `arsol_project_status.php` | `plain/arsol_project_status.php` | Customer + Lead + Admin |

## 👥 Role-Specific Templates

### Customer Templates (Portal-focused)
- `arsol_new_request.php` - Request confirmation with portal link
- `arsol_request_status.php` - Status updates with next steps
- `arsol_proposal_processing.php` - Work started notification
- `arsol_proposal_ready.php` - Action required: review proposal
- `arsol_project_creation.php` - Action required: complete payment
- `arsol_project_status.php` - Project progress updates

### Admin Templates (Management-focused)
- `admin-arsol_new_request.php` - New request requiring review
- `admin-proposal-decision.php` - Customer decision notifications

### Project Lead Templates (Assignment-focused)
- `lead-proposal-processing.php` - New assignment notification

## 🎨 Template Features

### Color Schemes by Type
- **🔧 Processing**: Blue background (#cce5ff)
- **🎉 Success**: Green background (#d4edda)  
- **⏰ Action Required**: Yellow background (#fff3cd)
- **⚠️ Error/Warning**: Red background (#f8d7da)

### Common Elements
All templates include:
- ✅ Status-appropriate icons
- ✅ Color-coded design
- ✅ Clear call-to-action buttons
- ✅ Portal/admin links
- ✅ Contact information
- ✅ Responsive design

## 🔄 Workflow Email Flow

### Request Workflow
1. **New Request** → `arsol_new_request.php`
2. **Status: Under Review** → `arsol_request_status.php`
3. **Status: On Hold** → `arsol_request_status.php`
4. **Status: Approved** → `arsol_request_status.php`

### Proposal Workflow  
5. **Processing Started** → `arsol_proposal_processing.php`
6. **Ready for Review** → `arsol_proposal_ready.php`
7. **Customer Decision** → `admin-proposal-decision.php`

### Project Workflow
8. **Project Created** → `arsol_project_creation.php`
9. **Status Updates** → `arsol_project_status.php`

## 📱 Template Variables Reference

### Universal Variables
```php
$email_heading     // Email heading text
$status_icon       // Emoji for email type
$color_scheme      // Array: background, text, cta colors
$portal_url        // Customer portal link
$admin_url         // Admin edit link
```

### User Objects
```php
$customer          // WP_User - Customer
$project_lead      // WP_User - Assigned project lead  
```

### Content Objects
```php
$request           // WP_Post - Request object
$proposal          // WP_Post - Proposal object
$project           // WP_Post - Project object
$order             // WC_Order - WooCommerce order
```

### Status Variables
```php
$old_status        // Previous status
$new_status        // Current status
$status_label      // Human-readable status
```

## 🛠️ Quick Template Edits

### Change Email Heading
Edit the `<h2>` tag in any template:
```php
<h2 style="color: <?php echo esc_attr($color_scheme['text']); ?>;">
    <?php echo $status_icon; ?> Your Custom Heading
</h2>
```

### Modify Call-to-Action Button
Edit the button link and text:
```php
<a href="<?php echo esc_url($portal_url); ?>" 
   style="background-color: <?php echo esc_attr($color_scheme['cta']); ?>; color: white; padding: 12px 25px;">
    Your Custom Button Text
</a>
```

### Add Custom Content Section
Insert between existing sections:
```php
<div style="background-color: #f8f9fa; padding: 15px; margin: 15px 0;">
    <h4>Custom Section</h4>
    <p>Your custom content here...</p>
</div>
```

## ✅ Template Checklist

When creating/editing templates, ensure:
- [ ] Mobile-responsive design
- [ ] Clear call-to-action
- [ ] Proper variable escaping (`esc_html`, `esc_url`, `esc_attr`)
- [ ] Consistent color scheme
- [ ] Status-appropriate messaging
- [ ] Portal/admin links work
- [ ] Plain text version exists
- [ ] Email client compatibility tested

## 📊 Template Statistics

- **Total Templates**: 12 files
- **HTML Templates**: 9 files
- **Plain Text Templates**: 4 files  
- **Admin Templates**: 2 files
- **Customer Templates**: 6 files
- **Project Lead Templates**: 1 file
- **Total Size**: ~45KB
