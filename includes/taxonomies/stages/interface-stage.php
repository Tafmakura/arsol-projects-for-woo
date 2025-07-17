<?php
/**
 * Stage Interface for Arsol Projects for Woo
 *
 * @package Arsol_Projects_For_Woo
 * @since 10*/

declare(strict_types=1);

namespace Arsol_Projects_For_Woo\Taxonomies\Stages;

/**
 * Interface for stage taxonomy entities
 *
 * Provides a consistent API for all stage entities (project-stage, proposal-stage, request-stage)
 * following WooCommerce status patterns for familiarity and compatibility.
 */
interface Stage_Interface
{
    /**
     * Get the current stage
     *
     * @return string The current stage slug
     */
    public function get_stage(): string;

    /**
     * Set the stage
     *
     * @param string $stage The stage slug to set
     * @return bool True on success, false on failure
     */
    public function set_stage(string $stage): bool;

    /**
     * Update the stage with optional notes
     *
     * @param string $new_stage The new stage slug
     * @param string $note Optional note about the stage change
     * @return bool True on success, false on failure
     */
    public function update_stage(string $new_stage, string $note = ''): bool;

    /**
     * Get the stage label
     *
     * @return string The human-readable stage label
     */
    public function get_stage_label(): string;

    /**
     * Get the stage color
     *
     * @return string The stage color (hex code or CSS class)
     */
    public function get_stage_color(): string;

    /**
     * Get stage notes
     *
     * @return string The stage notes
     */
    public function get_stage_notes(): string;

    /**
     * Set stage notes
     *
     * @param string $notes The notes to set
     * @return bool True on success, false on failure
     */
    public function set_stage_notes(string $notes): bool;

    /**
     * Get stage history
     *
     * @return array Array of stage change history
     */
    public function get_stage_history(): array;

    /**
     * Get allowed stage transitions
     *
     * @return array Array of allowed stage transitions
     */
    public function get_allowed_transitions(): array;

    /**
     * Check if a stage transition is allowed
     *
     * @param string $new_stage The stage to transition to
     * @return bool True if transition is allowed
     */
    public function can_transition_to(string $new_stage): bool;

    /**
     * Get stage duration
     *
     * @return int Duration in seconds
     */
    public function get_stage_duration(): int;

    /**
     * Get total time in current stage
     *
     * @return int Time in seconds
     */
    public function get_time_in_current_stage(): int;

    /**
     * Check if stage is final
     *
     * @return bool True if this is a final stage
     */
    public function is_final_stage(): bool;

    /**
     * Check if stage is initial
     *
     * @return bool True if this is an initial stage
     */
    public function is_initial_stage(): bool;
}

/**
 * Interface for WooCommerce-compatible stage methods
 *
 * Extends the base stage interface with WooCommerce-specific functionality
 */
interface WooCommerce_Stage_Interface extends Stage_Interface
{
    /**
     * Get WooCommerce status equivalent
     *
     * @return string WooCommerce status
     */
    public function get_wc_status(): string;

    /**
     * Set WooCommerce status
     *
     * @param string $status WooCommerce status
     * @return bool True on success, false on failure
     */
    public function set_wc_status(string $status): bool;

    /**
     * Sync with WooCommerce order status
     *
     * @return bool True on success, false on failure
     */
    public function sync_with_wc_order(): bool;
}

/**
 * Interface for static stage methods
 *
 * Provides factory and bulk operations for stage entities
 */
interface Static_Stage_Interface
{
    /**
     * Create a new stage entity
     *
     * @param int $entity_id The entity ID (project, proposal, or request)
     * @param string $initial_stage The initial stage
     * @return Stage_Interface The created stage entity
     */
    public static function create(int $entity_id, string $initial_stage): Stage_Interface;

    /**
     * Get stage entity by ID
     *
     * @param int $entity_id The entity ID
     * @return Stage_Interface|null The stage entity or null if not found
     */
    public static function get(int $entity_id): ?Stage_Interface;

    /**
     * Get all entities with a specific stage
     *
     * @param string $stage The stage to search for
     * @return array Array of entity IDs
     */
    public static function get_entities_by_stage(string $stage): array;

    /**
     * Bulk update stage for multiple entities
     *
     * @param array $entity_ids Array of entity IDs
     * @param string $new_stage The new stage
     * @param string $note Optional note
     * @return int Number of entities updated
     */
    public static function bulk_update_stage(array $entity_ids, string $new_stage, string $note = ''): int;

    /**
     * Get stage statistics
     *
     * @return array Array of stage statistics
     */
    public static function get_stage_statistics(): array;
} 