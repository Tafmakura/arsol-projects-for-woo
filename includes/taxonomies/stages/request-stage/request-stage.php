<?php
/**
 * Request Stage Entity for Arsol Projects for Woo
 *
 * @package Arsol_Projects_For_Woo
 * @since 10*/

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Taxonomies\Stages;

use Arsol_Projects_For_Woo\Taxonomies\Stages\Stage_Interface;
use Arsol_Projects_For_Woo\Taxonomies\Stages\WooCommerce_Stage_Interface;
use Arsol_Projects_For_Woo\Taxonomies\Stages\Static_Stage_Interface;

/**
 * Request Stage Entity Class
 *
 * Provides OOP methods for managing request stages with WooCommerce compatibility
 */
class Request_Stage implements Stage_Interface, WooCommerce_Stage_Interface, Static_Stage_Interface
{
    private int $request_id;
    private string $stage;
    private string $notes;
    private array $history;

    public function __construct(int $request_id)
    {
        $this->request_id = $request_id;
        $this->load_stage_data();
    }

    private function load_stage_data(): void
    {
        $this->stage = get_post_meta($this->request_id, '_arsol_pfw_request_stage', true) ?: 'draft';
        $this->notes = get_post_meta($this->request_id, '_arsol_pfw_request_stage_notes', true) ?: '';
        $this->history = get_post_meta($this->request_id, '_arsol_pfw_request_stage_history', true) ?: [];
    }

    private function save_stage_data(): void
    {
        update_post_meta($this->request_id, '_arsol_pfw_request_stage', $this->stage);
        update_post_meta($this->request_id, '_arsol_pfw_request_stage_notes', $this->notes);
        update_post_meta($this->request_id, '_arsol_pfw_request_stage_history', $this->history);
    }

    public function get_stage(): string
    {
        return $this->stage;
    }

    public function set_stage(string $stage): bool
    {
        if (!$this->can_transition_to($stage)) {
            return false;
        }
        $old_stage = $this->stage;
        $this->stage = $stage;
        $this->add_to_history($old_stage, $stage);
        $this->save_stage_data();
        do_action('arsol_pfw_request_stage_changed', $this->request_id, $old_stage, $stage);
        do_action("arsol_pfw_request_stage_{$stage}", $this->request_id);
        do_action("arsol_pfw_request_stage_{$old_stage}_to_{$stage}", $this->request_id);
        return true;
    }

    public function update_stage(string $new_stage, string $note = ''): bool
    {
        if ($note) {
            $this->set_stage_notes($note);
        }
        return $this->set_stage($new_stage);
    }

    public function get_stage_label(): string
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['label'] ?? ucfirst($this->stage);
    }

    public function get_stage_color(): string
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['color'] ?? '#666666';
    }

    public function get_stage_notes(): string
    {
        return $this->notes;
    }

    public function set_stage_notes(string $notes): bool
    {
        $this->notes = $notes;
        $this->save_stage_data();
        return true;
    }

    public function get_stage_history(): array
    {
        return $this->history;
    }

    public function get_allowed_transitions(): array
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['transitions'] ?? [];
    }

    public function can_transition_to(string $new_stage): bool
    {
        $allowed_transitions = $this->get_allowed_transitions();
        return in_array($new_stage, $allowed_transitions, true);
    }

    public function get_stage_duration(): int
    {
        $current_time = time();
        $stage_start = $this->get_stage_start_time();
        return $current_time - $stage_start;
    }

    public function get_time_in_current_stage(): int
    {
        return $this->get_stage_duration();
    }

    public function is_final_stage(): bool
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['final'] ?? false;
    }

    public function is_initial_stage(): bool
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['initial'] ?? false;
    }

    public function get_wc_status(): string
    {
        $stages = $this->get_available_stages();
        return $stages[$this->stage]['wc_status'] ?? 'pending';
    }

    public function set_wc_status(string $status): bool
    {
        $stages = $this->get_available_stages();
        foreach ($stages as $stage_slug => $stage_data) {
            if (($stage_data['wc_status'] ?? null) === $status) {
                return $this->set_stage($stage_slug);
            }
        }
        return false;
    }

    public function sync_with_wc_order(): bool
    {
        $order_id = get_post_meta($this->request_id, '_arsol_pfw_order_id', true);
        if (!$order_id) {
            return false;
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        return $this->set_wc_status($order->get_status());
    }

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

    private function get_stage_start_time(): int
    {
        if (empty($this->history)) {
            return get_post_time('U', true, $this->request_id);
        }
        $last_entry = end($this->history);
        return $last_entry['timestamp'] ?? time();
    }

    private function get_available_stages(): array
    {
        return apply_filters('arsol_pfw_request_stages', [
            'draft' => [
                'label' => __('Draft', 'arsol-projects-for-woo'),
                'color' => '#666666',
                'wc_status' => 'pending',
                'initial' => true,
                'final' => false,
                'transitions' => ['submitted', 'cancelled'],
            ],
            'submitted' => [
                'label' => __('Submitted', 'arsol-projects-for-woo'),
                'color' => '#0073aa',
                'wc_status' => 'processing',
                'initial' => false,
                'final' => false,
                'transitions' => ['review', 'cancelled'],
            ],
            'review' => [
                'label' => __('Under Review', 'arsol-projects-for-woo'),
                'color' => '#ffba00',
                'wc_status' => 'processing',
                'initial' => false,
                'final' => false,
                'transitions' => ['approved', 'revision', 'cancelled'],
            ],
            'revision' => [
                'label' => __('Revision Required', 'arsol-projects-for-woo'),
                'color' => '#dc3232',
                'wc_status' => 'processing',
                'initial' => false,
                'final' => false,
                'transitions' => ['submitted', 'cancelled'],
            ],
            'approved' => [
                'label' => __('Approved', 'arsol-projects-for-woo'),
                'color' => '#46b450',
                'wc_status' => 'completed',
                'initial' => false,
                'final' => true,
                'transitions' => [],
            ],
            'cancelled' => [
                'label' => __('Cancelled', 'arsol-projects-for-woo'),
                'color' => '#dc3232',
                'wc_status' => 'cancelled',
                'initial' => false,
                'final' => true,
                'transitions' => [],
            ],
        ]);
    }

    // Static methods implementation
    public static function create(int $entity_id, string $initial_stage): Stage_Interface
    {
        $stage_entity = new self($entity_id);
        $stage_entity->set_stage($initial_stage);
        return $stage_entity;
    }

    public static function get(int $entity_id): ?Stage_Interface
    {
        if (!get_post($entity_id)) {
            return null;
        }
        return new self($entity_id);
    }

    public static function get_entities_by_stage(string $stage): array
    {
        $args = [
            'post_type' => 'arsol_pfw_request',
            'post_status' => 'any',
            'meta_query' => [
                [
                    'key' => '_arsol_pfw_request_stage',
                    'value' => $stage,
                    'compare' => '=',
                ],
            ],
            'fields' => 'ids',
            'posts_per_page' => -1,
        ];
        return get_posts($args);
    }

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

    public static function get_stage_statistics(): array
    {
        $stages = (new self(0))->get_available_stages();
        $statistics = [];
        foreach (array_keys($stages) as $stage) {
            $entity_ids = self::get_entities_by_stage($stage);
            $statistics[$stage] = [
                'count' => count($entity_ids),
                'label' => $stages[$stage]['label'],
                'color' => $stages[$stage]['color'],
            ];
        }
        return $statistics;
    }
} 