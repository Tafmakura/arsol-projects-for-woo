<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="arsol-no-permission-message">
    <div class="arsol-no-permission-content">
        <h2><?php esc_html_e('Access Denied', 'arsol-pfw'); ?></h2>
        <p><?php esc_html_e('You do not have permission to access project features. Please contact your administrator for access.', 'arsol-pfw'); ?></p>
    </div>
</div>

<style>
.arsol-no-permission-message {
    padding: 2rem;
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    text-align: center;
    margin: 2rem 0;
}

.arsol-no-permission-content h2 {
    color: #d63638;
    margin-bottom: 1rem;
}

.arsol-no-permission-content p {
    color: #50575e;
    margin: 0;
}
</style>
