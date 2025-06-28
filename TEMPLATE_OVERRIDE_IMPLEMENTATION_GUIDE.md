# Template Override System Implementation Guide

## Overview
This document provides a comprehensive guide for implementing the new shortcode override system in the Arsol Projects for WooCommerce plugin. Use this as a prompt to restore context and continue implementation.

## Plugin Details
- **Plugin Name**: Arsol Projects for WooCommerce (arsol-pfw)
- **Namespace**: `Arsol_Projects_For_Woo`
- **Main Class**: `Frontend_Template_Overrides`
- **Settings Option**: `arsol_projects_templates_settings`

## Current Implementation Status

### ✅ Completed
1. **New Methods Added** to `includes/classes/class-frontend-template-overrides.php`:
   - `get_shortcode_override($default_shortcode)` - Main override method
   - `is_registered_shortcode($shortcode)` - Validates shortcode using `shortcode_exists()`
   - `render_with_override($default_shortcode)` - Helper method for one-liner usage

2. **Shortcode Naming Standardization**:
   - Updated `arsol_pfw_proposal_list` to `arsol_pfw_proposals_list` (plural)
   - All shortcodes follow `arsol_pfw_*` naming convention
   - Setting names match shortcode names exactly

3. **CPT Slug Usage**:
   - Templates use `$project_type = $project->post_type` (CPT slugs)
   - Removed hardcoded project type assignments

### 🔄 Current Implementation Logic

#### New Override Method
```php
public static function get_shortcode_override($default_shortcode) {
    // Extract shortcode name from [arsol_pfw_projects_list]
    preg_match('/^\[([^\s\]]+)/', $default_shortcode, $matches);
    $shortcode_name = isset($matches[1]) ? $matches[1] : '';
    
    // Validate plugin shortcode (must start with arsol_pfw_)
    if (strpos($shortcode_name, 'arsol_pfw_') !== 0) {
        return false;
    }
    
    // Check for override in settings (setting key = shortcode name)
    $advanced_settings = get_option('arsol_projects_templates_settings', []);
    if (isset($advanced_settings[$shortcode_name])) {
        $override_shortcode = trim($advanced_settings[$shortcode_name]);
        
        // Validate override is registered using shortcode_exists()
        if (!empty($override_shortcode) && self::is_registered_shortcode($override_shortcode)) {
            return $override_shortcode;
        }
    }
    
    return false;
}
```

#### Validation Features
- Uses WordPress native `shortcode_exists()` function
- Allows ANY registered shortcode as override (Contact Form 7, Elementor, etc.)
- Validates `arsol_pfw_*` prefix for plugin shortcodes
- Debug logging when WP_DEBUG enabled

### 📋 Templates Requiring Updates

#### 1. WooCommerce MyAccount Templates
**File**: `includes/ui/templates/frontend/woocommerce/myaccount/projects.php`
- Lines 94-95: `arsol-pfw-proposals-list` → `[arsol_pfw_proposals_list]`
- Lines 101-102: `arsol-pfw-requests-list` → `[arsol_pfw_requests_list]`
- Lines 109-110: `arsol-pfw-projects-list` → `[arsol_pfw_projects_list]`

**File**: `includes/ui/templates/frontend/woocommerce/myaccount/project-overview.php`
- Lines 66-68: Use `[arsol_pfw_project_overview]` with project_id parameter

**File**: `includes/ui/templates/frontend/woocommerce/myaccount/project-view-proposal.php`
- Lines 66-68: Use `[arsol_pfw_proposal_overview]` with project_id parameter

**File**: `includes/ui/templates/frontend/woocommerce/myaccount/project-view-request.php`
- Lines 102-104: Use `[arsol_pfw_request_overview]` with project_id parameter

#### 2. Page Templates
**File**: `includes/ui/templates/frontend/page-projects-listing.php`
- Lines 51-52: Update to use new pattern
- Lines 58-59: Update to use new pattern  
- Lines 66-67: Update to use new pattern

**File**: `includes/ui/templates/frontend/page-access-denied.php`
- Lines 14-15: Use `[arsol_pfw_no_access]`

### 🔄 Required Pattern Changes

#### Current Old Pattern
```php
if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('arsol-pfw-proposals-list')) {
    echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('arsol-pfw-proposals-list');
} else {
    echo do_shortcode('[arsol_pfw_proposals_list]');
}
```

#### New Preferred Pattern (Easy Reading)
```php
$override = \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_shortcode_override('[arsol_pfw_proposals_list]');
if ($override) {
    echo do_shortcode($override);
} else {
    echo do_shortcode('[arsol_pfw_proposals_list]');
}
```

### 📝 Registered Shortcodes Map

#### Main Template Shortcodes
- `arsol_pfw_project_overview` → `arsol_pfw_project_overview` setting
- `arsol_pfw_proposal_overview` → `arsol_pfw_proposal_overview` setting
- `arsol_pfw_request_overview` → `arsol_pfw_request_overview` setting
- `arsol_pfw_project_form` → `arsol_pfw_project_form` setting
- `arsol_pfw_request_form` → `arsol_pfw_request_form` setting
- `arsol_pfw_proposal_form` → `arsol_pfw_proposal_form` setting
- `arsol_pfw_projects_list` → `arsol_pfw_projects_list` setting
- `arsol_pfw_proposals_list` → `arsol_pfw_proposals_list` setting
- `arsol_pfw_requests_list` → `arsol_pfw_requests_list` setting
- `arsol_pfw_no_access` → `arsol_pfw_no_access` setting

### 🎯 Implementation Instructions

#### Phase 1: Update Template Files
1. Replace all `has_template_override()` and `get_template_override()` calls
2. Use the new `get_shortcode_override()` with if/else pattern
3. Update shortcode names to use `arsol_pfw_*` format
4. Add proper project_id parameters where needed

### 🔧 Key Technical Details

#### CPT Slugs Used
- `arsol-project` (main projects)
- `arsol-pfw-proposal` (proposals)
- `arsol-pfw-request` (requests)

#### Template Variable Pattern
```php
$project_type = $project->post_type; // Uses CPT slug directly
```

### 🚨 Important Notes

1. **No Backwards Compatibility**: This is a new plugin, legacy support removed
2. **Shortcode Validation**: Only registered shortcodes allowed as overrides
3. **Prefix Validation**: Plugin shortcodes must start with `arsol_pfw_`
4. **Setting Names**: Must match shortcode names exactly
5. **Debug Support**: Logs invalid shortcodes when WP_DEBUG enabled

## Context Restoration Prompt
"I need to update WordPress plugin templates to use a new shortcode override system. The plugin is 'Arsol Projects for WooCommerce' with namespace 'Arsol_Projects_For_Woo'. I have implemented a new get_shortcode_override() method in the Frontend_Template_Overrides class that validates shortcodes using shortcode_exists() and allows any registered shortcode as an override. I need to update template files to use the new pattern: get override, if exists use it, else use default shortcode. Please update the templates listed in the guide above using the if/else pattern for easy reading."
