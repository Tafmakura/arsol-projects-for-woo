<?php
/**
 * Project Navigation Component
 *
 * This template displays the navigation tabs for a project.
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.0.0
 *
 * @var string $project_title The title of the project
 * @var array $tabs The navigation tabs
 * @var string $current_tab The current active tab
 */

defined('ABSPATH') || exit;
?>

<div class="arsol-project-header">
    <h2><?php echo esc_html($project_title); ?></h2>
    <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>" class="back-to-projects">
        <?php esc_html_e('← Back to projects', 'arsol-pfw'); ?>
    </a>
</div>

<div class="arsol-project-navigation">
    <nav class="arsol-project-tabs">
        <ul>
            <?php foreach ($tabs as $tab_id => $tab) : ?>
                <li class="<?php echo $current_tab === $tab_id ? 'active' : ''; ?>">
                    <a href="<?php echo esc_url($tab['url']); ?>">
                        <?php echo esc_html($tab['label']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</div>