<?php
/**
 * No Access template
 * 
 * Shows when user has no access to any projects-related content
 * This template replaces the entire content wrapper structure
 *
 * @package Arsol_Projects_For_Woo
 * @version 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Set project type for hook compatibility
$project_type = 'no-access';

// Prepare wrapper data for hooks
$wrapper_data = array('no_access' => true);

/**
 * Hook: arsol_pfw_project_wrapper_before
 * 
 * @param string $project_type Project type: 'no-access'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_before', $project_type, $wrapper_data);
?>

<div class="arsol-pfw-no-access">
    <div class="arsol-pfw-header">
        <h3 class="arsol-pfw-title"><?php $title = \Arsol_Projects_For_Woo\Core\Access_Handler::get_no_access_title($context);
        echo esc_html($title); ?></h3>
    </div>
    
    <div class="arsol-pfw-content-wrapper" id="no-access-wrapper">
        <?php
        /**
         * Hook: arsol_pfw_project_wrapper_start
         * 
         * @param string $project_type Project type: 'no-access'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_wrapper_start', $project_type, $wrapper_data);
        ?>
        
        <div class="arsol-pfw-content">
            <?php
            /**
             * Hook: arsol_pfw_project_content_before
             * 
             * @param string $project_type Project type: 'no-access'
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_content_before', $project_type, $wrapper_data);
            ?>
            
            <div class="arsol-no-permission">
                <?php
                // Check for shortcode override using the new system
                $override = \Arsol_Projects_For_Woo\Frontend\Template\Overrides::get_shortcode_override('[arsol_pfw_no_access]');
                if ($override) {
                    echo do_shortcode($override);
                } else {
                    echo do_shortcode('[arsol_pfw_no_access]');
                }
                ?>
            </div>
            
            <?php
            /**
             * Hook: arsol_pfw_project_content_after
             * 
             * @param string $project_type Project type: 'no-access'
             * @param array $data Wrapper data
             */
            do_action('arsol_pfw_project_content_after', $project_type, $wrapper_data);
            ?>
        </div>
        
        <?php
        /**
         * Hook: arsol_pfw_project_wrapper_end
         * 
         * @param string $project_type Project type: 'no-access'
         * @param array $data Wrapper data
         */
        do_action('arsol_pfw_project_wrapper_end', $project_type, $wrapper_data);
        ?>
    </div>
</div>

<?php
/**
 * Hook: arsol_pfw_project_wrapper_after
 * 
 * @param string $project_type Project type: 'no-access'
 * @param array $data Wrapper data
 */
do_action('arsol_pfw_project_wrapper_after', $project_type, $wrapper_data);
?>
