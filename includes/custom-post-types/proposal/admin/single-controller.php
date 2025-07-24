<?php

namespace Arsol_Projects_For_Woo\Custom_Post_Types\Proposal\Admin;

if (!defined('ABSPATH')) exit;

class Single_Controller {
    private $validation_errors = array();
    private $post_id_being_saved = null;
    
    public function __construct() {
        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_proposal_details_meta_box'));
        
        // Save proposal details
        add_action('save_post_arsol-pfw-proposal', array($this, 'save_proposal_details'));
        
        // Initialize proposal stage on creation
        add_action('wp_insert_post', array($this, 'initialize_proposal_stage'), 10, 3);
        
        // Prevent deletion if proposal has associated projects
        add_action('before_delete_post', array($this, 'prevent_proposal_deletion_with_projects'));
    }

    /**
     * Initialize proposal stage when first created
     */
    public function initialize_proposal_stage($post_id, $post, $update) {
        // Only run for new proposals, not updates
        if ($update || $post->post_type !== 'arsol-pfw-proposal') {
            return;
        }
        
        // Initialize the stage entity with default stage
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        $current_stage = $proposal->get_stage();
        
        // Only set if not already set
        if (empty($current_stage)) {
            $proposal->set_stage('draft');
        }
    }

    /**
     * Add proposal actions meta box
     */
    public function add_proposal_details_meta_box() {
        add_meta_box(
            'arsol-pfw-proposal-actions-metabox',
            __('Proposal Actions', 'arsol-pfw'),
            array($this, 'render_proposal_actions_metabox'),
            'arsol-pfw-proposal',
            'side',
            'high'
        );

        // Customer Notice metabox
        add_meta_box(
            'arsol-pfw-proposal-customer-notice-metabox',
            __('Customer Notice', 'arsol-pfw'),
            array($this, 'render_customer_notice_metabox'),
            'arsol-pfw-proposal',
            'normal',
            'high'
        );
    }

    /**
     * Render proposal actions metabox
     */
    public function render_proposal_actions_metabox($post) {
        // Add nonce for security
        wp_nonce_field('arsol-pfw-proposal-actions-metabox', 'arsol_pfw_proposal_actions_metabox_nonce');

        // Get current values
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
        // Get proposal costing type
        $proposal_costing_type = $proposal->get_costing_type();
        if (empty($proposal_costing_type)) {
            $proposal_costing_type = 'none'; // Default to none
        }

        $start_date = $proposal->get_start_date();
        $delivery_date = $proposal->get_due_date();
        $expiration_date = $proposal->get_proposal_expiration_date();

        // Get original request data for comparison
        $requested_budget = $proposal->get_requested_project_budget();
        $requested_start_date = $proposal->get_requested_project_start_date();
        $request_due_date = $proposal->get_requested_project_due_date();

        // WordPress automatically preserves form data on validation failures - no temporary storage needed

        // Get author dropdown
        $author_dropdown = wp_dropdown_users(array(
            'name' => 'customer_id',
            'selected' => $post->post_author,
            'include_selected' => true,
            'echo' => false,
            'class' => 'widefat'
        ));

        ?>
        <div class="proposal-details">
            <!-- Main content area for any future proposal-specific content -->
        </div>
        
        <?php
        // Check for project-tied proposal first to determine message
        $is_project_tied = false;
        $parent_project_data = false;
        
        // Check URL parameter first (for new proposals)
        if (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
            $parent_project_id = intval($_GET['parent_project']);
            $parent_project = get_post($parent_project_id);
            
            if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                $is_project_tied = true;
                $parent_project_data = array(
                    'id' => $parent_project_id,
                    'title' => $parent_project->post_title
                );
            }
        } 
        // Fallback to meta data check (for existing proposals)
        elseif ($post->ID > 0) {
            $parent_project_id = $proposal->get_parent_project_id();
            if (!empty($parent_project_id)) {
                $parent_project = get_post($parent_project_id);
                if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                    $is_project_tied = true;
                    $parent_project_data = array(
                        'id' => $parent_project_id,
                        'title' => $parent_project->post_title
                    );
                }
            }
        }
        ?>
        
        <div class="major-actions">
                <?php if ($post->post_status === 'publish'): ?>
                    <input type="submit" id="save-post" name="save" class="button button-primary" value="<?php _e('Update', 'arsol-pfw'); ?>">
                <?php else: ?>
                    <input type="submit" id="publish" name="publish" class="button button-primary" value="<?php _e('Publish', 'arsol-pfw'); ?>">
                <?php endif; ?>
            
            <?php
            // Use the project-tied data we already determined above
            if ($is_project_tied && $parent_project_data) {
                // Show View Project button for project-tied proposals
                $view_url = admin_url('post.php?post=' . $parent_project_data['id'] . '&action=edit');
                ?>
                <a href="<?php echo esc_url($view_url); ?>" 
                   class="button button-secondary" 
                   target="_blank" 
                   rel="noopener noreferrer">
                    <?php _e('View Project', 'arsol-pfw'); ?>
                </a>
                <?php
            } else {
                // Show Convert to Project button for regular proposals
            // Check if proposal is published for conversion eligibility
            $is_disabled = $post->post_status !== 'publish';
            
            $convert_url = admin_url('admin-post.php?action=arsol_convert_to_project&proposal_id=' . $post->ID);
            $convert_url = wp_nonce_url($convert_url, 'arsol_convert_to_project_nonce');
            $confirm_message = esc_js(__('Are you sure you want to convert this proposal to a project? This will create a new project and delete the original proposal. Orders and subscriptions will be created if the Quotation costing is selected.', 'arsol-pfw'));
            
            $tooltip_text = $is_disabled
                ? __('The proposal must be published before it can be converted.', 'arsol-pfw')
                : __('Converts this proposal into a new project.', 'arsol-pfw');
            ?>
            <?php if (!$is_disabled): ?>
                <a href="<?php echo esc_url($convert_url); ?>" 
                   class="button button-secondary" 
                   onclick="return confirm('<?php echo $confirm_message; ?>');">
                    <?php _e('Convert to Project', 'arsol-pfw'); ?>
                </a>
            <?php else: ?>
                <span title="<?php echo esc_attr($tooltip_text); ?>">
                    <button class="button button-secondary" disabled><?php _e('Convert to Project', 'arsol-pfw'); ?></button>
                </span>
            <?php endif; ?>
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
        $proposal_costing_type = isset($_POST['arsol_pfw_proposal_costing_type']) ? sanitize_text_field($_POST['arsol_pfw_proposal_costing_type']) : 'none';
        
        // Store post ID for error display and validate data
        $this->post_id_being_saved = $post_id;
        
        // Determine what kind of save operation this is
        $is_trying_to_publish = isset($_POST['publish']); // Draft -> Publish
        $is_updating_published = isset($_POST['save']) && get_post_status($post_id) === 'publish'; // Published -> Published
        $should_validate = $is_trying_to_publish || $is_updating_published;
        
        if ($should_validate) {
        $validation_errors = $this->validate_proposal_data($post_id, $proposal_costing_type);
        
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
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        $proposal->set_proposal_costing_type($proposal_costing_type);

        // Handle project-tied proposal meta keys - CONSOLIDATED LOGIC
        $parent_project_id = null;
        
        // Priority 1: Hidden input from form (for existing project-tied proposals)
        if (isset($_POST['parent_project_id']) && !empty($_POST['parent_project_id'])) {
            $parent_project_id = intval($_POST['parent_project_id']);
        }
        // Priority 2: URL parameter (for new proposals created from project)
        elseif (isset($_GET['parent_project']) && !empty($_GET['parent_project'])) {
            $parent_project_id = intval($_GET['parent_project']);
        }
        
        // If we have a parent project ID, validate and save it
        if ($parent_project_id) {
            $parent_project = get_post($parent_project_id);
            
            if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                // Save parent project ID with proper naming convention
                $proposal->set_parent_project_id($parent_project_id);
            }
        }
        
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
                $proposal->set_proposal_secondary_status($secondary_status);
            }
        }

        // Get currency
        $currency = get_woocommerce_currency();

        // Conditionally save/delete budget data
        if ($proposal_costing_type === 'budget') {
            $budget_data = array();
            
            // Handle one-time budget
            if (isset($_POST['arsol_pfw_proposal_budget_onetime_amount']) && !empty($_POST['arsol_pfw_proposal_budget_onetime_amount'])) {
                $onetime_amount = \Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration::clean_amount_input($_POST['arsol_pfw_proposal_budget_onetime_amount']);
                $onetime_amount = floatval(wc_format_decimal($onetime_amount));
                
                if ($onetime_amount > 0) {
                    $onetime_details = isset($_POST['arsol_pfw_proposal_budget_onetime_amount_details']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_onetime_amount_details']) : '';
                    
                    $budget_data[] = array(
                        'type' => 'one_time',
                        'amount' => $onetime_amount,
                        'description' => $onetime_details,
                        'currency' => $currency,
                        'currency_symbol' => get_woocommerce_currency_symbol($currency)
                    );
                }
            }
            
            // Handle recurring budget
            if (isset($_POST['arsol_pfw_proposal_budget_recurring_amount']) && !empty($_POST['arsol_pfw_proposal_budget_recurring_amount'])) {
                $recurring_amount = \Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration::clean_amount_input($_POST['arsol_pfw_proposal_budget_recurring_amount']);
                $recurring_amount = floatval(wc_format_decimal($recurring_amount));
                
                if ($recurring_amount > 0) {
                    $recurring_details = isset($_POST['arsol_pfw_proposal_budget_recurring_amount_details']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_details']) : '';
                    $billing_interval = isset($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_interval']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_interval']) : '1';
                    $billing_period = isset($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_period']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_amount_billing_period']) : 'month';
                    $start_date = isset($_POST['arsol_pfw_proposal_budget_recurring_billing_start_date']) ? sanitize_text_field($_POST['arsol_pfw_proposal_budget_recurring_billing_start_date']) : '';
                    
                    $budget_data[] = array(
                        'type' => 'recurring',
                        'amount' => $recurring_amount,
                        'description' => $recurring_details,
                        'currency' => $currency,
                        'currency_symbol' => get_woocommerce_currency_symbol($currency),
                        'billing_interval' => $billing_interval,
                        'billing_period' => $billing_period,
                        'start_date' => $start_date
                    );
                }
            }
            
            $proposal->set_budget($budget_data);
            
        } else {
            // If not budget estimates, clear budget data
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
            $proposal->set_budget(array());
        }

        // Conditionally delete quotation data if it's not the selected type
        if ($proposal_costing_type !== 'quotation') {
             delete_post_meta($post_id, '_arsol_pfw_proposed_project_quotation');
        }

        // Save start date
        if (isset($_POST['arsol_pfw_proposal_start_date'])) {
            update_post_meta($post_id, '_arsol_pfw_proposed_project_start_date', sanitize_text_field($_POST['arsol_pfw_proposal_start_date']));
        }

        // Save due date
        if (isset($_POST['arsol_pfw_proposal_due_date'])) {
            update_post_meta($post_id, '_arsol_pfw_proposed_project_due_date', sanitize_text_field($_POST['arsol_pfw_proposal_due_date']));
        }

        // Save project lead
        if (isset($_POST['proposal_project_lead'])) {
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
            $proposal->set_proposed_project_lead(sanitize_text_field($_POST['proposal_project_lead']));
        }

        // Save expiration date
        if (isset($_POST['arsol_pfw_proposal_expiration_date'])) {
            update_post_meta($post_id, '_arsol_pfw_proposal_expiration_date', sanitize_text_field($_POST['arsol_pfw_proposal_expiration_date']));
        }
        
        // Save proposal notes based on costing type
        if ($proposal_costing_type === 'budget') {
            // Save notes
            $proposal->set_proposal_budget_notes(wp_kses_post($_POST['arsol_pfw_proposal_notes']));
        } elseif ($proposal_costing_type === 'quotation') {
            // Save proposal quotation notes
            if (isset($_POST['arsol_pfw_proposal_quotation_notes'])) {
                $proposal->set_proposal_quotation_notes(wp_kses_post($_POST['arsol_pfw_proposal_quotation_notes']));
            }
        }
        
        // Save customer notice
        if (isset($_POST['proposal_customer_notice_section_nonce']) && wp_verify_nonce($_POST['proposal_customer_notice_section_nonce'], 'proposal_customer_notice_section')) {
            if (isset($_POST['arsol_pfw_proposal_customer_notice'])) {
                $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
                $proposal->set_proposal_customer_notice(wp_kses_post($_POST['arsol_pfw_proposal_customer_notice']));
                $proposal->save();
            }
        }
        
        // Save customer ID from customer_id field
        if (isset($_POST['customer_id']) && !empty($_POST['customer_id'])) {
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
            $proposal->set_customer_id(intval($_POST['customer_id']));
            $proposal->save();
        }
        
        // Handle parent project ID for project-tied proposals
        // Check if this is a new proposal created from a project
        if (get_post_status($post_id) === 'auto-draft' || (get_post_status($post_id) === 'draft' && !$proposal->get_parent_project_id())) {
            $creation_data = get_transient('arsol_proposal_created_from_project_' . get_current_user_id());
            if ($creation_data && is_array($creation_data) && isset($creation_data['parent_project_id'])) {
                $proposal->set_parent_project_id(intval($creation_data['parent_project_id']));
                
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
        
        // Handle conversion after save (WordPress-native approach)
        if (isset($_POST['arsol_convert_after_save']) && !empty($_POST['arsol_convert_after_save'])) {
            // No stage check – proceed with conversion redirect
            $conversion_url = esc_url_raw($_POST['arsol_convert_after_save']);
            add_action('admin_notices', function() use ($conversion_url) {
                echo '<script type="text/javascript">
                    setTimeout(function() {
                        window.location.href = "' . $conversion_url . '";
                    }, 100);
                </script>';
            });
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
    private function validate_proposal_data($post_id, $proposal_costing_type) {
        $errors = array();
        
        // Universal validation
        $errors = array_merge($errors, $this->validate_universal_fields($post_id));
        
        // Type-specific validation
        switch ($proposal_costing_type) {
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
        
        // Validate customer assignment (customer ID from meta)
        $customer_id = get_post_meta($post_id, '_arsol_pfw_customer_id', true);
        if (empty($customer_id) || $customer_id <= 0) {
            $errors[] = __('A customer must be assigned to the proposal.', 'arsol-pfw');
        }
        
        return $errors;
    }
    
    /**
     * Validate quotation-specific fields
     */
    private function validate_quotation_fields($post_id) {
        $errors = array();
        
        // Get quotation line items using entity method
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        $quotation_line_items = $proposal->get_quotation();
        
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
        
        if (!$has_valid_item) {
            $errors[] = __('Quotation proposals must have at least one valid line item with description and amount.', 'arsol-pfw');
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
                $cleaned_budget = \Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration::clean_amount_input($proposal_budget);
                $budget_amount = floatval(wc_format_decimal($cleaned_budget));
            }
        }
        
        $recurring_budget_amount = 0;
        if (!empty($proposal_recurring_budget)) {
            if (is_array($proposal_recurring_budget) && isset($proposal_recurring_budget['amount'])) {
                $recurring_budget_amount = floatval($proposal_recurring_budget['amount']);
            } else {
                // Remove commas and other non-numeric characters except decimal point
                $cleaned_recurring_budget = \Arsol_Projects_For_Woo\Integrations\WooCommerce\Integration::clean_amount_input($proposal_recurring_budget);
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
            wp_die(__('Security check failed.', 'arsol-pfw'));
        }
        
        $parent_project_id = intval($_GET['parent_project']);
        
        // Verify parent project exists and user can access it
        $parent_project = get_post($parent_project_id);
        if (!$parent_project || $parent_project->post_type !== 'arsol-pfw-project') {
            wp_die(__('Invalid project.', 'arsol-pfw'));
        }
        
        if (!current_user_can('edit_post', $parent_project_id)) {
            wp_die(__('You do not have permission to create proposals from this project.', 'arsol-pfw'));
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
            __('Proposal successfully created from project. <a href="%s">View proposal</a> or continue editing this proposal.', 'arsol-pfw'),
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
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        return !empty($proposal->get_parent_project_id()) && is_numeric($proposal->get_parent_project_id());
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
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
        $parent_project_id = $proposal->get_parent_project_id();
        $parent_project = get_post($parent_project_id);
        if (!$parent_project || $parent_project->post_type !== 'arsol-pfw-project') {
            return false;
        }
        $customer_id = get_post_meta($parent_project_id, '_arsol_pfw_customer_id', true);
        $parent_project_entity = new \Arsol_Projects_For_Woo\Custom_Post_Types\Project($parent_project_id);
        $lead_id = $parent_project_entity->get_project_lead();
        return array(
            'id' => $parent_project_id,
            'title' => $parent_project->post_title,
            'customer_id' => $customer_id,
            'lead_id' => $lead_id
        );
    }

    /**
     * Prevent proposal deletion if tied projects exist
     */
    public function prevent_proposal_deletion_with_projects($post_id) {
        if ($post_id && get_post_type($post_id) === 'arsol-pfw-proposal') {
            $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post_id);
            $parent_project_id = $proposal->get_parent_project_id();
            if (!empty($parent_project_id)) {
                $parent_project = get_post($parent_project_id);
                if ($parent_project && $parent_project->post_type === 'arsol-pfw-project') {
                    wp_die(__('Cannot delete this proposal as it is tied to a project.', 'arsol-pfw'));
                }
            }
        }
    }

    /**
     * Render customer notice metabox
     */
    public function render_customer_notice_metabox($post) {
        $this->render_customer_notice_content($post);
    }

    /**
     * Render customer notice content
     */
    public function render_customer_notice_content($post) {
        // Add nonce for security
        wp_nonce_field('proposal_customer_notice_section', 'proposal_customer_notice_section_nonce');

        // Get current values using Proposal entity
        $proposal = new \Arsol_Projects_For_Woo\Custom_Post_Types\Arsol_PFW_Proposal($post->ID);
        $notice = $proposal->get_proposal_customer_notice();
        ?>
        <div>
            <p class="description">
                <?php _e('Add any important notices or updates that should be communicated to the customer regarding this proposal.', 'arsol-pfw'); ?>
            </p>
            
            <div class="arsol-request-feedback-editor">
                <?php
                $editor_settings = array(
                    'textarea_name' => 'arsol_pfw_proposal_customer_notice',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny' => false,
                    'quicktags' => array(
                        'buttons' => 'strong,em,ul,ol,li,link,close'
                    ),
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,bullist,numlist,link,unlink,undo,redo',
                        'toolbar2' => '',
                        'toolbar3' => ''
                    )
                );
                
                wp_editor($notice, 'arsol_pfw_proposal_customer_notice', $editor_settings);
                ?>
            </div>
        </div>
        <?php
    }
}