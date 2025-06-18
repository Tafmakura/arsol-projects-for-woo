<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\ProjectProposal\Admin;

if (!defined('ABSPATH')) exit;

class Proposal {
    private $validation_errors = array();
    private $post_id_being_saved = null;
    
    public function __construct() {
        // Add meta boxes for single proposal admin screen
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        
        // Save proposal data
        add_action('save_post', array($this, 'save_proposal_details'));
        // Action to set review status when a proposal is published
        add_action('transition_post_status', array($this, 'set_proposal_review_status'), 10, 3);
        
        // Add admin notices for validation errors
        add_action('admin_notices', array($this, 'display_validation_errors'));
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

        // WordPress automatically preserves form data on validation failures - no temporary storage needed

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
                <?php if ($post->post_status === 'publish'): ?>
                    <input type="submit" id="save-post" name="save" class="button button-primary" value="<?php _e('Update', 'arsol-pfw'); ?>">
                <?php else: ?>
                    <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php _e('Publish', 'arsol-pfw'); ?>">
                <?php endif; ?>
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
        // Check if our nonce is set.
        if (!isset($_POST['proposal_details_meta_box_nonce'])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST['proposal_details_meta_box_nonce'], 'proposal_details_meta_box')) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'arsol-pfw-proposal' == $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
            }
        }
        
        // It's safe for us to save the data now.
        $cost_proposal_type = isset($_POST['cost_proposal_type']) ? sanitize_text_field($_POST['cost_proposal_type']) : 'none';
        
        // Store post ID for error display and validate data
        $this->post_id_being_saved = $post_id;
        
        // Determine what kind of save operation this is
        $is_trying_to_publish = isset($_POST['publish']); // Draft -> Publish
        $is_updating_published = isset($_POST['save']) && get_post_status($post_id) === 'publish'; // Published -> Published
        $should_validate = $is_trying_to_publish || $is_updating_published;
        
        if ($should_validate) {
            $validation_errors = $this->validate_proposal_data($post_id, $cost_proposal_type);
            
            if (!empty($validation_errors)) {
                // Store errors for display
                $this->validation_errors = $validation_errors;
                
                if ($is_trying_to_publish) {
                    // Prevent publishing by changing status to draft
                    add_filter('wp_insert_post_data', function($data, $postarr) use ($post_id) {
                        if (isset($postarr['ID']) && $postarr['ID'] == $post_id) {
                            $data['post_status'] = 'draft';
                        }
                        return $data;
                    }, 10, 2);
                } else if ($is_updating_published) {
                    // For published posts, prevent the save entirely to avoid data corruption
                    // The user will see the validation errors and can fix them
                    return;
                }
            }
        }
        
        // Save all meta data normally (no temporary data needed)
        update_post_meta($post_id, '_cost_proposal_type', $cost_proposal_type);

        // Save secondary status
        if (isset($_POST['proposal_secondary_status'])) {
            $secondary_status = sanitize_text_field($_POST['proposal_secondary_status']);
            // Validate the value is one of the allowed options
            if (in_array($secondary_status, ['ready_for_review', 'processing'])) {
                update_post_meta($post_id, '_proposal_secondary_status', $secondary_status);
            }
        }

        // Get currency
        $currency = get_woocommerce_currency();

        // Conditionally save/delete budget data
        if ($cost_proposal_type === 'budget') {
            // Sanitize and save the budget amount
            if (isset($_POST['proposal_budget'])) {
                $budget_amount = wc_format_decimal(sanitize_text_field($_POST['proposal_budget']));
                $budget_data = array(
                    'amount' => $budget_amount,
                    'currency' => $currency
                );
                update_post_meta($post_id, '_proposal_budget', $budget_data);
            }

            // Sanitize and save the recurring budget amount
        if (isset($_POST['proposal_recurring_budget'])) {
                $recurring_budget_amount = wc_format_decimal(sanitize_text_field($_POST['proposal_recurring_budget']));
                $recurring_budget_data = array(
                    'amount' => $recurring_budget_amount,
                    'currency' => $currency
                );
                update_post_meta($post_id, '_proposal_recurring_budget', $recurring_budget_data);
            } else {
                delete_post_meta($post_id, '_proposal_recurring_budget');
        }

            // Save billing cycle if recurring budget is set
            if (!empty($_POST['proposal_recurring_budget']) && $_POST['proposal_recurring_budget'] > 0) {
        if (isset($_POST['proposal_billing_interval'])) {
            update_post_meta($post_id, '_proposal_billing_interval', sanitize_text_field($_POST['proposal_billing_interval']));
        }
        if (isset($_POST['proposal_billing_period'])) {
            update_post_meta($post_id, '_proposal_billing_period', sanitize_text_field($_POST['proposal_billing_period']));
        }
        if (isset($_POST['proposal_recurring_start_date'])) {
            update_post_meta($post_id, '_proposal_recurring_start_date', sanitize_text_field($_POST['proposal_recurring_start_date']));
                }
            } else {
                // If there's no recurring budget, delete the meta
                delete_post_meta($post_id, '_proposal_billing_interval');
                delete_post_meta($post_id, '_proposal_billing_period');
                delete_post_meta($post_id, '_proposal_recurring_start_date');
            }
        } else {
            // If not budget estimates, delete all budget meta to keep things clean
            delete_post_meta($post_id, '_proposal_budget');
            delete_post_meta($post_id, '_proposal_recurring_budget');
            delete_post_meta($post_id, '_proposal_billing_interval');
            delete_post_meta($post_id, '_proposal_billing_period');
            delete_post_meta($post_id, '_proposal_recurring_start_date');
        }

        // Conditionally delete quotation data if it's not the selected type
        if ($cost_proposal_type !== 'quotation') {
             delete_post_meta($post_id, '_arsol_proposal_quotation_line_items');
             delete_post_meta($post_id, '_arsol_proposal_one_time_total');
             delete_post_meta($post_id, '_arsol_proposal_recurring_totals_grouped');
        }

        // Save start date
        if (isset($_POST['proposal_start_date'])) {
            update_post_meta($post_id, '_proposal_start_date', sanitize_text_field($_POST['proposal_start_date']));
        }

        // Save delivery date
        if (isset($_POST['proposal_delivery_date'])) {
            update_post_meta($post_id, '_proposal_delivery_date', sanitize_text_field($_POST['proposal_delivery_date']));
        }

        // Save expiration date
        if (isset($_POST['proposal_expiration_date'])) {
            update_post_meta($post_id, '_proposal_expiration_date', sanitize_text_field($_POST['proposal_expiration_date']));
        }
    }
    
    /**
     * Display validation errors as admin notices
     */
    public function display_validation_errors() {
        global $post;
        
        // Only show on proposal edit screen
        if (!$post || $post->post_type !== 'arsol-pfw-proposal' || empty($this->validation_errors)) {
            return;
        }
        
        // Only show errors for the post that was just saved
        if ($this->post_id_being_saved && $post->ID !== $this->post_id_being_saved) {
            return;
        }
        
        foreach ($this->validation_errors as $error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
        }
        
        // Clear errors after displaying
        $this->validation_errors = array();
        $this->post_id_being_saved = null;
    }
    
    /**
     * Validate proposal data based on type
     */
    private function validate_proposal_data($post_id, $cost_proposal_type) {
        $errors = array();
        
        // Universal validation
        $errors = array_merge($errors, $this->validate_universal_fields($post_id));
        
        // Type-specific validation
        switch ($cost_proposal_type) {
            case 'quotation':
                $errors = array_merge($errors, $this->validate_quotation_fields($post_id));
                break;
            case 'budget':
                $errors = array_merge($errors, $this->validate_budget_fields($post_id));
                break;
        }
        
        return $errors;
    }
    
    /**
     * Validate universal proposal fields
     */
    private function validate_universal_fields($post_id) {
        $errors = array();
        
        // Get post data
        $post = get_post($post_id);
        
        // Validate title
        if (empty($post->post_title) || trim($post->post_title) === '') {
            $errors[] = __('Proposal title is required.', 'arsol-pfw');
        }
        
        // Validate content/description
        if (empty($post->post_content) || trim($post->post_content) === '') {
            $errors[] = __('Proposal description is required.', 'arsol-pfw');
        }
        
        // Validate customer assignment (post author)
        if (empty($post->post_author) || $post->post_author <= 0) {
            $errors[] = __('A customer must be assigned to the proposal.', 'arsol-pfw');
        }
        
        return $errors;
    }
    
    /**
     * Validate quotation-specific fields
     */
    private function validate_quotation_fields($post_id) {
        $errors = array();
        
        // Get quotation line items
        $quotation_line_items = get_post_meta($post_id, '_arsol_proposal_quotation_line_items', true);
        
        if (empty($quotation_line_items) || !is_array($quotation_line_items)) {
            $errors[] = __('Quotation proposals must have at least one line item.', 'arsol-pfw');
            return $errors;
        }
        
        $has_valid_item = false;
        
        // Check products
        if (!empty($quotation_line_items['products']) && is_array($quotation_line_items['products'])) {
            foreach ($quotation_line_items['products'] as $item) {
                if (!empty($item['description']) && !empty($item['regular_price']) && $item['regular_price'] > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        // Check one-time fees
        if (!$has_valid_item && !empty($quotation_line_items['one_time_fees']) && is_array($quotation_line_items['one_time_fees'])) {
            foreach ($quotation_line_items['one_time_fees'] as $item) {
                if (!empty($item['description']) && !empty($item['amount']) && $item['amount'] > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        // Check recurring fees
        if (!$has_valid_item && !empty($quotation_line_items['recurring_fees']) && is_array($quotation_line_items['recurring_fees'])) {
            foreach ($quotation_line_items['recurring_fees'] as $item) {
                if (!empty($item['description']) && !empty($item['amount']) && $item['amount'] > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        // Check shipping fees
        if (!$has_valid_item && !empty($quotation_line_items['shipping_fees']) && is_array($quotation_line_items['shipping_fees'])) {
            foreach ($quotation_line_items['shipping_fees'] as $item) {
                if (!empty($item['description']) && !empty($item['amount']) && $item['amount'] > 0) {
                    $has_valid_item = true;
                    break;
                }
            }
        }
        
        if (!$has_valid_item) {
            $errors[] = __('Quotation proposals must have at least one valid line item with description and amount greater than zero.', 'arsol-pfw');
        }
        
        return $errors;
    }
    
    /**
     * Validate budget-specific fields
     */
    private function validate_budget_fields($post_id) {
        $errors = array();
        
        // Get budget data
        $budget_data = get_post_meta($post_id, '_proposal_budget', true);
        $recurring_budget_data = get_post_meta($post_id, '_proposal_recurring_budget', true);
        
        // At least one budget amount is required
        $has_budget = false;
        
        if (!empty($budget_data['amount']) && $budget_data['amount'] > 0) {
            $has_budget = true;
        }
        
        if (!empty($recurring_budget_data['amount']) && $recurring_budget_data['amount'] > 0) {
            $has_budget = true;
            
            // Validate recurring budget billing cycle
            $billing_interval = get_post_meta($post_id, '_proposal_billing_interval', true);
            $billing_period = get_post_meta($post_id, '_proposal_billing_period', true);
            
            if (empty($billing_interval) || empty($billing_period)) {
                $errors[] = __('Recurring budget requires billing interval and period to be specified.', 'arsol-pfw');
            }
        }
        
        if (!$has_budget) {
            $errors[] = __('Budget proposals must have at least one budget amount greater than zero.', 'arsol-pfw');
        }
        
        return $errors;
    }
}