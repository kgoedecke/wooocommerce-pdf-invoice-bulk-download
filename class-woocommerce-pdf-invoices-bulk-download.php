<?php

require __DIR__ . '/vendor/autoload.php';

if ( ! class_exists( 'WooCommerce_PDF_Invoices_Bulk_Download' ) ) {
    class WooCommerce_PDF_Invoices_Bulk_Download {

        /**
         * A reference to an instance of this class.
         * 
         * @since 1.0.0
         * @var   object
         */
        private static $instance;

        /**
         * A reference to an instance of WooCommerce_PDF_Invoices_Bulk_Download class.
         * 
         * @since 1.0.0
         * @var   object
         */
        protected $process_single;

        /**
         * The target folder name.
         * 
         * @since 1.0.0
         * @var   string
         */
        public static $dir_name = 'woocommerce-invoice-archives/';

        /**
         * Slug of the plugin screen.
         *
         * @since 1.0.0
         * @var   string
         */
        protected $plugin_screen_hook_suffix = null;

        /**
         * Returns an instance of this class.
         * 
         * @since  1.0.0
         * @return object A single instance of this class.
         */
        public static function get_instance() {

            if ( null == self::$instance ) {
                self::$instance = new WooCommerce_PDF_Invoices_Bulk_Download();
            }

            return self::$instance;
        }

        /**
         * Initializes the plugin by setting filters and administration functions.
         * 
         * @since 1.0.0
         */
        private function __construct() {
            $this->init();
            
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
            add_action( 'wp_loaded', array( $this, 'load' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_css' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'load_plugin_js' ) );
        }

        /**
         * Async Request initialization.
         *
         * @since 1.0.0
         */
        public function init() {
            require_once plugin_dir_path( __FILE__ ) . 'inc/class-async-request.php';

            $this->process_single = new WooCommerce_PDF_Invoices_Bulk_Async_Request();
        }

        /**
         * Load the plugin text domain for translation.
         *
         * @since 1.0.0
         */
        public function load_plugin_textdomain() {
            load_plugin_textdomain(
                'woocommerce-pdf-invoices-bulk-download',
                false,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/'
            );
        }

        /**
         * Load plugin functionality.
         *
         * @since 1.0.0
         */
        function load() {
            add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
        }

        /**
         * Register and enqueue admin-specific JavaScript.
         *
         * @since 1.0.0
         */
        public function load_plugin_js() {
            if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
                return;
            }

            $screen = get_current_screen();

            if ( $this->plugin_screen_hook_suffix == $screen->id ) {
                wp_register_script( 'woo-pdf-invoices-bulk-download', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-form' ), '1.0.0', true );
                wp_localize_script( 'woo-pdf-invoices-bulk-download', 'BulkDownloadVars', array(
                    'ajaxurl'  => esc_url( admin_url( 'admin-ajax.php' ) ),
                    'nonce'    => wp_create_nonce( 'wp_pdf_invoices_request' ),
                    'messages' => array(
                        'generalError' => esc_html__( 'Something wrong. Please, try later.', 'woocommerce-pdf-invoices-bulk-download' ),
                        'serverError'  => esc_html__( 'Please, increase your System Status Limits (PHP Time Limit, PHP Memory Limit, PHP Max Input Vars) or contact with your hosting.', 'woocommerce-pdf-invoices-bulk-download' ),
                    ),
                ) );
                wp_enqueue_script( 'woo-pdf-invoices-bulk-download' );
            }
        }

        /**
         * Register and enqueue admin-specific stylesheet.
         *
         * @since 1.0.0
         */
        public function load_plugin_css() {

            if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
                return;
            }

            $screen = get_current_screen();

            if ( $this->plugin_screen_hook_suffix == $screen->id ) {
                wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), '1.12.1' );
                wp_enqueue_style( 'woo-pdf-invoices-bulk-download', plugin_dir_url( __FILE__ ) . 'css/style.css', array( 'jquery-ui' ), '1.0.0' );
            }
        }

        /**
         * Add the options page and menu item.
         *
         * @since 1.0.0
         */
        public function add_admin_page() {
            $icon_url = plugins_url( 'woocommerce-pdf-invoices-bulk-download/images/invoice-download-icon.svg' );

            $this->plugin_screen_hook_suffix = add_menu_page( 
                esc_html__( 'Invoice Bulk Download', 'woocommerce-pdf-invoices-bulk-download' ),
                esc_html__( 'Invoice Bulk Download', 'woocommerce-pdf-invoices-bulk-download' ),
                'manage_options',
                'invoice-bulk-download.php',
                array( $this, 'add_settings_page' ),
                $icon_url,
                66
            );
        }

        /**
         * Render the settings page for plugin.
         *
         * @since 1.0.0
         */
        public function add_settings_page() {
            include_once 'inc/settings-page.php';
        }

        /**
         * Get all order statuses.
         *
         * @since 1.0.0
         * @return array
         */
        public function get_all_order_statuses() {
            return wc_get_order_statuses();
        }

        /**
         * Fired when the plugin is activated.
         *
         * @since 1.0.0
         */
        public static function activate() {
            self::create_target_dir();
        }

        /**
         * Recursive directory creation based on full path.
         *
         * @since 1.0.0
         * @return boolean
         */
        public static function create_target_dir() {
            $upload_dir = wp_upload_dir();
            $path       = trailingslashit( path_join( $upload_dir['basedir'], self::$dir_name ) );

            return wp_mkdir_p( $path );
        }
    }
}
