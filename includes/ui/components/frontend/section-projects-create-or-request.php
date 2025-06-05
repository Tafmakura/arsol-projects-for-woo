<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$admin_users = new \Arsol_Projects_For_Woo\Admin\Users();
$can_create = $admin_users->can_user_create_projects($user_id);
$can_request = $admin_users->can_user_request_projects($user_id);

$button_url = '';
$button_label = '';
if ($can_create) {
    $button_url = wc_get_account_endpoint_url('project-create');
    $button_label = __('+ Create Project', 'arsol-pfw');
} elseif ($can_request) {
    $button_url = wc_get_account_endpoint_url('project-request');
    $button_label = __('+ Request Project', 'arsol-pfw');
}
?>

<?php if ($button_url && $button_label): ?>
    <div class="arsol-table-header-button">
        <a href="<?php echo esc_url($button_url); ?>" class="arsol-create-or-request-button button">
            <?php echo esc_html($button_label); ?>
        </a>
    </div>
<?php endif; ?>
