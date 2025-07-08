<?php
/**
 * Project Proposal Content
 *
 * Shows proposal information about a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// The global $post is set up by the shortcode (same pattern as request template)
if (!isset($post) || !$post) {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

// Use factory function to get proposal object
$proposal = arsol_pfw_get_proposal($post->ID);
if (!$proposal) {
    echo '<p>' . esc_html__('Proposal not found.', 'arsol-pfw') . '</p>';
    return;
}

// Get proposal details using CRUD methods
$proposal_budget = $proposal->get_budget();
$proposal_timeline = $proposal->get_timeline();
$proposal_stage = $proposal->get_stage();
$proposal_quotation = $proposal->get_quotation();
$proposal_expiry = $proposal->get_prop('expiry_date');
$related_request_id = $proposal->get_prop('related_request_id');

$wp_button_class = function_exists('wc_wp_theme_get_element_class_name') ? ' ' . wc_wp_theme_get_element_class_name('button') : '';
?>

<?php
// 1. Customer Notice (first)
$customer_notice = \Arsol_Projects_For_Woo\Admin\Setup_Defaults::get_effective_customer_notice($proposal->get_id(), 'proposal');
if (!empty($customer_notice)) : ?>
    <div class="arsol-pfw-customer-notice">
        <div class="arsol-pfw-notice-content">
            <?php echo wp_kses_post(wpautop($customer_notice)); ?>
        </div>
    </div>
<?php endif; ?>

<?php
// 2. Proposal Status and Details
?>
<div class="arsol-pfw-proposal-status">
    <?php if ($proposal_stage) : ?>
        <p><strong><?php _e('Status:', 'arsol-pfw'); ?></strong> <?php echo esc_html(\Arsol_Projects_For_Woo\Core\Stage_Manager::get_stage_label('proposal', $proposal_stage)); ?></p>
    <?php endif; ?>
    
    <?php if ($proposal_budget) : ?>
        <p><strong><?php _e('Budget:', 'arsol-pfw'); ?></strong> <?php echo wp_kses_post(wc_price($proposal_budget)); ?></p>
    <?php endif; ?>
    
    <?php if ($proposal_timeline) : ?>
        <p><strong><?php _e('Timeline:', 'arsol-pfw'); ?></strong> <?php echo esc_html($proposal_timeline); ?></p>
    <?php endif; ?>
    
    <?php if ($proposal_expiry) : ?>
        <p><strong><?php _e('Expires:', 'arsol-pfw'); ?></strong> <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($proposal_expiry))); ?></p>
    <?php endif; ?>
</div>

<?php
// 3. Quotation Details
if (!empty($proposal_quotation)) : ?>
    <div class="arsol-pfw-proposal-quotation">
        <h4><?php _e('Quotation Details', 'arsol-pfw'); ?></h4>
        <?php 
        // This would need to be formatted based on quotation structure
        // For now, we'll just indicate that quotation data exists
        ?>
        <p><?php _e('Quotation details are available.', 'arsol-pfw'); ?></p>
    </div>
<?php endif; ?>

<?php
// 4. Post Content (proposal description)
if (!empty($proposal->get_name()) && !empty($post->post_content)) : ?>
    <div class="arsol-pfw-post-content">
        <h4><?php _e('Proposal Details', 'arsol-pfw'); ?></h4>
        <?php echo wp_kses_post($post->post_content); ?>
    </div>
<?php endif; ?>
