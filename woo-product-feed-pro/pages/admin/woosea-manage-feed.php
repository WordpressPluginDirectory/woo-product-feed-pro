<?php
use AdTribes\PFP\Helpers\Product_Feed_Helper;

$total_projects = Product_Feed_Helper::get_total_product_feed();
$plugin_data    = get_plugin_data( __FILE__ );
$versions       = array(
    'PHP'                          => (float) phpversion(),
    'Wordpress'                    => get_bloginfo( 'version' ),
    'WooCommerce'                  => WC()->version,
    'WooCommerce Product Feed PRO' => WOOCOMMERCESEA_PLUGIN_VERSION,
);

/**
 * Change default footer text, asking to review our plugin.
 *
 * @param string $default Default footer text.
 *
 * @return string Footer text asking to review our plugin.
 **/
function my_footer_text( $default ) {
    $rating_link = sprintf(
        /* translators: %s: WooCommerce Product Feed PRO plugin rating link */
        esc_html__( 'If you like our %1$s plugin please leave us a %2$s rating. Thanks in advance!', 'woo-product-feed-pro' ),
        '<strong>WooCommerce Product Feed PRO</strong>',
        '<a href="https://wordpress.org/support/plugin/woo-product-feed-pro/reviews?rate=5#new-post" target="_blank" class="woo-product-feed-pro-ratingRequest">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
    );
    return $rating_link;
}
add_filter( 'admin_footer_text', 'my_footer_text' );

/**
 * Create notification object and get message and message type as WooCommerce is inactive
 * also set variable allowed on 0 to disable submit button on step 1 of configuration
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications();
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '9', 'false' );
} else {
    $notifications_box = $notifications_obj->get_admin_notifications( '8', 'false' );
}

if ( $versions['PHP'] < 5.6 ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '11', 'false' );
}

if ( $versions['WooCommerce'] < 3 ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '13', 'false' );
}

if ( ! wp_next_scheduled( 'woosea_cron_hook' ) ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '12', 'false' );
}

// create nonce
$nonce = wp_create_nonce( 'woosea_ajax_nonce' );

?>
<div class="wrap">
    <div class="woo-product-feed-pro-form-style-2">
        <tbody class="woo-product-feed-pro-body">
            <?php
            // Set default notification to show
            $getelite_notice = get_option( 'woosea_getelite_notification' );
            if ( empty( $getelite_notice['show'] ) ) {
                $getelite_notice              = array();
                $getelite_notice['show']      = 'yes';
                $getelite_notice['timestamp'] = date( 'd-m-Y' );
            }

            if ( $getelite_notice['show'] != 'no' ) {
            ?>
                <div class="notice notice-info get_elite is-dismissible">
                    <p>
                        <strong><?php esc_html_e( 'Would you like to get more out of your product feeds? Upgrade to the Elite version of the plugin and you will get:', 'woo-product-feed-pro' ); ?></strong><br /></br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Priority support - we will help you to get your product feed(s) up-and-running;', 'woo-product-feed-pro' ); ?><br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'GTIN, Brand, MPN, EAN, Condition and more fields for your product feeds', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/add-gtin-mpn-upc-ean-product-condition-optimised-title-and-brand-attributes/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticeaddingfields" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Solve Googe Shopping price mismatch product disapprovals', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/woocommerce-structured-data-bug/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticestructureddatabug" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Advanced product data manipulation', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/feature-product-data-manipulation/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticeproductdatamanipulation" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'WPML support - including their currency switcher', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/wpml-support/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticewpmlsupport" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Aelia  & Curcy currency switcher support', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/aelia-currency-switcher-feature/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticeaeliasupport" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Polylang support', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/polylang-support-product-feeds/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticepolylangsupport" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br />
                        <span class="dashicons dashicons-yes"></span><?php esc_html_e( 'Facebook pixel feature', 'woo-product-feed-pro' ); ?> [<a href="https://adtribes.io/facebook-pixel-feature/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradenoticefacebookpixelfeature" target="_blank"><?php esc_html_e( 'Read more', 'woo-product-feed-pro' ); ?></a>];<br /><br />
                        <a class="button button-pink" href="https://adtribes.io/pricing/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradetoelitenoticebutton" target="_blank"><?php esc_html_e( 'Upgrade To Elite', 'woo-product-feed-pro' ); ?></a>
                    </p>
                </div>
            <?php
            }

            if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php esc_html_e( 'WARNING: Your WP-Cron is disabled', 'woo-product-feed-pro' ); ?></strong><br /></br />
                        We detected that your WP-cron has been disabled in your wp-config.php file. Our plugin heavily depends on the WP-cron being active otherwise it cannot update and generate your product feeds. <a href="https://adtribes.io/help-my-feed-processing-is-stuck/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=cron-warning&utm_content=notification" target="_blank"><strong>Please enable your WP-cron first</strong></a>.
                    </p>
                </div>
            <?php
            }

            // Double check if the woosea_cron_hook is there, when it is not create a new one
            if ( ! wp_next_scheduled( 'woosea_cron_hook' ) ) {
                wp_schedule_event( time(), 'hourly', 'woosea_cron_hook' );
            }

            /**
             * Request our plugin users to write a review
             */
            if ( 0 > $total_projects ) {
                $first_activation         = get_option( 'woosea_first_activation' );
                $notification_interaction = get_option( 'woosea_review_interaction' );
                $current_time             = time();
                $show_after               = 604800; // Show only after one week
                $is_active                = $current_time - $first_activation;
                $page                     = sanitize_text_field( basename( $_SERVER['REQUEST_URI'] ) );

                if ( ( $total_projects > 0 ) && ( $is_active > $show_after ) && ( $notification_interaction != 'yes' ) ) {
                    echo '<div class="notice notice-info review-notification">';
                    echo '<table><tr><td></td><td><font color="green" style="font-weight:normal";><p>Hey, I noticed you have been using our plugin, <u>Product Feed PRO for WooCommerce by AdTribes.io</u>, for over a week now and have created product feed projects with it - that\'s awesome! Could you please do our support volunteers and me a BIG favor and give it a <strong>5-star rating</strong> on WordPress? Just to help us spread the word and boost our motivation. We would greatly appreciate if you would do so :)<br/>~ Adtribes.io support team<br><ul><li><span class="ui-icon ui-icon-caret-1-e" style="display: inline-block;"></span><a href="https://wordpress.org/support/plugin/woo-product-feed-pro/reviews?rate=5#new-post" target="_blank" class="dismiss-review-notification">Ok, you deserve it</a></li><li><span class="ui-icon ui-icon-caret-1-e" style="display: inline-block;"></span><a href="#" class="dismiss-review-notification">Nope, maybe later</a></li><li><span class="ui-icon ui-icon-caret-1-e" style="display: inline-block;"></span><a href="#" class="dismiss-review-notification">I already did</a></li></ul></p></font></td></tr></table>';
                    echo '</div>';
                }
            }
            ?>

            <div class="woo-product-feed-pro-form-style-2-heading">
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( WOOCOMMERCESEA_PLUGIN_URL . '/images/adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-pro' ); ?>"></a>
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank" class="logo-upgrade">Upgrade to Elite</a>
                <h1 class="title"><?php esc_html_e( 'Manage feeds', 'woo-product-feed-pro' ); ?></h1>
            </div>
            <div class="woo-product-feed-pro-table-wrapper">
                <div class="woo-product-feed-pro-table-left">
                    <?php require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'manage-feed/view-manage-feed-table.php'; ?>
                </div>
                <?php require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'view-sidebar.php'; ?>
            </div>
        </tbody>
    </div>
</div>
