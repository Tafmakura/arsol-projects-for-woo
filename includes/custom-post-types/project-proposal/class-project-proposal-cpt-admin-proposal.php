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
        
        // Handle parent_project parameter for new proposals
        add_action('load-post-new.php', array($this, 'handle_new_proposal_from_project'));
        add_action('admin_notices', array($this, 'display_creation_success_message'));
    }

    public function set_proposal_review_status($new_status, $old_status, $post) {
        if ($post->post_type === 'arsol-pfw-proposal' && $new_status === 'publish' && $old_status !== 'publish') {
            // Set the review status to 'pending-approval'
            wp_set_object_terms($post->ID, 'pending-approval', 'arsol-proposal-status');
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
        $cost_proposal_type = get_post_meta($post->ID, '_arsol_pfw_proposal_costing_type', true);
        if (empty($cost_proposal_type)) {
            $cost_proposal_type = 'none'; // Default to none
        }

        $start_date = get_post_meta($post->ID, '_arsol_pfw_proposal_start_date', true);
        $delivery_date = get_post_meta($post->ID, '_arsol_pfw_proposal_delivery_date', true);
        $expiration_date = get_post_meta($post->ID, '_arsol_pfw_proposal_expiration_date', true);

        // Get original request data
        $original_budget = get_post_meta($post->ID, '_arsol_pfw_proposal_request_budget', true);
        $original_start_date = get_post_meta($post->ID, '_arsol_pfw_proposal_request_start_date', true);
        $original_delivery_date = get_post_meta($post->ID, '_arsol_pfw_proposal_request_delivery_date', true);

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
            <!-- Main content area for any future proposal-specific content -->
        </div>
        
        <p class="proposal-conversion-description">
            <?php _e('This action will create a new project based on this proposal and permanently delete the original proposal. The proposal status must be set to "Approved" before conversion. Orders and invoices will be created for quotation proposals. This action cannot be undone.', 'arsol-pfw'); ?>
        </p>
        
        <div class="major-actions">
                <?php if ($post->post_status === 'publish'): ?>
                    <input type="submit" id="save-post" name="save" class="button button-primary" value="<?php _e('Update', 'arsol-pfw'); ?>">
                <?php else: ?>
                    <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php _e('Publish', 'arsol-pfw'); ?>">
                <?php endif; ?>
            
            <?php
            // Check for project-tied proposal - simple URL parameter check
            $is_project_tied = false;
            $parent_project_data = false;
            
            // Check URL parameter first (for new proposals)
            if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
                $parent_project_id = intval($_GET['parent_project']);
                $parent_project = get_post($parent_project_id);
                
                if ($parent_project && $parent_project->post_type === 'arsol-project') {
                    $is_project_tied = true;
                    $parent_project_data = array(
                        'id' => $parent_project_id,
                        'title' => $parent_project->post_title
                    );
                }
            } 
            // Fallback to meta data check (for existing proposals)
            elseif ($post->ID > 0) {
                $parent_project_id = get_post_meta($post->ID, '_arsol_parent_project_id', true);
                if (!empty($parent_project_id)) {
                    $parent_project = get_post($parent_project_id);
                    if ($parent_project && $parent_project->post_type === 'arsol-project') {
                        $is_project_tied = true;
                        $parent_project_data = array(
                            'id' => $parent_project_id,
                            'title' => $parent_project->post_title
                        );
                    }
                }
            }
            
            if ($is_project_tied && $parent_project_data) {
                // Show View Project button for project-tied proposals
                $view_url = admin_url('post.php?post=' . $parent_project_data['id'] . '&action=edit');
                $confirm_message = esc_js(__('This will save the current proposal and return to the parent project. Continue?', 'arsol-projects-for-woo'));
                ?>
                <input type="button" 
                       class="button button-secondary arsol-view-project" 
                       value="<?php _e('View Project', 'arsol-projects-for-woo'); ?>" 
                       data-url="<?php echo esc_url($view_url); ?>" 
                       data-message="<?php echo $confirm_message; ?>" />
                <?php
            } else {
                // Show Convert to Project button for regular proposals
            // Check proposal status for conversion eligibility
            $proposal_status_terms = wp_get_object_terms($post->ID, 'arsol-proposal-status', array('fields' => 'slugs'));
            $current_proposal_status = !empty($proposal_status_terms) ? $proposal_status_terms[0] : '';
            
            $is_not_published = $post->post_status !== 'publish';
            $is_not_approved = $current_proposal_status !== 'approved';
            $is_disabled = $is_not_published || $is_not_approved;
            
            $convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
            $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
            $confirm_message = esc_js(__('Are you sure you want to convert this proposal to a project? This will create a new project and delete the original proposal. Orders and subscriptions will be created if the Quotation costing is selected.', 'arsol-pfw'));
            
            if ($is_not_published) {
                $tooltip_text = __('The proposal must be published before it can be converted.', 'arsol-pfw');
            } elseif ($is_not_approved) {
                $tooltip_text = sprintf(__('The proposal status must be "Approved" before it can be converted. Current status: "%s".', 'arsol-pfw'), $current_proposal_status);
            } else {
                $tooltip_text = __('Converts this proposal into a new project.', 'arsol-pfw');
            }
            ?>
            <span title="<?php echo esc_attr($tooltip_text); ?>">
                <input type="button" 
                       class="button button-secondary arsol-confirm-conversion" 
                       value="<?php _e('Convert to Project', 'arsol-pfw'); ?>" 
                       data-url="<?php echo esc_url($convert_url); ?>" 
                       data-message="<?php echo $confirm_message; ?>"
                       <?php disabled($is_disabled, true); ?> />
            </span>
                <?php
            }
            ?>
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
        $cost_proposal_type = isset($_POST['arsol_pfw_proposal_costing_type']) ? sanitize_text_field($_POST['arsol_pfw_proposal_costing_type']) : 'none';
        
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
                }
                // Note: Removed the early return for published posts to allow meta data saving
                // even when validation fails. Users will still see validation errors.
            }
        }
        
        // Save all meta data normally (no temporary data needed)

        // Save custom title for project-tied proposals
        if (isset($_POST["custom_proposal_title"]) && !empty($_POST["custom_proposal_title"])) {
            $custom_title = sanitize_text_field($_POST["custom_proposal_title"]);
            wp_update_post(array(
                "ID" => $post_id,
                "post_title" => $custom_title
            ));
        }        update_post_meta($post_id, '_arsol_pfw_proposal_costing_type', $cost_proposal_type);

        // Save custom title for project-tied proposals
        if (isset($_POST['custom_proposal_title']) && !empty($_POST['custom_proposal_title'])) {
            $custom_title = sanitize_text_field($_POST['custom_proposal_title']);
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $custom_title
            ));
        }

        // Save secondary status
        if (isset($_POST['arsol_pfw_proposal_secondary_status'])) {
            $secondary_status = sanitize_text_field($_POST['arsol_pfw_proposal_secondary_status']);
            // Validate the value is one of the allowed options
            if (in_array($secondary_status, ['ready_for_review', 'processing'])) {
                update_post_meta($post_id, '_arsol_pfw_proposal_secondary_status', $secondary_status);
            }
        }

        // Get currency
        $currency = get_woocommerce_currency();

        // Conditionally save/delete budget data
        if ($cost_proposal_type === 'budget') {
            // Sanitize and save the budget amount
            if (isset($_POST['arsol_pfw_proposal_budget_onetime_amount'])) {
                $budget_input = $_POST['arsol_pfw_proposal_budget_onetime_amount'];
                
                // Handle both form input (string) and conversion data (array)
                if (is_array($budget_input) && isset($budget_input['amount'])) {
                    // Data is already in correct format from conversion
                    $budget_data = array(
                        'amount' => $budget_input['amount'],
                        'currency' => isset($budget_input['currency']) ? $budget_input['currency'] : $currency
                    );
                } else {
                    // Data is from form input, needs processing
                    if (!empty($budget_input)) {
                        $cleaned_input = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($budget_input);
                        $budget_amount = wc_format_decimal($cleaned_input);
                        $budget_data = array(
                            'amount' => $budget_amount,
                            'currency' => $currency
                        );
                    }
                }
                update_post_meta($post_id, '_arsol_pfw_proposal_budget_onetime_amount', $budget_data);
            }
            
            // Save budget details
            if (isset($_POST['arsol_pfw_proposal_budget_onetime_amount_details'])) {
                update_post_meta($post_id, '_arsol_pfw_proposal_budget_onetime_amount_details', sanitize_text_field($_POST['arsol_pfw_proposal_budget_onetime_amount_details']));
            }

            // Sanitize and save the recurring budget amount
            if (isset($_POST['arsol_pfw_proposal_budget_recurring_amount'])) {
                $recurring_budget_input = $_POST['arsol_pfw_proposal_budget_recurring_amount'];
                
                // Handle both form input (string) and conversion data (array)
                if (is_array($recurring_budget_input) && isset($recurring_budget_input['amount'])) {
                    // Data is already in correct format from conversion
                    $recurring_budget_data = array(
                        'amount' => $recurring_budget_input['amount'],
                        'currency' => isset($recurring_budget_input['currency']) ? $recurring_budget_input['currency'] : $currency
                    );
                } else {
                    // Data is from form input, needs processing
                    if (!empty($recurring_budget_input)) {
                        $cleaned_recurring_input = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($recurring_budget_input);
                        $recurring_budget_amount = wc_format_decimal($cleaned_recurring_input);
                        $recurring_budget_data = array(
                            'amount' => $recurring_budget_amount,
                            'currency' => $currency
                        );
                    }
                }
                update_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount', $recurring_budget_data);
            } else {
                delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount');
            }
        
            // Save recurring budget details
            if (isset($_POST['arsol_pfw_proposal_budget_recurring_amount_details'])) {
                update_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_details', sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_details']));
        }

            // Save billing cycle if recurring budget is set
            $recurring_budget_value = $_POST['arsol_pfw_proposal_budget_recurring_amount'] ?? null;
            $has_recurring_budget = false;
            
            if ($recurring_budget_value) {
                if (is_array($recurring_budget_value) && isset($recurring_budget_value['amount'])) {
                    $has_recurring_budget = $recurring_budget_value['amount'] > 0;
                } else {
                    $has_recurring_budget = $recurring_budget_value > 0;
                }
            }
            
            if ($has_recurring_budget) {
                if (isset($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_interval'])) {
                    update_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_billing_interval', sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_interval']));
                }
                if (isset($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_period'])) {
                    update_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_billing_period', sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_period']));
                }
                if (isset($_POST['arsol_pfw_proposal_budget_recurring_billing_start_date'])) {
                    update_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_billing_start_date', sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_billing_start_date']));
                }
            } else {
                // If there's no recurring budget, delete the meta
                delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_billing_interval');
                delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_billing_period');
                delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_billing_start_date');
            }
        } else {
            // If not budget estimates, delete all budget meta to keep things clean
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_onetime_amount');
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_onetime_amount_details');
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount');
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_details');
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_billing_interval');
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_amount_billing_period');
            delete_post_meta($post_id, '_arsol_pfw_proposal_budget_recurring_billing_start_date');
        }

        // Conditionally delete quotation data if it's not the selected type
        if ($cost_proposal_type !== 'quotation') {
             delete_post_meta($post_id, '_arsol_pfw_proposal_quotation_line_items');
             delete_post_meta($post_id, '_arsol_pfw_proposal_quotation_onetime_total');
             delete_post_meta($post_id, '_arsol_pfw_proposal_quotation_recurring_totals_grouped');
        }

        // Save start date
        if (isset($_POST['arsol_pfw_proposal_start_date'])) {
            update_post_meta($post_id, '_arsol_pfw_proposal_start_date', sanitize_text_field($_POST['arsol_pfw_proposal_start_date']));
        }

        // Save delivery date
        if (isset($_POST['arsol_pfw_proposal_delivery_date'])) {
            update_post_meta($post_id, '_arsol_pfw_proposal_delivery_date', sanitize_text_field($_POST['arsol_pfw_proposal_delivery_date']));
        }

        // Save expiration date
        if (isset($_POST['arsol_pfw_proposal_expiration_date'])) {
            update_post_meta($post_id, '_arsol_pfw_proposal_expiration_date', sanitize_text_field($_POST['arsol_pfw_proposal_expiration_date']));
        }
        
        // Save proposal notes (used by both budget and quotation types)
        if (isset($_POST['arsol_pfw_proposal_notes'])) {
            update_post_meta($post_id, '_arsol_pfw_proposal_notes', wp_kses_post($_POST['arsol_pfw_proposal_notes']));
        }
        
        // Handle parent project ID for project-tied proposals
        // Check if this is a new proposal created from a project
        if (get_post_status($post_id) === 'auto-draft' || (get_post_status($post_id) === 'draft' && !get_post_meta($post_id, '_arsol_parent_project_id', true))) {
            $creation_data = get_transient('arsol_proposal_created_from_project_' . get_current_user_id());
            if ($creation_data && is_array($creation_data) && isset($creation_data['parent_project_id'])) {
                update_post_meta($post_id, '_arsol_parent_project_id', intval($creation_data['parent_project_id']));
                
                // Set default title if not already set
                $current_title = get_the_title($post_id);
                if (empty($current_title) || $current_title === 'Auto Draft') {
                    wp_update_post(array(
                        'ID' => $post_id,
                        'post_title' => $creation_data['default_title']
                    ));
                }
            }
        }
        
        // Handle view project after save
        if (isset($_POST['arsol_view_after_save']) && !empty($_POST['arsol_view_after_save'])) {
            wp_redirect(esc_url_raw($_POST['arsol_view_after_save']));
            exit;
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
        $quotation_line_items = get_post_meta($post_id, '_arsol_pfw_proposal_quotation_line_items', true);
        
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
        
        // Validate against POST data (what's being submitted) not existing meta data
        $proposal_budget = isset($_POST['arsol_pfw_proposal_budget_onetime_amount']) ? $_POST['arsol_pfw_proposal_budget_onetime_amount'] : '';
        $proposal_recurring_budget = isset($_POST['arsol_pfw_proposal_budget_recurring_amount']) ? $_POST['arsol_pfw_proposal_budget_recurring_amount'] : '';
        
        // Convert to numeric values for validation, handling both string and array inputs
        $budget_amount = 0;
        if (!empty($proposal_budget)) {
            if (is_array($proposal_budget) && isset($proposal_budget['amount'])) {
                $budget_amount = floatval($proposal_budget['amount']);
            } else {
                // Remove commas and other non-numeric characters except decimal point
                $cleaned_budget = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($proposal_budget);
                $budget_amount = floatval(wc_format_decimal($cleaned_budget));
            }
        }
        
        $recurring_budget_amount = 0;
        if (!empty($proposal_recurring_budget)) {
            if (is_array($proposal_recurring_budget) && isset($proposal_recurring_budget['amount'])) {
                $recurring_budget_amount = floatval($proposal_recurring_budget['amount']);
            } else {
                // Remove commas and other non-numeric characters except decimal point
                $cleaned_recurring_budget = \Arsol_Projects_For_Woo\Woocommerce::clean_amount_input($proposal_recurring_budget);
                $recurring_budget_amount = floatval(wc_format_decimal($cleaned_recurring_budget));
            }
        }
        
        // At least one budget amount is required
        $has_budget = false;
        
        if ($budget_amount > 0) {
            $has_budget = true;
        }
        
        if ($recurring_budget_amount > 0) {
            $has_budget = true;
            
            // Validate recurring budget billing cycle from POST data
            $billing_interval = isset($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_interval']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_interval']) : '';
            $billing_period = isset($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_period']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_period']) : '';
            
            if (empty($billing_interval) || empty($billing_period)) {
                $errors[] = __('Recurring budget requires billing interval and period to be specified.', 'arsol-pfw');
            }
        }
        
        if (!$has_budget) {
            $errors[] = __('Budget proposals must have at least one budget amount greater than zero.', 'arsol-pfw');
        }
        
        return $errors;
    }
    
    /**
     * Handle creation of new proposal from project
     */
    public function handle_new_proposal_from_project() {
        // Only handle proposal creation
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Check for parent_project parameter
        if (!isset($_GET['parent_project']) || empty($_GET['parent_project'])) {
            return;
        }
        
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'arsol_create_proposal_nonce')) {
            wp_die(__('Security check failed.', 'arsol-projects-for-woo'));
        }
        
        $parent_project_id = intval($_GET['parent_project']);
        
        // Verify parent project exists and user can access it
        $parent_project = get_post($parent_project_id);
        if (!$parent_project || $parent_project->post_type !== 'arsol-project') {
            wp_die(__('Invalid project.', 'arsol-projects-for-woo'));
        }
        
        if (!current_user_can('edit_post', $parent_project_id)) {
            wp_die(__('You do not have permission to create proposals from this project.', 'arsol-projects-for-woo'));
        }
        
        // Store parent project info for success message and proposal creation
        set_transient('arsol_proposal_created_from_project_' . get_current_user_id(), array(
            'parent_project_id' => $parent_project_id,
            'parent_project_title' => $parent_project->post_title,
            'default_title' => 'Proposal for ' . $parent_project->post_title
        ), 300); // 5 minutes
    }
    
    /**
     * Display success message when proposal is created from project
     */
    public function display_creation_success_message() {
        global $post;
        
        // Only show on proposal edit screen
        if (!$post || $post->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Only show on new proposal creation (not existing proposals)
        if ($post->post_status !== 'auto-draft') {
            return;
        }
        
        // Check for transient data
        $creation_data = get_transient('arsol_proposal_created_from_project_' . get_current_user_id());
        if (!$creation_data || !is_array($creation_data)) {
            return;
        }
        
        // Clear the transient
        delete_transient('arsol_proposal_created_from_project_' . get_current_user_id());
        
        $parent_project_title = esc_html($creation_data['parent_project_title']);
        $parent_project_id = intval($creation_data['parent_project_id']);
        $parent_project_url = admin_url('post.php?post=' . $parent_project_id . '&action=edit');
        
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>' . sprintf(
            __('This proposal has been created based on project "%s". You can <a href="%s">return to the project</a> or continue editing this proposal.', 'arsol-projects-for-woo'),
            $parent_project_title,
            esc_url($parent_project_url)
        ) . '</p>';
        echo '</div>';
    }
    
    /**
     * Check if proposal is project-tied
     * @param int $post_id Proposal post ID
     * @return bool True if proposal is tied to a project
     */
    public function is_project_tied_proposal($post_id) {
        $parent_project_id = get_post_meta($post_id, '_arsol_parent_project_id', true);
        return !empty($parent_project_id) && is_numeric($parent_project_id);
    }
    
    /**
     * Get parent project data for project-tied proposal
     * @param int $post_id Proposal post ID
     * @return array|false Parent project data or false if not project-tied
     */
    public function get_parent_project_data($post_id) {
        if (!$this->is_project_tied_proposal($post_id)) {
            return false;
        }
        
        $parent_project_id = get_post_meta($post_id, '_arsol_parent_project_id', true);
        $parent_project = get_post($parent_project_id);
        
        if (!$parent_project || $parent_project->post_type !== 'arsol-project') {
            return false;
        }
        
        // Get parent project's customer and lead data
        $customer_id = get_post_meta($parent_project_id, '_arsol_pfw_project_customer_id', true);
        $lead_id = get_post_meta($parent_project_id, '_arsol_pfw_project_lead_id', true);
        
        return array(
            'id' => $parent_project_id,
            'title' => $parent_project->post_title,
            'customer_id' => $customer_id,
            'lead_id' => $lead_id
        );
    }
}