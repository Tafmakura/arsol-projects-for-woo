<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal {
    public function __construct() {
        // Add meta boxes for single proposal admin screen
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        
        // Save proposal data
        add_action('save_post', array($this, 'save_proposal_details'));
        // Action to set review status when a proposal is published
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);
    }

    public function set_proposal_review_status($new_status, $old_status, $post) {
        if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
            // Set the review status to 'under-review'
            wp_set_object_terms($post->ID, 'under-review', 'arsol-review-status');
        }
    }

    /**
     * Add proposal details meta box
     */
    public function add_proposal_details_meta_box() {
        add_meta_box(
            'proposal_details_meta_box',
            __('Project Actions', 'arsol-pfw'),
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
        $cost_proposal_type = get_post_meta($post->ID, '_cost_proposal_type', true);
        if (empty($cost_proposal_type)) {
            $cost_proposal_type = 'none'; // Default to none
        }

        $start_date = get_post_meta($post->ID, '_proposal_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_proposal_delivery_date', true);
        $expiration_date = get_post_meta($post->ID, '_proposal_expiration_date', true);

        // Get original request data
        $original_budget = get_post_meta($post->ID, '_original_request_budget', true);
        $original_start_date = get_post_meta($post->ID, '_original_request_start_date', true);
        $original_delivery_date = get_post_meta($post->ID, '_original_request_delivery_date', true);

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'post_author_override',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));

        ?>
        <div class="proposal-details">
        </div>
        <div class="major-actions">
            <div class="arsol-pfw-admin-project-actions">
                <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php echo ($post->post_status === 'publish') ? __('Update', 'arsol-pfw') : __('Publish', 'arsol-pfw'); ?>">
            <?php
            $is_disabled = $post->post_status !== 'publish';
            $convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
            $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
            $confirm_message = esc_js(__('Are you sure you want to convert this proposal to a project? This will create a new project and delete the original proposal. Invoices will be created if selected.', 'arsol-pfw'));
            $tooltip_text = $is_disabled
                ? __('The proposal must be published before it can be converted.', 'arsol-pfw')
                : __('Converts this proposal into a new project.', 'arsol-pfw');
            ?>
            <span title="<?php echo esc_attr($tooltip_text); ?>">
                <input type="button" 
                       class="button button-secondary arsol-confirm-conversion" 
                       value="<?php _e('Convert to Project', 'arsol-pfw'); ?>" 
                       data-url="<?php echo esc_url($convert_url); ?>" 
                       data-message="<?php echo $confirm_message; ?>"
                       <?php disabled($is_disabled, true); ?> />
            </span>
            </div>
        </div>
        <?php
    }

    /**
     * Save proposal details
     */
    public function save_proposal_details($post_id) {
        // Check if this is our post type
        if (get_post_type($post_id) !== 'arsol-pfw-proposal') {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get currency
        $currency = get_woocommerce_currency();
        
        // Get cost proposal type
        $cost_proposal_type = isset($_POST['cost_proposal_type']) ? sanitize_text_field($_POST['cost_proposal_type']) : 'none';
        update_post_meta($post_id, '_cost_proposal_type', $cost_proposal_type);

        // Save secondary status
        if (isset($_POST['proposal_secondary_status'])) {
            $secondary_status = sanitize_text_field($_POST['proposal_secondary_status']);
            if (in_array($secondary_status, ['ready_for_review', 'processing'])) {
                update_post_meta($post_id, '_proposal_secondary_status', $secondary_status);
            }
        }

        // Save basic proposal fields
        if (isset($_POST['proposal_start_date'])) {
            update_post_meta($post_id, '_proposal_start_date', sanitize_text_field($_POST['proposal_start_date']));
        }
        if (isset($_POST['proposal_delivery_date'])) {
            update_post_meta($post_id, '_proposal_delivery_date', sanitize_text_field($_POST['proposal_delivery_date']));
        }
        if (isset($_POST['proposal_expiration_date'])) {
            update_post_meta($post_id, '_proposal_expiration_date', sanitize_text_field($_POST['proposal_expiration_date']));
        }

        // Save notes (for all types)
        if (isset($_POST['arsol_proposal_notes'])) {
            update_post_meta($post_id, '_arsol_proposal_notes', wp_kses_post($_POST['arsol_proposal_notes']));
        }

        // Handle budget data
        if ($cost_proposal_type === 'budget') {
            // Save budget data
            if (isset($_POST['proposal_budget'])) {
                $budget_amount = wc_format_decimal(sanitize_text_field($_POST['proposal_budget']));
                update_post_meta($post_id, '_proposal_onetime_budget', array('amount' => $budget_amount, 'currency' => $currency));
            }
            if (isset($_POST['proposal_budget_details'])) {
                update_post_meta($post_id, '_proposal_onetime_budget_details', sanitize_text_field($_POST['proposal_budget_details']));
            }

            // Save recurring budget data
            if (isset($_POST['proposal_recurring_budget'])) {
                $recurring_budget_amount = wc_format_decimal(sanitize_text_field($_POST['proposal_recurring_budget']));
                update_post_meta($post_id, '_proposal_recurring_budget', array('amount' => $recurring_budget_amount, 'currency' => $currency));
            } else {
                delete_post_meta($post_id, '_proposal_recurring_budget');
            }
            if (isset($_POST['proposal_recurring_budget_details'])) {
                update_post_meta($post_id, '_proposal_recurring_budget_details', sanitize_text_field($_POST['proposal_recurring_budget_details']));
            }

            // Save billing cycle if recurring budget is set
            if (!empty($_POST['proposal_recurring_budget']) && $_POST['proposal_recurring_budget'] > 0) {
                if (isset($_POST['proposal_billing_interval'])) {
                    update_post_meta($post_id, '_proposal_recurring_budget_billing_interval', sanitize_text_field($_POST['proposal_billing_interval']));
                }
                if (isset($_POST['proposal_billing_period'])) {
                    update_post_meta($post_id, '_proposal_recurring_budget_billing_period', sanitize_text_field($_POST['proposal_billing_period']));
                }
                if (isset($_POST['proposal_recurring_start_date'])) {
                    update_post_meta($post_id, '_proposal_recurring_budget_start_date', sanitize_text_field($_POST['proposal_recurring_start_date']));
                }
            } else {
                // If there's no recurring budget, delete the meta
                delete_post_meta($post_id, '_proposal_recurring_budget_billing_interval');
                delete_post_meta($post_id, '_proposal_recurring_budget_billing_period');
                delete_post_meta($post_id, '_proposal_recurring_budget_start_date');
            }

            // Clean up quotation data when budget is selected
            delete_post_meta($post_id, '_arsol_proposal_quotation_line_items');
            delete_post_meta($post_id, '_arsol_proposal_one_time_total');
            delete_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped');
        }
        // Handle quotation data
        elseif ($cost_proposal_type === 'quotation') {
            // Save quotation line items
            if (isset($_POST['quotation_line_items'])) {
                $line_items = $_POST['quotation_line_items'];
                
                // Process and sanitize line items
                $sanitized_items = array();
                foreach ($line_items as $item) {
                    $sanitized_item = array(
                        'type' => sanitize_text_field($item['type']),
                        'description' => sanitize_text_field($item['description']),
                        'amount' => wc_format_decimal($item['amount']),
                        'currency' => $currency
                    );
                    
                    // Add optional fields based on type
                    if (!empty($item['start_date'])) {
                        $sanitized_item['start_date'] = sanitize_text_field($item['start_date']);
                    }
                    if (!empty($item['billing_interval'])) {
                        $sanitized_item['billing_interval'] = sanitize_text_field($item['billing_interval']);
                    }
                    if (!empty($item['billing_period'])) {
                        $sanitized_item['billing_period'] = sanitize_text_field($item['billing_period']);
                    }
                    
                    $sanitized_items[] = $sanitized_item;
                }
                
                update_post_meta($post_id, '_arsol_proposal_quotation_line_items', $sanitized_items);
            }

            // Save totals
            if (isset($_POST['one_time_total'])) {
                update_post_meta($post_id, '_arsol_proposal_one_time_total', wc_format_decimal($_POST['one_time_total']));
            }
            if (isset($_POST['recurring_totals_grouped'])) {
                update_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped', $_POST['recurring_totals_grouped']);
            }

            // Clean up budget data when quotation is selected
            delete_post_meta($post_id, '_proposal_onetime_budget');
            delete_post_meta($post_id, '_proposal_onetime_budget_details');
            delete_post_meta($post_id, '_proposal_recurring_budget');
            delete_post_meta($post_id, '_proposal_recurring_budget_details');
            delete_post_meta($post_id, '_proposal_recurring_budget_billing_interval');
            delete_post_meta($post_id, '_proposal_recurring_budget_billing_period');
            delete_post_meta($post_id, '_proposal_recurring_budget_start_date');
        }
        // Handle 'none' type - clean up all cost proposal data
        else {
            // Clean up budget data
            delete_post_meta($post_id, '_proposal_onetime_budget');
            delete_post_meta($post_id, '_proposal_onetime_budget_details');
            delete_post_meta($post_id, '_proposal_recurring_budget');
            delete_post_meta($post_id, '_proposal_recurring_budget_details');
            delete_post_meta($post_id, '_proposal_recurring_budget_billing_interval');
            delete_post_meta($post_id, '_proposal_recurring_budget_billing_period');
            delete_post_meta($post_id, '_proposal_recurring_budget_start_date');
            
            // Clean up quotation data
            delete_post_meta($post_id, '_arsol_proposal_quotation_line_items');
            delete_post_meta($post_id, '_arsol_proposal_one_time_total');
            delete_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped');
        }
    }
}