<?php
/**
 * Default Workflow Setup Class
 *
 * Handles the setup and configuration of the default workflow for the
 * Arsol Projects for WooCommerce plugin.
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Workflows\Default
 * @since 1.0.0
 */

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Workflows\Default;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Default Workflow Setup Class
 *
 * Manages the default workflow configuration and initialization.
 * This class handles the setup of the standard project workflow stages,
 * transitions, and related functionality.
 *
 * @since 1.0.0
 */
class Setup {

    /**
     * Class instance
     *
     * @var Setup|null
     */
    private static $instance = null;

    /**
     * Workflow stages
     *
     * @var array
     */
    private $stages = [];

    /**
     * Workflow transitions
     *
     * @var array
     */
    private $transitions = [];

    /**
     * Get class instance
     *
     * @return Setup
     */
    public static function get_instance(): Setup {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the default workflow
     *
     * @return void
     */
    private function init(): void {
        $this->setup_stages();
        $this->setup_transitions();
        $this->setup_hooks();
    }

    /**
     * Setup default workflow stages
     *
     * @return void
     */
    private function setup_stages(): void {
        $this->stages = [
            'request' => [
                'label' => __('Request', 'arsol-pfw'),
                'description' => __('Initial project request stage', 'arsol-pfw'),
                'color' => '#3498db',
                'order' => 1,
            ],
            'proposal' => [
                'label' => __('Proposal', 'arsol-pfw'),
                'description' => __('Proposal review and approval stage', 'arsol-pfw'),
                'color' => '#f39c12',
                'order' => 2,
            ],
            'project' => [
                'label' => __('Active Project', 'arsol-pfw'),
                'description' => __('Active project work stage', 'arsol-pfw'),
                'color' => '#27ae60',
                'order' => 3,
            ],
            'archive' => [
                'label' => __('Archive', 'arsol-pfw'),
                'description' => __('Completed and archived projects', 'arsol-pfw'),
                'color' => '#95a5a6',
                'order' => 4,
            ],
        ];
    }

    /**
     * Setup default workflow transitions
     *
     * @return void
     */
    private function setup_transitions(): void {
        $this->transitions = [
            'request_to_proposal' => [
                'from' => 'request',
                'to' => 'proposal',
                'label' => __('Create Proposal', 'arsol-pfw'),
                'permission' => 'manage_proposals',
                'auto_trigger' => false,
            ],
            'proposal_to_project' => [
                'from' => 'proposal',
                'to' => 'project',
                'label' => __('Approve & Start Project', 'arsol-pfw'),
                'permission' => 'manage_projects',
                'auto_trigger' => false,
            ],
            'project_to_archive' => [
                'from' => 'project',
                'to' => 'archive',
                'label' => __('Complete & Archive', 'arsol-pfw'),
                'permission' => 'manage_projects',
                'auto_trigger' => false,
            ],
        ];
    }

    /**
     * Setup WordPress hooks
     *
     * @return void
     */
    private function setup_hooks(): void {
        add_action('init', [$this, 'register_workflow'], 10);
        add_filter('arsol_pfw_default_workflow_stages', [$this, 'get_stages'], 10);
        add_filter('arsol_pfw_default_workflow_transitions', [$this, 'get_transitions'], 10);
    }

    /**
     * Register the default workflow
     *
     * @return void
     */
    public function register_workflow(): void {
        /**
         * Fires when registering the default workflow
         *
         * @since 1.0.0
         *
         * @param Setup $this The workflow setup instance
         */
        do_action('arsol_pfw_register_default_workflow', $this);
    }

    /**
     * Get workflow stages
     *
     * @return array
     */
    public function get_stages(): array {
        return $this->stages;
    }

    /**
     * Get workflow transitions
     *
     * @return array
     */
    public function get_transitions(): array {
        return $this->transitions;
    }

    /**
     * Get a specific stage
     *
     * @param string $stage_key The stage key
     * @return array|null
     */
    public function get_stage(string $stage_key): ?array {
        return $this->stages[$stage_key] ?? null;
    }

    /**
     * Get a specific transition
     *
     * @param string $transition_key The transition key
     * @return array|null
     */
    public function get_transition(string $transition_key): ?array {
        return $this->transitions[$transition_key] ?? null;
    }

    /**
     * Check if a transition is allowed
     *
     * @param string $from_stage From stage
     * @param string $to_stage To stage
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool
     */
    public function is_transition_allowed(string $from_stage, string $to_stage, int $user_id = 0): bool {
        if (0 === $user_id) {
            $user_id = get_current_user_id();
        }

        foreach ($this->transitions as $transition) {
            if ($transition['from'] === $from_stage && $transition['to'] === $to_stage) {
                return user_can($user_id, $transition['permission']);
            }
        }

        return false;
    }

    /**
     * Get available transitions for a stage
     *
     * @param string $from_stage From stage
     * @param int $user_id User ID (optional, defaults to current user)
     * @return array
     */
    public function get_available_transitions(string $from_stage, int $user_id = 0): array {
        if (0 === $user_id) {
            $user_id = get_current_user_id();
        }

        $available = [];
        foreach ($this->transitions as $key => $transition) {
            if ($transition['from'] === $from_stage && user_can($user_id, $transition['permission'])) {
                $available[$key] = $transition;
            }
        }

        return $available;
    }
} 