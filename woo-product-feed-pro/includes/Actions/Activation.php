<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFP\Actions
 */

namespace AdTribes\PFP\Actions;

use AdTribes\PFP\Abstracts\Abstract_Class;
use AdTribes\PFP\Helpers\Helper;

// Updates.
use AdTribes\PFP\Updates\Version_13_3_5_Update;

/**
 * Activation class.
 *
 * @since 13.3.3
 */
class Activation extends Abstract_Class {

    /**
     * Holds boolean value whether the plugin is being activated network wide.
     *
     * @since 13.3.3
     * @access protected
     *
     * @var bool
     */
    protected $network_wide;

    /**
     * Constructor.
     *
     * @since 13.3.3
     * @access public
     *
     * @param bool $network_wide Whether the plugin is being activated network wide.
     */
    public function __construct( $network_wide ) {

        $this->network_wide = $network_wide;
    }

    /**
     * Activate the plugin.
     *
     * @since 13.3.3
     * @access private
     *
     * @param int $blog_id Blog ID.
     */
    private function _activate_plugin( $blog_id ) {
        /**
         * If previous multisite installs site store license options using normal get/add/update_option functions.
         * These stores the option on a per sub-site basis. We need move these options network wide in multisite setup
         * via get/add/update_site_option functions.
         */
        if ( is_multisite() ) {
            $installed_version = get_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION );
            if ( $installed_version ) {
                update_site_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION, $installed_version );
                delete_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION );
            }
        }

        /***************************************************************************
         * Version 13.3.5 Update
         ***************************************************************************
         *
         * This version is the custom post type update.
         */
        ( new Version_13_3_5_Update() )->run();

        // Update current installed plugin version.
        update_site_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION, Helper::get_plugin_version() );

        /***************************************************************************
         * Run legacy activation class.
         ***************************************************************************
         *
         * This class is responsible for running the activation checks.
         * It is a legacy class and should be removed in future versions.
         */
        require WOOCOMMERCESEA_PATH . 'classes/class-activate.php';
        \WooSEA_Activation::activate_checks();

        update_option( 'adt_pfp_activation_code_triggered', 'yes' );
    }

    /**
     * Run plugin activation actions.
     *
     * @since 13.3.3
     * @access public
     */
    public function run() {
        global $wpdb;

        if ( is_multisite() ) {
            if ( $this->network_wide ) {
                // get ids of all sites.
                $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

                foreach ( $blog_ids as $blog_id ) {
                    switch_to_blog( $blog_id );
                    $this->_activate_plugin( $blog_id );
                }
                restore_current_blog();
            } else {
                $this->_activate_plugin( $wpdb->blogid );
            }
            // activated on a single site, in a multi-site.
        } else {
            // activated on a single site.
            $this->_activate_plugin( $wpdb->blogid );
        }
    }
}
