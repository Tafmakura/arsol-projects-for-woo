# Project-Tied Proposal Implementation Strategy

## Overview
This document outlines the complete implementation strategy for project-tied proposals in the Arsol Projects for WooCommerce plugin. Project-tied proposals are proposals created from within a project edit screen that have locked fields and restricted behavior.

## Core Requirements

### Functional Requirements
1. **Creation Method**: Only creatable from project edit screen via "Create Proposal" button
2. **Field Restrictions**: Customer, Project Lead, and Cost Proposal Type are locked to parent project values
3. **Title Behavior**: Custom editable titles with pre-populated defaults
4. **Status Workflow**: Identical to regular proposals (no restrictions)
5. **Conversion Restriction**: Cannot convert to projects (View Project button instead)
6. **Deletion Protection**: Parent projects cannot be deleted if tied proposals exist
7. **No Backward Compatibility**: Existing proposals remain independent

### Technical Requirements
1. **WordPress/WooCommerce Native**: Use standard WP/WC patterns and hooks
2. **URL Parameter Detection**: `parent_project=xxxx` in new proposal URL
3. **Meta Storage**: Store parent project ID in proposal meta
4. **Nonce Security**: All operations must be nonce-protected
5. **User Permissions**: Respect existing capability checks

## Implementation Details

### 1. Title & Header Display

#### Title Field Behavior
- **Keep title field editable** (do not hide)
- **Pre-populate with default**: "Proposal for [Project Name]"
- **Allow custom titles** for flexibility

#### Header Display Pattern
```php
// Location: includes/ui/components/admin/section-edit-proposal-header.php
<h2><?php printf(__('Proposal #%d details', 'arsol-pfw'), $proposal_id); ?></h2>

<?php if ($is_project_tied): ?>
    <p class="order_number">
        <?php printf(__('For Project: %s', 'arsol-pfw'), esc_html($parent_project->post_title)); ?>
    </p>
<?php endif; ?>
```

#### Header Placement
- Display "For Project: [Parent Project Title]" **after the title**
- **Static text only** (no links, not clickable)
- Use existing `order_number` class for consistent styling

### 2. Locked Fields Implementation

#### Fields to Lock
1. **Customer Field**: Locked to parent project customer
2. **Project Lead Field**: Locked to parent project lead
3. **Cost Proposal Type**: Locked to "Quotation" type

#### Locking Pattern (WordPress/WooCommerce Native)
```php
// Use existing arsol-disabled-select class pattern
<select name="field_name" class="arsol-disabled-select" disabled>
    <option value="locked_value" selected><?php echo esc_html($display_text); ?></option>
</select>
<input type="hidden" name="field_name" value="<?php echo esc_attr($actual_value); ?>">
```

#### Visual Styling
- **No visual indicators** (clean UI)
- Use existing CSS: `background-color: #f7f7f7; color: #666; cursor: not-allowed`
- Fields appear disabled but maintain professional appearance

### 3. Action Button Replacement

#### Convert Button Replacement
- **Remove**: "Convert to Project" button for project-tied proposals
- **Add**: "View Project" button with same positioning and styling

#### View Project Button Implementation
```php
// Button HTML
<input type="button" class="button button-secondary arsol-view-project" 
       data-url="<?php echo esc_url($view_project_url); ?>" 
       data-message="<?php echo esc_attr($confirm_message); ?>" 
       value="<?php esc_attr_e('View Project', 'arsol-pfw'); ?>">
```

#### JavaScript Behavior
```javascript
// Same pattern as arsol-confirm-conversion
$(document).on('click', '.arsol-view-project', function(e) {
    // 1. HTML5 form validation
    // 2. Confirmation alert
    // 3. Add hidden input for redirect
    // 4. Submit form
});
```

#### Server-Side Handling
```php
// In save_proposal_details method
if (isset($_POST['arsol_view_after_save']) && !empty($_POST['arsol_view_after_save'])) {
    wp_redirect(esc_url_raw($_POST['arsol_view_after_save']));
    exit;
}
```

### 4. Project Deletion Protection

#### Protection Logic
```php
// In project deletion hook
public function prevent_project_deletion_with_proposals($post_id) {
    if (get_post_type($post_id) !== 'arsol-pfw-project') return;
    
    $tied_proposals = get_posts(array(
        'post_type' => 'arsol-pfw-proposal',
        'meta_key' => '_arsol_parent_project_id',
        'meta_value' => $post_id,
        'post_status' => 'any',
        'numberposts' => 1
    ));
    
    if (!empty($tied_proposals)) {
        wp_die(__('Cannot delete project with tied proposals.', 'arsol-pfw'));
    }
}
```

#### Error Handling
- **WordPress native**: Use `wp_die()` for deletion prevention
- **Error message**: "Cannot delete project with tied proposals. Please delete or untie proposals first."
- **Back link**: Automatic WordPress back button

### 5. Quotation Behavior Implementation

#### Cost Type Locking
- **Always lock to "Quotation"** for project-tied proposals
- **Show conditional fields** that appear for Quotation type
- **Maintain existing logic** for quotation-specific functionality

#### Implementation Pattern
```php
// In proposal admin class
if ($is_project_tied) {
    $cost_type = 'quotation'; // Force quotation type
    $cost_type_locked = true;
} else {
    $cost_type = get_post_meta($post->ID, '_arsol_cost_type', true);
    $cost_type_locked = false;
}
```

### 6. Bulk Operations Handling

#### List Table Modifications
- **Add Project Column**: Show "#XXXX" project ID for tied proposals
- **Prevent bulk editing** of Customer/Project Lead fields for project-tied proposals
- **Allow other bulk operations** (status changes, deletion, etc.)

#### Column Implementation
```php
// In proposal list table class
public function add_project_column($columns) {
    $columns['project'] = __('Project', 'arsol-pfw');
    return $columns;
}

public function display_project_column($column, $post_id) {
    if ($column === 'project') {
        $parent_id = get_post_meta($post_id, '_arsol_parent_project_id', true);
        if ($parent_id) {
            echo '#' . $parent_id;
        } else {
            echo '—';
        }
    }
}
```

#### Bulk Edit Restrictions
```php
// In bulk edit functionality
public function restrict_bulk_edit_fields($post_ids) {
    foreach ($post_ids as $post_id) {
        $parent_id = get_post_meta($post_id, '_arsol_parent_project_id', true);
        if ($parent_id) {
            // Skip customer/lead updates for this proposal
            continue;
        }
    }
}
```

## File Modifications Required

### 1. Proposal Admin Class
**File**: `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-proposal.php`

#### New Methods to Add:
```php
/**
 * Handle creation of new proposal from project
 */
public function handle_new_proposal_from_project()

/**
 * Display creation success message
 */
public function display_creation_success_message()

/**
 * Check if proposal is project-tied
 */
public function is_project_tied_proposal($post_id)

/**
 * Get parent project data
 */
public function get_parent_project_data($post_id)
```

#### Constructor Modifications:
```php
// Add to constructor
add_action('load-post-new.php', array($this, 'handle_new_proposal_from_project'));
add_action('admin_notices', array($this, 'display_creation_success_message'));
```

### 2. Header Template
**File**: `includes/ui/components/admin/section-edit-proposal-header.php`

#### Modifications:
- Add conditional "For Project:" display
- Use existing styling patterns
- Static text implementation

### 3. Column Templates
**Files**: 
- `includes/ui/components/admin/section-edit-proposal-header-column-1.php`
- `includes/ui/components/admin/section-edit-proposal-header-column-2.php`

#### Modifications:
- Add locked field detection
- Implement disabled select patterns
- Add hidden input maintenance

### 4. JavaScript File
**File**: `assets/js/arsol-pfw-admin-proposal.js`

#### New Handler:
```javascript
// Add arsol-view-project handler
$(document).on('click', '.arsol-view-project', function(e) {
    // Implementation following arsol-confirm-conversion pattern
});
```

### 5. Project Admin Class
**File**: `includes/custom-post-types/project/class-project-cpt-admin-project.php`

#### New Method:
```php
/**
 * Prevent project deletion with tied proposals
 */
public function prevent_project_deletion_with_proposals($post_id)
```

#### Hook Addition:
```php
// Add to constructor
add_action('before_delete_post', array($this, 'prevent_project_deletion_with_proposals'));
```

### 6. List Table Class
**File**: `includes/custom-post-types/project-proposal/class-project-proposal-cpt-admin-list-table.php`

#### New Methods:
```php
/**
 * Add project column to list table
 */
public function add_project_column($columns)

/**
 * Display project column content
 */
public function display_project_column($column, $post_id)

/**
 * Restrict bulk edit for project-tied proposals
 */
public function restrict_bulk_edit_fields($post_ids)
```

## Implementation Sequence

### Phase 1: Core Detection & Storage
1. Add `handle_new_proposal_from_project()` method
2. Implement parent project ID storage in meta
3. Add `is_project_tied_proposal()` helper method
4. Test URL parameter detection and meta storage

### Phase 2: UI Modifications
1. Update header template with "For Project:" display
2. Implement locked field patterns in column templates
3. Add hidden input maintenance for locked fields
4. Test field locking and form submission

### Phase 3: Action Button Replacement
1. Add "View Project" button logic
2. Implement JavaScript handler for view project
3. Add server-side redirect handling
4. Test save-and-redirect workflow

### Phase 4: Protection & Restrictions
1. Add project deletion protection
2. Implement bulk operation restrictions
3. Add project column to list table
4. Test all protection mechanisms

### Phase 5: Integration & Testing
1. Test complete workflow from project to proposal
2. Verify all locked fields work correctly
3. Test edge cases and error conditions
4. Performance testing with multiple proposals

## Testing Scenarios

### Happy Path Testing
1. **Create Project-Tied Proposal**: From project edit screen
2. **Verify Locked Fields**: Customer, Lead, Cost Type disabled but functional
3. **Test Title Behavior**: Custom titles work with project display
4. **Test View Project**: Save and redirect to parent project
5. **Test Status Changes**: All status workflows function normally

### Edge Case Testing
1. **Invalid Parent Project**: Non-existent project ID in URL
2. **Permission Checks**: Users without proper capabilities
3. **Nonce Validation**: Tampered or expired nonces
4. **Concurrent Editing**: Multiple users editing same proposal
5. **Project Deletion**: Attempt to delete project with tied proposals

### Bulk Operations Testing
1. **Bulk Status Changes**: Multiple project-tied proposals
2. **Bulk Edit Restrictions**: Customer/Lead fields protected
3. **Mixed Bulk Operations**: Regular and project-tied proposals together
4. **List Table Display**: Project column shows correct IDs

## Security Considerations

### Nonce Protection
- All form submissions must include valid nonces
- URL parameters must be nonce-protected
- AJAX operations require nonce validation

### Capability Checks
- Respect existing proposal editing capabilities
- Parent project access determines proposal access
- No privilege escalation through proposal creation

### Data Validation
- Sanitize all input data
- Validate parent project exists and is accessible
- Escape all output data

### SQL Injection Prevention
- Use WordPress meta query functions
- Prepared statements for custom queries
- Avoid direct SQL where possible

## Performance Considerations

### Database Queries
- Use efficient meta queries for parent project lookups
- Cache parent project data when possible
- Minimize database calls in list table columns

### JavaScript Loading
- Only load additional JS on proposal admin screens
- Use existing arsol-pfw-admin-proposal.js file
- Avoid jQuery conflicts

### Template Loading
- Leverage existing template structure
- Minimize additional template files
- Use WordPress template hierarchy

## Backward Compatibility

### Existing Proposals
- **No changes** to existing proposal behavior
- **No migration** required for existing data
- **Existing workflows** remain unchanged

### Plugin Updates
- **Graceful degradation** if parent project deleted externally
- **Safe activation** on existing installations
- **No breaking changes** to existing APIs

## Future Considerations

### Potential Enhancements
1. **Proposal Templates**: Pre-populate content from project
2. **Automatic Numbering**: Sequential proposal numbers per project
3. **Project Dashboard**: Show all tied proposals in project edit screen
4. **Bulk Project Operations**: Create multiple proposals from project list

### Migration Path
- Current implementation allows for future enhancements
- Meta storage structure supports additional fields
- Hook system allows for extension without core changes

## Documentation Requirements

### User Documentation
1. **How to create project-tied proposals**
2. **Understanding locked fields behavior**
3. **Managing project-tied proposals**
4. **Bulk operations limitations**

### Developer Documentation
1. **Hook reference for project-tied proposals**
2. **Meta field documentation**
3. **Template modification guide**
4. **Extension development patterns**

---

**Document Version**: 1.0  
**Last Updated**: Current Implementation  
**Status**: Ready for Implementation
