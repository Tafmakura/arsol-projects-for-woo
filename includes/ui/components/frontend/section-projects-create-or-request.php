<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$can_create = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_projects($user_id);
$can_request = \Arsol_Projects_For_Woo\Admin\Admin_Capabilities::can_create_project_requests($user_id);

if ($can_create) {
    $button_url = wc_get_account_endpoint_url('project-create');
    $button_label = __('+ Create Project', 'arsol-pfw');
} elseif ($can_request) {
    $button_url = wc_get_account_endpoint_url('project-request');
    $button_label = __('+ Request Project', 'arsol-pfw');
} else {
    $button_url = '';
    $button_label = '';
}
?>

<?php if ($button_url && $button_label): ?>
    <div style="display: flex; justify-content: flex-end; margin-bottom: 2em;">
        <a href="<?php echo esc_url($button_url); ?>" class="arsol-create-or-request-btn" style="background-color: #2271b1; color: #fff; padding: 20px 40px; border-radius: 8px; font-size: 1.5em; font-weight: 600; text-decoration: none; display: inline-block;">
            <?php echo esc_html($button_label); ?>
        </a>
    </div>
<?php endif; ?>

<style>
.arsol-create-or-request-btn {
    transition: background 0.2s;
}
.arsol-create-or-request-btn:hover {
    background-color: #135e96;
    color: #fff;
    text-decoration: none;
}
</style>
