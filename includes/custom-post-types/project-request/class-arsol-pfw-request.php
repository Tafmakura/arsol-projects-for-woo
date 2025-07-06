<?php
/**
 * Request Entity Class for Arsol Projects for WooCommerce
 *
 * @package Arsol_PFW\Custom_Post_Types\Request
 * @since   1.0.0
 */

declare(strict_types=1);

namespace Arsol_PFW\Custom_Post_Types\Request;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Request Entity Class
 *
 * TODO: Full implementation to be added
 */
class Entity {
    
    protected $id = 0;
    protected $data = array();
    
    public function __construct($request = 0) {
        // TODO: Implement constructor
    }
    
    public function get_id() {
        return $this->id;
    }
    
    public function save() {
        // TODO: Implement save method
        return false;
    }
    
    public function delete($force_delete = false) {
        // TODO: Implement delete method
        return false;
    }
} 