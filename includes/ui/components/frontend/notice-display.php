<?php
/**
 * Notice Display Component
 *
 * @package Arsol_Projects_For_Woo
 */

// Prevent direct access.
if (!defined('ABSPATH')) {
    exit;
}

// Get notice data from URL parameters
$notice_type = isset($_GET['notice_type']) ? sanitize_text_field($_GET['notice_type']) : '';
$notice_message = isset($_GET['notice_message']) ? sanitize_text_field(urldecode($_GET['notice_message'])) : '';

// Only display if we have valid notice data
if (!empty($notice_type) && !empty($notice_message) && in_array($notice_type, array('success', 'error', 'info', 'warning'))) :
?>

<div class="arsol-notice arsol-notice-<?php echo esc_attr($notice_type); ?>" id="arsol-notice">
    <span class="notice-message"><?php echo esc_html($notice_message); ?></span>
    <button type="button" class="notice-dismiss" onclick="document.getElementById('arsol-notice').remove();">×</button>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var notice = document.getElementById('arsol-notice');
    if (notice) {
        // Auto-dismiss success notices after 5 seconds
        <?php if ($notice_type === 'success') : ?>
        setTimeout(function() {
            if (notice.parentElement) {
                notice.remove();
            }
        }, 5000);
        <?php endif; ?>
        
        // Clean URL after displaying notice
        if (window.history && window.history.replaceState) {
            var url = new URL(window.location);
            url.searchParams.delete('notice_type');
            url.searchParams.delete('notice_message');
            window.history.replaceState({}, document.title, url);
        }
    }
});
</script>

<style type="text/css">
.arsol-notice {
    position: relative;
    padding: 12px 40px 12px 16px;
    margin: 20px 0;
    border-left: 4px solid;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    font-size: 14px;
    line-height: 1.4;
    border-radius: 0 4px 4px 0;
    z-index: 999999;
}

.arsol-notice-success {
    border-left-color: #46b450;
    background-color: #f7fcf0;
    color: #155724;
}

.arsol-notice-error {
    border-left-color: #dc3232;
    background-color: #fdf2f2;
    color: #721c24;
}

.arsol-notice-warning {
    border-left-color: #ffb900;
    background-color: #fefbf3;
    color: #856404;
}

.arsol-notice-info {
    border-left-color: #00a0d2;
    background-color: #f0f8ff;
    color: #004085;
}

.arsol-notice .notice-dismiss {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    font-weight: bold;
    color: inherit;
    opacity: 0.6;
    padding: 0;
    width: 20px;
    height: 20px;
    line-height: 1;
}

.arsol-notice .notice-dismiss:hover {
    opacity: 1;
}

@media (max-width: 768px) {
    .arsol-notice {
        margin: 10px;
        padding: 10px 35px 10px 12px;
        font-size: 13px;
    }
}
</style>

<?php endif; ?> 