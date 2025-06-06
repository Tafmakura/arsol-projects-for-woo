<?php
/**
 * Projects Sidebar: Active
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<h4><?php esc_html_e('Active Projects', 'arsol-pfw'); ?></h4>
<p><?php esc_html_e('Here you can find a summary of your active projects.', 'arsol-pfw'); ?></p>
<?php do_action('arsol_pfw_projects_sidebar_active'); ?>
