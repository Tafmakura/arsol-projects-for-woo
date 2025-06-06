<?php
/**
 * Project Header
 *
 * @package Arsol_Projects_For_Woo
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($project)) {
    return;
}
?>

<h2><?php echo esc_html($project['title']); ?></h2> 