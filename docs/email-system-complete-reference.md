# Arsol Projects for Woo - Email System Complete Reference

**Version**: 1.0  
**Date**: 2024  
**Purpose**: Complete documentation of the email notification system for Arsol Projects for Woo plugin

---

## Table of Contents

1. [Overview](#overview)
2. [Email Architecture](#email-architecture)
3. [Complete Email Matrix](#complete-email-matrix)
4. [Stage 1: Project Requests](#stage-1-project-requests)
5. [Stage 2: Project Proposals](#stage-2-project-proposals)
6. [Stage 3: Active Projects](#stage-3-active-projects)
7. [Billing Notifications](#billing-notifications)
8. [Email Templates](#email-templates)
9. [Implementation Reference](#implementation-reference)
10. [Portal URLs](#portal-urls)

---

## Overview

The Arsol Projects for Woo plugin implements a comprehensive email notification system that manages communication between **Customers**, **Project Leads**, and **Admins** throughout the complete project lifecycle from initial request to project completion.

### Key Principles

- **Portal-First Content**: Every email prioritizes portal links as primary CTAs
- **Role-Based Distribution**: Different user types receive appropriate notifications
- **Complete Admin Oversight**: Admins get notifications for ALL workflow steps
- **Customer-Focused Journey**: Customers only get actionable, relevant emails
- **WooCommerce Integration**: Leverages WooCommerce's native email system

---

## Email Architecture

### User Types & Access Levels

| User Type | Request Stage | Proposal Stage | Project Stage |
|-----------|---------------|----------------|---------------|
| **👤 Customers** | ✅ Submitter | ✅ Reviewer/Approver | ✅ Project Owner |
| **👨‍💼 Project Leads** | ❌ Not involved | ✅ Assigned & Working | ✅ Project Manager |
| **🔧 Admins** | ✅ Full oversight | ✅ Full oversight | ✅ Full oversight |

### Email Integration

```php
// WooCommerce Email System Integration
add_filter('woocommerce_email_classes', array($this, 'add_email_classes'));

// Custom Email Classes
$email_classes['Arsol_New_Request_Email'] = new \Arsol_Projects_For_Woo\Emails\New_Request_Email();
$email_classes['Arsol_Request_Status_Email'] = new \Arsol_Projects_For_Woo\Emails\Request_Status_Email();
$email_classes['Arsol_Proposal_Processing_Email'] = new \Arsol_Projects_For_Woo\Emails\Proposal_Processing_Email();
// ... additional email classes
```

---

## Complete Email Matrix

### Email Summary Statistics

| **Stage** | **Customer Emails** | **Project Lead Emails** | **Admin Emails** | **Total** |
|-----------|-------------------|------------------------|------------------|-----------|
| **Requests** | 3 | 0 | 4 | **7** |
| **Proposals** | 2 | 4 | 5 | **11** |
| **Projects** | 4 | 4 | 4 | **12** |
| **Billing** | 1 | 1 | 1 | **3** |
| **TOTAL** | **10** | **9** | **14** | **33** |

---

## Stage 1: Project Requests

*Users Involved: Admins + Customers (NO Project Leads)*

### 1. New Request Submitted
```php
do_action('arsol_new_request_created', $request_id, $customer_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | Request Submitted Successfully | `"Your Project Request Has Been Received - #{Request ID}"` | `/my-account/project-view-request/{request_id}` |
| **🔧 Admins** | New Project Request Submitted | `"New Project Request: {Request Title}"` | Admin edit link |

### 2. Request Status: → Under Review
```php
do_action('arsol_request_status_changed', $request_id, 'pending-review', 'under-review');
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | Request Under Review | `"Update: Your Request is Being Reviewed - #{Request ID}"` | `/my-account/project-view-request/{request_id}` |
| **🔧 Admins** | Request Under Review | `"Request Status: Under Review - {Request Title}"` | Admin link |

### 3. Request Status: → On Hold
```php
do_action('arsol_request_status_changed', $request_id, $old_status, 'on-hold');
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | Request On Hold | `"Status Update: Request On Hold - #{Request ID}"` | `/my-account/project-view-request/{request_id}` |
| **🔧 Admins** | Request On Hold | `"Request On Hold: {Request Title} - #{Request ID}"` | Admin link |

### 4. Request Status: → Approved (Admin Only)
```php
do_action('arsol_request_status_changed', $request_id, $old_status, 'approved');
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **🔧 Admins** | Request Approved - Ready for Conversion | `"Request Approved: {Request Title} - #{Request ID}"` | Admin conversion link |

*Note: Customer gets "proposal processing" email instead of "request approved"*

---

## Stage 2: Project Proposals

*Users Involved: Admins + Project Leads + Customers*

### 5. New Proposal Created (Internal)
```php
do_action('arsol_new_proposal_created', $proposal_id, $customer_id, $project_lead_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👨‍💼 Project Lead** | New Project Assignment | `"You've been assigned: {Project Title} - #{Proposal ID}"` | Admin proposal link |
| **🔧 Admins** | New Proposal Created | `"Proposal Generated: {Project Title} - Lead: {Lead Name}"` | Admin proposal link |

### 6. Proposal Processing Started
```php
do_action('arsol_proposal_processing_started', $proposal_id, $customer_id, $project_lead_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | 🔧 We're Working on Your Proposal | `"We're Working on Your Proposal: {Project Title} - #{Proposal ID}"` | `/my-account/project-view-proposal/{proposal_id}` |
| **👨‍💼 Project Lead** | Proposal Assignment Active | `"Now Processing: {Project Title} - #{Proposal ID}"` | Admin proposal link |
| **🔧 Admins** | Proposal Processing Started | `"Processing Started: {Project Title} - Lead: {Lead Name}"` | Admin proposal link |

### 7. Proposal Status: Processing → Pending Approval
```php
do_action('arsol_proposal_status_changed', $proposal_id, 'processing', 'pending-approval');
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | ⏰ Action Required: Review Your Proposal | `"Please Review: {Project Title} Proposal - #{Proposal ID}"` | `/my-account/project-view-proposal/{proposal_id}` |
| **👨‍💼 Project Lead** | Proposal Sent to Customer | `"Awaiting Customer Response: {Project Title} - #{Proposal ID}"` | Admin proposal link |
| **🔧 Admins** | Proposal Pending Customer Response | `"Customer Review: {Project Title} - #{Proposal ID}"` | Admin proposal link |

### 8. Proposal Status: Pending Approval → Approved
```php
do_action('arsol_proposal_status_changed', $proposal_id, 'pending-approval', 'approved');
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👨‍💼 Project Lead** | 🎉 Project Approved & Created! | `"Ready to Start: {Project Title} - #{Project ID}"` | Admin project link |
| **🔧 Admins** | Project Created from Approved Proposal | `"New Active Project: {Project Title} - #{Project ID}"` | Admin project link |

### 9. Proposal Status: Pending Approval → Rejected
```php
do_action('arsol_proposal_status_changed', $proposal_id, 'pending-approval', 'rejected');
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👨‍💼 Project Lead** | Proposal Rejected - Customer Feedback | `"Revision Needed: {Project Title} - #{Proposal ID}"` | Admin proposal link |
| **🔧 Admins** | Proposal Rejected - Follow-up Needed | `"Customer Feedback: {Project Title} - #{Proposal ID}"` | Admin proposal link |

*Note: Customer doesn't get notification for their own rejection*

---

## Stage 3: Active Projects

*Users Involved: Admins + Project Leads + Customers*

### 10. Your Order Is Ready (After Approval)
```php
do_action('arsol_proposal_approved_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | 🎉 Your Order Is Ready! | `"Your Order Is Ready: {Project Title} - #{Project ID}"` | `/my-account/project-overview/{project_id}` |

### 11. Project Created (Internal)
```php
do_action('arsol_new_project_created', $project_id, $proposal_id, $customer_id, $project_lead_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👨‍💼 Project Lead** | Project Officially Active | `"Project Kickoff: {Project Title} - #{Project ID}"` | Admin project link |
| **🔧 Admins** | New Active Project | `"Project Live: {Project Title} - Lead: {Lead Name}"` | Admin project link |

### 12. Project Status: Not Started → Custom Status
```php
do_action('arsol_project_status_changed', $project_id, 'not-started', $new_status, $project_lead_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | 🚀 Project Status Update | `"Status Update: {Project Title} - Now {New Status}"` | `/my-account/project-overview/{project_id}` |
| **👨‍💼 Project Lead** | Project Status Updated | `"Project Active: {Project Title} - #{Project ID}"` | Admin project link |
| **🔧 Admins** | Project Status Update | `"Status Change: {Project Title} - Now {New Status}"` | Admin project link |

### 13. Project Status: In Progress → Completed
```php
do_action('arsol_project_status_changed', $project_id, 'in-progress', 'completed', $project_lead_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | 🎉 Project Delivered! | `"Project Complete: {Project Title} - #{Project ID}"` | `/my-account/project-overview/{project_id}` |
| **👨‍💼 Project Lead** | Project Completion Confirmed | `"Project Delivered: {Project Title} - #{Project ID}"` | Admin project link |
| **🔧 Admins** | Project Completed | `"Project Completed: {Project Title} - #{Project ID}"` | Admin project link |

---

## Billing Notifications

### 14. Order/Subscription Created
```php
do_action('arsol_project_order_created', $project_id, $order_id);
```

| Recipient | Email | Subject | Portal URL |
|-----------|-------|---------|------------|
| **👤 Customer** | Payment Setup Complete | `"Billing Activated: {Project Title} - #{Project ID}"` | `/my-account/project-overview/{project_id}` |
| **👨‍💼 Project Lead** | Project Billing Active | `"Billing Confirmed: {Project Title} - #{Project ID}"` | Admin project link |
| **🔧 Admins** | Project Order Created | `"Order Created: {Project Title} - #{Order ID}"` | Admin order link |

---

## Email Templates

### Template Structure

All emails follow this structure:

```html
<!-- Status Header -->
<div style="background: {status_color}; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h2 style="color: {text_color}; margin: 0;">{status_icon} {Email Title}</h2>
    <p style="margin: 10px 0 0; color: {text_color};">Status: {Current Status}</p>
</div>

<!-- Main Content -->
<p>Hi {Recipient Name},</p>
<p>{Main message content}</p>

<!-- Information Box -->
<div style="background: #e8f4fd; padding: 15px; border-left: 4px solid #0073aa; margin: 20px 0;">
    <h3 style="margin: 0 0 10px; color: #0073aa;">{Info Title}</h3>
    <ul style="margin: 0; padding-left: 20px;">
        <li>{Info item 1}</li>
        <li>{Info item 2}</li>
    </ul>
</div>

<!-- Primary CTA -->
<div style="text-align: center; margin: 30px 0;">
    <a href="{portal_url}" 
       style="background: {cta_color}; color: white; padding: 15px 30px; 
              text-decoration: none; border-radius: 5px; font-weight: bold; 
              display: inline-block;">
        🔗 {CTA Text}
    </a>
</div>

<!-- Contact Information -->
<p><strong>Contact:</strong> {Contact Details}</p>
```

### Color Schemes

| Status | Background | Text | CTA |
|--------|------------|------|-----|
| **Processing** | `#cce5ff` | `#0073aa` | `#0073aa` |
| **Success** | `#d4edda` | `#155724` | `#28a745` |
| **Action Required** | `#fff3cd` | `#856404` | `#ffc107` |
| **Error/Hold** | `#f8d7da` | `#721c24` | `#dc3545` |

---

## Implementation Reference

### Required Email Classes

Create these email classes in `includes/emails/`:

```php
// includes/emails/class-new-request-email.php
class New_Request_Email extends WC_Email

// includes/emails/class-request-status-email.php  
class Request_Status_Email extends WC_Email

// includes/emails/class-proposal-processing-email.php
class Proposal_Processing_Email extends WC_Email

// includes/emails/class-proposal-status-email.php
class Proposal_Status_Email extends WC_Email

// includes/emails/class-project-ready-email.php
class Project_Ready_Email extends WC_Email

// includes/emails/class-project-status-email.php
class Project_Status_Email extends WC_Email

// includes/emails/class-billing-notification-email.php
class Billing_Notification_Email extends WC_Email
```

### Email Action Hooks Implementation

Add these action triggers throughout the codebase:

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

// Billing Stage
do_action('arsol_project_order_created', $project_id, $order_id);
```

---

## Portal URLs

### Customer Portal URLs

| Stage | URL Pattern | Example |
|-------|-------------|---------|
| **Requests** | `/my-account/project-view-request/{request_id}` | `/my-account/project-view-request/123` |
| **Proposals** | `/my-account/project-view-proposal/{proposal_id}` | `/my-account/project-view-proposal/456` |
| **Projects** | `/my-account/project-overview/{project_id}` | `/my-account/project-overview/789` |

### Admin URLs

| Stage | URL Pattern | Example |
|-------|-------------|---------|
| **Requests** | `admin.php?post={request_id}&action=edit` | `admin.php?post=123&action=edit` |
| **Proposals** | `admin.php?post={proposal_id}&action=edit` | `admin.php?post=456&action=edit` |
| **Projects** | `admin.php?post={project_id}&action=edit` | `admin.php?post=789&action=edit` |

---

## Customer Email Journey

The complete customer experience:

1. **📨 Request Submitted** → Portal access to track
2. **🔍 Request Under Review** → Status update
3. **⏸️ Request On Hold** (if applicable) → Reason & timeline
4. **🔧 Proposal Processing** → Work beginning notification
5. **⏰ Proposal Ready for Review** → Action required
6. **🎉 Your Order Is Ready** → Project created & billing active
7. **🚀 Project Status Updates** → Work progress
8. **🎉 Project Delivered** → Completion & deliverables
9. **💳 Billing Notifications** → Payment confirmations

This comprehensive email system ensures clear, actionable communication while maintaining appropriate information sharing across all user types throughout the complete project lifecycle. 