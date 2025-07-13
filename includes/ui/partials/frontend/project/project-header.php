<?php
/**
 * Unified Project Header
 * 
 * A single project header that automatically adapts based on available variables.
 * 
 * Dashboard Mode (when $project_id, $project (WP_Post object), $current_tab are available):
 * @var int $project_id - The project ID
 * @var WP_Post $project - The project object (use $project->post_title)
 * @var string $current_tab - Current active tab (overview, orders, subscriptions)
 * 
 * Individual Item Mode (when $post_id, $post_type are available):
 * @var int $post_id - The post ID
 * @var string $post_type - The post type (arsol-pfw-project, arsol-pfw-proposal, arsol-pfw-request)
 * @var string $status - Current status slug (optional)
 * @var string $status_label - Current status label (optional)
 * 
 * Page Mode (when $page_title is available):
 * @var string $page_title - Main page title
 * @var string $page_subtitle - Page subtitle (optional)
 * @var int $project_id - Related project ID (optional)
 * @var WP_Post $project - Related project object (optional)
 */

if (!defined('ABSPATH')) exit;

// Auto-detect mode based on available variables
$is_dashboard = !empty($project_id) && !empty($project) && !empty($current_tab);
$is_individual_item = !empty($post_id) && !empty($post_type);
$is_page_mode = !empty($page_title) && !$is_dashboard && !$is_individual_item;

// Dashboard Mode: Project dashboard with navigation tabs
if ($is_dashboard) {
    // Build tabs array
    $tabs = array(
        'overview' => array('label' => __('Overview', 'arsol-pfw'), 'url' => wc_get_account_endpoint_url('view-project/' . $project_id)),
        'orders' => array('label' => __('Orders', 'woocommerce'), 'url' => wc_get_account_endpoint_url('view-project-orders/' . $project_id))
    );
    
    // Only add subscriptions tab if WooCommerce Subscriptions is active
    if (class_exists('WC_Subscriptions')) {
        $tabs['subscriptions'] = array('label' => __('Subscriptions', 'woocommerce-subscriptions'), 'url' => wc_get_account_endpoint_url('view-project-subscriptions/' . $project_id));
    }
    ?>
    <div class="arsol-pfw-project-intro">
        <p>
            <?php 
            // Create intro text based on available features
            if (class_exists('WC_Subscriptions')) {
                // Full intro with subscriptions
                echo sprintf(
                    esc_html__('This is your %s project dashboard. The %s tab shows project details, the %s tab displays your project %s, and the %s tab displays all your project %s.', 'arsol-pfw'),
                    '<strong>' . esc_html(method_exists($project, 'get_title') ? $project->get_title() : $project->post_title) . '</strong>',
                    '<strong>' . esc_html__('Overview', 'arsol-pfw') . '</strong>',
                    '<strong>' . esc_html__('Orders', 'woocommerce') . '</strong>',
                    esc_html__('orders', 'woocommerce'),
                    '<strong>' . esc_html__('Subscriptions', 'woocommerce-subscriptions') . '</strong>',
                    esc_html__('subscriptions', 'woocommerce-subscriptions')
                );
            } else {
                // Simplified intro without subscriptions
                echo sprintf(
                    esc_html__('This is your %s project dashboard. The %s tab shows project details and the %s tab displays your project %s.', 'arsol-pfw'),
                    '<strong>' . esc_html(method_exists($project, 'get_title') ? $project->get_title() : $project->post_title) . '</strong>',
                    '<strong>' . esc_html__('Overview', 'arsol-pfw') . '</strong>',
                    '<strong>' . esc_html__('Orders', 'woocommerce') . '</strong>',
                    esc_html__('orders', 'woocommerce')
                );
            }
            ?>
        </p>
    </div>
    <div class="arsol-pfw-project-navigation">
        <div class="arsol-button-container">
            <div class="arsol-button-groups">
                <?php foreach ($tabs as $tab_id => $tab_data) : ?>
                    <button class="arsol-btn-secondary arsol-pfw-project-btn <?php echo $current_tab === $tab_id ? 'active' : ''; ?>" 
                            onclick="window.location.href='<?php echo esc_url($tab_data['url']); ?>'">
                        <?php echo esc_html($tab_data['label']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
    return; // Exit early for dashboard
}

// Individual Item Mode or Page Mode: Standard header with breadcrumbs and title
$post = null;
$main_title = '';
$subtitle = '';

if ($is_individual_item) {
    // Individual project/proposal/request
    $post = get_post($post_id);
    if (!$post) {
        return;
    }
    
    $main_title = $post->post_title;
    
    // Determine subtitle based on post type
    switch ($post_type) {
        case 'arsol-pfw-project':
            $subtitle = __('Project Overview', 'arsol-pfw');
            break;
        case 'arsol-pfw-proposal':
            $subtitle = __('Proposal Details', 'arsol-pfw');
            break;
        case 'arsol-pfw-request':
            $subtitle = __('Request Details', 'arsol-pfw');
            break;
        default:
            $subtitle = __('Project Overview', 'arsol-pfw');
            break;
    }
} elseif ($is_page_mode) {
    // Page mode
    $main_title = $page_title;
    $subtitle = $page_subtitle ?? '';
}

if (empty($main_title)) {
    return; // No valid content to display
}
?>

<div class="arsol-pfw-project-header">
    <div class="project-header-content">
        <div class="project-breadcrumb">
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>">
                <?php _e('← Back to Projects', 'arsol-pfw'); ?>
            </a>
            
            <?php if ($is_page_mode && !empty($project_id) && !empty($project)): ?>
                <span class="breadcrumb-separator">/</span>
                <a href="<?php echo esc_url(wc_get_account_endpoint_url('view-project') . '/' . $project_id); ?>">
                    <?php echo esc_html(method_exists($project, 'get_title') ? $project->get_title() : $project->post_title); ?>
                </a>
            <?php endif; ?>
        </div>
        
        <h1 class="project-title"><?php echo esc_html($main_title); ?></h1>
        <?php if (!empty($subtitle)): ?>
            <p class="project-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        
        <?php if ($is_individual_item && !empty($status) && !empty($status_label)): ?>
            <div class="project-header-status">
                <?php 
                // Include status badge component
                $show_icon = true;
                $css_class = 'header-status-badge';
                include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/status-badge.php';
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="project-header-actions">
        <?php if ($is_individual_item): ?>
            <?php
            // Default actions based on post type
            switch ($post_type) {
                case 'arsol-pfw-project':
                    $url = wc_get_account_endpoint_url('create-project') . '?edit=' . $post_id;
                    $label = __('Edit Project', 'arsol-pfw');
                    $type = 'secondary';
                    $icon = 'dashicons-edit';
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                    break;
                    
                case 'arsol-pfw-proposal':
                    $url = wc_get_account_endpoint_url('create-project') . '?edit_proposal=' . $post_id;
                    $label = __('Edit Proposal', 'arsol-pfw');
                    $type = 'secondary';
                    $icon = 'dashicons-edit';
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                    break;
                    
                case 'arsol-pfw-request':
                    $url = wc_get_account_endpoint_url('create-project') . '?from_request=' . $post_id;
                    $label = __('Convert to Project', 'arsol-pfw');
                    $type = 'primary';
                    $icon = 'dashicons-arrow-right-alt';
                    include ARSOL_PROJECTS_PLUGIN_DIR . 'includes/ui/components/frontend/action-button.php';
                    break;
            }
            ?>
        <?php elseif ($is_page_mode && !empty($project_id)): ?>
            <!-- Default action for project pages: View Project -->
            <a href="<?php echo esc_url(wc_get_account_endpoint_url('view-project') . '/' . $project_id); ?>" class="button button-secondary">
                <?php _e('View Project', 'arsol-pfw'); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
