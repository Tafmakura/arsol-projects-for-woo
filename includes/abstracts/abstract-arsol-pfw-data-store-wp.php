<?php
/**
 * Abstract Data Store
 *
 * @package Arsol_Projects_For_Woo
 * @subpackage Abstracts
 */

namespace Arsol_Projects_For_Woo;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Abstract Data Store
 * 
 * Base class for all our data stores, extends WooCommerce's data store
 */
abstract class ARSOL_PFW_Data_Store_WP extends \WC_Data_Store_WP implements ARSOL_PFW_Object_Data_Store_Interface {
    
    /**
     * Internal meta keys which are not saved to the database
     * @var array
     */
    protected $internal_meta_keys = array();
    
    /**
     * Meta type for this data store
     * @var string
     */
    protected $meta_type = 'post';
    
    /**
     * Get stage from taxonomy terms
     *
     * @param int $post_id Post ID
     * @param string $taxonomy Taxonomy name
     * @param string $default_stage Default stage if none found
     * @return string Stage slug
     */
    protected function get_stage_from_taxonomy($post_id, $taxonomy, $default_stage = '') {
        $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
        return !empty($terms) ? $terms[0] : $default_stage;
    }
    
    /**
     * Save stage to taxonomy terms
     *
     * @param int $post_id Post ID
     * @param string $stage Stage slug
     * @param string $taxonomy Taxonomy name
     * @return bool|WP_Error Result of wp_set_object_terms
     */
    protected function save_stage_to_taxonomy($post_id, $stage, $taxonomy) {
        return wp_set_object_terms($post_id, $stage, $taxonomy);
    }
    
    /**
     * Get available stages from taxonomy
     *
     * @param string $taxonomy Taxonomy name
     * @return array Array of stage_slug => stage_name
     */
    public function get_available_stages($taxonomy = '') {
        if (empty($taxonomy)) {
            return array();
        }
        
        $terms = get_terms(array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
        ));
        
        $stages = array();
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $stages[$term->slug] = $term->name;
            }
        }
        
        return $stages;
    }
    
    /**
     * Get stage counts from taxonomy
     *
     * @param string $taxonomy Taxonomy name
     * @return array Array of stage_slug => count
     */
    public function get_stage_counts($taxonomy = '') {
        if (empty($taxonomy)) {
            return array();
        }
        
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        $counts = array();
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $counts[$term->slug] = $term->count;
            }
        }
        
        return $counts;
    }
    
    /**
     * Update post meta data for an object
     *
     * @param \WC_Data $object Object to update meta for
     */
    protected function update_post_meta(&$object) {
        $updated_props = array();
        $meta_key_to_props = $this->get_props_to_meta_keys();
        
        $props_to_update = $this->get_props_to_update($object, $meta_key_to_props, $this->meta_type);
        
        foreach ($props_to_update as $meta_key => $prop) {
            $value = $object->{"get_$prop"}('edit');
            $value = is_string($value) ? wp_slash($value) : $value;
            
            $updated = $this->update_or_delete_post_meta($object, $meta_key, $value);
            
            if ($updated) {
                $updated_props[] = $prop;
            }
        }
        
        do_action('arsol_pfw_object_updated_props', $object, $updated_props);
    }
    
    /**
     * Maps object properties to meta keys
     * Override in child classes
     *
     * @return array Array of prop => meta_key
     */
    protected function get_props_to_meta_keys() {
        return array();
    }
    
    /**
     * Get props to update for meta
     *
     * @param \WC_Data $object Object being updated
     * @param array $meta_key_to_props Mapping of meta keys to props
     * @param string $meta_type Meta type (post, user, etc.)
     * @return array Props to update
     */
    protected function get_props_to_update($object, $meta_key_to_props, $meta_type = 'post') {
        $props_to_update = array();
        $changed_props = $object->get_changes();
        
        // Props should be updated if they are a part of the $changed array or don't exist yet
        foreach ($meta_key_to_props as $meta_key => $prop) {
            if (array_key_exists($prop, $changed_props) || !metadata_exists($meta_type, $object->get_id(), $meta_key)) {
                $props_to_update[$meta_key] = $prop;
            }
        }
        
        return $props_to_update;
    }
    
    /**
     * Update or delete post meta
     *
     * @param \WC_Data $object Object being updated
     * @param string $meta_key Meta key to update
     * @param mixed $meta_value Meta value to set
     * @return bool True if updated
     */
    protected function update_or_delete_post_meta($object, $meta_key, $meta_value) {
        if (in_array($meta_value, array(array(), ''), true)) {
            $updated = delete_post_meta($object->get_id(), $meta_key);
        } else {
            $updated = update_post_meta($object->get_id(), $meta_key, $meta_value);
        }
        return (bool) $updated;
    }
} 