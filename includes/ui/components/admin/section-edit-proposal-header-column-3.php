<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
    return;
}

$proposal_id = $post->ID;
$expiration_date = get_post_meta($proposal_id, '_proposal_expiration_date', true);
?>

<?php if (!empty($expiration_date)) : ?>
<p class="form-field form-field-wide">
    <label><strong><?php _e('Expiration Date:', 'arsol-pfw'); ?></strong></label>
    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($expiration_date))); ?>
</p>
<?php endif; ?> 