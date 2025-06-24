        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'arsol-pfw' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'arsol-pfw' ),
                'default' => 'yes',
            ),
            'subject' => array(
                'title'       => __( 'Subject', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{project_id}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading' => array(
                'title'       => __( 'Email heading', 'arsol-pfw' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => sprintf( __( 'Available placeholders: %s', 'arsol-pfw' ), '<code>{project_id}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'arsol-pfw' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'arsol-pfw' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
}
