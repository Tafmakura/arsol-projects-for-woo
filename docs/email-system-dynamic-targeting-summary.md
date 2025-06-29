# Email System Dynamic Targeting Summary

## Overview
All emails now use **dynamic recipient targeting** while remaining **configurable** in WooCommerce email settings for enable/disable, subject, heading, and email type.

## Dynamic Targeting Implementation

### Customer Emails (6 emails)
All customer emails use `$this->customer_email = true` and dynamic customer targeting:

1. **Project Customer: Request Submitted** (`class-wc-email-new-request.php`)
   - Hook: `arsol_new_request_created`
   - Trigger: `trigger($request_id, $customer_id)`
   - Target: Customer who submitted the request
   - Dynamic: `get_user_by('id', $customer_id)->user_email`

2. **Project Customer: Request Status Update** (`class-wc-email-request-status.php`)
   - Hook: `arsol_request_status_changed`
   - Trigger: `trigger($request_id, $old_status, $new_status, $customer_id)`
   - Target: Customer who owns the request
   - Dynamic: `get_user_by('id', $customer_id)->user_email`

3. **Project Customer: Proposal Ready for Review** (`class-wc-email-proposal-ready.php`)
   - Hook: `arsol_proposal_ready`
   - Trigger: `trigger($proposal_id, $customer_id)`
   - Target: Customer who owns the proposal
   - Dynamic: `get_user_by('id', $customer_id)->user_email`

4. **Project Customer: Your Project Order Is Ready** (`class-wc-email-project-creation.php`)
   - Hook: `arsol_project_created`
   - Trigger: `trigger($project_id, $customer_id)`
   - Target: Customer who owns the project
   - Dynamic: `get_user_by('id', $customer_id)->user_email`

5. **Project Customer: Request Stage Update** (`class-wc-email-request-stage.php`)
   - Hook: `arsol_request_stage_changed`

6. **Project Customer: Project Completed** (`class-wc-email-project-completion.php`)
   - Hook: `arsol_project_completed`
   - Trigger: `trigger($project_id, $customer_id)`
   - Target: Customer who owns the project
   - Dynamic: `get_user_by('id', $customer_id)->user_email`

### Project Lead Emails (2 emails)
Both project lead emails use dynamic project lead targeting:

1. **Project Lead: Proposal Processing** (`class-wc-email-proposal-processing.php`)
   - Hook: `arsol_proposal_processing_started`
   - Trigger: `trigger($proposal_id, $customer_id, $project_lead_id)`
   - Target: Selected project lead for the proposal
   - Dynamic: `get_user_by('id', $project_lead_id)->user_email`

2. **Project Lead: Proposal Decision** (`class-wc-email-proposal-decision.php`)
   - Hook: `arsol_proposal_status_changed`
   - Trigger: `trigger($proposal_id, $old_status, $new_status)`
   - Target: Project lead assigned to the proposal
   - Dynamic: `get_post_meta($proposal_id, 'project_lead_id', true)` → `get_user_by('id', $project_lead_id)->user_email`

### Shop Manager Email (1 email)
The shop manager email remains configurable:

1. **New Project Request** (`class-wc-email-admin-new-request.php`)
   - Hook: `arsol_new_request_created`
   - Trigger: `trigger($request_id)`
   - Target: Configurable (defaults to admin email)
   - Configurable: Has recipient field in settings

## Configuration Options
All emails remain **fully configurable** in WooCommerce → Settings → Emails:

- ✅ **Enable/Disable** - Each email can be turned on/off
- ✅ **Subject Line** - Customizable with placeholders
- ✅ **Email Heading** - Customizable with placeholders
- ✅ **Email Type** - HTML/Plain text options
- ❌ **Recipients** - Removed from customer/project lead emails (dynamic only)
- ✅ **Recipients** - Available only for shop manager email

## Key Benefits

1. **No Manual Configuration**: Customer and project lead emails automatically target the right person
2. **Role Separation**: Each email targets exactly one role - no duplicates
3. **Still Configurable**: Admins can still customize subjects, headings, and enable/disable emails
4. **Dynamic Targeting**: Emails always go to the right person based on the specific request/proposal/project
5. **Fail-Safe**: If no customer/project lead is found, email simply doesn't send (no errors)

## Template Files
All emails use templates in `includes/email/templates/`:
- `email-new-request.php`
- `email-request-status.php`
- `email-proposal-ready.php`
- `email-project-creation.php`
- `email-request-stage.php`
- `email-project-completion.php`
- `email-admin-new-request.php`
- `email-proposal-processing.php`
- `email-proposal-decision.php`

## Email Manager
All 9 emails are loaded via `includes/email/class-email-manager.php` using the `woocommerce_email_classes` filter. 