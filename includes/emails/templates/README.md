# Email Templates Directory

This directory contains all email templates for the Arsol Projects for Woo plugin email system.

## Directory Structure

```
templates/
├── README.md                              # This file
├── plain/                                 # Plain text versions
│   ├── arsol_new_request.php             # Plain text: New request submitted
│   ├── arsol_request_status.php          # Plain text: Request status changed
│   ├── arsol_proposal_ready.php          # Plain text: Proposal ready for review
│   └── arsol_project_creation.php        # Plain text: Project/order created
├── arsol_new_request.php                 # HTML: New request submitted (customer)
├── admin-arsol_new_request.php           # HTML: New request submitted (admin)
├── arsol_request_status.php              # HTML: Request status changed
├── arsol_proposal_processing.php         # HTML: Proposal processing started
├── arsol_proposal_ready.php              # HTML: Proposal ready for review
├── arsol_project_creation.php            # HTML: Project/order created
├── arsol_project_status.php              # HTML: Project status changed
├── admin-proposal-decision.php           # HTML: Proposal approved/rejected (admin)
└── lead-proposal-processing.php          # HTML: Proposal assignment (project lead)
```

## Template Naming Convention

Templates follow this naming pattern:
- **Customer emails**: `{email_id}.php` (e.g., `arsol_new_request.php`)
- **Admin emails**: `admin-{description}.php` (e.g., `admin-proposal-decision.php`)
- **Project Lead emails**: `lead-{description}.php` (e.g., `lead-proposal-processing.php`)
- **Plain text**: Same names in `plain/` subdirectory

## Email Template IDs

| Email Class | Template ID | Purpose |
|-------------|-------------|---------|
| `New_Request_Email` | `arsol_new_request` | Customer receives request confirmation |
| `Request_Status_Email` | `arsol_request_status` | Request status updates |
| `Proposal_Processing_Email` | `arsol_proposal_processing` | Proposal work started |
| `Proposal_Ready_Email` | `arsol_proposal_ready` | Proposal ready for customer review |
| `Proposal_Decision_Email` | `arsol_proposal_decision` | Proposal approved/rejected |
| `Project_Creation_Email` | `arsol_project_creation` | Project created, order ready |
| `Project_Status_Email` | `arsol_project_status` | Project status updates |

## Available Variables

### Common Variables (Available in all templates)
- `$email` - The email object instance
- `$email_heading` - Email heading text
- `$status_icon` - Emoji/icon for the email type
- `$color_scheme` - Array with 'background', 'text', 'cta' colors

### Customer-Specific Variables
- `$customer` - WP_User object for the customer
- `$portal_url` - URL to customer portal for this item

### Request Templates
- `$request` - WP_Post object for the request
- `$old_status` - Previous request status (for status emails)
- `$new_status` - New request status (for status emails)
- `$status_label` - Human-readable status label

### Proposal Templates
- `$proposal` - WP_Post object for the proposal
- `$project_lead` - WP_User object for assigned project lead (if any)

### Project Templates
- `$project` - WP_Post object for the project
- `$order` - WC_Order object (for creation emails)
- `$checkout_url` - URL for order payment (creation emails)

### Admin Templates
- `$admin_url` - URL to admin edit page for the item

## Template Features

### Responsive Design
- All HTML templates use inline CSS for maximum email client compatibility
- Mobile-friendly layouts with proper spacing
- Consistent typography and color schemes

### Color Coding
Templates use different color schemes based on email type:
- **Processing**: Blue (`#0073aa`) - Work in progress
- **Success**: Green (`#28a745`) - Positive outcomes
- **Action Required**: Yellow (`#ffc107`) - Customer action needed
- **Error/Hold**: Red (`#dc3545`) - Issues or problems

### Status Icons
- 🔧 Processing/work in progress
- 🎉 Success/completion
- ⏰ Action required
- ⚠️ Warning/error
- 📋 General update
- ✅ Approval/delivered

### Portal-First Approach
Every customer email includes:
1. **Primary CTA**: Link to customer portal
2. **Clear instructions**: What the customer should do
3. **Context**: Why they're receiving the email
4. **Next steps**: What happens after they take action

## Customization

### WordPress Hooks
Templates can be customized using WordPress filters:
```php
// Customize email heading
add_filter('arsol_email_heading', function($heading, $email_id) {
    if ($email_id === 'arsol_new_request') {
        return 'Custom Request Confirmation';
    }
    return $heading;
}, 10, 2);

// Customize template variables
add_filter('arsol_email_template_vars', function($vars, $email_id) {
    $vars['custom_var'] = 'Custom value';
    return $vars;
}, 10, 2);
```

### Template Override
To override templates in your theme:
1. Create directory: `your-theme/arsol-projects-for-woo/emails/`
2. Copy template file to this directory
3. Modify as needed - the plugin will use your version

### CSS Customization
Since templates use inline CSS, you can:
1. Edit the template files directly
2. Use the `arsol_email_template_vars` filter to pass custom CSS
3. Override entire templates in your theme

## Development Notes

### Testing Templates
Use the email manager's test functionality:
```php
// Send test email
do_action('arsol_send_test_email', 'new_request', 'test@example.com');
```

### Template Debugging
Enable email logging in WooCommerce settings:
- Go to WooCommerce > Settings > Emails
- Find "Arsol Projects Email Settings"
- Enable "Email Logging"

### Adding New Templates
1. Create the HTML template file
2. Create the plain text version in `plain/`
3. Update the email class to reference the new template
4. Test thoroughly across email clients

## Browser Support

Templates are tested and compatible with:
- Gmail (web and mobile)
- Outlook (2016, 2019, Office 365)
- Apple Mail (macOS and iOS)
- Yahoo Mail
- Thunderbird
- Most other modern email clients

## Performance Notes

- Templates use minimal external resources
- All CSS is inline for faster loading
- Images should be optimized and hosted reliably
- Keep email size under 100KB for best deliverability
