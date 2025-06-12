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

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label><strong><?php _e('Proposal Status:', 'arsol-pfw'); ?></strong></label>
        <?php echo esc_html(ucfirst(str_replace('-', ' ', $proposal_status))); ?>
    </p>
</div>

<?php if (!empty($expiration_date)): ?>
<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label><strong><?php _e('Expiration Date:', 'arsol-pfw'); ?></strong></label>
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($expiration_date))); ?>
    </p>
</div>
<?php endif; ?>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label><strong><?php _e('Actions:', 'arsol-pfw'); ?></strong></label>
        <a href="#" class="button"><?php _e('Convert to Project', 'arsol-pfw'); ?></a>
        <a href="#" class="button"><?php _e('Send to Customer', 'arsol-pfw'); ?></a>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_attachments"><?php _e('Attachments:', 'arsol-pfw'); ?></label>
        <input type="file" id="proposal_attachments" name="proposal_attachments[]" multiple class="widefat">
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_terms"><?php _e('Terms & Conditions:', 'arsol-pfw'); ?></label>
        <textarea id="proposal_terms" name="proposal_terms" class="widefat" rows="4"><?php echo esc_textarea($terms); ?></textarea>
    </p>
</div>

<div class="form-field-row">
    <p class="form-field form-field-wide">
        <label for="proposal_payment_terms"><?php _e('Payment Terms:', 'arsol-pfw'); ?></label>
        <textarea id="proposal_payment_terms" name="proposal_payment_terms" class="widefat" rows="4"><?php echo esc_textarea($payment_terms); ?></textarea>
    </p>
</div> 