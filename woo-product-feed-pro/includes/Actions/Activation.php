<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFP\Actions
 */

namespace AdTribes\PFP\Actions;

use AdTribes\PFP\Abstracts\Abstract_Class;
use AdTribes\PFP\Helpers\Helper;

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
     * Run plugin activation actions.
     *
     * @since 13.3.3
     * @access public
     */
    public function run() {

        // Update plugin version installed in database.
        if ( version_compare( Helper::get_plugin_version(), get_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION ), '!=' ) ) {
            update_site_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION, Helper::get_plugin_version() );
        }

        /***************************************************************************
         * Run legacy activation class.
         ***************************************************************************
         *
         * This class is responsible for running the activation checks.
         * It is a legacy class and should be removed in future versions.
         */
        require WOOCOMMERCESEA_PATH . 'classes/class-activate.php';
        \WooSEA_Activation::activate_checks();
    }
}
