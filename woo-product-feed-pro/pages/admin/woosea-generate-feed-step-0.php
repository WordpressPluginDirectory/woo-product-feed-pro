<?php
use AdTribes\PFP\Factories\Product_Feed;

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
$versions = array(
    'PHP'                          => (float) phpversion(),
    'Wordpress'                    => get_bloginfo( 'version' ),
    'WooCommerce'                  => WC()->version,
    'WooCommerce Product Feed PRO' => WOOCOMMERCESEA_PLUGIN_VERSION,
);

$nonce = wp_create_nonce( 'woosea_ajax_nonce' );

$notifications_obj = new WooSEA_Get_Admin_Notifications();
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '9', 'false' );
    $locale            = 'NL';
} else {
    $notifications_box = $notifications_obj->get_admin_notifications( '0', 'false' );
    $default           = wc_get_base_location();
    $locale            = apply_filters( 'woocommerce_countries_base_country', $default['country'] );
}

if ( $versions['PHP'] < 5.6 ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '11', 'false' );
}

if ( ! wp_next_scheduled( 'woosea_cron_hook' ) ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '12', 'false' );
}

if ( $versions['WooCommerce'] < 3 ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '13', 'false' );
}

/**
 * Get shipping zones
 */
$shipping_zones    = WC_Shipping_Zones::get_zones();
$nr_shipping_zones = count( $shipping_zones );

/**
 * Get channels
 */
$channel_configs = get_option( 'channel_statics' );

/**
 * Get countries and channels
 */
$channel_obj = new WooSEA_Attributes();
$countries   = $channel_obj->get_channel_countries();
$channels    = $channel_obj->get_channels( $locale );

$feed         = null;
$project_hash = isset( $_GET['project_hash'] ) ? sanitize_text_field( $_GET['project_hash'] ) : '';
if ( array_key_exists( 'project_hash', $_GET ) ) {
    $feed                = new Product_Feed( sanitize_text_field( $_GET['project_hash'] ) );
    $feed_legacy_country = $feed->get_legacy_country();
    $manage_project      = 'yes';
}

/**
 * Action hook to add content before the product feed manage page.
 *
 * @param int                      $step         Step number.
 * @param string                   $project_hash Project hash.
 * @param array|Product_Feed|null  $feed         Product_Feed object or array of project data.
 */
do_action( 'adt_before_product_feed_manage_page', 0, $project_hash, $feed );
?>

<div class="wrap">
    <div class="woo-product-feed-pro-form-style-2">
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
                    <a class="button button-pink" href="https://adtribes.io/pro-vs-elite/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=upgradetoelitenoticebutton" target="_blank"><?php esc_html_e( 'Upgrade To Elite', 'woo-product-feed-pro' ); ?></a>
                </p>
            </div>
        <?php
        }
        ?>
        <div class="woo-product-feed-pro-form-style-2-heading">
            <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( WOOCOMMERCESEA_PLUGIN_URL . '/images/adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-pro' ); ?>"></a> 
            <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank" class="logo-upgrade">Upgrade to Elite</a>
            <h1 class="title"><?php esc_html_e( 'General feed settings', 'woo-product-feed-pro' ); ?></h1>
        </div>

        <form action="" id="myForm" method="post" name="myForm">
            <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>

            <div class="woo-product-feed-pro-table-wrapper">
                <div class="woo-product-feed-pro-table-left">

                    <table class="woo-product-feed-pro-table">
                        <tbody class="woo-product-feed-pro-body">
                            <div id="projecterror"></div>
                            <tr>
                                <td width="30%"><span><?php esc_html_e( 'Project name', 'woo-product-feed-pro' ); ?>:<span class="required">*</span></span></td>
                                <td>
                                    <div style="display: block;">
                                        <?php if ( $feed ) : ?> 
                                            <input type="text" class="input-field" id="projectname" name="projectname" value="<?php echo esc_attr( $feed->title ); ?>"/>
                                            <div id="projecterror"></div>
                                        <?php else : ?>
                                            <input type="text" class="input-field" id="projectname" name="projectname"/> <div id="projecterror"></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Country', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <select name="countries" id="countries" class="select-field woo-sea-select2">
                                        <option><?php esc_html_e( 'Select a country', 'woo-product-feed-pro' ); ?></option>
                                        <?php foreach ( $countries as $value ) : ?>
                                            <?php if ( $feed && ( $value == $feed_legacy_country ) ) : ?>
                                                <option value="<?php echo esc_attr( $value ); ?>" selected><?php echo esc_html( $value ); ?></option>
                                            <?php else : ?>
                                                <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $value ); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Channel', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <?php if ( $feed ) : ?>
                                    <select name="channel_hash" id="channel_hash" class="select-field" disabled>
                                        <option value="<?php echo esc_html( $feed->channel_hash ); ?>" selected><?php echo esc_html( $feed->get_channel( 'name' ) ); ?></option>
                                    </select>
                                    <?php
                                    else :
                                        $customfeed           = '';
                                        $advertising          = '';
                                        $marketplace          = '';
                                        $shopping             = '';
                                        $optgroup_customfeed  = 0;
                                        $optgroup_advertising = 0;
                                        $optgroup_marketplace = 0;
                                        $optgroup_shopping    = 0;

                                        print '<select name="channel_hash" id="channel_hash" class="select-field woo-sea-select2">';

                                        foreach ( $channels as $key => $val ) {
                                            if ( $val['type'] == 'Custom Feed' ) {
                                                if ( $optgroup_customfeed == 1 ) {
                                                    $customfeed .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                } else {
                                                    $customfeed          = '<optgroup label="Custom Feed">';
                                                    $customfeed         .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    $optgroup_customfeed = 1;
                                                }
                                            }

                                            if ( $val['type'] == 'Advertising' ) {
                                                if ( $optgroup_advertising == 1 ) {
                                                    $advertising .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                } else {
                                                    $advertising          = '<optgroup label="Advertising">';
                                                    $advertising         .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    $optgroup_advertising = 1;
                                                }
                                            }

                                            if ( $val['type'] == 'Marketplace' ) {
                                                if ( $optgroup_marketplace == 1 ) {
                                                    $marketplace .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                } else {
                                                    $marketplace          = '<optgroup label="Marketplace">';
                                                    $marketplace         .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                    $optgroup_marketplace = 1;
                                                }
                                            }

                                            if ( $val['type'] == 'Comparison shopping engine' ) {
                                                if ( $optgroup_shopping == 0 ) {
                                                    $shopping .= "<option value=\"$val[channel_hash]\">$key</option>";
                                                } else {
                                                    $shopping          = '<optgroup label="Comparison Shopping Engine">';
                                                    $shopping         .= "<option value=\"$val[channel_hash]\" selected>$key</option>";
                                                    $optgroup_shopping = 1;
                                                }
                                            }
                                        }
                                        echo "$customfeed";
                                        echo "$advertising";
                                        echo "$marketplace";
                                        echo "$shopping";
                                        print '</select>';
                                    endif;
                                    ?>
                                </td>
                            </tr>
                            <tr id="product_variations">
                                <td><span><?php esc_html_e( 'Include product variations', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <label class="woo-product-feed-pro-switch">
                                        <?php
                                        if ( $feed && $feed->include_product_variations ) {
                                            print '<input type="checkbox" id="variations" name="product_variations" class="checkbox-field" checked>';
                                        } else {
                                            print '<input type="checkbox" id="variations" name="product_variations" class="checkbox-field">';
                                        }
                                        ?>
                                        <div class="woo-product-feed-pro-slider round"></div>
                                    </label>
                                </td>
                            </tr>
                            <tr id="default_variation">
                                <td><span><?php esc_html_e( 'And only include default product variation', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <label class="woo-product-feed-pro-switch">
                                        <?php
                                        if ( $feed && $feed->only_include_default_product_variation ) {
                                            print '<input type="checkbox" id="default_variations" name="default_variations" class="checkbox-field" checked>';
                                        } else {
                                            print '<input type="checkbox" id="default_variations" name="default_variations" class="checkbox-field">';
                                        }
                                        ?>
                                        <div class="woo-product-feed-pro-slider round"></div>
                                    </label>
                                </td>
                            </tr>
                            <tr id="lowest_price_variation">
                                <td><span><?php esc_html_e( 'And only include lowest priced product variation(s)', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <label class="woo-product-feed-pro-switch">
                                        <?php
                                        if ( $feed && $feed->only_include_lowest_product_variation ) {
                                            print '<input type="checkbox" id="lowest_price_variations" name="lowest_price_variations" class="checkbox-field" checked>';
                                        } else {
                                            print '<input type="checkbox" id="lowest_price_variations" name="lowest_price_variations" class="checkbox-field">';
                                        }
                                        ?>
                                        <div class="woo-product-feed-pro-slider round"></div>
                                    </label>
                                </td>
                            </tr>
                            <tr id="file">
                                <td><span><?php esc_html_e( 'File format', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <select name="fileformat" id="fileformat" class="select-field">
                                        <?php
                                        $format_arr = array( 'xml', 'csv', 'txt', 'tsv' );
                                        foreach ( $format_arr as $format ) {
                                            $format_upper = strtoupper( $format );
                                            if ( $feed && ( $format == $feed->file_format ) ) {
                                                echo "<option value=\"$format\" selected>$format_upper</option>";
                                            } else {
                                                echo "<option value=\"$format\">$format_upper</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr id="delimiter">
                                <td><span><?php esc_html_e( 'Delimiter', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <select name="delimiter" class="select-field">
                                        <?php
                                        $delimiter_arr = array( ',', '|', ';', 'tab', '#' );
                                        foreach ( $delimiter_arr as $delimiter ) {
                                            if ( $feed && ( $delimiter == $feed->delimiter ) ) {
                                                echo "<option value=\"$delimiter\" selected>$delimiter</option>";
                                            } else {
                                                echo "<option value=\"$delimiter\">$delimiter</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><span><?php esc_html_e( 'Refresh interval', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <select name="cron" class="select-field">
                                        <?php
                                        $refresh_arr = array( 'daily', 'twicedaily', 'hourly', 'no refresh' );
                                        foreach ( $refresh_arr as $refresh ) {
                                            $refresh_upper = ucfirst( $refresh );
                                            if ( $feed && ( $refresh == $feed->refresh_interval ) ) {
                                                echo "<option value=\"$refresh\" selected>$refresh_upper</option>";
                                            } else {
                                                echo "<option value=\"$refresh\">$refresh_upper</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php esc_html_e( 'Refresh only when products changed', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <?php
                                    if ( $feed && $feed->refresh_only_when_product_changed ) {
                                        print '<input name="products_changed" type="checkbox" class="checkbox-field" checked> <a href="https://adtribes.io/update-product-feed-products-changed-new-ones-added/" target="_blank">Read our tutorial about this feature</a>';
                                    } else {
                                        print '<input name="products_changed" type="checkbox" class="checkbox-field"> <a href="https://adtribes.io/update-product-feed-products-changed-new-ones-added/" target="_blank">Read our tutorial about this feature</a>';
                                    }
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php esc_html_e( 'Create a preview of the feed', 'woo-product-feed-pro' ); ?>:</span></td>
                                <td>
                                    <?php
                                    if ( $feed && $feed->create_preview ) {
                                        print '<input name="preview_feed" type="checkbox" class="checkbox-field" checked> <a href="https://adtribes.io/create-product-feed-preview/" target="_blank">Read our tutorial about this feature</a>';
                                    } else {
                                        print '<input name="preview_feed" type="checkbox" class="checkbox-field"> <a href="https://adtribes.io/create-product-feed-preview/" target="_blank">Read our tutorial about this feature</a>';
                                    }
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <?php if ( $feed ) : ?>
                                        <input type="hidden" name="project_hash" id="project_hash" value="<?php echo esc_attr( $feed->legacy_project_hash ); ?>" />
                                        <input type="hidden" name="channel_hash" id="channel_hash" value="<?php echo esc_attr( $feed->channel_hash ); ?>" />
                                        <input type="hidden" name="project_update" id="project_update" value="yes" />
                                        <input type="hidden" name="step" id="step" value="100" />
                                        <input type="submit" id="goforit" value="Save" />
                                    <?php else : ?>
                                        <input type="hidden" name="step" id="step" value="99" />
                                        <input type="submit" id="goforit" value="Save & continue" />
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <?php require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'view-sidebar.php'; ?>
            </div>
        </form>
    </div>
</div>
