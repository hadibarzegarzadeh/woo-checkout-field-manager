<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCFM_Admin {
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_menu_page() {
        add_submenu_page(
            'woocommerce',
            __( 'Checkout Fields', 'woo-checkout-field-manager' ),
            __( 'Checkout Fields', 'woo-checkout-field-manager' ),
            'manage_woocommerce',
            'wcfm-settings',
            array( $this, 'render_settings_page' )
        );
    }

    public function register_settings() {
        register_setting( 'wcfm_settings_group', 'wcfm_fields_data', array( $this, 'sanitize_data' ) );
    }

    public function sanitize_data( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }

        $sanitized = array();

        foreach ( $input as $group => $fields ) {
            if ( ! is_array( $fields ) ) {
                continue;
            }

            $sanitized_group = sanitize_key( $group );

            foreach ( $fields as $key => $data ) {
                $sanitized_key = sanitize_key( $key );

                $sanitized[$sanitized_group][$sanitized_key] = array(
                    'enabled'     => isset( $data['enabled'] ) ? 1 : 0,
                    'required'    => isset( $data['required'] ) ? 1 : 0,
                    'label'       => sanitize_text_field( $data['label'] ?? '' ),
                    'placeholder' => sanitize_text_field( $data['placeholder'] ?? '' ),
                    'priority'    => absint( $data['priority'] ?? 10 ),
                );
            }
        }

        return $sanitized;
    }

    public function render_settings_page() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $saved_data = get_option( 'wcfm_fields_data', array() );
        $default_fields = $this->get_default_fields();

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Manage Checkout Fields', 'woo-checkout-field-manager' ) . '</h1>';
        echo '<form method="post" action="options.php">';
        
        settings_fields( 'wcfm_settings_group' );

        foreach ( $default_fields as $group_key => $fields ) {
            echo '<h2>' . esc_html( ucfirst( $group_key ) ) . '</h2>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__( 'Field', 'woo-checkout-field-manager' ) . '</th>';
            echo '<th>' . esc_html__( 'Enabled', 'woo-checkout-field-manager' ) . '</th>';
            echo '<th>' . esc_html__( 'Required', 'woo-checkout-field-manager' ) . '</th>';
            echo '<th>' . esc_html__( 'Label', 'woo-checkout-field-manager' ) . '</th>';
            echo '<th>' . esc_html__( 'Placeholder', 'woo-checkout-field-manager' ) . '</th>';
            echo '<th>' . esc_html__( 'Priority', 'woo-checkout-field-manager' ) . '</th>';
            echo '</tr></thead><tbody>';

            foreach ( $fields as $field_key => $field_defaults ) {
                $current = isset( $saved_data[$group_key][$field_key] ) ? $saved_data[$group_key][$field_key] : array();
                
                $enabled = isset( $current['enabled'] ) ? $current['enabled'] : 1;
                $required = isset( $current['required'] ) ? $current['required'] : ( $field_defaults['required'] ? 1 : 0 );
                $label = isset( $current['label'] ) ? $current['label'] : $field_defaults['label'];
                $placeholder = isset( $current['placeholder'] ) ? $current['placeholder'] : '';
                $priority = isset( $current['priority'] ) ? $current['priority'] : $field_defaults['priority'];

                $name_prefix = 'wcfm_fields_data[' . esc_attr( $group_key ) . '][' . esc_attr( $field_key ) . ']';

                echo '<tr>';
                echo '<td><strong>' . esc_html( $field_key ) . '</strong></td>';
                echo '<td><input type="checkbox" name="' . $name_prefix . '[enabled]" value="1" ' . checked( 1, $enabled, false ) . ' /></td>';
                echo '<td><input type="checkbox" name="' . $name_prefix . '[required]" value="1" ' . checked( 1, $required, false ) . ' /></td>';
                echo '<td><input type="text" name="' . $name_prefix . '[label]" value="' . esc_attr( $label ) . '" class="regular-text" /></td>';
                echo '<td><input type="text" name="' . $name_prefix . '[placeholder]" value="' . esc_attr( $placeholder ) . '" class="regular-text" /></td>';
                echo '<td><input type="number" name="' . $name_prefix . '[priority]" value="' . esc_attr( $priority ) . '" class="small-text" /></td>';
                echo '</tr>';
            }

            echo '</tbody></table><br>';
        }

        submit_button();
        echo '</form></div>';
    }

    private function get_default_fields() {
        return array(
            'billing' => array(
                'billing_first_name' => array( 'label' => 'First name', 'required' => true, 'priority' => 10 ),
                'billing_last_name'  => array( 'label' => 'Last name', 'required' => true, 'priority' => 20 ),
                'billing_company'    => array( 'label' => 'Company name', 'required' => false, 'priority' => 30 ),
                'billing_country'    => array( 'label' => 'Country / Region', 'required' => true, 'priority' => 40 ),
                'billing_address_1'  => array( 'label' => 'Street address', 'required' => true, 'priority' => 50 ),
                'billing_address_2'  => array( 'label' => 'Apartment, suite, unit, etc.', 'required' => false, 'priority' => 60 ),
                'billing_city'       => array( 'label' => 'Town / City', 'required' => true, 'priority' => 70 ),
                'billing_state'      => array( 'label' => 'State / County', 'required' => true, 'priority' => 80 ),
                'billing_postcode'   => array( 'label' => 'Postcode / ZIP', 'required' => true, 'priority' => 90 ),
                'billing_phone'      => array( 'label' => 'Phone', 'required' => true, 'priority' => 100 ),
                'billing_email'      => array( 'label' => 'Email address', 'required' => true, 'priority' => 110 ),
            ),
            'shipping' => array(
                'shipping_first_name' => array( 'label' => 'First name', 'required' => true, 'priority' => 10 ),
                'shipping_last_name'  => array( 'label' => 'Last name', 'required' => true, 'priority' => 20 ),
                'shipping_company'    => array( 'label' => 'Company name', 'required' => false, 'priority' => 30 ),
                'shipping_country'    => array( 'label' => 'Country / Region', 'required' => true, 'priority' => 40 ),
                'shipping_address_1'  => array( 'label' => 'Street address', 'required' => true, 'priority' => 50 ),
                'shipping_address_2'  => array( 'label' => 'Apartment, suite, unit, etc.', 'required' => false, 'priority' => 60 ),
                'shipping_city'       => array( 'label' => 'Town / City', 'required' => true, 'priority' => 70 ),
                'shipping_state'      => array( 'label' => 'State / County', 'required' => true, 'priority' => 80 ),
                'shipping_postcode'   => array( 'label' => 'Postcode / ZIP', 'required' => true, 'priority' => 90 ),
            ),
            'order' => array(
                'order_comments' => array( 'label' => 'Order notes', 'required' => false, 'priority' => 10 ),
            )
        );
    }
}
