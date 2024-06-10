<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFP\Actions
 */

namespace AdTribes\PFP\Actions;

use AdTribes\PFP\Abstracts\Abstract_Class;

/**
 * Deactivation class.
 *
 * @since 13.3.3
 */
class Deactivation extends Abstract_Class {

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
     * Cleanup cron.
     *
     * @since 13.3.3
     * @access public
     */
    protected function cleanup_cron() {
        wp_clear_scheduled_hook( 'woosea_cron_hook' );
    }

    /**
     * Cleanup options.
     *
     * @since 13.3.3
     * @access public
     */
    protected function cleanup_options() {
        delete_option( 'woosea_getelite_notification' );
        delete_option( 'woosea_license_notification_closed' );
    }

    /**
     * Run plugin deactivation actions.
     *
     * @since 13.3.3
     * @access public
     */
    public function run() {
        $this->cleanup_cron();
        $this->cleanup_options();
    }
}
