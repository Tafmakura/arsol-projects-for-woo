# Markdown Content Files

This directory contains markdown files that provide default content for various sections of the Arsol Projects for WooCommerce plugin.

## File Structure

```
includes/ui/markdown/
├── frontend/
│   ├── content-active-empty.md      # Active Project (Empty)
│   ├── content-proposal-empty.md     # Project Proposal (Empty)
│   ├── content-request-on-hold.md    # Project Request (On-Hold)
│   └── content-request-under-review.md # Project Request (Under Review)
└── README.md                          # This documentation
```

## File Mappings

Each markdown file corresponds to a specific default message setting:

- `content-active-empty.md` → **Active Project (Empty)** - Shown when active projects have no content
- `content-proposal-empty.md` → **Project Proposal (Empty)** - Shown when proposals have no content  
- `content-request-on-hold.md` → **Project Request (On-Hold)** - Shown when requests are on hold
- `content-request-under-review.md` → **Project Request (Under Review)** - Shown when requests are under review

## How It Works

1. **File-based defaults**: The plugin loads default content from these markdown files
2. **Admin interface**: Administrators can see these defaults as placeholders in the settings
3. **User customization**: Admin can override defaults by entering custom content in the settings
4. **Fallback system**: If files don't exist, hardcoded fallbacks are used
5. **Frontend display**: Empty database fields automatically show markdown content as placeholders

## Editing Content

To customize the default messages:

1. **For developers**: Edit the markdown files directly
2. **For administrators**: Use the WordPress admin interface at Advanced Settings → Default Messages
3. **Version control**: File changes are tracked in git, database changes are not

## Benefits

- ✅ Easy content management through files
- ✅ Version control with git  
- ✅ Professional defaults with rich markdown formatting
- ✅ Clean separation of content from code
- ✅ No database pollution
- ✅ User-friendly admin interface

## Directory Structure

```
includes/ui/markdown/
├── frontend/
│   ├── content-project-empty.md      # Active Project (Empty)
│   ├── content-proposal-empty.md     # Project Proposal (Empty)
│   ├── content-request-on-hold.md    # Project Request (On-Hold)
│   └── content-request-under-review.md # Project Request (Under Review)
└── README.md
```

## How It Works

### Two-Layer System
1. **Layer 1 (Hardcoded)**: Markdown files provide professional defaults
2. **Layer 2 (Database)**: User customizations override defaults when present

### File Loading
- Files are loaded via `Setup_Defaults::get_hardcoded_defaults()`
- If a file doesn't exist, fallback text is used
- Content is cached and only loaded when needed

### Admin Interface
- Empty database fields automatically show markdown content as placeholders
- "Load Default Content" buttons copy markdown into editable fields
- Users can customize any or all messages

## Editing Guidelines

### Markdown Features Supported
- **Bold text**: `**bold**`
- *Italic text*: `*italic*`
- [Links](URL): `[text](URL)`
- `Code`: `` `code` ``
- Lists: `- item` or `1. item`
- Quotes: `> quote`
- Headers: `## Header`

### Best Practices
1. **Keep it professional** - These are customer-facing messages
2. **Use clear language** - Avoid technical jargon
3. **Include next steps** - Tell users what to expect
4. **Brand consistency** - Match your company's tone
5. **Contact information** - Provide ways to get help

### File Naming Convention
- `content-{context}-{state}.md`
- Use lowercase with hyphens
- Be descriptive but concise

## Testing

You can test if files are loading correctly by calling:
```php
$debug = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::debug_markdown_files();
var_dump($debug);
```

## Version Control

These files are part of the plugin and should be version controlled. Updates to these files will automatically appear for all users on plugin update, unless they have customized the specific message in the admin interface. 