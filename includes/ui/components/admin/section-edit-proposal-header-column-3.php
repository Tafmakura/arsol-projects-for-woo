<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$proposal_status = get_post_status($post);
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
?>

<p class="form-field form-field-wide">
    <label><?php _e('Proposal Status:', 'arsol-pfw'); ?></label>
    <div><?php echo esc_html(ucfirst(str_replace('-', ' ', $proposal_status))); ?></div>
</p>

<?php if (!empty($expiration_date)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Expiration Date:', 'arsol-pfw'); ?></label>
    <div><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($expiration_date))); ?></div>
</p>
<?php endif; ?>

<p class="form-field form-field-wide">
    <label><?php _e('Actions:', 'arsol-pfw'); ?></label>
    <a href="#" class="button"><?php _e('Convert to Project', 'arsol-pfw'); ?></a>
    <a href="#" class="button"><?php _e('Send to Customer', 'arsol-pfw'); ?></a>
</p> 