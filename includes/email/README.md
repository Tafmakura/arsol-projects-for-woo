# Arsol Projects for Woo - Email System

## Overview

This is a **simplified, WooCommerce-standard email system** that follows official WooCommerce email development practices. The system has been completely refactored from the previous complex approach to use the standard WooCommerce email architecture.

## Architecture

### Core Components

1. **Email Classes** (`/classes/`) - Individual email classes extending `WC_Email`
2. **Email Templates** (`/templates/`) - HTML templates for email content
3. **Email WooCommerce Integration** (`class-email-woocommerce.php`) - Registration and trigger management
4. **Email Setup** (`class-email-setup.php`) - Initialization wrapper

### Email Classes

Each email class follows the standard WooCommerce pattern:

- Extends `WC_Email`
- Implements `trigger()`, `get_content_html()`, `get_content_plain()`, `init_form_fields()`
- Uses `wc_get_template_html()` for template loading
- Follows WooCommerce email settings integration

## Email List

### Admin/Shop Manager Emails
- **`Arsol_Email_Admin_New_Request`** - New request notifications
- **`Arsol_Email_Admin_Proposal_Decision`** - Approved proposals needing conversion

### Project Lead Emails  
- **`Arsol_Email_Lead_New_Proposal`** - New proposal assignments
- **`Arsol_Email_Lead_Proposal_Decision`** - Proposal decision notifications
- **`Arsol_Email_Lead_Proposal_Processing`** - Work started notifications

### Customer Emails
- **`Arsol_Email_Customer_Proposal_Ready`** - Proposal ready for review
- **`Arsol_Email_Customer_Project_Creation`** - Project/order ready
- **`Arsol_Email_Customer_Project_Status`** - Project status updates
- **`Arsol_Email_Customer_Project_Completion`** - Project completion

### Shared Emails
- **`Arsol_Email_Request_Status`** - Request status changes (customer + lead)

## How It Works

### 1. Initialization Flow
```
Main Plugin File
└── class-setup.php
    └── class-email-setup.php
        └── class-email-woocommerce.php
            └── Registers all email classes
```

### 2. Registration
The `Arsol_Email_WooCommerce::init()` method:
- Includes all email class files
- Registers classes with WooCommerce via `woocommerce_email_classes` filter
- Sets up action hooks for email triggers

### 3. Email Triggering
When workflow events occur, action hooks are fired:
```php
do_action('arsol_pfw_new_request_submitted', $request_id, $customer_id);
do_action('arsol_pfw_proposal_ready_for_review', $proposal_id, $customer_id);
// etc.
```

### 4. Email Sending
The Email Manager catches these actions and triggers the appropriate email classes:
```php
public static function trigger_new_request_admin($request_id, $customer_id) {
    $emails = WC()->mailer()->get_emails();
    if (isset($emails['Arsol_Email_Admin_New_Request'])) {
        $emails['Arsol_Email_Admin_New_Request']->trigger($request_id, $customer_id);
    }
}
```

## Key Features

### ✅ WooCommerce Native Integration
- Appears in WooCommerce > Settings > Emails
- Uses WooCommerce email styling and templates
- Supports all WooCommerce email features (enable/disable, custom recipients, etc.)

### ✅ Proper Template System
- Templates in `/templates/` directory
- Uses `wc_get_template_html()` for loading
- Supports WooCommerce email header/footer actions

### ✅ Placeholder Support
Each email supports placeholders like:
- `{request_title}`, `{proposal_title}`, `{project_title}`
- `{customer_name}`, `{lead_name}`
- `{site_title}`, `{request_id}`, etc.

### ✅ Multi-Recipient Support
- Admin emails: Shop managers/admins
- Lead emails: Project leads
- Customer emails: Customers
- Shared emails: Both customers and leads

### ✅ Portal Integration
- Customer emails link to customer portal
- Admin emails link to WordPress admin
- Proper URL generation for each context

## Usage

### In Your Code
To trigger emails from your workflow code:
```php
// New request submitted
do_action('arsol_pfw_new_request_submitted', $request_id, $customer_id);

// Proposal ready for review
do_action('arsol_pfw_proposal_ready_for_review', $proposal_id, $customer_id);

// Project status updated
do_action('arsol_pfw_project_status_updated', $project_id, $new_status);
```

### For Administrators
1. Go to WooCommerce > Settings > Emails
2. Configure each email type individually
3. Set recipients, subjects, and content
4. Enable/disable as needed

## File Structure

```
includes/email/
├── class-email-setup.php                    # Email system initialization
├── class-email-woocommerce.php              # WooCommerce integration and registration
├── classes/                                 # Individual email classes
│   ├── class-email-admin-new-request.php
│   ├── class-email-admin-proposal-decision.php
│   ├── class-email-lead-new-proposal.php
│   ├── class-email-lead-proposal-decision.php
│   ├── class-email-lead-proposal-processing.php
│   ├── class-email-customer-proposal-ready.php
│   ├── class-email-customer-project-creation.php
│   ├── class-email-customer-project-status.php
│   ├── class-email-customer-project-completion.php
│   └── class-email-request-status.php
└── templates/                               # Email templates
    ├── email-admin-new-request.php
    ├── email-admin-proposal-decision.php
    ├── email-new-proposal.php
    ├── email-proposal-decision.php
    ├── email-proposal-processing.php
    ├── email-proposal-ready.php
    ├── email-project-creation.php
    ├── email-project-status.php
    ├── email-project-completion.php
    └── email-request-status.php
```

## Benefits of This Approach

1. **Follows WooCommerce Standards** - Uses official WooCommerce email architecture
2. **Simple and Maintainable** - Each email is a single, focused class
3. **Native WooCommerce Integration** - Appears in WooCommerce settings naturally
4. **Extensible** - Easy to add new emails or modify existing ones
5. **Template-Based** - Clean separation of logic and presentation
6. **Multi-Recipient Ready** - Supports different recipient types cleanly

## Migration from Previous System

This system completely replaces the previous complex email architecture. The old system had:
- Complex base classes with multiple inheritance
- Complicated recipient logic
- Preview system issues
- Template path problems

The new system is much simpler and follows WooCommerce conventions exactly. 