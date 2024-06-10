<?php
$cron_projects            = get_option( 'cron_projects' );
$add_manipulation_support = get_option( 'add_manipulation_support' );
$plugin_data              = get_plugin_data( __FILE__ );
$versions                 = array(
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
            if ( array_key_exists( 'debug', $_GET ) ) {

                // KILL SWITCH, THIS WILL REMOVE ALL YOUR FEED PROJECTS
                // delete_option( 'cron_projects');

            } elseif ( array_key_exists( 'force-active', $_GET ) ) {
                // Force active all feeds
                foreach ( $cron_projects as $key => $value ) {
                    $cron_projects[ $key ]['active'] = 'true';
                }
                update_option( 'cron_projects', $cron_projects, 'no' );
            } elseif ( array_key_exists( 'force-clean', $_GET ) ) {
                if ( current_user_can( 'manage_options' ) ) {
                    // Forcefully remove all feed and plugin configurations
                    delete_option( 'cron_projects' );
                    delete_option( 'channel_statics' );
                    delete_option( 'woosea_getelite_notification' );
                    delete_option( 'woosea_license_notification_closed' );
                    wp_clear_scheduled_hook( 'woosea_cron_hook' );
                }
            } elseif ( array_key_exists( 'force-deduplication', $_GET ) ) {
                // Force deduplication
                foreach ( $cron_projects as $key => $value ) {
                    $channel_hash       = $cron_projects[ $key ]['channel_hash'];
                    $channel_duplicates = 'woosea_duplicates_' . $channel_hash;
                    delete_option( $channel_duplicates );
                }
            } else {
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
            if ( ! empty( $cron_projects ) ) {
                $nr_projects              = count( $cron_projects );
                $first_activation         = get_option( 'woosea_first_activation' );
                $notification_interaction = get_option( 'woosea_review_interaction' );
                $current_time             = time();
                $show_after               = 604800; // Show only after one week
                $is_active                = $current_time - $first_activation;
                $page                     = sanitize_text_field( basename( $_SERVER['REQUEST_URI'] ) );

                if ( ( $nr_projects > 0 ) && ( $is_active > $show_after ) && ( $notification_interaction != 'yes' ) ) {
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

                    <table id="woosea_main_table" class="woo-product-feed-pro-table">
                        <tr>
                            <td><strong><?php esc_html_e( 'Active', 'woo-product-feed-pro' ); ?></strong></td>
                            <td><strong><?php esc_html_e( 'Project name and channel', 'woo-product-feed-pro' ); ?></strong></td>
                            <td><strong><?php esc_html_e( 'Format', 'woo-product-feed-pro' ); ?></strong></td>
                            <td><strong><?php esc_html_e( 'Refresh interval', 'woo-product-feed-pro' ); ?></strong></td>
                            <td><strong><?php esc_html_e( 'Status', 'woo-product-feed-pro' ); ?></strong></td>
                            <td></td>
                        </tr>

                        <?php
                        if ( $cron_projects ) {
                            $toggle_count = 1;
                            $class        = '';

                            foreach ( $cron_projects as $key => $val ) {
                                if ( isset( $val['active'] ) && ( $val['active'] == 'true' ) ) {
                                    $checked = 'checked';
                                    $class   = '';
                                } else {
                                    $checked = '';
                                }

                                if ( isset( $val['filename'] ) ) {
                                    $projectname = ucfirst( $val['projectname'] );
                        ?>
                                    <form action="" method="post">
                                        <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>
                                        <tr class="<?php echo "$class"; ?>">
                                            <td>
                                                <label class="woo-product-feed-pro-switch">
                                                    <input type="hidden" name="manage_record" value="<?php echo "$val[project_hash]"; ?>"><input type="checkbox" name="project_active[]" class="checkbox-field" value="<?php echo "$val[project_hash]"; ?>" <?php echo "$checked"; ?>>
                                                    <div class="woo-product-feed-pro-slider round"></div>
                                                </label>
                                            </td>
                                            <td><span><?php echo "$projectname</span><br/><span class=\"woo-product-feed-pro-channel\">Channel: $val[name]</span>"; ?></span></td>
                                            <td><span><?php echo "$val[fileformat]"; ?></span></td>
                                            <td><span><?php echo "$val[cron]"; ?></span></td>
                                            <?php
                                            if ( $val['running'] == 'processing' ) {
                                                $proc_perc = round( ( $val['nr_products_processed'] / $val['nr_products'] ) * 100 );
                                                echo "<td><span class=\"woo-product-feed-pro-blink_me\" id=\"woosea_proc_$val[project_hash]\">$val[running] ($proc_perc%)</span></td>";
                                            } else {
                                                echo "<td><span class=\"woo-product-feed-pro-blink_off_$val[project_hash]\" id=\"woosea_proc_$val[project_hash]\">$val[running]</span></td>";
                                            }
                                            ?>
                                            <td>
                                                <div class="actions">
                                                    <span class="gear dashicons dashicons-admin-generic" id="gear_<?php echo "$val[project_hash]"; ?>" title="project settings" style="display: inline-block;"></span>
                                                    <?php
                                                    if ( $val['running'] != 'processing' ) {
                                                    ?>
                                                        <?php
                                                        if ( $val['active'] == 'true' ) {
                                                            echo "<span class=\"dashicons dashicons-admin-page\" id=\"copy_$val[project_hash]\" title=\"copy project\" style=\"display: inline-block;\"></span>";
                                                            echo "<span class=\"dashicons dashicons-update\" id=\"refresh_$val[project_hash]\" title=\"manually refresh productfeed\" style=\"display: inline-block;\"></span>";

                                                            if ( $val['running'] != 'not run yet' ) {
                                                                echo "<a href=\"$val[external_file]\" target=\"_blank\" class=\"dashicons dashicons-download\" id=\"download\" title=\"download productfeed\" style=\"display: inline-block\"></a>";
                                                            }
                                                        }
                                                        ?>
                                                        <span class="trash dashicons dashicons-trash" id="trash_<?php echo "$val[project_hash]"; ?>" title="delete project and productfeed" style="display: inline-block;"></span>
                                                        <?php
                                                        if ( $val['fields'] == 'google_shopping' ) {
                                                        ?>
                                                            <!--    
                                                                            <a href="admin.php?page=woo-product-feed-pro&action=edit_project&step=11&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>" class="dashicons dashicons-warning" id="warning_<?php echo "$val[project_hash]"; ?>" title="check notifications" style="display: inline-block;" target="_blank"></a>
                                        -->
                                                        <?php
                                                        }
                                                        ?>
                                                    <?php
                                                    } else {
                                                        echo "<span class=\"dashicons dashicons-dismiss\" id=\"cancel_$val[project_hash]\" title=\"cancel processing productfeed\" style=\"display: inline-block;\"></span>";
                                                    }
                                                    ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td id="manage_inline" colspan="8">
                                                <div>
                                                    <table class="woo-product-feed-pro-inline_manage">

                                                        <?php
                                                        if ( ( $val['running'] == 'ready' ) || ( $val['running'] == 'stopped' ) || ( $val['running'] == 'not run yet' ) ) {
                                                        ?>
                                                            <tr>
                                                                <td>
                                                                    <strong><?php esc_html_e( 'Change settings', 'woo-product-feed-pro' ); ?></strong><br />
                                                                    <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=woo-product-feed-pro&action=edit_project&step=0&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e( 'General feed settings', 'woo-product-feed-pro' ); ?></a><br />
                                                                    <?php
                                                                    if ( $val['fields'] == 'standard' ) {
                                                                        echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"admin.php?page=woo-product-feed-pro&action=edit_project&step=2&project_hash=$val[project_hash]&channel_hash=$val[channel_hash]\">";
                                                                        esc_html_e( 'Attribute selection', 'woo-product-feed-pro' );
                                                                        print '</a></br/>';
                                                                    } else {
                                                                        echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"admin.php?page=woo-product-feed-pro&action=edit_project&step=7&project_hash=$val[project_hash]&channel_hash=$val[channel_hash]\">";
                                                                        esc_html_e( 'Field mapping', 'woo-product-feed-pro' );
                                                                        print '</a><br/>';
                                                                    }

                                                                    if ( $val['taxonomy'] != 'none' ) {
                                                                        echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"admin.php?page=woo-product-feed-pro&action=edit_project&step=1&project_hash=$val[project_hash]&channel_hash=$val[channel_hash]\">";
                                                                        esc_html_e( 'Category mapping', 'woo-product-feed-pro' );
                                                                        print '</a><br/>';
                                                                    }
                                                                    ?>

                                                                    <?php
                                                                    if ( ( isset( $add_manipulation_support ) ) && ( $add_manipulation_support == 'yes' ) ) {
                                                                    ?>
                                                                        <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=woo-product-feed-pro&action=edit_project&step=9&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e( 'Product data manipulation', 'woo-product-feed-pro' ); ?></a><br />
                                                                    <?php
                                                                    }
                                                                    ?>
                                                                    <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=woo-product-feed-pro&action=edit_project&step=4&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e( 'Feed filters and rules', 'woo-product-feed-pro' ); ?></a><br />
                                                                    <span class="dashicons dashicons-arrow-right" style="display: inline-block;"></span> <a href="admin.php?page=woo-product-feed-pro&action=edit_project&step=5&project_hash=<?php echo "$val[project_hash]"; ?>&channel_hash=<?php echo "$val[channel_hash]"; ?>"><?php esc_html_e( 'Conversion & Google Analytics settings' ); ?></a><br />
                                                                </td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?php esc_html_e( 'Feed URL', 'woo-product-feed-pro' ); ?></strong><br />
                                                                <?php
                                                                if ( ( $val['active'] == 'true' ) && ( $val['running'] != 'not run yet' ) ) {
                                                                    echo "<span class=\"dashicons dashicons-arrow-right\" style=\"display: inline-block;\"></span> <a href=\"$val[external_file]\" target=\"_blank\">$val[external_file]</a>";
                                                                } else {
                                                                    print '<span class="dashicons dashicons-warning"></span> Whoops, there is no active product feed for this project as the project has been disabled or did not run yet.';
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>

                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    </form>
                            <?php
                                    ++$toggle_count;
                                } else {
                                    // Removing this partly configured feed as it results in PHP warnings
                                    unset( $cron_projects[ $key ] );
                                    update_option( 'cron_projects', $cron_projects, 'no' );
                                }
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6"><br />
                                    <span class="dashicons dashicons-warning"></span> <?php esc_html_e( "You haven't configured a product feed yet", 'woo-product-feed-pro' ); ?>,
                                    <a href="admin.php?page=woo-product-feed-pro">
                                    <?php
                                    printf(
                                        // translators: %s: close <a> tag
                                        esc_html__( 'please create one first%s or read our tutorial on', 'woo-product-feed-pro' ),
                                        '</a>',
                                    );
                                    ?>
                                    <a href="https://adtribes.io/setting-up-your-first-google-shopping-product-feed/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=first shopping feed" target="_blank"><?php esc_html_e( 'how to set up your very first Google Shopping product feed', 'woo-product-feed-pro' ); ?></a>.<br /><br />
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </table>
                </div>
                <div class="woo-product-feed-pro-table-right">

                    <table class="woo-product-feed-pro-table">
                        <tr>
                            <td><strong><?php esc_html_e( 'Why upgrade to Elite?', 'woo-product-feed-pro' ); ?></strong></td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Enjoy all priviliges of our Elite features and priority support and upgrade to the Elite version of our plugin now!', 'woo-product-feed-pro' ); ?>
                                <ul>
                                    <li><strong>1.</strong> <?php esc_html_e( 'Priority support: get your feeds live faster', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>2.</strong> <?php esc_html_e( 'More products approved by Google', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>3.</strong> <?php esc_html_e( 'Add GTIN, brand and more fields to your store', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>4.</strong> <?php esc_html_e( 'Exclude individual products from your feeds', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>5.</strong> <?php esc_html_e( 'WPML support', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>6.</strong> <?php esc_html_e( 'Aelia currency switcher support', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>7.</strong> <?php esc_html_e( 'Curcy currency switcher support', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>8.</strong> <?php esc_html_e( 'Facebook pixel feature', 'woo-product-feed-pro' ); ?></li>
                                    <li><strong>9.</strong> <?php esc_html_e( 'Polylang support', 'woo-product-feed-pro' ); ?></li>
                                </ul>
                                <strong>
                                    <a href="https://adtribes.io/pro-vs-elite/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=why-upgrade-box" target="_blank"><?php esc_html_e( 'Upgrade to Elite here!', 'woo-product-feed-pro' ); ?></a>
                                </strong>
                            </td>
                        </tr>
                    </table><br />

                    <table class="woo-product-feed-pro-table">
                        <tr>
                            <td><strong><?php esc_html_e( 'We have got you covered!', 'woo-product-feed-pro' ); ?></strong></td>
                        </tr>
                        <tr>
                            <td>
                                <?php esc_html_e( 'Need assistance? Check out our:', 'woo-product-feed-pro' ); ?>
                                <ul>
                                    <li><strong><a href="https://adtribes.io/support/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=faq" target="_blank"><?php esc_html_e( 'Frequently Asked Questions', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong><a href="https://www.youtube.com/channel/UCXp1NsK-G_w0XzkfHW-NZCw" target="_blank"><?php esc_html_e( 'YouTube tutorials', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong><a href="https://adtribes.io/tutorials/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=tutorials" target="_blank"><?php esc_html_e( 'Tutorials', 'woo-product-feed-pro' ); ?></a></strong></li>
                                </ul>
                                <?php esc_html_e( 'Or just reach out to us at', 'woo-product-feed-pro' ); ?> <strong><a href="https://wordpress.org/support/plugin/woo-product-feed-pro/" target="_blank"><?php esc_html_e( 'our WordPress forum', 'woo-product-feed-pro' ); ?></a></strong> <?php esc_html_e( 'and we will make sure your product feeds will be up-and-running within no-time.', 'woo-product-feed-pro' ); ?>
                            </td>
                        </tr>
                    </table><br />

                    <table class="woo-product-feed-pro-table">
                        <tr>
                            <td><strong><?php esc_html_e( 'Our latest tutorials', 'woo-product-feed-pro' ); ?></strong></td>
                        </tr>
                        <tr>
                            <td>
                                <ul>
                                    <li><strong>1. <a href="https://adtribes.io/setting-up-your-first-google-shopping-product-feed/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=first shopping feed" target="_blank"><?php esc_html_e( 'Create a Google Shopping feed', 'woo-product-feed-pro' ); ?></a></strong></li>

                                    <li><strong>2. <a href="https://adtribes.io/feature-product-data-manipulation/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=product_data_manipulation" target="_blank"><?php esc_html_e( 'Product data manipulation', 'woo-product-feed-pro' ); ?></a></strong></li>

                                    <li><strong>3. <a href="https://adtribes.io/how-to-create-filters-for-your-product-feed/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=how to create filters" target="_blank"><?php esc_html_e( 'How to create filters for your product feed', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>4. <a href="https://adtribes.io/how-to-create-rules/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=how to create rules" target="_blank"><?php esc_html_e( 'How to set rules for your product feed', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>5. <a href="https://adtribes.io/add-gtin-mpn-upc-ean-product-condition-optimised-title-and-brand-attributes/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=adding fields" target="_blank"><?php esc_html_e( 'Adding GTIN, Brand, MPN and more', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>6. <a href="https://adtribes.io/woocommerce-structured-data-bug/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=structured data bug" target="_blank"><?php esc_html_e( 'WooCommerce structured data markup bug', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>7. <a href="https://adtribes.io/wpml-support/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=wpml support" target="_blank"><?php esc_html_e( 'Enable WPML support', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>8. <a href="https://adtribes.io/aelia-currency-switcher-feature/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=aelia support" target="_blank"><?php esc_html_e( 'Enable Aelia currency switcher support', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>9. <a href="https://adtribes.io/help-my-feed-processing-is-stuck/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=feed stuck" target="_blank"><?php esc_html_e( 'Help, my feed is stuck!', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>10. <a href="https://adtribes.io/help-i-have-none-or-less-products-in-my-product-feed-than-expected/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=too few products" target="_blank"><?php esc_html_e( 'Help, my feed has no or too few products!', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>11. <a href="https://adtribes.io/polylang-support-product-feeds/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=polylang support" target="_blank"><?php esc_html_e( 'How to use the Polylang feature', 'woo-product-feed-pro' ); ?></a></strong></li>
                                    <li><strong>12. <a href="https://adtribes.io/curcy-currency-switcher-feature/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=curcy support" target="_blank"><?php esc_html_e( 'Enable Curcy currency switcher support', 'woo-product-feed-pro' ); ?></a></strong></li>
                                </ul>
                            </td>
                        </tr>
                    </table><br />

                </div>
            </div>
        </tbody>
    </div>
</div>
