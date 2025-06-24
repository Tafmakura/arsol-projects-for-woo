# Arsol Projects for Woo - Role-Based Email System Reference

**Version**: 2.0  
**Date**: 2024  
**Purpose**: Complete documentation of the role-based email notification system

---

## Overview

The Arsol Projects for Woo plugin implements a **role-based email notification system** with strict separation:

- **No email is sent to multiple roles**
- **Each email targets one specific role only**
- **Clean workflow integration with WooCommerce**

### User Roles & Email Distribution

| Role | Description | Email Count |
|------|-------------|-------------|
| **👤 Customers** | Project requesters and owners | 5 emails |
| **👨‍💼 Project Leads** | Assigned project managers | 2 emails |
| **🏪 Shop Managers** | Administrative oversight | 3 emails |
| **Total** | | **10 emails** |

---

## Email System Architecture

### Workflow Action Hooks

The system uses standardized workflow hooks that align with business processes:

```php
// Request Stage
do_action('arsol_new_request_created', $request_id, $customer_id);
do_action('arsol_request_status_changed', $request_id, $old_status, $new_status);

// Proposal Stage  
do_action('arsol_new_proposal_created', $proposal_id, $customer_id, $project_lead_id);
do_action('arsol_proposal_processing_started', $proposal_id, $customer_id, $project_lead_id);
do_action('arsol_proposal_status_changed', $proposal_id, $old_status, $new_status);

// Project Stage
do_action('arsol_proposal_approved_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);
do_action('arsol_new_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);
do_action('arsol_project_status_changed', $project_id, $old_status, $new_status, $project_lead_id);
```

---

## Complete Email Matrix

### 👤 Customer Emails (5 total)

| Email Class | Trigger Hook | Purpose | Template |
|-------------|--------------|---------|----------|
| `WC_Email_New_Request` | `arsol_new_request_created` | Request submission confirmation | `email-new-request.php` |
| `WC_Email_Request_Status` | `arsol_request_status_changed` | Request status updates | `email-request-status.php` |
| `WC_Email_Proposal_Ready` | `arsol_proposal_ready_for_review` | Proposal ready for review | `email-proposal-ready.php` |
| `WC_Email_Project_Creation` | `arsol_proposal_approved_project_created` | Project/order ready | `email-project-creation.php` |
| `WC_Email_Project_Status` | `arsol_project_status_changed` | Project status updates | `email-project-status.php` |

### 👨‍💼 Project Lead Emails (2 total)

| Email Class | Trigger Hook | Purpose | Template |
|-------------|--------------|---------|----------|
| `WC_Email_Proposal_Processing` | `arsol_proposal_processing_started` | Proposal assignment | `email-proposal-processing.php` |
| `WC_Email_Proposal_Decision` | `arsol_proposal_status_changed` | Proposal decision feedback | `email-proposal-decision.php` |

### 🏪 Shop Manager Emails (3 total)

| Email Class | Trigger Hook | Purpose | Template |
|-------------|--------------|---------|----------|
| `WC_Email_Admin_New_Request` | `arsol_new_request_created` | New request oversight | `email-admin-new-request.php` |
| `WC_Email_New_Proposal` | `arsol_new_proposal_created` | New proposal oversight | `email-new-proposal.php` |
| `WC_Email_Project_Completion` | `arsol_project_status_changed` | Project completion oversight | `email-project-completion.php` |

---

## Workflow Email Sequence

### Stage 1: Project Request

**Customer submits new request**
```php
do_action('arsol_new_request_created', $request_id, $customer_id);
```

**Emails sent:**
- 👤 **Customer**: `WC_Email_New_Request` - "Your request has been received"
- 🏪 **Shop Manager**: `WC_Email_Admin_New_Request` - "New request requires review"

**Request status changes**
```php
do_action('arsol_request_status_changed', $request_id, $old_status, $new_status);
```

**Emails sent:**
- 👤 **Customer**: `WC_Email_Request_Status` - "Your request status: {new_status}"

### Stage 2: Proposal Creation & Processing

**New proposal created**
```php
do_action('arsol_new_proposal_created', $proposal_id, $customer_id, $project_lead_id);
```

**Emails sent:**
- 🏪 **Shop Manager**: `WC_Email_New_Proposal` - "New proposal created for oversight"

**Proposal processing starts**
```php
do_action('arsol_proposal_processing_started', $proposal_id, $customer_id, $project_lead_id);
```

**Emails sent:**
- 👨‍💼 **Project Lead**: `WC_Email_Proposal_Processing` - "You've been assigned to work on proposal"

**Proposal ready for customer review**
```php
do_action('arsol_proposal_ready_for_review', $proposal_id, $customer_id);
```

**Emails sent:**
- 👤 **Customer**: `WC_Email_Proposal_Ready` - "Please review your proposal"

**Proposal decision made**
```php
do_action('arsol_proposal_status_changed', $proposal_id, $old_status, $new_status);
```

**Emails sent:**
- 👨‍💼 **Project Lead**: `WC_Email_Proposal_Decision` - "Proposal {new_status} - next steps"

### Stage 3: Project Creation & Management

**Project created from approved proposal**
```php
do_action('arsol_proposal_approved_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);
```

**Emails sent:**
- 👤 **Customer**: `WC_Email_Project_Creation` - "Your order is ready!"

**Project status updates**
```php
do_action('arsol_project_status_changed', $project_id, $old_status, $new_status, $project_lead_id);
```

**Emails sent:**
- 👤 **Customer**: `WC_Email_Project_Status` - "Project status: {new_status}"
- 🏪 **Shop Manager**: `WC_Email_Project_Completion` - (only when status = 'completed')

---

## Email Targeting Logic

### Customer Email Targeting
```php
// Get customer email from user ID
$customer = get_user_by( 'id', $customer_id );
if ( $customer ) {
    $this->recipient = $customer->user_email;
}
```

### Project Lead Email Targeting
```php
// Get project lead email from user ID
$project_lead = get_user_by( 'id', $project_lead_id );
if ( $project_lead ) {
    $this->recipient = $project_lead->user_email;
}
```

### Shop Manager Email Targeting
```php
// Use configured shop manager emails
$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
```

---

## Portal URL Integration

### Customer Portal URLs
- **Requests**: `/my-account/project-view-request/{request_id}`
- **Proposals**: `/my-account/project-view-proposal/{proposal_id}`
- **Projects**: `/my-account/project-overview/{project_id}`

### Admin Portal URLs
- **Requests**: `admin.php?post={request_id}&action=edit`
- **Proposals**: `admin.php?post={proposal_id}&action=edit`
- **Projects**: `admin.php?post={project_id}&action=edit`

---

## Implementation Files

### Email Manager
- `includes/email/class-email-manager.php` - Central email registration

### Customer Email Classes
- `includes/email/class-wc-email-new-request.php`
- `includes/email/class-wc-email-request-status.php`
- `includes/email/class-wc-email-proposal-ready.php`
- `includes/email/class-wc-email-project-creation.php`
- `includes/email/class-wc-email-project-status.php`

### Project Lead Email Classes
- `includes/email/class-wc-email-proposal-processing.php`
- `includes/email/class-wc-email-proposal-decision.php`

### Shop Manager Email Classes
- `includes/email/class-wc-email-admin-new-request.php`
- `includes/email/class-wc-email-new-proposal.php`
- `includes/email/class-wc-email-project-completion.php`

### Email Templates
- `includes/email/templates/email-*.php` - HTML email templates

---

## Key Benefits

✅ **Role Separation**: No email duplication across roles  
✅ **Workflow Integration**: Uses standard business process hooks  
✅ **WooCommerce Native**: Full integration with WooCommerce email system  
✅ **Portal-First**: All emails include relevant portal links  
✅ **Maintainable**: Clear, organized structure  
✅ **Scalable**: Easy to add new emails or modify existing ones  

This role-based email system ensures clear, targeted communication while maintaining complete workflow coverage from initial request through project completion.
