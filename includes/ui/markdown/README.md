# Markdown Content Files

This directory contains markdown files that provide default content for various sections of the Arsol Projects for WooCommerce plugin.

## File Structure

```
includes/ui/markdown/
├── frontend/
│   ├── content-active-empty.md         # Active Project (Empty)
│   ├── content-proposal-empty.md       # Project Proposal (Empty)
│   ├── content-request-on-hold.md      # Project Request (On-Hold)
│   └── content-request-under-review.md # Project Request (Under Review)
└── README.md                            # This documentation
```

## File Mappings

Each markdown file corresponds to a specific default message setting:

- `content-active-empty.md` → **Active Project (Empty)** - Shown when active projects have no content
- `content-proposal-empty.md` → **Project Proposal (Empty)** - Shown when proposals have no content  
- `content-request-on-hold.md` → **Project Request (On-Hold)** - Shown when requests are on hold
- `content-request-under-review.md` → **Project Request (Under Review)** - Shown when requests are under review

## Two-Layer System

The plugin uses a simple two-layer system:

1. **Markdown files** - Professional default content (part of plugin)
2. **Database overrides** - User customizations when set

### Loading Priority:
```
User Database Setting → Markdown File → Hardcoded Fallback
```

## How It Works

1. **File-based defaults**: The plugin loads default content from markdown files
2. **Admin interface**: Administrators can see these defaults as placeholders in the settings
3. **User customization**: Admin can override defaults by entering custom content in the settings
4. **Template simplification**: All hardcoded text has been moved to markdown files
5. **Single point of control**: All template content is managed through markdown files
6. **Frontend display**: Empty database fields automatically show markdown content

## Content Management

### For Developers (File-based)
- Edit the markdown files directly for default content
- All content is version controlled with git
- No hardcoded text in PHP templates
- Clean separation of content, structure, and logic

### For Administrators (Database-based)
- Use WordPress admin interface: **Advanced Settings → Default Messages**
- Override any default by entering custom content
- Leave fields empty to use file-based defaults
- Use "Load Default Content" buttons to copy file content for editing

## Template Structure

Templates now contain only HTML structure and PHP logic:

```php
// Get content from file system or database override
$default_message = Setup_Defaults::get_effective_default_message('key');

// Display with HTML structure
echo wp_kses_post(wpautop($default_message));
```

## Fallback System

Since markdown files are **part of the plugin**, they should always exist. The fallback system is simple:

1. **User setting** - If admin has customized the message
2. **Markdown file** - Default content from file (should always work)
3. **Hardcoded text** - Minimal fallback only if file is missing/corrupted

## Benefits

- ✅ **Single point of control** - All content in markdown files
- ✅ **Template simplification** - No hardcoded text in PHP templates
- ✅ **Simple architecture** - No unnecessary fallback complexity
- ✅ **Easy content management** - Edit files or use admin interface
- ✅ **Version control** - File changes tracked in git
- ✅ **Professional defaults** - Rich markdown formatting
- ✅ **Clean separation** - Content separated from code and structure
- ✅ **No database pollution** - Files don't clutter database
- ✅ **User-friendly** - Admin interface with placeholders and load buttons

## Debug Information

For administrators, debug information is available:

```php
$debug = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::debug_markdown_files();
```

This shows the status of all markdown files and effective content being used.

## File Reliability

Since these markdown files are distributed with the plugin:
- Files should always exist after plugin installation
- Files are version controlled and tested
- No need for complex fallback file systems
- Simple and reliable architecture 