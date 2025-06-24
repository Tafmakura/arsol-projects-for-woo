<?php
/**
 * New Request Email Template
 *
 * @package Arsol_Projects_For_Woo
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php _e( 'A new project request has been submitted:', 'arsol-projects-for-woo' ); ?></p>

<h2><?php _e( 'Request Details', 'arsol-projects-for-woo' ); ?></h2>

<?php if ( $request ) : ?>
<ul>
    <li><strong><?php _e( 'Request ID:', 'arsol-projects-for-woo' ); ?></strong> #<?php echo esc_html( $request->ID ); ?></li>
    <li><strong><?php _e( 'Title:', 'arsol-projects-for-woo' ); ?></strong> <?php echo esc_html( $request->post_title ); ?></li>
    <li><strong><?php _e( 'Date:', 'arsol-projects-for-woo' ); ?></strong> <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $request->post_date ) ) ); ?></li>
</ul>

<p><a href="<?php echo esc_url( admin_url( 'post.php?post=' . $request->ID . '&action=edit' ) ); ?>"><?php _e( 'View Request in Admin', 'arsol-projects-for-woo' ); ?></a></p>
<?php endif; ?>

<?php
/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
?> 