<?php
/**
 * Atomic Design Test Template
 * 
 * Test template to verify atomic design components are working properly.
 * This can be removed after successful implementation.
 * 
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) exit;

?>

<div class="arsol-atomic-test">
    <h2>Atomic Design Test</h2>
    
    <h3>Atoms (Elements)</h3>
    
    <h4>Status Badge</h4>
    <?php
    arsol_load_element('StatusBadge.php', [
        'status' => 'in-progress',
        'status_label' => 'In Progress',
        'show_icon' => true
    ]);
    ?>
    
    <h4>Action Button</h4>
    <?php
    arsol_load_element('ActionButton.php', [
        'url' => '#test',
        'label' => 'Test Button',
        'type' => 'primary',
        'icon' => 'dashicons-visibility'
    ]);
    ?>
    
    <h4>Meta Field</h4>
    <?php
    arsol_load_element('MetaField.php', [
        'label' => 'Budget',
        'value' => 5000,
        'type' => 'currency'
    ]);
    ?>
    
    <h3>Molecules (Components)</h3>
    
    <h4>Sidebar Meta</h4>
    <?php
    // This would need a real project ID to test properly
    // arsol_load_molecule('SidebarMeta.php', [
    //     'post_id' => 123,
    //     'post_type' => 'arsol-project'
    // ]);
    echo '<p><em>SidebarMeta requires a real project ID to test</em></p>';
    ?>
    
    <h3>Organisms (Sections)</h3>
    
    <h4>Project Sidebar</h4>
    <?php
    // This would need a real project ID to test properly
    // arsol_load_organism('ProjectSidebar.php', [
    //     'post_id' => 123,
    //     'post_type' => 'arsol-project',
    //     'status' => 'in-progress',
    //     'status_label' => 'In Progress'
    // ]);
    echo '<p><em>ProjectSidebar requires a real project ID to test</em></p>';
    ?>
    
</div>

<style>
.arsol-atomic-test {
    padding: 20px;
    border: 1px solid #ddd;
    margin: 20px 0;
}

.arsol-atomic-test h3 {
    border-bottom: 2px solid #0073aa;
    padding-bottom: 5px;
}

.arsol-atomic-test h4 {
    margin-top: 20px;
    color: #666;
}

/* Basic styling for atomic components */
.arsol-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-in-progress {
    background: #007cba;
    color: white;
}

.arsol-action-button {
    display: inline-block;
    padding: 8px 16px;
    margin: 5px;
    text-decoration: none;
    border-radius: 3px;
}

.button-primary {
    background: #0073aa;
    color: white;
}

.button-secondary {
    background: #f1f1f1;
    color: #333;
}

.arsol-meta-field {
    margin: 5px 0;
}

.arsol-meta-label {
    font-weight: bold;
    margin-right: 10px;
}
</style> 