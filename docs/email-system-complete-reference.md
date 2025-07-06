# Email System - Complete Reference

## Overview

The **Arsol Projects for WooCommerce** email system provides comprehensive email notifications for all project lifecycle events. Built on WooCommerce's email framework, it offers advanced targeting, role-based delivery, and dynamic content generation.

## Key Features

- ✅ **10 Email Types** covering all project lifecycle events
- ✅ **Role-Based Targeting** with dynamic recipient detection
- ✅ **Template Override System** for complete customization
- ✅ **Dynamic Content** with shortcode support
- ✅ **Admin Controls** for enable/disable per email type
- ✅ **WooCommerce Integration** with standard email settings

---

## Email Types

### 1. Request Stage Changes
**Class:** `WC_Email_Request_Stage`
**Triggers:** When project request stage changes
**Recipients:** Request author, project managers
**Template:** `email-request-stage.php`

### 2. New Request Notifications
**Class:** `WC_Email_New_Request`
**Triggers:** When new project request is created
**Recipients:** Request author (confirmation)
**Template:** `email-new-request.php`

### 3. Admin New Request
**Class:** `WC_Email_Admin_New_Request`
**Triggers:** When new project request is created
**Recipients:** Site administrators, project managers
**Template:** `email-admin-new-request.php`

### 4. Proposal Processing
**Class:** `WC_Email_Proposal_Processing`
**Triggers:** When proposal moves to processing stage
**Recipients:** Project managers
**Template:** `email-proposal-processing.php`

### 5. Proposal Ready
**Class:** `WC_Email_Proposal_Ready`
**Triggers:** When proposal is ready for client review
**Recipients:** Project owner, proposal author
**Template:** `email-proposal-ready.php`

### 6. Proposal Decision
**Class:** `WC_Email_Proposal_Decision`
**Triggers:** When proposal is approved/rejected
**Recipients:** Project managers, proposal author
**Template:** `email-proposal-decision.php`

### 7. Project Creation
**Class:** `WC_Email_Project_Creation`
**Triggers:** When new project is created
**Recipients:** Project owner, project managers
**Template:** `email-project-creation.php`

### 8. Project Stage Changes
**Class:** `WC_Email_Project_Stage`
**Triggers:** When project stage changes
**Recipients:** Project owner, project managers
**Template:** `email-project-stage.php`

### 9. Project Completion
**Class:** `WC_Email_Project_Completion`
**Triggers:** When project is marked as completed
**Recipients:** Project owner, project managers
**Template:** `email-project-completion.php`

### 10. Admin New Project
**Class:** `WC_Email_Admin_New_Project`
**Triggers:** When new project is created
**Recipients:** Site administrators
**Template:** `email-admin-new-project.php`

---

## Role-Based Targeting

### Dynamic Recipient Detection

The system automatically determines recipients based on:
- **User Roles** (project managers, administrators)
- **Project Ownership** (project authors, assigned users)
- **Stage-Specific Rules** (different recipients for different stages)

### Target Roles

#### Project Managers
- Users with `arsol_pfw_manage` capability
- Configurable via Settings → General → Project Manager Permissions
- Default: Administrator, Shop Manager

#### Project Owners
- Original project author
- Users assigned to specific projects
- WooCommerce customers with project access

#### Site Administrators
- Users with `manage_options` capability
- Receive system-wide notifications
- Can override all email settings

---

## Template System

### Template Locations

```
your-theme/arsol-projects-for-woo/emails/
├── email-request-stage.php
├── email-new-request.php
├── email-admin-new-request.php
├── email-proposal-processing.php
├── email-proposal-ready.php
├── email-proposal-decision.php
├── email-project-creation.php
├── email-project-stage.php
├── email-project-completion.php
└── email-admin-new-project.php
```

### Template Variables

All email templates receive these variables:

```php
// Core Entity Data
$project_id     // Project ID (if applicable)
$proposal_id    // Proposal ID (if applicable)
$request_id     // Request ID (if applicable)

// Stage Information
$old_stage      // Previous stage
$new_stage      // New stage
$stage_name     // Human-readable stage name

// User Data
$user_id        // Recipient user ID
$user_email     // Recipient email
$user_name      // Recipient display name

// Email Settings
$email_heading  // Email heading text
$email_title    // Email subject line
$sent_to_admin  // Boolean: is admin email
```

### Template Structure

```php
<?php
// Email header
do_action('woocommerce_email_header', $email_heading, $email);

// Email content
echo '<h2>' . esc_html($email_title) . '</h2>';
echo '<p>' . sprintf(__('Hello %s,', 'arsol-pfw'), esc_html($user_name)) . '</p>';

// Entity-specific content
if ($project_id) {
    echo '<p>' . sprintf(__('Project: %s', 'arsol-pfw'), get_the_title($project_id)) . '</p>';
}

// Stage change information
if ($old_stage && $new_stage) {
    echo '<p>' . sprintf(__('Stage changed from %s to %s', 'arsol-pfw'), $old_stage, $new_stage) . '</p>';
}

// Email footer
do_action('woocommerce_email_footer', $email);
?>
```

---

## Email Configuration

### Admin Settings

Navigate to **WooCommerce → Settings → Emails** to configure:

#### Per-Email Settings
- **Enable/Disable** each email type
- **Subject Line** customization
- **Heading** text modification
- **Additional Content** sections

#### Global Settings
- **From Name** and **From Email**
- **Email Template** selection
- **Header Image** configuration

### Programmatic Configuration

```php
// Enable/disable specific email
add_filter('woocommerce_email_enabled_email_request_stage', '__return_false');

// Modify email subject
add_filter('woocommerce_email_subject_email_request_stage', function($subject, $email) {
    return 'Custom: ' . $subject;
}, 10, 2);

// Add custom recipients
add_filter('woocommerce_email_recipient_email_request_stage', function($recipients, $email) {
    return $recipients . ',custom@example.com';
}, 10, 2);
```

---

## Shortcode Support

### Available Shortcodes

All email templates support these shortcodes:

#### Entity Information
```
[arsol_project_title]      // Project title
[arsol_project_status]     // Project status
[arsol_project_stage]      // Project stage
[arsol_proposal_title]     // Proposal title
[arsol_request_title]      // Request title
```

#### User Information
```
[arsol_user_name]          // User display name
[arsol_user_email]         // User email
[arsol_user_role]          // User role
```

#### Links
```
[arsol_project_link]       // Link to project
[arsol_proposal_link]      // Link to proposal
[arsol_request_link]       // Link to request
[arsol_dashboard_link]     // Link to user dashboard
```

#### Dates
```
[arsol_current_date]       // Current date
[arsol_project_created]    // Project creation date
[arsol_last_updated]       // Last update date
```

### Custom Shortcodes

```php
// Add custom shortcode for emails
add_shortcode('arsol_custom_email_content', function($atts) {
    $atts = shortcode_atts(array(
        'project_id' => 0,
        'format' => 'default'
    ), $atts);
    
    // Generate custom content based on project
    return 'Custom email content for project ' . $atts['project_id'];
});
```

---

## Hooks & Filters

### Action Hooks

#### Email Triggering
```php
// Trigger request stage email
do_action('arsol_pfw_request_stage_changed', $request_id, $old_stage, $new_stage);

// Trigger new request email
do_action('arsol_pfw_new_request_created', $request_id, $user_id);

// Trigger proposal decision email
do_action('arsol_pfw_proposal_decision_made', $proposal_id, $decision, $user_id);
```

#### Email Customization
```php
// Before email content
do_action('arsol_pfw_email_before_content', $email_type, $email_data);

// After email content
do_action('arsol_pfw_email_after_content', $email_type, $email_data);
```

### Filter Hooks

#### Recipient Filtering
```php
// Filter email recipients
add_filter('arsol_pfw_email_recipients', function($recipients, $email_type, $entity_id) {
    // Add custom recipients based on logic
    return $recipients;
}, 10, 3);

// Filter manager recipients
add_filter('arsol_pfw_email_manager_recipients', function($managers, $project_id) {
    // Add/remove managers for specific projects
    return $managers;
}, 10, 2);
```

#### Content Filtering
```php
// Filter email subject
add_filter('arsol_pfw_email_subject', function($subject, $email_type, $entity_id) {
    return 'Custom: ' . $subject;
}, 10, 3);

// Filter email heading
add_filter('arsol_pfw_email_heading', function($heading, $email_type, $entity_id) {
    return 'Custom: ' . $heading;
}, 10, 3);
```

---

## Advanced Features

### Conditional Email Delivery

```php
// Send emails only during business hours
add_filter('arsol_pfw_should_send_email', function($should_send, $email_type, $recipient) {
    $current_hour = date('H');
    return ($current_hour >= 9 && $current_hour <= 17) ? $should_send : false;
}, 10, 3);
```

### Email Queuing

```php
// Queue emails for batch processing
add_action('arsol_pfw_queue_email', function($email_type, $recipients, $data) {
    // Add to email queue instead of sending immediately
    wp_schedule_single_event(time() + 300, 'arsol_pfw_process_email_queue', array($email_type, $recipients, $data));
});
```

### Email Logging

```php
// Log all email sends
add_action('arsol_pfw_email_sent', function($email_type, $recipient, $subject, $success) {
    error_log("Email sent: {$email_type} to {$recipient} - " . ($success ? 'SUCCESS' : 'FAILED'));
});
```

---

## Troubleshooting

### Common Issues

#### Emails Not Sending
1. **Check WooCommerce Email Settings**
   - Ensure WooCommerce emails are enabled
   - Verify SMTP configuration

2. **Verify Plugin Email Settings**
   - Check if specific email types are enabled
   - Verify recipient settings

3. **Check Server Email Configuration**
   - Ensure PHP mail() function works
   - Check server email logs

#### Missing Email Templates
1. **Template Location**: Ensure templates are in correct theme directory
2. **File Permissions**: Check file read permissions
3. **Template Syntax**: Verify PHP syntax in custom templates

#### Wrong Recipients
1. **Role Configuration**: Check project manager role settings
2. **User Capabilities**: Verify user has required capabilities
3. **Email Filters**: Check for custom recipient filters

### Debug Mode

Enable email debugging:

```php
// Add to wp-config.php
define('ARSOL_PFW_EMAIL_DEBUG', true);

// Check email logs
$logs = get_option('arsol_pfw_email_debug_log', array());
```

---

## Best Practices

### Template Development
1. **Always Use Escaping**: `esc_html()`, `esc_attr()`, etc.
2. **Check Data Existence**: Verify variables exist before using
3. **Responsive Design**: Use email-safe CSS
4. **Testing**: Test across email clients

### Performance Optimization
1. **Limit Recipients**: Avoid sending to large recipient lists
2. **Queue Heavy Operations**: Use email queuing for batch sends
3. **Cache Template Data**: Cache complex data generation

### Security
1. **Validate Input**: Sanitize all email content
2. **Limit Shortcodes**: Restrict shortcode usage in email settings
3. **User Permissions**: Verify user can access referenced content

---

## Migration & Updates

### Template Updates
When updating the plugin:
1. **Backup Custom Templates**: Save theme template overrides
2. **Check Template Changes**: Compare with new template structure
3. **Update Hooks**: Verify custom hooks still work

### Database Changes
Email settings are stored in:
- `wp_options` table with `woocommerce_email_*` keys
- User meta for individual email preferences
- Project meta for project-specific settings

---

This completes the comprehensive email system reference. The system provides powerful, flexible email notifications while maintaining WooCommerce compatibility and WordPress standards. 