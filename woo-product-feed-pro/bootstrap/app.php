<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFP
 */

namespace AdTribes\PFP;

use AdTribes\PFP\Actions\Activation;
use AdTribes\PFP\Actions\Deactivation;
use AdTribes\PFP\Factories\Admin_Notice;
use AdTribes\PFP\Helpers\Helper;
use AdTribes\PFP\Traits\Singleton_Trait;

defined( 'ABSPATH' ) || exit;

require_once WOOCOMMERCESEA_PATH . 'includes/autoload.php';

/**
 * Class App
 */
class App {

    use Singleton_Trait;

    /**
     * Holds the class object instances.
     *
     * @since 13.3.3
     * @access protected
     *
     * @var array An array of object class instance.
     */
    protected $objects;

    /**
     * App constructor.
     *
     * @since 13.3.3
     * @access public
     */
    public function __construct() {

        $this->objects = array();
    }

    /**
     * Called at the end of file to initialize autoloader.
     *
     * @since 13.3.3
     * @access public
     */
    public function boot() {

        register_activation_hook( WOOCOMMERCESEA_FILE, array( $this, 'activation_actions' ) );
        register_deactivation_hook( WOOCOMMERCESEA_FILE, array( $this, 'deactivation_actions' ) );

        // Execute codes that need to run on 'init' hook.
        add_action( 'init', array( $this, 'initialize' ) );

        /***************************************************************************
         * Run the plugin
         ***************************************************************************
         *
         * Run the plugin classes on `setup_theme` hook with priority 100 as
         * it depends on WooCommerce plugin to be loaded first and we need to make
         * sure that WP_Rewrite global object is already available.
         */
        add_action( 'setup_theme', array( $this, 'run' ), 100 );

        // Added support for WooCommerce HPOS (High-Performnce Order Storage).
        add_action( 'before_woocommerce_init', array( $this, 'hpos_compatibility' ) );
    }

    /**
     * Register classes to run.
     *
     * @since 13.3.3
     * @access public
     *
     * @param array $objects Array of class instances.
     */
    public function register_objects( $objects ) {

        $this->objects = array_merge( $this->objects, $objects );
    }

    /**
     * Plugin activation actions
     *
     * @since 13.3.3
     * @access public
     *
     * @param bool $sitewide Whether the plugin is being activated network-wide.
     */
    public function activation_actions( $sitewide ) {

        // Run the plugin actions here when it's activated.
        ( new Activation( $sitewide ) )->run();

        flush_rewrite_rules();
    }

    /**
     * Method that houses codes to be executed on init hook.
     *
     * @since 13.3.5.1
     * @access public
     */
    public function initialize() {
        // Execute activation codebase if not yet executed on plugin activation ( Mostly due to plugin dependencies ).
        $installed_version = get_site_option( WOOCOMMERCESEA_OPTION_INSTALLED_VERSION, false );

        if ( version_compare( $installed_version, Helper::get_plugin_version(), '!=' ) || get_option( 'adt_pfp_activation_code_triggered', false ) !== 'yes' ) {
            if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
                require_once ABSPATH . '/wp-admin/includes/plugin.php';
            }

            $sitewide = is_plugin_active_for_network( 'woo-product-feed-pro/woocommerce-sea.php' );
            $this->activation_actions( $sitewide );
        }
    }


    /**
     * Run the plugin classes.
     *
     * @since 13.3.3
     * @access public
     */
    public function run() {

        /***************************************************************************
         * Check required plugins
         ***************************************************************************
         *
         * We check if the required plugins are active.
         */

        $missing_required_plugins = Helper::missing_required_plugins();
        $woo_sea_plugin_data      = get_plugin_data( WOOCOMMERCESEA_FILE );

        $admin_notice = null;
        if ( ! empty( $missing_required_plugins ) ) {

            $required_plugins = array();
            foreach ( $missing_required_plugins as $missing_required_plugin ) {
                $plugin_file = WP_PLUGIN_DIR . "/{$missing_required_plugin['plugin-base']}";
                if ( file_exists( $plugin_file ) ) {
                    $plugin_data = get_plugin_data( $plugin_file );

                    $required_plugins[] = sprintf(/* translators: %1$s = opening <a> tag; %2$s = closing </a> tag */
                        esc_html__(
                            '%1$sClick here to activate %3$s plugin &rarr;%2$s',
                            'woo-product-feed-pro'
                        ),
                        sprintf(
                            '<a href="%s" title="%s">',
                            wp_nonce_url(
                                self_admin_url(
                                    'plugins.php?action=activate&plugin='
                                ) . $missing_required_plugin['plugin-base'],
                                'activate-plugin_' . $missing_required_plugin['plugin-base']
                            ),
                            esc_attr__( 'Activate this plugin', 'woo-product-feed-pro' )
                        ),
                        '</a>',
                        $plugin_data['Name']
                    );
                } else {

                    $message = '';
                    if ( false !== strpos( $missing_required_plugin['plugin-base'], 'woocommerce.php' ) ) {
                        $message .= sprintf(/* translators: %1$s = opening <p> tag; %2$s = closing </p> tag; %3$s = Product Feed PRO for WooCommerce */
                            esc_html__(
                                '%1$sUnable to activate %3$s plugin. Please install and activate WooCoomerce plugin first.%2$s',
                                'woo-product-feed-pro'
                            ),
                            '<p>',
                            '</p>',
                            $woo_sea_plugin_data['Name']
                        );
                    }

                    $message .= sprintf(/* translators: %1$s = opening <a> tag; %2$s = closing </a> tag */
                        esc_html__(
                            '%1$sClick here to install %3$s plugin &rarr;%2$s',
                            'woo-product-feed-pro'
                        ),
                        sprintf(
                            '<a href="%s" title="%s">',
                            wp_nonce_url(
                                self_admin_url(
                                    'update.php?action=install-plugin&plugin='
                                ) . $missing_required_plugin['plugin-key'],
                                'install-plugin_' . $missing_required_plugin['plugin-key']
                            ),
                            esc_attr__( 'Install this plugin', 'woo-product-feed-pro' )
                        ),
                        '</a>',
                        $missing_required_plugin['plugin-name']
                    );

                    $required_plugins[] = $message;
                }
            }

            // Initialize the missing required plugins admin notice.
            $admin_notice = new Admin_Notice(
                sprintf(/* translators: %1$s = opening <strong> tag; %2$s = closing </strong> tag; %3$s = opening <p> tag; %4$s = closing </p> tag */
                    esc_html__(
                        '%3$s%1$sProduct Feed PRO for WooCommerce%2$s plugin is missing dependency:%4$s',
                        'woo-product-feed-pro'
                    ),
                    '<strong>',
                    '</strong>',
                    '<p>',
                    '</p>'
                ) . '<p>' . implode( '</p><p>', $required_plugins ) . '</p>',
                'error',
                'html'
            );
        }

        /***************************************************************************
         * Required plugins check failed
         ***************************************************************************
         *
         * Display the admin notice if the required plugins check failed
         * and bail out.
         */
        if ( null !== $admin_notice ) {
            $admin_notice->run();

            return;
        }

        /***************************************************************************
         * Run the plugin classes
         ***************************************************************************
         *
         * Make sure that the classes to be run extends the abstract class or has
         * implemented a `run` method.
         */
        foreach ( $this->objects as $object ) {
            if ( ! method_exists( $object, 'run' ) ) {
                _doing_it_wrong(
                    __METHOD__,
                    esc_html__(
                        'The class does not have a run method. Please make sure to extend the Abstract_Class class.',
                        'woo-product-feed-pro'
                    ),
                    esc_html( Helper::get_plugin_data( 'Version' ) )
                );
                continue;
            }
            $class_object = strtolower( wp_basename( get_class( $object ) ) );

            $this->objects[ $class_object ] = apply_filters(
                'woo_sea_class_object',
                $object,
                $class_object,
                $this
            );
            $this->objects[ $class_object ]->run();
        }
    }

    /**
     * Plugin deactivation actions
     *
     * @since 13.3.3
     * @access public
     *
     * @param bool $sitewide Whether the plugin is being deactivated network-wide.
     */
    public function deactivation_actions( $sitewide ) {

        // Run the plugin deactivation actions.
        ( new Deactivation( $sitewide ) )->run();

        flush_rewrite_rules();
    }

    /**
     * HPOS compatibility
     *
     * @since 13.3.3
     * @access public
     */
    public function hpos_compatibility() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WOOCOMMERCESEA_FILE, true );
        }
    }
}

/***************************************************************************
 * Instantiate classes
 ***************************************************************************
 *
 * Instantiate classes to be registered and run.
 */
App::instance()->register_objects(
    array_merge(
        require_once WOOCOMMERCESEA_PATH . 'bootstrap/class-objects.php',
    )
);

return App::instance();
