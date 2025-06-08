<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="arsol-pfw-request-submitted">
    <h3><?php esc_html_e('Thank you for your request!', 'arsol-pfw'); ?></h3>
    <p><?php esc_html_e('Your project request has been submitted successfully. We will review it shortly and get back to you.', 'arsol-pfw'); ?></p>
    <p>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('projects')); ?>?tab=requests" class="button"><?php esc_html_e('View My Requests', 'arsol-pfw'); ?></a>
    </p>
</div>
