# Stage Handler Complete Reference

## Overview

The Stage Handler system provides simplified, efficient stage management using WordPress taxonomies directly. It replaces the complex stage entities with lightweight taxonomy operations while maintaining all essential functionality.

## Core Class: Stage_Handler

**File:** `includes/taxonomies/stages/stage-handler.php`  
**Namespace:** `Arsol_Projects_For_Woo\Taxonomies\Stages`

### Static Methods

#### `set_stage($post_id, $stage_slug, $taxonomy, $create_if_missing = false)`

Sets a stage for a post, optionally creating the stage if it doesn't exist.

**Parameters:**
- `$post_id` (int) - The post ID
- `$stage_slug` (string) - The stage slug
- `$taxonomy` (string) - The taxonomy name
- `$create_if_missing` (bool) - Whether to create the stage if it doesn't exist

**Returns:** (bool) True on success

**Example:**
```php
// Set stage, create if missing
\Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::set_stage(123, 'processing', 'arsol-pfw-project-stage', true);

// Set stage, fail if missing
\Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::set_stage(123, 'processing', 'arsol-pfw-project-stage', false);
```

#### `create_stage($stage_slug, $taxonomy, $stage_name = null)`

Creates a stage if it doesn't exist.

**Parameters:**
- `$stage_slug` (string) - The stage slug
- `$taxonomy` (string) - The taxonomy name
- `$stage_name` (string|null) - Optional stage name (defaults to slug)

**Returns:** (bool) True if created or already exists

**Example:**
```php
// Create stage with custom name
\Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::create_stage('custom-stage', 'arsol-pfw-project-stage', 'Custom Stage');

// Create stage with default name
\Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::create_stage('custom-stage', 'arsol-pfw-project-stage');
```

#### `remove_stage($post_id, $taxonomy)`

Removes all stages from a post.

**Parameters:**
- `$post_id` (int) - The post ID
- `$taxonomy` (string) - The taxonomy name

**Returns:** (bool) True on success

**Example:**
```php
\Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::remove_stage(123, 'arsol-pfw-project-stage');
```

#### `get_available_stages($taxonomy)`

Gets all available stages for a taxonomy.

**Parameters:**
- `$taxonomy` (string) - The taxonomy name

**Returns:** (array) Array of WP_Term objects

**Example:**
```php
$stages = \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_available_stages('arsol-pfw-project-stage');
foreach ($stages as $stage) {
    echo $stage->name . ' (' . $stage->slug . ')';
}
```

#### `get_stage($post_id, $taxonomy)`

Gets the current stage slug for a post.

**Parameters:**
- `$post_id` (int) - The post ID
- `$taxonomy` (string) - The taxonomy name

**Returns:** (string|false) Stage slug or false if not found

**Example:**
```php
$stage = \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage(123, 'arsol-pfw-project-stage');
if ($stage) {
    echo "Current stage: $stage";
}
```

#### `get_stage_name($post_id, $taxonomy)`

Gets the human-readable stage name for a post.

**Parameters:**
- `$post_id` (int) - The post ID
- `$taxonomy` (string) - The taxonomy name

**Returns:** (string|false) Stage name or false if not found

**Example:**
```php
$stage_name = \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name(123, 'arsol-pfw-project-stage');
if ($stage_name) {
    echo "Current stage: $stage_name";
}
```

#### `get_stage_name_by_slug($stage_slug, $taxonomy)`

Gets the stage name by slug.

**Parameters:**
- `$stage_slug` (string) - The stage slug
- `$taxonomy` (string) - The taxonomy name

**Returns:** (string|false) Stage name or false if not found

**Example:**
```php
$stage_name = \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_name_by_slug('processing', 'arsol-pfw-project-stage');
if ($stage_name) {
    echo "Stage name: $stage_name";
}
```

#### `get_posts_by_stage($stage_slug, $taxonomy, $post_type, $args = [])`

Gets posts by stage.

**Parameters:**
- `$stage_slug` (string) - The stage slug
- `$taxonomy` (string) - The taxonomy name
- `$post_type` (string) - The post type
- `$args` (array) - Additional query arguments

**Returns:** (array) Array of post IDs

**Example:**
```php
$project_ids = \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_posts_by_stage(
    'processing', 
    'arsol-pfw-project-stage', 
    'arsol-pfw-project',
    ['posts_per_page' => 10]
);
```

#### `get_stage_statistics($taxonomy, $post_type)`

Gets stage statistics.

**Parameters:**
- `$taxonomy` (string) - The taxonomy name
- `$post_type` (string) - The post type

**Returns:** (array) Array of stage statistics

**Example:**
```php
$stats = \Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Handler::get_stage_statistics('arsol-pfw-project-stage', 'arsol-pfw-project');
foreach ($stats as $stage_slug => $data) {
    echo "Stage: {$data['label']}, Count: {$data['count']}";
}
```

## Entity Class Methods

Each entity class (Project, Proposal, Request) now has direct stage methods:

### Project Entity Methods

```php
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);

// Core stage methods
$project->get_stage();                    // Get current stage slug
$project->set_stage('processing');        // Set stage
$project->get_stage_name();               // Get stage name
$project->get_stage_name_by_slug('processing'); // Get name by slug
$project->remove_stage();                 // Remove stage
$project->get_available_stages();         // Get all stages

// Utility methods
$project->get_projects_by_stage('processing'); // Get projects by stage
$project->get_stage_statistics();         // Get stage statistics
```

### Proposal Entity Methods

```php
$proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($proposal_id);

// Core stage methods
$proposal->get_stage();                   // Get current stage slug
$proposal->set_stage('processing');       // Set stage
$proposal->get_stage_name();              // Get stage name
$proposal->get_stage_name_by_slug('processing'); // Get name by slug
$proposal->remove_stage();                // Remove stage
$proposal->get_available_stages();        // Get all stages

// Utility methods
$proposal->get_proposals_by_stage('processing'); // Get proposals by stage
$proposal->get_stage_statistics();        // Get stage statistics
```

### Request Entity Methods

```php
$request = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Request($request_id);

// Core stage methods
$request->get_stage();                    // Get current stage slug
$request->set_stage('processing');        // Set stage
$request->get_stage_name();               // Get stage name
$request->get_stage_name_by_slug('processing'); // Get name by slug
$request->remove_stage();                 // Remove stage
$request->get_available_stages();         // Get all stages

// Utility methods
$request->get_requests_by_stage('processing'); // Get requests by stage
$request->get_stage_statistics();         // Get stage statistics
```

## Factory Functions

**File:** `includes/functions/stage-functions.php`

### Core Factory Functions

```php
// Get Stage Handler instance
$handler = arsol_pfw_get_stage_handler();

// Core operations
arsol_pfw_set_stage($post_id, $stage_slug, $taxonomy, $create_if_missing);
arsol_pfw_create_stage($stage_slug, $taxonomy, $stage_name);
arsol_pfw_remove_stage($post_id, $taxonomy);
arsol_pfw_get_stage($post_id, $taxonomy);
arsol_pfw_get_stage_name($post_id, $taxonomy);
arsol_pfw_get_stage_name_by_slug($stage_slug, $taxonomy);
arsol_pfw_get_posts_by_stage($stage_slug, $taxonomy, $post_type, $args);
arsol_pfw_get_stage_statistics($taxonomy, $post_type);
```

### Entity-Specific Factory Functions

#### Project Functions
```php
arsol_pfw_get_project_stages();           // Get all project stages
arsol_pfw_get_project_stage_name('processing'); // Get stage name
arsol_pfw_get_projects_by_stage('processing'); // Get projects by stage
arsol_pfw_get_project_stage_statistics(); // Get stage statistics
```

#### Proposal Functions
```php
arsol_pfw_get_proposal_stages();          // Get all proposal stages
arsol_pfw_get_proposal_stage_name('processing'); // Get stage name
arsol_pfw_get_proposals_by_stage('processing'); // Get proposals by stage
arsol_pfw_get_proposal_stage_statistics(); // Get stage statistics
```

#### Request Functions
```php
arsol_pfw_get_request_stages();           // Get all request stages
arsol_pfw_get_request_stage_name('processing'); // Get stage name
arsol_pfw_get_requests_by_stage('processing'); // Get requests by stage
arsol_pfw_get_request_stage_statistics(); // Get stage statistics
```

## Taxonomy Names

The system uses these taxonomy names:

- **Project Stages:** `arsol-pfw-project-stage`
- **Proposal Stages:** `arsol-pfw-proposal-stage`
- **Request Stages:** `arsol-pfw-request-stage`

## Default Stages

### Project Stages
- `not-started` - Not Started
- `in-progress` - In Progress
- `paused` - Paused
- `completed` - Completed
- `cancelled` - Cancelled

### Proposal Stages
- `draft` - Draft
- `processing` - Processing
- `ready` - Ready
- `approved` - Approved
- `rejected` - Rejected

### Request Stages
- `draft` - Draft
- `pending-review` - Pending Review
- `under-review` - Under Review
- `approved` - Approved
- `rejected` - Rejected

## Usage Examples

### Setting a Project Stage
```php
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);

// Set stage, create if missing
$project->set_stage('processing', true);

// Get current stage
$current_stage = $project->get_stage();
$stage_name = $project->get_stage_name();

echo "Project is in stage: $stage_name ($current_stage)";
```

### Getting Projects by Stage
```php
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project();

// Get all projects in processing stage
$processing_projects = $project->get_projects_by_stage('processing');

// Get projects with additional query args
$recent_processing = $project->get_projects_by_stage('processing', [
    'posts_per_page' => 5,
    'orderby' => 'date',
    'order' => 'DESC'
]);
```

### Using Factory Functions
```php
// Get all project stages
$stages = arsol_pfw_get_project_stages();
foreach ($stages as $stage) {
    echo "Stage: {$stage->name} ({$stage->slug})";
}

// Get stage statistics
$stats = arsol_pfw_get_project_stage_statistics();
foreach ($stats as $stage_slug => $data) {
    echo "Stage: {$data['label']}, Count: {$data['count']}";
}
```

### Creating Custom Stages
```php
// Create a custom stage
arsol_pfw_create_stage('custom-stage', 'arsol-pfw-project-stage', 'Custom Stage');

// Set a project to the custom stage
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
$project->set_stage('custom-stage');
```

## Benefits

✅ **Efficiency:** No object instantiation on every method call  
✅ **Simplicity:** Direct WordPress taxonomy operations  
✅ **Performance:** Reduced database queries and memory usage  
✅ **Maintainability:** Single source of truth for stage operations  
✅ **WooCommerce Alignment:** Factory functions follow WooCommerce patterns  
✅ **WordPress Native:** Uses built-in taxonomy functions  

## Migration from Old System

The old complex stage entities have been removed. If you were using:

```php
// OLD (removed)
$stage_entity = new \Arsol_Projects_For_Woo\Taxonomies\Stages\Project_Stage($project_id);
$stage_entity->set_stage('processing');

// NEW
$project = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($project_id);
$project->set_stage('processing');
```

All functionality is preserved but with much better performance and simplicity. 