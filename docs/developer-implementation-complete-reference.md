# Developer Implementation - Complete Reference

## Overview

The **Arsol Projects for WooCommerce** plugin provides extensive developer implementation details covering custom post types, meta keys, content defaults, conditional visibility, and technical architecture. This reference is the complete technical guide for developers.

## Key Features

- ✅ **WordPress-Native Architecture** with custom post types and taxonomies
- ✅ **Capability System** integrated with WordPress roles and permissions
- ✅ **Meta Key System** with over 100 documented meta fields
- ✅ **Content Defaults** with markdown support and dynamic generation
- ✅ **Conditional Visibility** with auto-discovery CSS class system
- ✅ **WooCommerce Integration** with orders, subscriptions, and billing

---

## Technical Architecture

### Custom Post Types

#### Project CPT (`arsol-pfw-project`)
```php
register_post_type('arsol-pfw-project', array(
    'label' => 'Projects',
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'capability_type' => array('arsol_pfw_project', 'arsol_pfw_projects'),
    'map_meta_cap' => true,
    'supports' => array('title', 'editor', 'author', 'comments'),
    'taxonomies' => array('arsol-pfw-project-stage'),
    'rewrite' => false
));
```

#### Proposal CPT (`arsol-pfw-proposal`)
```php
register_post_type('arsol-pfw-proposal', array(
    'label' => 'Proposals',
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'capability_type' => array('arsol_pfw_proposal', 'arsol_pfw_proposals'),
    'map_meta_cap' => true,
    'supports' => array('title', 'editor', 'author'),
    'taxonomies' => array('arsol-pfw-proposal-stage'),
    'rewrite' => false
));
```

#### Request CPT (`arsol-pfw-request`)
```php
register_post_type('arsol-pfw-request', array(
    'label' => 'Requests',
    'public' => false,
    'show_ui' => true,
    'show_in_menu' => true,
    'capability_type' => array('arsol_pfw_request', 'arsol_pfw_requests'),
    'map_meta_cap' => true,
    'supports' => array('title', 'editor', 'author'),
    'taxonomies' => array('arsol-pfw-request-stage'),
    'rewrite' => false
));
```

### Custom Taxonomies

#### Project Stage (`arsol-pfw-project-stage`)
```php
register_taxonomy('arsol-pfw-project-stage', 'arsol-pfw-project', array(
    'label' => 'Project Stages',
    'hierarchical' => false,
    'public' => false,
    'show_ui' => true,
    'show_admin_column' => true,
    'meta_box_cb' => 'arsol_pfw_project_stage_meta_box',
    'capabilities' => array(
        'manage_terms' => 'arsol_pfw_manage',
        'edit_terms' => 'arsol_pfw_manage',
        'delete_terms' => 'arsol_pfw_manage',
        'assign_terms' => 'edit_arsol_pfw_projects'
    )
));
```

#### Proposal Stage (`arsol-pfw-proposal-stage`)
```php
register_taxonomy('arsol-pfw-proposal-stage', 'arsol-pfw-proposal', array(
    'label' => 'Proposal Stages',
    'hierarchical' => false,
    'public' => false,
    'show_ui' => true,
    'show_admin_column' => true,
    'meta_box_cb' => 'arsol_pfw_proposal_stage_meta_box',
    'capabilities' => array(
        'manage_terms' => 'arsol_pfw_manage',
        'edit_terms' => 'arsol_pfw_manage',
        'delete_terms' => 'arsol_pfw_manage',
        'assign_terms' => 'edit_arsol_pfw_proposals'
    )
));
```

#### Request Stage (`arsol-pfw-request-stage`)
```php
register_taxonomy('arsol-pfw-request-stage', 'arsol-pfw-request', array(
    'label' => 'Request Stages',
    'hierarchical' => false,
    'public' => false,
    'show_ui' => true,
    'show_admin_column' => true,
    'meta_box_cb' => 'arsol_pfw_request_stage_meta_box',
    'capabilities' => array(
        'manage_terms' => 'arsol_pfw_manage',
        'edit_terms' => 'arsol_pfw_manage',
        'delete_terms' => 'arsol_pfw_manage',
        'assign_terms' => 'edit_arsol_pfw_requests'
    )
));
```

#### Project Phase (`arsol-pfw-project-phase`)
Workflow taxonomy for lifecycle management:
```php
register_taxonomy('arsol-pfw-project-phase', array('arsol-pfw-project', 'arsol-pfw-proposal', 'arsol-pfw-request'), array(
    'label' => 'Project Phases',
    'hierarchical' => false,
    'public' => false,
    'show_ui' => false,
    'meta_box_cb' => false
));

// Default phases: request, proposal, project, archive
```

---

## Capability System

### Master Capability
```php
'arsol_pfw_manage' // Full plugin access - assigned to administrators and project managers
```

### Project Capabilities
```php
// Project capabilities
'edit_arsol_pfw_project'           // Edit own projects
'edit_arsol_pfw_projects'          // Edit all projects
'edit_others_arsol_pfw_projects'   // Edit others' projects
'edit_private_arsol_pfw_projects'  // Edit private projects
'edit_published_arsol_pfw_projects' // Edit published projects
'publish_arsol_pfw_projects'       // Publish projects
'read_arsol_pfw_project'           // Read projects
'read_private_arsol_pfw_projects'  // Read private projects
'delete_arsol_pfw_project'         // Delete own projects
'delete_arsol_pfw_projects'        // Delete all projects
'delete_others_arsol_pfw_projects' // Delete others' projects
'delete_private_arsol_pfw_projects' // Delete private projects
'delete_published_arsol_pfw_projects' // Delete published projects
```

### Proposal Capabilities
```php
// Proposal capabilities (same pattern as projects)
'edit_arsol_pfw_proposal'
'edit_arsol_pfw_proposals'
'edit_others_arsol_pfw_proposals'
'edit_private_arsol_pfw_proposals'
'edit_published_arsol_pfw_proposals'
'publish_arsol_pfw_proposals'
'read_arsol_pfw_proposal'
'read_private_arsol_pfw_proposals'
'delete_arsol_pfw_proposal'
'delete_arsol_pfw_proposals'
'delete_others_arsol_pfw_proposals'
'delete_private_arsol_pfw_proposals'
'delete_published_arsol_pfw_proposals'
```

### Request Capabilities
```php
// Request capabilities (same pattern as projects)
'edit_arsol_pfw_request'
'edit_arsol_pfw_requests'
'edit_others_arsol_pfw_requests'
'edit_private_arsol_pfw_requests'
'edit_published_arsol_pfw_requests'
'publish_arsol_pfw_requests'
'read_arsol_pfw_request'
'read_private_arsol_pfw_requests'
'delete_arsol_pfw_request'
'delete_arsol_pfw_requests'
'delete_others_arsol_pfw_requests'
'delete_private_arsol_pfw_requests'
'delete_published_arsol_pfw_requests'
```

### Capability Assignment
```php
/**
 * Assign capabilities to roles
 */
function arsol_pfw_assign_capabilities() {
    // Get project manager and customer roles from settings
    $manager_roles = get_option('arsol_pfw_general_settings')['project_manager_roles'] ?? array('administrator');
    $customer_roles = get_option('arsol_pfw_general_settings')['project_customer_roles'] ?? array('administrator');
    
    // Assign manager capabilities
    foreach ($manager_roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->add_cap('arsol_pfw_manage');
            
            // Add all project management capabilities
            $capabilities = array(
                'edit_arsol_pfw_projects', 'edit_others_arsol_pfw_projects',
                'publish_arsol_pfw_projects', 'read_private_arsol_pfw_projects',
                'delete_arsol_pfw_projects', 'delete_others_arsol_pfw_projects',
                'edit_arsol_pfw_proposals', 'edit_others_arsol_pfw_proposals',
                'publish_arsol_pfw_proposals', 'read_private_arsol_pfw_proposals',
                'delete_arsol_pfw_proposals', 'delete_others_arsol_pfw_proposals',
                'edit_arsol_pfw_requests', 'edit_others_arsol_pfw_requests',
                'publish_arsol_pfw_requests', 'read_private_arsol_pfw_requests',
                'delete_arsol_pfw_requests', 'delete_others_arsol_pfw_requests'
            );
            
            foreach ($capabilities as $cap) {
                $role->add_cap($cap);
            }
        }
    }
    
    // Assign customer capabilities (own entities only)
    foreach ($customer_roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $capabilities = array(
                'edit_arsol_pfw_project', 'read_arsol_pfw_project',
                'edit_arsol_pfw_proposal', 'read_arsol_pfw_proposal',
                'edit_arsol_pfw_request', 'read_arsol_pfw_request', 'publish_arsol_pfw_requests'
            );
            
            foreach ($capabilities as $cap) {
                $role->add_cap($cap);
            }
        }
    }
}
```

---

## Meta Keys Reference

### Project Meta Keys

#### Core Project Data
```php
'_project_stage'            // Current project stage (string)
'_project_phase'            // Project lifecycle phase (string)
'_project_budget'           // Project budget amount (float)
'_project_deadline'         // Project deadline (timestamp)
'_project_priority'         // Project priority (low/medium/high)
'_project_status'           // Project status (active/inactive)
'_project_progress'         // Progress percentage (0-100)
'_project_manager'          // Assigned project manager (user_id)
'_project_team_members'     // Team members array (user_ids)
'_project_description'      // Additional project description
'_project_requirements'     // Project requirements
'_project_deliverables'     // Project deliverables
'_project_notes'            // Internal project notes
```

#### Project Relationships
```php
'_source_proposal'          // Source proposal ID
'_source_request'           // Source request ID
'_parent_project'           // Parent project ID (for sub-projects)
'_child_projects'           // Child project IDs array
'_related_projects'         // Related project IDs array
```

#### Project Billing
```php
'_woocommerce_order_id'     // Associated WooCommerce order
'_woocommerce_subscription_id' // Associated subscription
'_billing_method'           // Billing method (one-time/recurring)
'_billing_frequency'        // Billing frequency (monthly/yearly)
'_billing_amount'           // Billing amount
'_billing_status'           // Billing status (active/inactive)
'_invoice_ids'              // Array of invoice IDs
```

#### Project Files & Assets
```php
'_project_files'            // Uploaded project files array
'_project_images'           // Project images array
'_project_documents'        // Project documents array
'_project_attachments'      // General attachments array
'_file_permissions'         // File access permissions
```

#### Project Tracking
```php
'_creation_date'            // Project creation timestamp
'_start_date'               // Project start timestamp
'_completion_date'          // Project completion timestamp
'_last_updated'             // Last update timestamp
'_stage_log'                // Stage change history array
'_activity_log'             // Activity log array
'_time_tracking'            // Time tracking data
```

### Proposal Meta Keys

#### Core Proposal Data
```php
'_proposal_stage'           // Current proposal stage
'_proposal_budget'          // Proposed budget amount
'_proposal_timeline'        // Proposed timeline
'_proposal_scope'           // Project scope description
'_proposal_deliverables'    // Proposed deliverables
'_proposal_terms'           // Terms and conditions
'_proposal_notes'           // Internal proposal notes
'_proposal_version'         // Proposal version number
```

#### Proposal Decision
```php
'_approval_status'          // Approval status (pending/approved/rejected)
'_approval_date'            // Approval timestamp
'_approved_by'              // User who approved (user_id)
'_rejection_date'           // Rejection timestamp
'_rejected_by'              // User who rejected (user_id)
'_rejection_reason'         // Rejection reason
'_revision_requested_date'  // Revision request timestamp
'_revision_requested_by'    // User who requested revision
'_revision_notes'           // Revision notes
```

#### Proposal Relationships
```php
'_source_request'           // Source request ID
'_converted_to_project'     // Converted project ID
'_assigned_to'              // Assigned team member (user_id)
'_proposal_author'          // Proposal author (user_id)
```

### Request Meta Keys

#### Core Request Data
```php
'_request_stage'            // Current request stage
'_request_priority'         // Request priority (low/medium/high)
'_request_type'             // Type of request
'_request_category'         // Request category
'_estimated_budget'         // Estimated budget range
'_estimated_timeline'       // Estimated timeline
'_request_details'          // Detailed request information
'_request_requirements'     // Specific requirements
'_contact_preference'       // Preferred contact method
```

#### Request Processing
```php
'_submission_date'          // Request submission timestamp
'_review_date'              // Review start timestamp
'_decision_date'            // Decision timestamp
'_converted_to_proposal'    // Converted proposal ID
'_assigned_reviewer'        // Assigned reviewer (user_id)
'_review_notes'             // Review notes
'_decision_notes'           // Decision notes
```

### User Meta Keys

#### User Project Permissions
```php
'arsol_pfw_user_permission' // Individual user permission level
'arsol_pfw_project_access'  // Array of accessible project IDs
'arsol_pfw_manager_projects' // Array of managed project IDs
'arsol_pfw_notification_preferences' // Notification settings
```

#### User Settings
```php
'arsol_pfw_dashboard_layout' // Dashboard layout preference
'arsol_pfw_email_frequency'  // Email notification frequency
'arsol_pfw_timezone'         // User timezone preference
'arsol_pfw_language'         // Language preference
```

---

## Content Defaults System

### Markdown Content Defaults

#### Project Customer Notice
**File:** `includes/ui/markdown/frontend/content-default-project-customer-notice.md`

```markdown
## Project Overview

Your project is currently in the **{project_stage}** stage. 

### Current Status
- **Stage:** {project_stage}
- **Last Updated:** {last_updated}
- **Progress:** {project_progress}%

### Next Steps
{stage_specific_content}

### Need Help?
Contact your project manager or [reach out to support](mailto:support@example.com).
```

#### Proposal Customer Notice
**File:** `includes/ui/markdown/frontend/content-default-proposal-customer-notice.md`

```markdown
## Proposal Review

Your proposal is ready for review.

### Proposal Details
- **Budget:** ${proposal_budget}
- **Timeline:** {proposal_timeline}
- **Deliverables:** {proposal_deliverables}

### Action Required
Please review the proposal and provide your decision:
- **Approve** to proceed with the project
- **Request Changes** if modifications are needed
- **Reject** if the proposal doesn't meet your needs

[Review Proposal]({proposal_link})
```

#### Request Customer Notice
**File:** `includes/ui/markdown/frontend/content-default-request-customer-notice.md`

```markdown
## Request Status Update

Your project request has been updated.

### Request Information
- **Title:** {request_title}
- **Status:** {request_stage}
- **Priority:** {request_priority}
- **Submitted:** {submission_date}

### Current Status
{stage_specific_content}

### Track Your Request
[View Request Details]({request_link})
```

### Dynamic Content Generation

#### Stage-Specific Content
```php
/**
 * Get stage-specific content for customer notices
 */
function arsol_pfw_get_stage_specific_content($entity_type, $stage, $entity_id) {
    $content_map = array(
        'project' => array(
            'active' => 'Your project is now active and work has begun. You\'ll receive regular updates on progress.',
            'in_progress' => 'Work is actively being performed on your project. Check back for progress updates.',
            'review' => 'Your project is ready for review. Please check the deliverables and provide feedback.',
            'completed' => 'Congratulations! Your project has been completed successfully.',
            'on_hold' => 'Your project is temporarily on hold. We\'ll notify you when work resumes.',
            'cancelled' => 'Your project has been cancelled. Contact support if you have questions.'
        ),
        'proposal' => array(
            'processing' => 'We\'re working on your proposal. You\'ll be notified when it\'s ready for review.',
            'ready' => 'Your proposal is ready for review. Please review and make your decision.',
            'approved' => 'Thank you for approving the proposal. Your project will be created shortly.',
            'rejected' => 'We understand the proposal wasn\'t quite right. We\'re here to help with revisions.',
            'revision' => 'We\'re making the requested changes to your proposal.'
        ),
        'request' => array(
            'pending_review' => 'Your request has been received and is awaiting review.',
            'under_review' => 'We\'re currently reviewing your request and will update you soon.',
            'approved' => 'Great news! Your request has been approved and a proposal is being prepared.',
            'rejected' => 'Unfortunately, we cannot proceed with this request at this time.',
            'on_hold' => 'Your request is on hold while we gather additional information.'
        )
    );
    
    $base_content = $content_map[$entity_type][$stage] ?? '';
    
    // Allow filtering for custom content
    return apply_filters('arsol_pfw_stage_specific_content', $base_content, $entity_type, $stage, $entity_id);
}
```

#### Shortcode Processing
```php
/**
 * Process shortcodes in markdown content
 */
function arsol_pfw_process_content_shortcodes($content, $entity_type, $entity_id) {
    $entity = get_post($entity_id);
    if (!$entity) {
        return $content;
    }
    
    // Define replacements
    $replacements = array(
        '{entity_title}' => $entity->post_title,
        '{entity_id}' => $entity_id,
        '{last_updated}' => get_the_modified_date('F j, Y', $entity_id),
        '{current_date}' => date('F j, Y'),
        '{entity_link}' => arsol_pfw_get_frontend_entity_url($entity_type, $entity_id)
    );
    
    // Add entity-specific replacements
    switch ($entity_type) {
        case 'project':
            $project_stage = wp_get_post_terms($entity_id, 'arsol-pfw-project-stage', array('fields' => 'names'));
            $replacements['{project_stage}'] = !empty($project_stage) ? $project_stage[0] : 'Unknown';
            $replacements['{project_progress}'] = get_post_meta($entity_id, '_project_progress', true) ?: '0';
            $replacements['{project_budget}'] = get_post_meta($entity_id, '_project_budget', true) ?: 'TBD';
            break;
            
        case 'proposal':
            $proposal_stage = wp_get_post_terms($entity_id, 'arsol-pfw-proposal-stage', array('fields' => 'names'));
            $replacements['{proposal_stage}'] = !empty($proposal_stage) ? $proposal_stage[0] : 'Unknown';
            $replacements['{proposal_budget}'] = get_post_meta($entity_id, '_proposal_budget', true) ?: 'TBD';
            $replacements['{proposal_timeline}'] = get_post_meta($entity_id, '_proposal_timeline', true) ?: 'TBD';
            break;
            
        case 'request':
            $request_stage = wp_get_post_terms($entity_id, 'arsol-pfw-request-stage', array('fields' => 'names'));
            $replacements['{request_stage}'] = !empty($request_stage) ? $request_stage[0] : 'Unknown';
            $replacements['{request_priority}'] = get_post_meta($entity_id, '_request_priority', true) ?: 'Normal';
            $replacements['{submission_date}'] = get_the_date('F j, Y', $entity_id);
            break;
    }
    
    // Process stage-specific content
    $stage = '';
    if ($entity_type === 'project') {
        $stage_terms = wp_get_post_terms($entity_id, 'arsol-pfw-project-stage', array('fields' => 'slugs'));
        $stage = !empty($stage_terms) ? $stage_terms[0] : '';
    } elseif ($entity_type === 'proposal') {
        $stage_terms = wp_get_post_terms($entity_id, 'arsol-pfw-proposal-stage', array('fields' => 'slugs'));
        $stage = !empty($stage_terms) ? $stage_terms[0] : '';
    } elseif ($entity_type === 'request') {
        $stage_terms = wp_get_post_terms($entity_id, 'arsol-pfw-request-stage', array('fields' => 'slugs'));
        $stage = !empty($stage_terms) ? $stage_terms[0] : '';
    }
    
    $replacements['{stage_specific_content}'] = arsol_pfw_get_stage_specific_content($entity_type, $stage, $entity_id);
    
    // Apply replacements
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);
    
    // Process WordPress shortcodes
    $content = do_shortcode($content);
    
    return $content;
}
```

---

## Conditional Visibility System

### CSS Class Pattern
```
arsol-pfw-{action}-if-{field-id}-is-{exact-value}
```

### Components
- **Prefix:** `arsol-pfw-` (required namespace)
- **Action:** `show` or `hide`
- **Field ID:** Exact HTML field ID (kebab-case with arsol-pfw prefix)
- **Value:** Exact field value match (no conversion!)

### JavaScript Implementation
```javascript
/**
 * ArsolConditionalVisibility - Global Admin Conditional Logic System
 */
window.ArsolConditionalVisibility = {
    fieldMappings: {},
    
    init: function() {
        this.discoverConditionalElements();
        this.bindFieldEvents();
        this.updateAllConditionalVisibility();
    },
    
    discoverConditionalElements: function() {
        this.fieldMappings = {};
        
        // Find all elements with conditional classes
        $('[class*="arsol-pfw-"][class*="-if-"]').each(function() {
            var $element = $(this);
            var classes = $element.attr('class').split(' ');
            
            classes.forEach(function(className) {
                if (className.includes('arsol-pfw-') && className.includes('-if-')) {
                    var match = className.match(/arsol-pfw-(show|hide)-if-(.+?)-is-(.+)$/);
                    
                    if (match) {
                        var action = match[1];
                        var fieldId = match[2];
                        var value = match[3];
                        
                        // Initialize field mapping if not exists
                        if (!ArsolConditionalVisibility.fieldMappings[fieldId]) {
                            ArsolConditionalVisibility.fieldMappings[fieldId] = [];
                        }
                        
                        // Add element to field mapping
                        ArsolConditionalVisibility.fieldMappings[fieldId].push({
                            element: $element,
                            action: action,
                            value: value,
                            className: className
                        });
                    }
                }
            });
        });
    },
    
    bindFieldEvents: function() {
        // Bind events to all discovered fields
        Object.keys(this.fieldMappings).forEach(function(fieldId) {
            var $field = $('#' + fieldId);
            
            if ($field.length) {
                $field.off('change.arsol-conditional').on('change.arsol-conditional', function() {
                    ArsolConditionalVisibility.updateConditionalVisibilityForField(fieldId);
                });
            }
        });
    },
    
    updateConditionalVisibilityForField: function(fieldId) {
        if (!this.fieldMappings[fieldId]) {
            return;
        }
        
        var $field = $('#' + fieldId);
        if (!$field.length) {
            return;
        }
        
        var currentValue = $field.val();
        
        // Process all elements for this field
        this.fieldMappings[fieldId].forEach(function(mapping) {
            var shouldShow = (currentValue === mapping.value);
            
            if (mapping.action === 'show') {
                mapping.element.toggle(shouldShow);
            } else if (mapping.action === 'hide') {
                mapping.element.toggle(!shouldShow);
            }
        });
    },
    
    updateAllConditionalVisibility: function() {
        var self = this;
        Object.keys(this.fieldMappings).forEach(function(fieldId) {
            self.updateConditionalVisibilityForField(fieldId);
        });
    },
    
    refresh: function() {
        this.discoverConditionalElements();
        this.bindFieldEvents();
        this.updateAllConditionalVisibility();
    }
};

// Initialize on document ready
$(document).ready(function() {
    ArsolConditionalVisibility.init();
});

// Refresh after AJAX calls
$(document).ajaxComplete(function() {
    setTimeout(function() {
        ArsolConditionalVisibility.refresh();
    }, 100);
});

// MutationObserver for dynamic content
if (typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function(mutations) {
        var shouldRefresh = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Check if any added nodes contain conditional elements
                for (var i = 0; i < mutation.addedNodes.length; i++) {
                    var node = mutation.addedNodes[i];
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        var $node = $(node);
                        if ($node.find('[class*="arsol-pfw-"][class*="-if-"]').length > 0 ||
                            $node.is('[class*="arsol-pfw-"][class*="-if-"]')) {
                            shouldRefresh = true;
                            break;
                        }
                    }
                }
            }
        });
        
        if (shouldRefresh) {
            setTimeout(function() {
                ArsolConditionalVisibility.refresh();
            }, 50);
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}
```

### Usage Examples

#### General Settings Implementation
```php
// Frontend Permissions Field
add_settings_field(
    'arsol-pfw-user-project-permissions',          // Field ID (kebab-case)
    __('Frontend Permissions', 'arsol-pfw'),
    array($this, 'render_select_field'),
    'arsol_pfw_general_settings',
    'arsol_projects_user_permissions',
    array(
        'field' => 'user_project_permissions',      // Database field (underscores)
        'options' => array(
            'none' => __('None', 'arsol-pfw'),
            'request' => __('Users can request projects', 'arsol-pfw'),
            'create' => __('Users can create projects', 'arsol-pfw'),
            'user_specific' => __('Set per user', 'arsol-pfw')  // Exact value
        ),
        'class' => 'arsol-pfw-frontend-permissions'
    )
);

// Conditional New User Permissions Field
add_settings_field(
    'arsol-pfw-default-user-permission',
    __('New User Permissions', 'arsol-pfw'),
    array($this, 'render_select_field'),
    'arsol_pfw_general_settings',
    'arsol_projects_user_permissions',
    array(
        'field' => 'default_user_permission',
        'options' => array(
            'none' => __('None', 'arsol-pfw'),
            'request' => __('Can request projects', 'arsol-pfw'),
            'create' => __('Can create projects', 'arsol-pfw')
        ),
        // CSS class uses exact field ID and exact value
        'class' => 'arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific arsol-pfw-new-user-permissions'
    )
);
```

#### Generated HTML
```html
<!-- Frontend Permissions Dropdown -->
<select id="arsol-pfw-user-project-permissions" name="arsol_pfw_general_settings[user_project_permissions]">
    <option value="user_specific">Set per user</option>
</select>

<!-- Conditional New User Permissions -->
<div class="arsol-pfw-show-if-arsol-pfw-user-project-permissions-is-user_specific">
    <select id="arsol-pfw-default-user-permission">
        <option value="none">None</option>
        <option value="request">Can request projects</option>
        <option value="create">Can create projects</option>
    </select>
</div>
```

---

## Database Schema

### Core Tables Used
- `wp_posts` - All CPT entities
- `wp_postmeta` - Entity meta data
- `wp_terms` - Taxonomy terms
- `wp_term_taxonomy` - Taxonomy structure
- `wp_term_relationships` - Entity-term relationships
- `wp_usermeta` - User preferences and permissions
- `wp_options` - Plugin settings

### Custom Tables (Optional)
```sql
-- Optional: Custom activity log table for performance
CREATE TABLE `wp_arsol_pfw_activity_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `entity_id` bigint(20) unsigned NOT NULL,
  `entity_type` varchar(20) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `activity_data` longtext,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entity_id` (`entity_id`),
  KEY `entity_type` (`entity_type`),
  KEY `user_id` (`user_id`),
  KEY `timestamp` (`timestamp`)
);

-- Optional: Custom notifications table
CREATE TABLE `wp_arsol_pfw_notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `entity_id` bigint(20) unsigned NOT NULL,
  `entity_type` varchar(20) NOT NULL,
  `notification_type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `created_at` (`created_at`)
);
```

---

## Performance Optimization

### Caching Strategy
```php
// Cache expensive queries
function arsol_pfw_get_user_projects_cached($user_id) {
    $cache_key = "arsol_pfw_user_projects_{$user_id}";
    $projects = wp_cache_get($cache_key);
    
    if (false === $projects) {
        $projects = get_posts(array(
            'post_type' => 'arsol-pfw-project',
            'author' => $user_id,
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        wp_cache_set($cache_key, $projects, '', 3600); // Cache for 1 hour
    }
    
    return $projects;
}

// Clear cache on relevant updates
add_action('save_post_arsol-pfw-project', function($post_id) {
    $post = get_post($post_id);
    wp_cache_delete("arsol_pfw_user_projects_{$post->post_author}");
});
```

### Database Optimization
```php
// Optimize meta queries
function arsol_pfw_get_projects_by_stage($stage, $limit = 10) {
    global $wpdb;
    
    // Use direct SQL for better performance
    $sql = $wpdb->prepare("
        SELECT p.ID, p.post_title 
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE p.post_type = 'arsol-pfw-project'
        AND p.post_status = 'publish'
        AND tt.taxonomy = 'arsol-pfw-project-stage'
        AND t.slug = %s
        ORDER BY p.post_date DESC
        LIMIT %d
    ", $stage, $limit);
    
    return $wpdb->get_results($sql);
}
```

---

## Security Implementation

### Input Validation
```php
/**
 * Validate and sanitize project data
 */
function arsol_pfw_validate_project_data($data) {
    $validated = array();
    
    // Required fields
    if (empty($data['title'])) {
        return new WP_Error('missing_title', 'Project title is required');
    }
    $validated['title'] = sanitize_text_field($data['title']);
    
    // Optional fields with validation
    if (isset($data['budget'])) {
        $budget = floatval($data['budget']);
        if ($budget < 0) {
            return new WP_Error('invalid_budget', 'Budget must be positive');
        }
        $validated['budget'] = $budget;
    }
    
    if (isset($data['deadline'])) {
        $deadline = strtotime($data['deadline']);
        if ($deadline === false || $deadline < time()) {
            return new WP_Error('invalid_deadline', 'Invalid deadline date');
        }
        $validated['deadline'] = $deadline;
    }
    
    // Sanitize description
    if (isset($data['description'])) {
        $validated['description'] = wp_kses_post($data['description']);
    }
    
    return $validated;
}
```

### Permission Checks
```php
/**
 * Check if user can perform action on entity
 */
function arsol_pfw_user_can_action($action, $entity_type, $entity_id, $user_id = 0) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    // Admin override
    if (user_can($user_id, 'arsol_pfw_manage')) {
        return true;
    }
    
    $entity = get_post($entity_id);
    if (!$entity) {
        return false;
    }
    
    // Check entity-specific permissions
    switch ($action) {
        case 'edit':
            // Own entity
            if ($entity->post_author == $user_id && user_can($user_id, "edit_arsol_pfw_{$entity_type}")) {
                return true;
            }
            // Others' entities
            return user_can($user_id, "edit_others_arsol_pfw_{$entity_type}s");
            
        case 'delete':
            // Own entity
            if ($entity->post_author == $user_id && user_can($user_id, "delete_arsol_pfw_{$entity_type}")) {
                return true;
            }
            // Others' entities
            return user_can($user_id, "delete_others_arsol_pfw_{$entity_type}s");
            
        case 'view':
            // Own entity
            if ($entity->post_author == $user_id && user_can($user_id, "read_arsol_pfw_{$entity_type}")) {
                return true;
            }
            // Private entities
            if ($entity->post_status === 'private') {
                return user_can($user_id, "read_private_arsol_pfw_{$entity_type}s");
            }
            // Public entities
            return user_can($user_id, "read_arsol_pfw_{$entity_type}");
    }
    
    return false;
}
```

---

This completes the comprehensive developer implementation reference. The system provides extensive technical documentation while maintaining WordPress standards and security best practices. 