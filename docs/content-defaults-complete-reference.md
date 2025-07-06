# Content Defaults System - Complete Reference

This document provides a comprehensive guide to the Content Defaults System in Arsol Projects for WooCommerce, covering architecture, usage, customization, and troubleshooting.

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [File Structure](#file-structure)
4. [Two-Layer System](#two-layer-system)
5. [Admin Interface](#admin-interface)
6. [Template Integration](#template-integration)
7. [Customization Guide](#customization-guide)
8. [Developer API](#developer-api)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)
11. [Migration Notes](#migration-notes)

## System Overview

The Content Defaults System provides centralized management of all template content through markdown files while maintaining the flexibility for administrators to customize through the WordPress admin interface.

### Key Features

- **File-based defaults**: Professional content stored in markdown files
- **Admin customization**: Override any default through WordPress admin
- **Template simplification**: No hardcoded content in PHP templates
- **Markdown support**: Rich formatting with markdown syntax
- **Fallback system**: Robust error handling and content delivery
- **Version control**: All defaults tracked in git

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                   Content Defaults System                  │
├─────────────────────────────────────────────────────────────┤
│  Layer 1: Markdown Files (Plugin Defaults)                 │
│  ├── content-active-empty.md                               │
│  ├── content-proposal-empty.md                             │
│  ├── content-request-on-hold.md                            │
│  └── content-request-under-review.md                       │
├─────────────────────────────────────────────────────────────┤
│  Layer 2: Database Overrides (User Customizations)         │
│  └── arsol_projects_advanced_settings option              │
├─────────────────────────────────────────────────────────────┤
│  Layer 3: Hardcoded Fallbacks (Emergency Only)            │
│  └── Minimal text in Setup_Defaults class                 │
├─────────────────────────────────────────────────────────────┤
│  Frontend Templates (HTML Structure Only)                  │
│  ├── section-project-overview-empty.php                   │
│  ├── section-project-content-proposal.php                 │
│  ├── section-project-content-request-on-hold.php          │
│  └── section-project-content-request-under-review.php     │
└─────────────────────────────────────────────────────────────┘
```

### Data Flow

```
User Request → Template → Setup_Defaults::get_effective_default_message()
                            ↓
                    Check Database Override
                            ↓
                    Load Markdown File (if no override)
                            ↓
                    Fallback to Hardcoded (if file missing)
                            ↓
                    Return Content → Template → Frontend
```

## File Structure

```
arsol-pfw/
├── includes/
│   ├── classes/
│   │   ├── class-admin-settings-advanced.php    # Admin interface
│   │   └── class-admin-setup-defaults.php       # Core system logic
│   └── ui/
│       ├── components/frontend/                 # Template files
│       │   ├── section-project-overview-empty.php
│       │   ├── section-project-content-proposal.php
│       │   ├── section-project-content-request-on-hold.php
│       │   └── section-project-content-request-under-review.php
│       └── markdown/
│           ├── frontend/                        # Content files
│           │   ├── content-active-empty.md
│           │   ├── content-proposal-empty.md
│           │   ├── content-request-on-hold.md
│           │   └── content-request-under-review.md
│           └── README.md                        # Documentation
└── docs/
    └── content-defaults-complete-reference.md   # This file
```

## Two-Layer System

### Layer 1: Markdown Files (Plugin Defaults)

**Location**: `includes/ui/markdown/frontend/`

**Purpose**: Professional, version-controlled default content

**Characteristics**:
- Part of plugin distribution
- Always available
- Version controlled with git
- Professional quality content
- Rich markdown formatting

**File Mappings**:
- `content-active-empty.md` → Active Project (Empty)
- `content-proposal-empty.md` → Project Proposal (Empty)
- `content-request-on-hold.md` → Project Request (On-Hold)
- `content-request-under-review.md` → Project Request (Under Review)

### Layer 2: Database Overrides (User Customizations)

**Location**: WordPress database (`arsol_projects_advanced_settings` option)

**Purpose**: Site-specific customizations

**Characteristics**:
- Optional overrides
- Stored only when customized
- Takes precedence over markdown files
- Managed through admin interface
- Not version controlled

**Database Keys**:
- `project_overview_message`
- `project_proposals_message`
- `project_request_on_hold_message`
- `project_request_under_review_message`

### Layer 3: Hardcoded Fallbacks (Emergency Only)

**Location**: `Setup_Defaults::get_hardcoded_fallback()`

**Purpose**: Absolute last resort

**Characteristics**:
- Minimal text content
- Only used if markdown files missing
- Should rarely be needed
- Ensures system never breaks

## Admin Interface

### Location

**WordPress Admin**: Settings → Arsol Projects → Advanced Settings → Default Messages

### Features

**Placeholder System**:
- Empty fields show markdown content as placeholders
- Immediate preview of default content
- No extra buttons or JavaScript needed

**Markdown Support**:
- Monospace font for editing
- Markdown reference guide
- Rich formatting capabilities

**Field Organization**:
1. Active Project (Empty)
2. Project Proposal (Empty)
3. Project Request (On-Hold)
4. Project Request (Under Review)

### Usage Instructions

1. **View Defaults**: Empty fields show current defaults as placeholders
2. **Customize Content**: Type in field to override default
3. **Reset to Default**: Clear field content to restore default
4. **Save Changes**: Click "Save Changes" to store customizations

## Template Integration

### Template Structure

Templates contain only HTML structure and PHP logic:

```php
<?php
// Get content from system
$default_message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message('key');

// Display with HTML structure
?>
<div class="arsol-pfw-default-message">
    <?php echo wp_kses_post(wpautop($default_message)); ?>
</div>
```

### CSS Classes

**Primary Classes** (Generic):
- `arsol-pfw-request-content` - All request content containers
- `arsol-pfw-default-message` - All markdown content containers
- `arsol-pfw-form-section` - All form sections
- `arsol-pfw-request-details` - All request details sections

**Secondary Classes** (Specific):
- `arsol-pfw-on-hold-content` - On-hold specific styling
- `arsol-pfw-under-review-content` - Under-review specific styling
- `arsol-pfw-on-hold-message` - On-hold message styling
- `arsol-pfw-under-review-message` - Under-review message styling

### Markdown Processing

Content is processed through:
1. `wp_kses_post()` - Security filtering
2. `wpautop()` - Paragraph formatting
3. WordPress markdown support (if available)

## Customization Guide

### For Developers

**Edit Markdown Files**:
```bash
# Navigate to markdown directory
cd includes/ui/markdown/frontend/

# Edit default content
vim content-active-empty.md
vim content-proposal-empty.md
vim content-request-on-hold.md
vim content-request-under-review.md

# Commit changes
git add .
git commit -m "Update default messages"
```

**Markdown Syntax**:
```markdown
# Heading 1
## Heading 2
### Heading 3

**Bold text**
*Italic text*
`Code text`

- List item 1
- List item 2

1. Numbered item 1
2. Numbered item 2

[Link text](https://example.com)

> Blockquote text

---

Horizontal rule
```

### For Administrators

**Through WordPress Admin**:
1. Go to Settings → Arsol Projects → Advanced Settings
2. Scroll to "Default Messages" section
3. Edit desired fields
4. Save changes

**Best Practices**:
- Keep content professional and clear
- Use markdown for formatting
- Test changes on staging first
- Document customizations

### For Theme Developers

**CSS Customization**:
```css
/* Target all default messages */
.arsol-pfw-default-message {
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
    padding: 1rem;
}

/* Target specific status messages */
.arsol-pfw-on-hold-message {
    border-left-color: #ffb900;
}

.arsol-pfw-under-review-message {
    border-left-color: #00a0d2;
}

/* Markdown elements */
.arsol-pfw-default-message h1,
.arsol-pfw-default-message h2,
.arsol-pfw-default-message h3 {
    color: #23282d;
    margin-top: 0;
}

.arsol-pfw-default-message ul,
.arsol-pfw-default-message ol {
    padding-left: 1.5rem;
}

.arsol-pfw-default-message code {
    background: #f1f1f1;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
}
```

## Developer API

### Core Methods

**Get Effective Message**:
```php
// Get the effective message (user override or default)
$message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message($key);
```

**Get All Messages**:
```php
// Get all effective messages
$messages = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_all_effective_default_messages();
```

**Load Markdown Content**:
```php
// Load content from markdown file
$content = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::load_markdown_content($key, $filename);
```

**Debug File Status**:
```php
// Debug markdown files (admin only)
$debug = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::debug_markdown_files();
```

### Available Keys

- `project_overview_message` - Active projects with no content
- `project_proposals_message` - Proposals with no content
- `project_request_on_hold_message` - Requests on hold
- `project_request_under_review_message` - Requests under review

### Hooks and Filters

**Before Content Display**:
```php
// Hook before empty state display
add_action('arsol_projects_before_empty_state', function($project_id) {
    // Custom logic before showing empty state
});
```

**After Content Display**:
```php
// Hook after empty state display
add_action('arsol_projects_after_empty_state', function($project_id) {
    // Custom logic after showing empty state
});
```

**Filter Default Content**:
```php
// Filter default message content
add_filter('arsol_pfw_default_message_content', function($content, $key, $project_id) {
    // Modify content based on context
    return $content;
}, 10, 3);
```

## Troubleshooting

### Common Issues

**Issue**: Default content not displaying
**Solution**: 
1. Check markdown files exist in `includes/ui/markdown/frontend/`
2. Verify file permissions are readable
3. Use debug method to check file status

**Issue**: Customizations not saving
**Solution**:
1. Check user has `manage_options` capability
2. Verify WordPress database connection
3. Check for plugin conflicts

**Issue**: Markdown not rendering
**Solution**:
1. Ensure `wpautop()` is being called
2. Check for theme conflicts with markdown
3. Verify `wp_kses_post()` isn't stripping content

**Issue**: CSS styling not applied
**Solution**:
1. Check CSS classes are present in HTML
2. Verify theme doesn't override styles
3. Use browser inspector to debug

### Debug Tools

**File Status Check**:
```php
// For administrators only
$debug = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::debug_markdown_files();
var_dump($debug);
```

**Database Check**:
```php
// Check stored customizations
$settings = get_option('arsol_projects_advanced_settings', array());
var_dump($settings);
```

**Template Debug**:
```php
// In template files
$message = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_default_message($key);
error_log("Default message for {$key}: " . $message);
```

### Log Locations

- **WordPress Debug Log**: `wp-content/debug.log`
- **Plugin Logs**: WooCommerce → Status → Logs → arsol-pfw
- **Server Logs**: Check hosting provider documentation

## Best Practices

### For Developers

1. **Version Control**: Always commit markdown file changes
2. **Testing**: Test on staging before production
3. **Documentation**: Document any customizations
4. **Backup**: Backup before major changes
5. **Standards**: Follow WordPress coding standards

### For Content Creators

1. **Professional Tone**: Keep content professional and clear
2. **User-Focused**: Write from user's perspective
3. **Actionable**: Include clear next steps
4. **Consistent**: Maintain consistent tone across messages
5. **Accessible**: Use clear, simple language

### For Administrators

1. **Staging First**: Test customizations on staging
2. **Document Changes**: Keep record of customizations
3. **Regular Review**: Review content periodically
4. **User Feedback**: Gather feedback from users
5. **Backup Settings**: Export settings before changes

### For Theme Developers

1. **Responsive Design**: Ensure mobile compatibility
2. **Accessibility**: Follow WCAG guidelines
3. **Performance**: Optimize CSS for speed
4. **Compatibility**: Test with common themes
5. **Graceful Degradation**: Handle missing styles

## Migration Notes

### From Hardcoded System

If migrating from a system with hardcoded content:

1. **Identify Content**: Locate all hardcoded messages
2. **Create Markdown**: Convert to markdown files
3. **Update Templates**: Replace hardcoded content with system calls
4. **Test Thoroughly**: Verify all scenarios work
5. **Document Changes**: Update documentation

### Version Compatibility

- **Minimum WordPress**: 5.0+
- **Minimum WooCommerce**: 4.0+
- **PHP Requirements**: 7.4+
- **Plugin Version**: 1.1.0+

### Breaking Changes

**Version 1.1.0**:
- Removed "Load Default Content" buttons
- Simplified admin interface
- Changed from three-layer to two-layer system
- Removed fallback markdown files

## Support and Resources

### Documentation
- [Plugin README](../README.md)
- [Markdown Guide](markdown/README.md)
- [Template Hooks Reference](template-hooks-reference.md)

### Development
- [GitHub Repository](https://github.com/your-repo)
- [Issue Tracker](https://github.com/your-repo/issues)
- [Development Guidelines](development-guidelines.md)

### Community
- [Support Forum](https://example.com/support)
- [Discord Channel](https://discord.gg/example)
- [Documentation Wiki](https://wiki.example.com)

---

**Last Updated**: December 2024  
**Version**: 1.1.0  
**Maintainer**: Arsol Projects Team 