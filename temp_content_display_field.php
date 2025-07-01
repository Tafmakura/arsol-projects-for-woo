    /**
     * Render content display field
     */
    public function render_content_display_field($args) {
        $settings = get_option('arsol_content_display_settings', array());
        $type = $args['type'];
        $taxonomy = $args['taxonomy'];
        
        $visibility = isset($settings[$type . '_visibility']) ? $settings[$type . '_visibility'] : 'hide';
        $stages = isset($settings[$type . '_stages']) ? $settings[$type . '_stages'] : array();
        
        // Get available stages
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false
        ));
        
        if (is_wp_error($terms)) {
            $terms = array();
        }
        
        // Visibility select (on top)
        echo '<div style="margin-bottom: 10px;">';
        echo '<select name="arsol_content_display_settings[' . esc_attr($type) . '_visibility]" style="width: 250px;">';
        echo '<option value="hide"' . selected($visibility, 'hide', false) . '>' . __('Hide for selected stages', 'arsol-pfw') . '</option>';
        echo '<option value="show"' . selected($visibility, 'show', false) . '>' . __('Show for selected stages', 'arsol-pfw') . '</option>';
        echo '</select>';
        echo '</div>';
        
        // Select2 multi-select for stages (below)
        echo '<div style="margin-bottom: 10px;">';
        echo '<select name="arsol_content_display_settings[' . esc_attr($type) . '_stages][]" multiple class="arsol-stages-select2" style="width: 100%; min-width: 300px;">';
        foreach ($terms as $term) {
            $selected = in_array($term->term_id, $stages) ? 'selected' : '';
            echo '<option value="' . esc_attr($term->term_id) . '" ' . $selected . '>' . esc_html($term->name) . '</option>';
        }
        echo '</select>';
        echo '</div>';
        
        echo '<p class="description">' . sprintf(__('Control when %s content appears on the frontend based on the current stage.', 'arsol-pfw'), esc_html($type)) . '</p>';
    }
