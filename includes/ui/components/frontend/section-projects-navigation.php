<?php
/**
 * Projects Navigation Component
 *
 * @package Arsol_Projects_For_Woo
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current tab from URL parameter
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'active';

// Get current user's account URL
$account_url = wc_get_account_endpoint_url('projects');

// Define tabs
$tabs = array(
    'active' => array(
        'label' => __('Active Projects', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'active', $account_url)
    ),
    'proposals' => array(
        'label' => __('Proposals', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'proposals', $account_url)
    ),
    'requests' => array(
        'label' => __('Requests', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'requests', $account_url)
    )
);
?>

<div class="arsol-project-navigation">
    <div class="arsol-button-container">
        <div class="arsol-button-groups">
            <?php foreach ($tabs as $tab_id => $tab) : ?>
                <button 
                    class="arsol-btn-secondary arsol-project-btn <?php echo $current_tab === $tab_id ? 'active' : ''; ?>" 
                    onclick="window.location.href='<?php echo esc_url($tab['url']); ?>'"
                >
                    <?php echo esc_html($tab['label']); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.arsol-project-navigation {
    margin-bottom: 2rem;
}

.arsol-button-container {
    display: flex;
    justify-content: flex-start;
    margin-bottom: 1rem;
}

.arsol-button-groups {
    display: flex;
    gap: 0.5rem;
}

.arsol-project-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.arsol-project-btn:hover {
    background: #f5f5f5;
}

.arsol-project-btn.active {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}
</style>
