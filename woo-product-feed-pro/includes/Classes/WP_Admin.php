<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFP\Classes
 */

namespace AdTribes\PFP\Classes;

use AdTribes\PFP\Abstracts\Abstract_Class;
use AdTribes\PFP\Helpers\Helper;
use AdTribes\PFP\Helpers\Product_Feed_Helper;
use AdTribes\PFP\Updates\Version_13_3_5_Update;

/**
 * General wp-admin related functionalities and/or overrides.
 *
 * @since 13.3.3
 */
class WP_Admin extends Abstract_Class {

    /**
     * Enqueue admin scripts.
     *
     * @since 13.3.3
     * @access public
     *
     * @param string $hook The current admin page.
     */
    public function admin_enqueue_scripts( $hook ) {
        $page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        // Enqueue scripts and styles only on the plugin pages.
        if ( Helper::is_pfp_page() ) {
            wp_enqueue_script( 'select2' );
            wp_enqueue_script( 'jquery-tiptip' );
            wp_enqueue_style( 'woocommerce_admin_styles' );
            wp_enqueue_style( 'pfp-admin-css', WOOCOMMERCESEA_PLUGIN_URL . '/css/pfp-admin.css', array(), WOOCOMMERCESEA_PLUGIN_VERSION );
        }

        // Admin wide styles and scripts.
        wp_enqueue_style( 'pfp-admin-wide-css', WOOCOMMERCESEA_PLUGIN_URL . '/css/pfp-admin-wide.css', array(), WOOCOMMERCESEA_PLUGIN_VERSION );
        wp_enqueue_script( 'pfp-admin-wide-js', WOOCOMMERCESEA_PLUGIN_URL . '/js/pfp-admin-wide.js', array( 'jquery' ), WOOCOMMERCESEA_PLUGIN_VERSION, true );
        wp_localize_script(
            'pfp-admin-wide-js',
            'pfp_admin_wide',
            array(
                'upgradelink' => 'https://adtribes.io/pricing/?utm_source=pfp&utm_medium=upsell&utm_campaign=menuprolink',
            )
        );
    }

    /**
     * Register about page menu
     *
     * @since 13.3.4
     * @access public
     */
    public function register_page_menu() {
        add_submenu_page(
            'woo-product-feed-pro',
            __( 'Help', 'woo-product-feed-pro' ),
            __( 'Help', 'woo-product-feed-pro' ),
            'manage_woocommerce', // phpcs:ignore
            'pfp-help-page',
            array( $this, 'view_help_page' ),
            9
        );

        add_submenu_page(
            'woo-product-feed-pro',
            __( 'About', 'woo-product-feed-pro' ),
            __( 'About', 'woo-product-feed-pro' ),
            'manage_woocommerce', // phpcs:ignore
            'pfp-about-page',
            array( $this, 'view_about_page' ),
            10
        );

        add_submenu_page(
            'woo-product-feed-pro',
            __( 'Upgrade To Elite', 'woo-product-feed-pro' ),
            __( 'Upgrade To Elite', 'woo-product-feed-pro' ),
            'manage_woocommerce', // phpcs:ignore.
            'pfp-upgrade-to-elite-page',
            '',
            99
        );
    }

    /**
     * View for About page.
     *
     * @since 13.3.4
     * @access public
     */
    public function view_about_page() {
        require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'view-about-page.php';
    }

    /**
     * View for Help page.
     *
     * @since 13.3.4
     * @access public
     */
    public function view_help_page() {
        require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'view-help-page.php';
    }

    /**
     * Add WC navigation bar to page.
     *
     * @since 13.3.4
     * @access public
     */
    public function wc_navigation_bar() {
        if ( function_exists( 'wc_admin_connect_page' ) ) {
            wc_admin_connect_page(
                array(
                    'id'        => 'php-about-page',
                    'screen_id' => 'product-feed-pro_page_pfp-about-page',
                    'title'     => __( 'About Page', 'woo-product-feed-pro' ),
                )
            );

            wc_admin_connect_page(
                array(
                    'id'        => 'php-help-page',
                    'screen_id' => 'product-feed-pro_page_pfp-help-page',
                    'title'     => __( 'Help Page', 'woo-product-feed-pro' ),
                )
            );
        }
    }

    /**
     * Show lite notice bar.
     *
     * This is a notice bar that will be shown on the top of the page.
     *
     * @since 13.3.4
     * @access public
     */
    public function show_notice_bar_lite() {
        if ( Helper::is_show_notice_bar_lite() ) {
            $upgrade_link = apply_filters( 'pfp_notice_bar_lite_upgrade_link', 'https://adtribes.io/pricing/?utm_source=pfp&utm_medium=upsell&utm_campaign=litebar' );
            $message      = apply_filters(
                'pfp_notice_bar_lite_message',
                sprintf(
                    // translators: %1$s and %2$s are placeholders for html tags.
                    __( 'You\'re using Product Feed Pro FREE VERSION. To unlock more features consider %1$supgrading to Elite%2$s.', 'woo-product-feed-pro' ),
                    '<a href="%s" target="_blank">',
                    '</a>'
                )
            );

            require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'view-notice-bar-lite.php';
        }
    }

    /***************************************************************************
     * AJAX ACTIONS
     * **************************************************************************
     */

    /**
     * Migrate to custom post type.
     *
     * @since 13.3.5
     * @access public
     */
    public function ajax_migrate_to_custom_post_type() {
        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'woo-product-feed-pro' ) ) );
        }

        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        // Run the migration.
        ( new Version_13_3_5_Update( true ) )->run();

        wp_send_json_success( array( 'message' => __( 'Migration successful.', 'woo-product-feed-pro' ) ) );
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 13.3.3
     */
    public function run() {

        if ( ! is_admin() ) {
            return;
        }

        // Enqueue admin styles and scripts.
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        // Add a new submenus.
        add_action( 'admin_menu', array( $this, 'register_page_menu' ), 99 );

        // Add WC navigation bar to page.
        add_action( 'init', array( $this, 'wc_navigation_bar' ) );

        // Add notice bar.
        add_action( 'in_admin_header', array( $this, 'show_notice_bar_lite' ), 10 );

        // Ajax actions.
        add_action( 'wp_ajax_adt_migrate_to_custom_post_type', array( $this, 'ajax_migrate_to_custom_post_type' ) );
    }
}
