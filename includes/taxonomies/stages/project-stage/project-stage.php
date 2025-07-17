<?php
/**
 * Project Stage Entity for Arsol Projects for Woo
 *
 * @package Arsol_Projects_For_Woo
 * @since 10*/

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Taxonomies\Stages;

use Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Interface;
use Arsol_Projects_For_Woo\Taxonomies\Stages\WooCommerce_Stage_Interface;
use Arsol_Projects_For_Woo\Taxonomies\Stages\Static_Stage_Interface;

/**
 * Project Stage Entity Class
 *
 * Provides OOP methods for managing project stages with WooCommerce compatibility
 */
class Project_Stage implements Stage_Interface, WooCommerce_Stage_Interface, Static_Stage_Interface
{
    /**
     * The project ID
     *
     * @var int
     */
    private $project_id;

    /**
     * The current stage
     *
     * @var string
     */
    private $stage;

    /**
     * Stage notes
     *
     * @var string
     */
    private $notes;

    /**
     * Stage history
     *
     * @var array
     */
    private $history;

    /**
     * Constructor
     *
     * @param int $project_id The project ID
     */
    public function __construct(int $project_id)
    {
        $this->project_id = $project_id;
        $this->load_stage_data();
    }

    /**
     * Load stage data from database
     */
    private function load_stage_data(): void
    {
        $this->stage = get_post_meta($this->project_id, '_arsol_pfw_project_stage', true) ?: 'draft';
        $this->notes = get_post_meta($this->project_id, '_arsol_pfw_project_stage_notes', true) ?: '';
        $this->history = get_post_meta($this->project_id, '_arsol_pfw_project_stage_history', true) ?: [];
    }

    /**
     * Save stage data to database
     */
    private function save_stage_data(): void
    {
        update_post_meta($this->project_id, '_arsol_pfw_project_stage', $this->stage);
        update_post_meta($this->project_id, '_arsol_pfw_project_stage_notes', $this->notes);
        update_post_meta($this->project_id, '_arsol_pfw_project_stage_history', $this->history);
    }

    /**
     * Get the current stage
     *
     * @return string The current stage slug
     */
    public function get_stage(): string
    {
        return $this->stage;
    }

    /**
     * Set the stage
     *
     * @param string $stage The stage slug to set
     * @return bool True on success, false on failure
     */
    public function set_stage(string $stage): bool
    {
        if (!$this->can_transition_to($stage)) {
            return false;
        }

        $old_stage = $this->stage;
        $this->stage = $stage;
        $this->add_to_history($old_stage, $stage);
        $this->save_stage_data();

        // Trigger hooks
        do_action('arsol_pfw_project_stage_changed', $this->project_id, $old_stage, $stage);
        do_action("arsol_pfw_project_stage_{$stage}", $this->project_id);
        do_action("arsol_pfw_project_stage_{$old_stage}_to_{$stage}", $this->project_id);

        return true;
    }

    /**
     * Update the stage with optional notes
     *
     * @param string $new_stage The new stage slug
     * @param string $note Optional note about the stage change
     * @return bool True on success, false on failure
     */
    public function update_stage(string $new_stage, string $note = ''): bool
    {
        if ($note) {
            $this->set_stage_notes($note);
        }

        return $this->set_stage($new_stage);
    }

    /**
     * Get the stage label
     *
     * @return string The human-readable stage label
     */
    public function get_stage_label(): string
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['label'] ?? ucfirst($this->stage);
    }

    /**
     * Get stage notes
     *
     * @return string The stage notes
     */
    public function get_stage_notes(): string
    {
        return $this->notes;
    }

    /**
     * Set stage notes
     *
     * @param string $notes The notes to set
     * @return bool True on success, false on failure
     */
    public function set_stage_notes(string $notes): bool
    {
        $this->notes = $notes;
        $this->save_stage_data();
        return true;
    }

    /**
     * Get stage history
     *
     * @return array Array of stage change history
     */
    public function get_stage_history(): array
    {
        return $this->history;
    }

    /**
     * Get allowed stage transitions
     *
     * @return array Array of allowed stage transitions
     */
    public function get_allowed_transitions(): array
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['transitions'] ?? [];
    }

    /**
     * Check if a stage transition is allowed
     *
     * @param string $new_stage The stage to transition to
     * @return bool True if transition is allowed
     */
    public function can_transition_to(string $new_stage): bool
    {
        $allowed_transitions = $this->get_allowed_transitions();
        return in_array($new_stage, $allowed_transitions, true);
    }

    /**
     * Get stage duration
     *
     * @return int Duration in seconds
     */
    public function get_stage_duration(): int
    {
        $current_time = time();
        $stage_start = $this->get_stage_start_time();
        return $current_time - $stage_start;
    }

    /**
     * Get total time in current stage
     *
     * @return int Time in seconds
     */
    public function get_time_in_current_stage(): int
    {
        return $this->get_stage_duration();
    }

    /**
     * Check if stage is final
     *
     * @return bool true if this is a final stage
     */
    public function is_final_stage(): bool
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['final'] ?? false;
    }

    /**
     * Check if stage is initial
     *
     * @return bool true this is an initial stage
     */
    public function is_initial_stage(): bool
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['initial'] ?? false;
    }

    /**
     * Get WooCommerce status equivalent
     *
     * @return string WooCommerce status
     */
    public function get_wc_status(): string
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['wc_status'] ?? 'pending';
    }

    /**
     * Set WooCommerce status
     *
     * @param string $status WooCommerce status
     * @return bool True on success, false on failure
     */
    public function set_wc_status(string $status): bool
    {
        // Find stage that matches this WC status
        $stages = $this->get_available_stages();
        foreach ($stages as $stage_slug => $stage_data) {
            if ($stage_data['wc_status'] === $status) {
                return $this->set_stage($stage_slug);
            }
        }
        return false;
    }

    /**
     * Sync with WooCommerce order status
     *
     * @return bool True on success, false on failure
     */
    public function sync_with_wc_order(): bool
    {
        $order_id = get_post_meta($this->project_id, '_arsol_pfw_order_id', true);
        if (!$order_id) {
            return false;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        return $this->set_wc_status($order->get_status());
    }

    /**
     * Add stage change to history
     *
     * @param string $old_stage The previous stage
     * @param string $new_stage The new stage
     */
    private function add_to_history(string $old_stage, string $new_stage): void
    {
        $this->history[] = [
            'from' => $old_stage,
            'to' => $new_stage,
            'timestamp' => time(),
            'user_id' => get_current_user_id(),
            'notes' => $this->notes,
        ];
    }

    /**
     * Get stage start time
     *
     * @return int Timestamp when current stage started
     */
    private function get_stage_start_time(): int
    {
        if (empty($this->history)) {
            return get_post_time(UUE, $this->project_id);
        }

        $last_entry = end($this->history);
        return $last_entry['timestamp'] ?? time();
    }

    /**
     * Get available stages configuration
     *
     * @return array Array of stage configurations
     */
    private function get_available_stages(): array
    {
        return apply_filters('arsol_pfw_project_stages', [
            'not-started' => [
                'label' => __('Not Started', 'arsol-projects-for-woo'),
                'wc_status' => 'pending',
                'initial' => true,
                'final' => false,
                'transitions' => ['in-progress', 'cancelled'],
            ],
            'in-progress' => [
                'label' => __('In Progress', 'arsol-projects-for-woo'),
                'wc_status' => 'processing',
                'initial' => false,
                'final' => false,
                'transitions' => ['paused', 'completed', 'cancelled'],
            ],
            'paused' => [
                'label' => __('Paused', 'arsol-projects-for-woo'),
                'wc_status' => 'processing',
                'initial' => false,
                'final' => false,
                'transitions' => ['in-progress', 'cancelled'],
            ],
            'completed' => [
                'label' => __('Completed', 'arsol-projects-for-woo'),
                'wc_status' => 'completed',
                'initial' => false,
                'final' => true,
                'transitions' => [],
            ],
            'cancelled' => [
                'label' => __('Cancelled', 'arsol-projects-for-woo'),
                'wc_status' => 'cancelled',
                'initial' => false,
                'final' => true,
                'transitions' => [],
            ],
        ]);
    }

    // Static methods implementation

    /**
     * Create a new stage entity
     *
     * @param int $entity_id The entity ID (project, proposal, or request)
     * @param string $initial_stage The initial stage
     * @return Stage_Interface The created stage entity
     */
    public static function create(int $entity_id, string $initial_stage): Stage_Interface
    {
        $stage_entity = new self($entity_id);
        $stage_entity->set_stage($initial_stage);
        return $stage_entity;
    }

    /**
     * Get stage entity by ID
     *
     * @param int $entity_id The entity ID
     * @return Stage_Interface|null The stage entity or null if not found
     */
    public static function get(int $entity_id): ?Stage_Interface
    {
        if (!get_post($entity_id)) {
            return null;
        }
        return new self($entity_id);
    }

    /**
     * Get all entities with a specific stage
     *
     * @param string $stage The stage to search for
     * @return array Array of entity IDs
     */
    public static function get_entities_by_stage(string $stage): array
    {
        $args = [
            'post_type' => 'arsol_pfw_project',
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_arsol_pfw_project_stage',
                    'value' => $stage,
                    'compare' => '=',
                ],
            ],
        ];

        return get_posts($args);
    }

    /**
     * Bulk update stage for multiple entities
     *
     * @param array $entity_ids Array of entity IDs
     * @param string $new_stage The new stage
     * @param string $note Optional note
     * @return int Number of entities updated
     */
    public static function bulk_update_stage(array $entity_ids, string $new_stage, string $note = ''): int
    {
        $updated_count = 0;

        foreach ($entity_ids as $entity_id) {
            $stage_entity = self::get($entity_id);
            if ($stage_entity && $stage_entity->update_stage($new_stage, $note)) {
                $updated_count++;
            }
        }

        return $updated_count;
    }

    /**
     * Get stage statistics
     *
     * @return array Array of stage statistics
     */
    public static function get_stage_statistics(): array
    {
        $stages = (new self(0))->get_available_stages();
        $statistics = [];

        foreach (array_keys($stages) as $stage) {
            $entity_ids = self::get_entities_by_stage($stage);
            $statistics[$stage] = [
                'count' => count($entity_ids),
                'label' => $stages[$stage]['label'],
            ];
        }

        return $statistics;
    }
} 