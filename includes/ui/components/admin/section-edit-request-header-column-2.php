<?php
if (!defined('ABSPATH')) {
    exit;
}

global $post;

if (!$post || $post->post_type !== 'arsol-pfw-request') {
    return;
}

$request_id = $post->ID;
$request_content = get_post_field('post_content', $request_id);
$attachments = get_attached_media('', $request_id);
?>

<?php if (!empty($request_content)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Request Description:', 'arsol-pfw'); ?></label>
    <span><?php echo wp_kses_post(wp_trim_words($request_content, 50)); ?></span>
</p>
<?php endif; ?>

<?php if (!empty($attachments)): ?>
<p class="form-field form-field-wide">
    <label><?php _e('Attachments:', 'arsol-pfw'); ?></label>
    <?php foreach ($attachments as $attachment): ?>
        <a href="<?php echo wp_get_attachment_url($attachment->ID); ?>" target="_blank">
            <?php echo esc_html($attachment->post_title); ?>
        </a><br>
    <?php endforeach; ?>
</p>
<?php endif; ?> 