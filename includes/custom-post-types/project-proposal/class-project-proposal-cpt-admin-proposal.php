<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal {
    public function __construct() {
        // Add meta boxes for single proposal admin screen
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        // Save proposal data
        add_action('save_post_arsol-pfw-proposal', array($this, 'save_proposal_details'));
    }

    /**
     * Add proposal details meta box
     */
    public function add_proposal_details_meta_box() {
        add_meta_box(
            'proposal_details_meta_box',
            __('Proposal Details', 'arsol-pfw'),
            array($this, 'render_proposal_details_meta_box'),
            'arsol-pfw-proposal',
            'side',
            'default'
        );
    }

    /**
     * Render proposal details meta box
     */
    public function render_proposal_details_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('proposal_details_meta_box', 'proposal_details_meta_box_nonce');

        // Get current values
        $current_status = wp_get_object_terms($post->ID, 'arsol-proposal-status', array('fields' => 'slugs'));
        $current_status = !empty($current_status) ? $current_status[0] : 'pending';
        $budget = get_post_meta($post->ID, '_proposal_budget', true);
        $start_date = get_post_meta($post->ID, '_proposal_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_proposal_delivery_date', true);
        $related_request = get_post_meta($post->ID, '_related_request', true);
        
        // Get statuses
        $statuses = get_terms(array(
            'taxonomy' => 'arsol-proposal-status',
            'hide_empty' => false,
        ));

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));

        // Get related request dropdown
        $requests = get_posts(array(
            'post_type' => 'arsol-pfw-request',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish'
        ));
        ?>
        <div class="proposal-details">
            <p>
                <label for="proposal_code" style="display:block;margin-bottom:5px;"><?php _e('Proposal Code:', 'arsol-pfw'); ?></label>
                <input type="text" 
                       id="proposal_code" 
                       value="<?php echo esc_attr($post->ID); ?>"
                       disabled
                       class="widefat">
            </p>

            <p>
                <label for="post_author_override" style="display:block;margin-bottom:5px;"><?php _e('Customer:', 'arsol-pfw'); ?></label>
                <?php echo $author_dropdown; ?>
            </p>

            <p>
                <label for="related_request" style="display:block;margin-bottom:5px;"><?php _e('Related Request:', 'arsol-pfw'); ?></label>
                <select name="related_request" id="related_request" class="widefat">
                    <option value=""><?php _e('Select Related Request', 'arsol-pfw'); ?></option>
                    <?php foreach ($requests as $request) : ?>
                        <option value="<?php echo esc_attr($request->ID); ?>" <?php selected($related_request, $request->ID); ?>>
                            <?php echo esc_html($request->post_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="proposal_status" style="display:block;margin-bottom:5px;"><?php _e('Proposal Status:', 'arsol-pfw'); ?></label>
                <select name="proposal_status" id="proposal_status" class="widefat">
                    <?php foreach ($statuses as $status) : ?>
                        <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($current_status, $status->slug); ?>>
                            <?php echo esc_html($status->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <label for="proposal_budget" style="display:block;margin-bottom:5px;"><?php _e('Budget:', 'arsol-pfw'); ?></label>
                <input type="number" 
                       id="proposal_budget" 
                       name="proposal_budget" 
                       value="<?php echo esc_attr($budget); ?>"
                       class="widefat"
                       step="0.01"
                       min="0">
            </p>

            <p>
                <label for="proposal_start_date" style="display:block;margin-bottom:5px;"><?php _e('Proposed Start Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="proposal_start_date" 
                       name="proposal_start_date" 
                       value="<?php echo esc_attr($start_date); ?>"
                       class="widefat">
            </p>

            <p>
                <label for="proposal_delivery_date" style="display:block;margin-bottom:5px;"><?php _e('Proposed Delivery Date:', 'arsol-pfw'); ?></label>
                <input type="date" 
                       id="proposal_delivery_date" 
                       name="proposal_delivery_date" 
                       value="<?php echo esc_attr($delivery_date); ?>"
                       class="widefat">
            </p>
        </div>
        <?php
    }

    /**
     * Save proposal details
     */
    public function save_proposal_details($post_id) {
        // Check if our nonce is set
        if (!isset($_POST['proposal_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid
        if (!wp_verify_nonce($_POST['proposal_details_meta_box_nonce'], 'proposal_details_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save proposal status
        if (isset($_POST['proposal_status'])) {
            wp_set_object_terms($post_id, sanitize_text_field($_POST['proposal_status']), 'arsol-proposal-status', false);
        }

        // Save related request
        if (isset($_POST['related_request'])) {
            update_post_meta($post_id, '_related_request', sanitize_text_field($_POST['related_request']));
        }

        // Save budget
        if (isset($_POST['proposal_budget'])) {
            update_post_meta($post_id, '_proposal_budget', sanitize_text_field($_POST['proposal_budget']));
        }

        // Save start date
        if (isset($_POST['proposal_start_date'])) {
            update_post_meta($post_id, '_proposal_start_date', sanitize_text_field($_POST['proposal_start_date']));
        }

        // Save delivery date
        if (isset($_POST['proposal_delivery_date'])) {
            update_post_meta($post_id, '_proposal_delivery_date', sanitize_text_field($_POST['proposal_delivery_date']));
        }
    }
}
