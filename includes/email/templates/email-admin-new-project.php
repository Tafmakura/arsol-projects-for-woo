<?php
/**
 * Admin New Project Email Template
 * 
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-new-project.php.
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action('woocommerce_email_header', $email_heading, $email); ?>

<p><?php printf( esc_html__( 'A new project has been created on your website.', 'arsol-projects-for-woo' ) ); ?></p>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
    <thead>
        <tr>
            <th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Project Details', 'arsol-projects-for-woo' ); ?></th>
            <th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Value', 'arsol-projects-for-woo' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                <?php esc_html_e( 'Project ID', 'arsol-projects-for-woo' ); ?>
            </td>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                #<?php echo esc_html( $project_id ); ?>
            </td>
        </tr>
        <tr>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                <?php esc_html_e( 'Customer ID', 'arsol-projects-for-woo' ); ?>
            </td>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo esc_html( $customer_id ); ?>
            </td>
        </tr>
        <tr>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                <?php esc_html_e( 'Date Created', 'arsol-projects-for-woo' ); ?>
            </td>
            <td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ); ?>
            </td>
        </tr>
    </tbody>
</table>

<p style="margin: 16px 0;">
    <a class="link" href="<?php echo esc_url( admin_url( 'post.php?post=' . $project_id . '&action=edit' ) ); ?>" style="background-color: #96588a; color: #ffffff; border-radius: 3px; display: inline-block; font-family: sans-serif; font-size: 14px; font-weight: bold; line-height: 38px; text-align: center; text-decoration: none; width: 200px; -webkit-text-size-adjust: none;">
        <?php esc_html_e( 'View Project', 'arsol-projects-for-woo' ); ?>
    </a>
</p>

<p><?php esc_html_e( 'Please review and manage this new project from your admin dashboard.', 'arsol-projects-for-woo' ); ?></p>

<?php do_action('woocommerce_email_footer', $email);
