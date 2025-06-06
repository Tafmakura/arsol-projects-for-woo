<?php
/**
 * Projects Sidebar: Proposals
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<h4><?php esc_html_e('Proposals', 'arsol-pfw'); ?></h4>
<p><?php esc_html_e('Manage your project proposals here.', 'arsol-pfw'); ?></p>
<?php do_action('arsol_pfw_projects_sidebar_proposals'); ?>
