# Implementation Guide: Project-Tied Request Functionality

## AI Prompt for Implementation

This document serves as a comprehensive prompt for implementing project-tied request functionality in a WordPress plugin. Use this as a complete specification for adding "Request Proposal" buttons to active projects that create requests tied to parent projects.

## Project Context

**Plugin**: Arsol Projects for WooCommerce  
**WordPress Version**: 6.0+  
**WooCommerce Version**: 8.0+  
**PHP Version**: 8.0+  

## Feature Requirements

### Core Functionality
Implement a "Request Proposal" button on active project pages that allows customers to create project requests tied to the parent project. These project-tied requests maintain a relationship with their parent project and display this relationship in both frontend and admin interfaces.

### User Stories
1. **As a customer**, I want to request additional proposals for my existing projects
2. **As an admin**, I want to see which requests are tied to existing projects
3. **As a customer**, I want the request form to clearly indicate which project I'm requesting a proposal for

## Implementation Specification

### Step 1: Frontend Button Implementation

**Target File**: `includes/ui/components/frontend/section-project-sidebar-active.php`

**Location**: Add before the `arsol_pfw_sidebar_after` hook

**Code to Add**:
```php
<?php
// Add Request Proposal button
$request_proposal_url = wc_get_account_endpoint_url('project-request') . '?parent_project=' . $project_id;
?>

<div class="arsol-pfw-project-action">
    <a href="<?php echo esc_url($request_proposal_url); ?>" class="brxe-button bricks-button button-primary">
        <?php esc_html_e('Request Proposal', 'arsol-pfw'); ?>
    </a>
</div>
```

**Requirements**:
- Use existing button classes: `brxe-button bricks-button button-primary`
- Generate URL using WooCommerce account endpoint
- Pass parent project ID as `parent_project` parameter
- Only visible to customers (existing access control applies)

### Step 2: Enhanced Request Form

**Target File**: `includes/ui/components/frontend/form-project-create-request.php`

**Modifications Required**:

1. **Add Parent Project Detection Logic** (after line 12):
```php
// Check for parent project parameter
$parent_project_id = 0;
$parent_project_title = '';

if (!$is_edit && isset($_GET['parent_project'])) {
    $parent_project_id = absint($_GET['parent_project']);
    if ($parent_project_id) {
        $parent_project = get_post($parent_project_id);
        if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
            $parent_project_title = $parent_project->post_title;
        } else {
            $parent_project_id = 0; // Invalid parent project
        }
    }
}

// If editing, check if this is a project-tied request
if ($is_edit) {
    $parent_project_id = get_post_meta($post->ID, '_arsol_pfw_parent_project_id', true);
    if ($parent_project_id) {
        $parent_project = get_post($parent_project_id);
        if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
            $parent_project_title = $parent_project->post_title;
        }
    }
}
```

2. **Update Form Title Logic** (replace existing form title logic):
```php
// Determine form title
$form_title = '';
if ($is_edit) {
    $form_title = __('Edit Your Project Request', 'arsol-pfw');
} elseif ($parent_project_id && $parent_project_title) {
    $form_title = sprintf(__('Request Proposal for %s', 'arsol-pfw'), $parent_project_title);
} else {
    $form_title = __('Submit a Project Request', 'arsol-pfw');
}
```

3. **Update Form Header** (replace existing h4 tag):
```php
<h4><?php echo esc_html($form_title); ?></h4>
```

4. **Add Hidden Field** (after existing hidden fields):
```php
<?php if ($parent_project_id) : ?>
    <input type="hidden" name="parent_project_id" value="<?php echo esc_attr($parent_project_id); ?>">
<?php endif; ?>
```

### Step 3: Backend Handler Updates

**Target File**: `includes/workflow/class-workflow-handler.php`

**Method to Modify**: `update_request_meta($post_id, $data)`

**Add at Beginning of Method**:
```php
// Save parent project if provided
if (isset($data['parent_project_id']) && !empty($data['parent_project_id'])) {
    $parent_project_id = absint($data['parent_project_id']);
    if ($parent_project_id) {
        update_post_meta($post_id, '_arsol_pfw_parent_project_id', $parent_project_id);
    }
}
```

**Meta Key**: `_arsol_pfw_parent_project_id`  
**Data Type**: Integer (WordPress post ID)

### Step 4: Admin Interface Updates

**Target File**: `includes/ui/components/admin/section-edit-request-header.php`

**Modifications Required**:

1. **Add Parent Project Detection** (after line 30):
```php
// Check for parent project
$parent_project_id = get_post_meta($request_id, '_arsol_pfw_parent_project_id', true);
$parent_project_data = null;
if ($parent_project_id) {
    $parent_project = get_post($parent_project_id);
    if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
        $parent_project_data = array(
            'id' => $parent_project_id,
            'title' => $parent_project->post_title
        );
    }
}
```

2. **Add Parent Project Display** (after the title display, around line 50):
```php
<?php
// Show parent project if this is a project-tied request
if ($parent_project_data) {
    echo '<p class="order_number">';
    printf(__('Parent Project: %s', 'arsol-pfw'), esc_html($parent_project_data['title']));
    echo '</p>';
}
?>
```

## Technical Specifications

### Database Schema
- **Meta Key**: `_arsol_pfw_parent_project_id`
- **Meta Value**: Integer (parent project post ID)
- **Post Type**: `arsol-pfw-request`
- **Validation**: Must reference existing `arsol-pfw-project` post

### URL Structure
- **Endpoint**: WooCommerce account endpoint `project-request`
- **Parameter**: `?parent_project={project_id}`
- **Example**: `/my-account/project-request/?parent_project=123`

### CSS Classes
- **Button**: `brxe-button bricks-button button-primary`
- **Container**: `arsol-pfw-project-action`
- **Admin Display**: `order_number` (existing WordPress admin styling)

## User Experience Flow

### Project-Tied Request Creation
1. Customer views active project overview
2. Clicks "Request Proposal" button
3. Redirects to request form with parent project context
4. Form title shows "Request Proposal for [Project Name]"
5. Customer completes form (no prepopulation)
6. Submits form → parent project relationship saved
7. Normal redirect to request view with update/cancel options

### Admin Experience
1. Admin receives new request notification
2. Opens request in admin interface
3. Sees "Parent Project: [Project Name]" below request title
4. Can process request with full context of parent project

## Validation Requirements

### Frontend Validation
- Verify `parent_project` parameter is numeric
- Validate parent project exists and is published
- Ensure parent project is correct post type
- Graceful fallback for invalid parent projects

### Backend Validation
- Server-side validation of parent project ID
- Sanitize all form inputs using WordPress functions
- Verify parent project exists before saving relationship
- Handle edge cases (deleted parent projects, etc.)

## Error Handling

### Invalid Parent Project
- Log warning but don't break functionality
- Fall back to normal request form behavior
- No error messages to user (seamless experience)

### Missing Permissions
- Respect existing access control mechanisms
- Graceful degradation if permissions change
- Maintain data integrity

## Testing Checklist

### Functional Testing
- [ ] "Request Proposal" button appears on active projects
- [ ] Button links to correct URL with parent project parameter
- [ ] Form title changes to "Request Proposal for [Project Name]"
- [ ] Hidden field captures parent project ID
- [ ] Form submission saves parent project relationship
- [ ] Admin interface displays parent project information
- [ ] Normal request flow still works without parent project

### Edge Case Testing
- [ ] Invalid parent project ID in URL
- [ ] Non-existent parent project
- [ ] Deleted parent project after request creation
- [ ] User permission changes
- [ ] Form validation with parent project context

### Security Testing
- [ ] SQL injection prevention in parent project ID
- [ ] XSS prevention in parent project title display
- [ ] CSRF protection via WordPress nonces
- [ ] Access control enforcement

## Code Quality Requirements

### WordPress Standards
- Follow WordPress Coding Standards
- Use WordPress sanitization functions (`absint`, `esc_html`, etc.)
- Implement proper nonce verification
- Use WordPress hooks and filters appropriately

### Plugin Integration
- Maintain existing plugin architecture
- Use established naming conventions
- Follow existing meta key patterns (`_arsol_pfw_*`)
- Preserve backward compatibility

### Performance
- Minimize additional database queries
- Use efficient WordPress functions
- Cache parent project data when possible
- Avoid N+1 query problems

## Success Criteria

### Functional Success
1. Customers can successfully create project-tied requests
2. Admin interface clearly shows parent project relationships
3. Normal request functionality remains unchanged
4. All validation and error handling works correctly

### Technical Success
1. Code follows WordPress and plugin standards
2. No performance degradation
3. Proper security implementation
4. Clean, maintainable code structure

### User Experience Success
1. Intuitive button placement and styling
2. Clear form title indicates context
3. Seamless flow from project to request
4. Admin has full context for processing requests

## Implementation Notes

### Development Order
1. Start with frontend button implementation
2. Add form enhancements and parent project detection
3. Implement backend handler updates
4. Add admin interface updates
5. Test thoroughly with all scenarios

### Deployment Considerations
- Test on staging environment first
- Verify backward compatibility with existing requests
- Monitor for any performance impacts
- Document changes for support team

This implementation guide provides everything needed to successfully add project-tied request functionality to the Arsol Projects for WooCommerce plugin while maintaining code quality, security, and user experience standards. 