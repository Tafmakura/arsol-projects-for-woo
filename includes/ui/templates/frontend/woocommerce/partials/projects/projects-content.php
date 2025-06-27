<?php
/**
 * Projects Listing Content
 * 
 * Main content area for projects listing page.
 * Variables: $current_tab (optional)
 */

if (!defined('ABSPATH')) exit;

// Get current tab from URL parameter or use passed variable
$current_tab = $current_tab ?? (isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'active');

?>

<div class="woocommerce-MyAccount-content">
    <?php
    switch ($current_tab) {
        case 'proposals':
            if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('project_proposal_listings')) {
                echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('project_proposal_listings');
            } else {
                echo do_shortcode('[arsol_pfw_projects_listing_proposals]');
            }
            break;
        case 'requests':
            if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('project_requests_listings')) {
                echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('project_requests_listings');
            } else {
                echo do_shortcode('[arsol_pfw_projects_listing_requests]');
            }
            break;
        case 'active':
        default:
            if (\Arsol_Projects_For_Woo\Frontend_Template_Overrides::has_template_override('projects_listing')) {
                echo \Arsol_Projects_For_Woo\Frontend_Template_Overrides::get_template_override('projects_listing');
            } else {
                echo do_shortcode('[arsol_pfw_projects_listing_active]');
            }
            break;
    }
    ?>
</div>
