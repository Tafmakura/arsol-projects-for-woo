<?php
/**
 * Admin email template for new project requests
 * 
 * @var WP_Post $request The request object
 * @var WP_User $customer The customer user object
 * @var string $admin_url Admin edit URL
 * @var string $email_heading Email heading
 * @var array $color_scheme Color scheme array
 * @var string $status_icon Status icon
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($email_heading); ?></title>
    <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .email-container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .email-header { background: <?php echo esc_attr($color_scheme['background']); ?>; color: <?php echo esc_attr($color_scheme['text']); ?>; padding: 30px 20px; text-align: center; }
        .email-body { padding: 30px 20px; }
        .email-footer { background: #f8f9fa; padding: 20px; text-align: center; color: #6c757d; font-size: 14px; }
        .cta-button { display: inline-block; background: <?php echo esc_attr($color_scheme['cta']); ?>; color: white !important; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
        .info-box { background: #f8f9fa; border-left: 4px solid <?php echo esc_attr($color_scheme['cta']); ?>; padding: 15px; margin: 20px 0; }
        .status-badge { background: <?php echo esc_attr($color_scheme['background']); ?>; color: <?php echo esc_attr($color_scheme['text']); ?>; padding: 5px 15px; border-radius: 20px; font-size: 14px; display: inline-block; margin: 10px 0; }
        h1, h2, h3 { color: <?php echo esc_attr($color_scheme['text']); ?>; }
        .request-details { background: #ffffff; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f8f9fa; }
        .detail-label { font-weight: bold; color: #495057; }
        .detail-value { color: #6c757d; }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1><?php echo esc_html($status_icon); ?> <?php echo esc_html($email_heading); ?></h1>
            <p>A new project request has been submitted and requires your attention.</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="status-badge">
                New Request #<?php echo esc_html($request->ID); ?>
            </div>

            <h2><?php echo esc_html($request->post_title); ?></h2>

            <div class="info-box">
                <strong>👤 Customer:</strong> <?php echo esc_html($customer->display_name); ?> (<?php echo esc_html($customer->user_email); ?>)<br>
                <strong>📅 Submitted:</strong> <?php echo esc_html(wp_date('F j, Y \a\t g:i A', strtotime($request->post_date))); ?><br>
                <strong>🏷️ Status:</strong> <?php echo esc_html(ucfirst(str_replace('-', ' ', $request->post_status))); ?>
            </div>

            <div class="request-details">
                <h3>Request Details</h3>
                <?php if (!empty($request->post_content)): ?>
                    <div class="detail-row">
                        <span class="detail-label">Description:</span>
                    </div>
                    <div style="margin: 10px 0; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                        <?php echo wp_kses_post(wpautop($request->post_content)); ?>
                    </div>
                <?php endif; ?>

                <?php
                // Get custom fields
                $budget = get_post_meta($request->ID, '_arsol_pfw_request_budget', true);
                $timeline = get_post_meta($request->ID, '_arsol_pfw_request_timeline', true);
                $priority = get_post_meta($request->ID, '_arsol_pfw_request_priority', true);
                ?>

                <?php if ($budget): ?>
                <div class="detail-row">
                    <span class="detail-label">Budget:</span>
                    <span class="detail-value"><?php echo esc_html($budget); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($timeline): ?>
                <div class="detail-row">
                    <span class="detail-label">Timeline:</span>
                    <span class="detail-value"><?php echo esc_html($timeline); ?></span>
                </div>
                <?php endif; ?>

                <?php if ($priority): ?>
                <div class="detail-row">
                    <span class="detail-label">Priority:</span>
                    <span class="detail-value"><?php echo esc_html(ucfirst($priority)); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="<?php echo esc_url($admin_url); ?>" class="cta-button">
                    📝 Review & Manage Request
                </a>
            </div>

            <div class="info-box">
                <strong>📋 Next Steps:</strong><br>
                • Review the request details<br>
                • Contact the customer if clarification is needed<br>
                • Convert to proposal when ready<br>
                • Update the request status to keep the customer informed
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p>This is an automated notification from your project management system.</p>
            <p><strong><?php echo esc_html(get_bloginfo('name')); ?></strong></p>
        </div>
    </div>
</body>
</html> 