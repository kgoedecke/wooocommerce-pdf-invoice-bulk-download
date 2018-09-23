<?php
/*
Plugin Name: WooCommerce PDF Invoice Bulk Download
Plugin URI:
Description: WooCommerce PDF Invoice Bulk Download Extension Plugin
Version:     1.0.0
Author:      Kevin Goedecke
Author URI:  https://havealooklabs.com/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: woocommerce-pdf-invoices-bulk-download
Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    add_action( 'admin_notices', 'woo_pdf_invoices_buld_download_required_fail' );
    return;
}

$depends_plugins = array(
    'woocommerce-pdf-invoices/bootstrap.php',
    'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php',
);

$plugin_is_activated = false;

foreach ( $depends_plugins as $plugin ) {

    if ( is_plugin_active( $plugin ) ) {
        require_once( plugin_dir_path( __FILE__ ) . 'class-woocommerce-pdf-invoices-bulk-download.php' );
        add_action( 'plugins_loaded', array( 'WooCommerce_PDF_Invoices_Bulk_Download', 'get_instance' ) );
        register_activation_hook( __FILE__, array( 'WooCommerce_PDF_Invoices_Bulk_Download', 'activate' ) );
        $plugin_is_activated = true;

        break;
    }
}

if ( ! $plugin_is_activated ) {
    add_action( 'admin_notices', 'woo_pdf_invoices_buld_download_depends_fail' );
    return;
}

/**
 * Show in WP Dashboard notice about the WooCommerce plugin is not activated.
 *
 * @since 1.0.0
 */
function woo_pdf_invoices_buld_download_required_fail() {
    $message = sprintf( __( 'plugin requires a <a href="%s" target="_blank">WooCommerce</a> plugin.', 'woocommerce-pdf-invoices-bulk-download' ), esc_url( 'https://wordpress.org/plugins/woocommerce/' ) );

    $html_message = sprintf( '<div class="error"><p><strong>%1$s</strong> %2$s</p></div>', esc_html__( 'WooCommerce PDF Invoices Bulk Download Extension', 'woocommerce-pdf-invoices-bulk-download' ), $message );

    echo wp_kses_post( $html_message );
}

/**
 * Show in WP Dashboard notice about the depends plugins.
 *
 * @since 1.0.0
 */
function woo_pdf_invoices_buld_download_depends_fail() {
    $messages = array();
    $messages[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://wordpress.org/plugins/woocommerce-pdf-invoices/' ), esc_html__( 'WooCommerce PDF Invoices', 'woocommerce-pdf-invoices-bulk-download' ) );
    $messages[] = sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/' ), esc_html__( 'WooCommerce PDF Invoices & Packing Slips', 'woocommerce-pdf-invoices-bulk-download' ) );

    $html_message = sprintf( '<div class="error"><p><strong>%1$s</strong> %2$s %3$s</p></div>', esc_html__( 'WooCommerce PDF Invoices Bulk Download Extension', 'woocommerce-pdf-invoices-bulk-download' ), esc_html__( 'plugin are depends from one of plugins:', 'woocommerce-pdf-invoices-bulk-download' ), join( ', ', $messages ) );

    echo wp_kses_post( $html_message );
}