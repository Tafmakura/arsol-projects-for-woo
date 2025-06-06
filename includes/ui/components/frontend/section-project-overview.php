<?php
/**
 * Project Overview
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

<div class="project-overview-content">
    <?php echo wp_kses_post($project['content']); ?>
</div> 