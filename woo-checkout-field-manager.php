<?php
/*
Plugin Name: Woo Checkout Field Manager
Plugin URI: https://github.com/hadibarzegarzadeh/woo-checkout-field-manager
Description: Manage WooCommerce checkout fields. Customize labels, placeholders, required status, priority, and visibility.
Version: 1.0.0
Author: Hadi Barzegarzadeh
License: GPL-2.0-or-later
Text Domain: woo-checkout-field-manager
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Woo_Checkout_Field_Manager {
    public function __construct() {
        add_action( 'before_woocommerce_init', array( $this, 'hpos_compatibility' ) );
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
    }

    public function hpos_compatibility() {
        if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }

    public function init_plugin() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }

        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcfm-admin.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/class-wcfm-frontend.php';

        $admin = new WCFM_Admin();
        $admin->init();

        $frontend = new WCFM_Frontend();
        $frontend->init();
    }
}

new Woo_Checkout_Field_Manager();
