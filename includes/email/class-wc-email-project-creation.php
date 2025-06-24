        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'arsol-projects-for-woo' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'arsol-projects-for-woo' ),
                'default' => 'yes',
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {project_id}, {customer_name}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'arsol-projects-for-woo' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-projects-for-woo' ), '<code>{site_title}, {project_id}, {customer_name}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'arsol-projects-for-woo' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'arsol-projects-for-woo' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
}
