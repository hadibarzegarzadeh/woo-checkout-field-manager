<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCFM_Frontend {
    public function init() {
        add_filter( 'woocommerce_checkout_fields', array( $this, 'modify_checkout_fields' ), 9999 );
    }

    public function modify_checkout_fields( $fields ) {
        $saved_data = get_option( 'wcfm_fields_data', array() );

        if ( empty( $saved_data ) ) {
            return $fields;
        }

        foreach ( $fields as $group_key => $group_fields ) {
            if ( ! isset( $saved_data[$group_key] ) ) {
                continue;
            }

            foreach ( $group_fields as $field_key => $field_data ) {
                if ( isset( $saved_data[$group_key][$field_key] ) ) {
                    $custom = $saved_data[$group_key][$field_key];

                    if ( empty( $custom['enabled'] ) ) {
                        unset( $fields[$group_key][$field_key] );
                        continue;
                    }

                    $fields[$group_key][$field_key]['required'] = ! empty( $custom['required'] );

                    if ( ! empty( $custom['label'] ) ) {
                        $fields[$group_key][$field_key]['label'] = $custom['label'];
                    }

                    if ( ! empty( $custom['placeholder'] ) ) {
                        $fields[$group_key][$field_key]['placeholder'] = $custom['placeholder'];
                    }

                    if ( isset( $custom['priority'] ) ) {
                        $fields[$group_key][$field_key]['priority'] = absint( $custom['priority'] );
                    }
                }
            }
        }

        return $fields;
    }
}
