<?php
//phpcs:disable
use AdTribes\PFP\Helpers\Product_Feed_Helper;

$total_projects      = Product_Feed_Helper::get_total_product_feed();
$domain              = sanitize_text_field( $_SERVER['HTTP_HOST'] );
$plugin_settings     = get_option( 'plugin_settings' );
$directory_perm_xml  = '';
$directory_perm_csv  = '';
$directory_perm_txt  = '';
$directory_perm_tsv  = '';
$directory_perm_logs = '';
$elite_disable       = 'disabled';
$count_variation     = wp_count_posts( 'product_variation' );
$count_single        = wp_count_posts( 'product' );
$published_single    = $count_single->publish;
$published_variation = $count_variation->publish;
$published_products  = $published_single + $published_variation;
$product_numbers     = array(
    'Single products'    => $published_single,
    'Variation products' => $published_variation,
    'Total products'     => $published_products,
);

$versions = array(
    'PHP'                          => (float) phpversion(),
    'Wordpress'                    => get_bloginfo( 'version' ),
    'WooCommerce'                  => WC()->version,
    'WooCommerce Product Feed PRO' => WOOCOMMERCESEA_PLUGIN_VERSION,
);

$order_rows = '';

/**
 * Create notification object and get message and message type as WooCommerce is inactive
 * also set variable allowed on 0 to disable submit button on step 1 of configuration
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications();
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '9', 'false' );
} else {
    $notifications_box = $notifications_obj->get_admin_notifications( '14', 'false' );
}

if ( $versions['PHP'] < 5.6 ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '11', 'false' );
    $php_validation    = 'False';
} else {
    $php_validation = 'True';
}

if ( $versions['WooCommerce'] < 3 ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '13', 'false' );
}

if ( ! wp_next_scheduled( 'woosea_cron_hook' ) ) {
    $notifications_box = $notifications_obj->get_admin_notifications( '12', 'false' );
}

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

// we check if the page is visited by click on the tabs or on the menu button.
// then we get the active tab.
$active_tab = 'woosea_manage_settings';

// create nonce
$nonce = wp_create_nonce( 'woosea_ajax_nonce' );

$header_text = __( 'Plugin settings', 'woo-product-feed-pro' );
if ( isset( $_GET['tab'] ) ) {
    if ( $_GET['tab'] == 'woosea_manage_settings' ) {
        $active_tab  = 'woosea_manage_settings';
        $header_text = __( 'Plugin settings', 'woo-product-feed-pro' );
    } elseif ( $_GET['tab'] == 'woosea_system_check' ) {
        $active_tab  = 'woosea_system_check';
        $header_text = __( 'Plugin systems check', 'woo-product-feed-pro' );
    } else {
        $active_tab  = 'woosea_manage_attributes';
        $header_text = __( 'Attribute settings', 'woo-product-feed-pro' );
    }
}
?>

<div class="wrap">

    <div class="woo-product-feed-pro-form-style-2">

        <tbody class="woo-product-feed-pro-body">
            <div class="woo-product-feed-pro-form-style-2-heading">
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( WOOCOMMERCESEA_PLUGIN_URL . '/images/adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-pro' ); ?>"></a>
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank" class="logo-upgrade">Upgrade to Elite</a>
                <h1 class="title"><?php echo esc_html( $header_text ); ?></h1>
            </div>

            <?php
            if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <strong><?php _e( 'WARNING: Your WP-Cron is disabled', 'woo-product-feed-pro' ); ?></strong><br /></br />
                        We detected that your WP-cron has been disabled in your wp-config.php file. Our plugin heavily depends on the WP-cron being active for it to be able to update and generate your product feeds. More information on the inner workings of our plugin and instructions on how to enable your WP-Cron can be found here: <a href="https://adtribes.io/help-my-feed-processing-is-stuck/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=cron-warning&utm_content=notification" target="_blank"><strong>My feed won't update or is stuck processing</strong></a>.
                    </p>
                </div>
            <?php
            }

            /**
             * Request our plugin users to write a review
             */
            if ( $total_projects > 0 ) {
                $first_activation         = get_option( 'woosea_first_activation' );
                $notification_interaction = get_option( 'woosea_review_interaction' );
                $current_time             = time();
                $show_after               = 604800; // Show only after one week
                $is_active                = $current_time - $first_activation;
                $page                     = sanitize_text_field( basename( $_SERVER['REQUEST_URI'] ) );

                if ( ( $is_active > $show_after ) && ( $notification_interaction != 'yes' ) ) {
                    echo '<div class="notice notice-info review-notification">';
                    echo '<table><tr><td></td><td><font color="green" style="font-weight:normal";><p>Hey, I noticed you have been using our plugin, <u>Product Feed PRO for WooCommerce by AdTribes.io</u>, for over a week now and have created product feed projects with it - that\'s awesome! Could you please do our support volunteers and me a BIG favor and give it a <strong>5-star rating</strong> on WordPress? Just to help us spread the word and boost our motivation. We would greatly appreciate if you would do so :)<br/>~ Adtribes.io support team<br><ul><li><span class="ui-icon ui-icon-caret-1-e" style="display: inline-block;"></span><a href="https://wordpress.org/support/plugin/woo-product-feed-pro/reviews?rate=5#new-post" target="_blank" class="dismiss-review-notification">Ok, you deserve it</a></li><li><span class="ui-icon ui-icon-caret-1-e" style="display: inline-block;"></span><a href="#" class="dismiss-review-notification">Nope, maybe later</a></li><li><span class="ui-icon ui-icon-caret-1-e" style="display: inline-block;"></span><a href="#" class="dismiss-review-notification">I already did</a></li></ul></p></font></td></tr></table>';
                    echo '</div>';
                }
            }
            ?>

            <!-- WordPress provides the styling for tabs. -->
            <h2 class="nav-tab-wrapper">
                <!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
                <a href="?page=woosea_manage_settings&tab=woosea_manage_settings" class="nav-tab 
                        <?php
                        if ( $active_tab == 'woosea_manage_settings' ) {
                            echo 'nav-tab-active';
                        }
                        ?>
"><?php _e( 'Plugin settings', 'woo-product-feed-pro' ); ?></a>
                <a href="?page=woosea_manage_settings&tab=woosea_system_check" class="nav-tab 
                        <?php
                        if ( $active_tab == 'woosea_system_check' ) {
                            echo 'nav-tab-active';
                        }
                        ?>
"><?php _e( 'Plugin systems check', 'woo-product-feed-pro' ); ?></a>
            </h2>

            <div class="woo-product-feed-pro-table-wrapper">
                <div class="woo-product-feed-pro-table-left">
                    <?php
                    if ( $active_tab == 'woosea_manage_settings' ) {
                    ?>

                        <table class="woo-product-feed-pro-table">
                            <tr>
                                <td><strong><?php _e( 'Plugin setting', 'woo-product-feed-pro' ); ?></strong></td>
                                <td><strong><?php _e( 'Off / On', 'woo-product-feed-pro' ); ?></strong></td>
                            </tr>

                            <form action="" method="post">
                                <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>

                                <tr>
                                    <td>
                                        <span><?php _e( 'Use parent variable product image for variations', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_mother_image = get_option( 'add_mother_image' );
                                            if ( $add_mother_image == 'yes' ) {
                                                print '<input type="checkbox" id="add_mother_image" name="add_mother_image" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="add_mother_image" name="add_mother_image" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><?php _e( 'Add shipping costs for all countries to your feed (Google Shopping / Facebook only)', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_all_shipping = get_option( 'add_all_shipping' );
                                            if ( $add_all_shipping == 'yes' ) {
                                                print '<input type="checkbox" id="add_all_shipping" name="add_all_shipping" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="add_all_shipping" name="add_all_shipping" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><?php _e( 'Remove all other shipping classes when free shipping criteria are met (Google Shopping / Facebook only)', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $free_shipping = get_option( 'free_shipping' );
                                            if ( $free_shipping == 'yes' ) {
                                                print '<input type="checkbox" id="free_shipping" name="free_shipping" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="free_shipping" name="free_shipping" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><?php _e( 'Remove the free shipping zone from your feed (Google Shopping / Facebook only)', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $remove_free_shipping = get_option( 'remove_free_shipping' );
                                            if ( $remove_free_shipping == 'yes' ) {
                                                print '<input type="checkbox" id="remove_free_shipping" name="remove_free_shipping" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="remove_free_shipping" name="remove_free_shipping" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><?php _e( 'Remove the local pickup shipping zone from your feed (Google Shopping / Facebook only)', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $local_pickup_shipping = get_option( 'local_pickup_shipping' );
                                            if ( $local_pickup_shipping == 'yes' ) {
                                                print '<input type="checkbox" id="local_pickup_shipping" name="local_pickup_shipping" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="local_pickup_shipping" name="local_pickup_shipping" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><?php _e( 'Show only basis attributes in field mapping and filter/rule drop-downs', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_woosea_basic = get_option( 'add_woosea_basic' );
                                            if ( $add_woosea_basic == 'yes' ) {
                                                print '<input type="checkbox" id="add_woosea_basic" name="add_woosea_basic" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="add_woosea_basic" name="add_woosea_basic" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span><?php _e( 'Enable logging', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_woosea_logging = get_option( 'add_woosea_logging' );
                                            if ( $add_woosea_logging == 'yes' ) {
                                                print '<input type="checkbox" id="add_woosea_logging" name="add_woosea_logging" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="add_woosea_logging" name="add_woosea_logging" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <tr id="facebook_pixel">
                                    <td>
                                        <span><?php _e( 'Add Facebook Pixel', 'woo-product-feed-pro' ); ?> (<a href="https://adtribes.io/facebook-pixel-feature/" target="_blank"><?php _e( 'Read more about this', 'woo-product-feed-pro' ); ?>)</a></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_facebook_pixel = get_option( 'add_facebook_pixel' );
                                            if ( $add_facebook_pixel == 'yes' ) {
                                                echo "<input type=\"checkbox\" id=\"add_facebook_pixel\" name=\"add_facebook_pixel\" class=\"checkbox-field\" value=\"$nonce\" checked>";
                                            } else {
                                                echo "<input type=\"checkbox\" id=\"add_facebook_pixel\" name=\"add_facebook_pixel\" class=\"checkbox-field\" value=\"$nonce\">";
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <?php
                                if ( $add_facebook_pixel == 'yes' ) {
                                    $facebook_pixel_id = get_option( 'woosea_facebook_pixel_id' );
                                    echo "<tr id=\"facebook_pixel_id\"><td colspan=\"2\"><span>Insert your Facebook Pixel ID</span>&nbsp;<input type=\"hidden\" name=\"nonce_facebook_pixel_id\" id=\"nonce_facebook_pixel_id\" value=\"$nonce\"><input type=\"text\" class=\"input-field-medium\" id=\"fb_pixel_id\" name=\"fb_pixel_id\" value=\"$facebook_pixel_id\">&nbsp;<input type=\"button\" id=\"save_facebook_pixel_id\" value=\"Save\"></td></tr>";
                                }
                                ?>

                                <?php
                                $content_ids = 'variation';
                                $content_ids = get_option( 'add_facebook_pixel_content_ids' );
                                ?>

                                <tr id="content_ids">
                                    <td colspan="2">
                                        <span><?php _e( 'Content IDS variable products Facebook Pixel', 'woo-product-feed-pro' ); ?></span>
                                        <select id="woosea_content_ids" name="woosea_content_ids" class="select-field">
                                            <?php
                                            if ( $content_ids == 'variation' ) {
                                                echo "<option value=\"variation\" selected>Variation product ID's</option>";
                                                print '<option value="variable">Variable product ID</option>';
                                            } else {
                                                echo "<option value=\"variation\" selected>Variation product ID's</option>";
                                                print '<option value="variable" selected>Variable product ID</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr id="remarketing">
                                    <td>
                                        <span><?php _e( 'Add Google Dynamic Remarketing Pixel:', 'woo-product-feed-pro' ); ?></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_remarketing = get_option( 'add_remarketing' );
                                            if ( $add_remarketing == 'yes' ) {
                                                echo "<input type=\"checkbox\" id=\"add_remarketing\" name=\"add_remarketing\" class=\"checkbox-field\" value=\"$nonce\" checked>";
                                            } else {
                                                echo "<input type=\"checkbox\" id=\"add_remarketing\" name=\"add_remarketing\" class=\"checkbox-field\" value=\"$nonce\">";
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <?php
                                if ( $add_remarketing == 'yes' ) {
                                    $adwords_conversion_id = get_option( 'woosea_adwords_conversion_id' );

                                    echo "<tr id=\"adwords_conversion_id\"><td colspan=\"2\"><span>Insert your Dynamic Remarketing Conversion tracking ID:</span>&nbsp;<input type=\"hidden\" name=\"nonce_adwords_conversion_id\" id=\"nonce_adwords_conversion_id\" value=\"$nonce\"><input type=\"text\" class=\"input-field-medium\" id=\"adwords_conv_id\" name=\"adwords_conv_id\" value=\"$adwords_conversion_id\">&nbsp;<input type=\"button\" id=\"save_conversion_id\" value=\"Save\"></td></tr>";
                                }
                                ?>

                                <tr id="batch">
                                    <td>
                                        <span><?php _e( 'Change products per batch number', 'woo-product-feed-pro' ); ?> (<a href="https://adtribes.io/batch-size-configuration-product-feed/?utm_source=pfp&utm_medium=manage-settings&utm_content=batch size" target="_blank"><?php _e( 'Read more about this', 'woo-product-feed-pro' ); ?>)</a></span>
                                    </td>
                                    <td>
                                        <label class="woo-product-feed-pro-switch">
                                            <?php
                                            $add_batch = get_option( 'add_batch' );
                                            if ( $add_batch == 'yes' ) {
                                                print '<input type="checkbox" id="add_batch" name="add_batch" class="checkbox-field" checked>';
                                            } else {
                                                print '<input type="checkbox" id="add_batch" name="add_batch" class="checkbox-field">';
                                            }
                                            ?>
                                            <div class="woo-product-feed-pro-slider round"></div>
                                        </label>
                                    </td>
                                </tr>
                                <?php
                                if ( $add_batch == 'yes' ) {
                                    $woosea_batch_size = get_option( 'woosea_batch_size' );
                                    echo "<tr id=\"woosea_batch_size\"><td colspan=\"2\"><span>Insert batch size:</span>&nbsp;<input type=\"hidden\" name=\"nonce_batch\" id=\"nonce_batch\" value=\"$nonce\"><input type=\"text\" class=\"input-field-medium\" id=\"batch_size\" name=\"batch_size\" value=\"$woosea_batch_size\">&nbsp;<input type=\"button\" id=\"save_batch_size\" value=\"Save\"></td></tr>";
                                }
                                ?>
                                <tr> 
                                    <td colspan="2">
                                        <input type="button" id="adt_migrate_to_custom_post_type" value="<?php esc_attr_e( 'Migrate to Custom Post Type', 'woo-product-feed-pro' ); ?>" >
                                    </td>
                                </tr>
                            </form>
                        </table>
                    <?php
                    } elseif ( $active_tab == 'woosea_system_check' ) {
                        // Check if the product feed directory is writeable
                        $upload_dir         = wp_upload_dir();
                        $external_base      = $upload_dir['basedir'];
                        $external_path      = $external_base . '/woo-product-feed-pro/';
                        $external_path_xml  = $external_base . '/woo-product-feed-pro/';
                        $external_path_csv  = $external_base . '/woo-product-feed-pro/';
                        $external_path_txt  = $external_base . '/woo-product-feed-pro/';
                        $external_path_tsv  = $external_base . '/woo-product-feed-pro/';
                        $external_path_logs = $external_base . '/woo-product-feed-pro/';
                        $test_file          = $external_path . '/tesfile.txt';
                        $test_file_xml      = $external_path . 'xml/tesfile.txt';
                        $test_file_csv      = $external_path . 'csv/tesfile.txt';
                        $test_file_txt      = $external_path . 'txt/tesfile.txt';
                        $test_file_tsv      = $external_path . 'tsv/tesfile.txt';
                        $test_file_logs     = $external_path . 'logs/tesfile.txt';

                        if ( is_writable( $external_path ) ) {
                            // Normal root category
                            $fp = @fopen( $test_file, 'w' );
                            @fwrite( $fp, 'Cats chase mice' );
                            @fclose( $fp );
                            if ( is_file( $test_file ) ) {
                                $directory_perm = 'True';
                            }

                            // XML subcategory
                            $fp = @fopen( $test_file_xml, 'w' );
                            if ( ! is_bool( $fp ) ) {
                                @fwrite( $fp, 'Cats chase mice' );
                                @fclose( $fp );
                                if ( is_file( $test_file_xml ) ) {
                                    $directory_perm_xml = 'True';
                                } else {
                                    $directory_perm_xml = 'False';
                                }
                            } else {
                                $directory_perm_xml = 'Unknown';
                            }

                            // CSV subcategory
                            $fp = @fopen( $test_file_csv, 'w' );
                            if ( ! is_bool( $fp ) ) {
                                @fwrite( $fp, 'Cats chase mice' );
                                @fclose( $fp );
                                if ( is_file( $test_file_csv ) ) {
                                    $directory_perm_csv = 'True';
                                } else {
                                    $directory_perm_csv = 'False';
                                }
                            } else {
                                $directory_perm_csv = 'Unknown';
                            }

                            // TXT subcategory
                            $fp = @fopen( $test_file_txt, 'w' );
                            if ( ! is_bool( $fp ) ) {
                                @fwrite( $fp, 'Cats chase mice' );
                                @fclose( $fp );
                                if ( is_file( $test_file_txt ) ) {
                                    $directory_perm_txt = 'True';
                                } else {
                                    $directory_perm_txt = 'False';
                                }
                            } else {
                                $directory_perm_txt = 'Unknown';
                            }
                            // TSV subcategory
                            $fp = @fopen( $test_file_tsv, 'w' );
                            if ( ! is_bool( $fp ) ) {
                                @fwrite( $fp, 'Cats chase mice' );
                                @fclose( $fp );
                                if ( is_file( $test_file_tsv ) ) {
                                    $directory_perm_tsv = 'True';
                                } else {
                                    $directory_perm_tsv = 'False';
                                }
                            } else {
                                $directory_perm_tsv = 'Uknown';
                            }

                            // Logs subcategory
                            $fp = @fopen( $test_file_logs, 'w' );
                            if ( ! is_bool( $fp ) ) {
                                @fwrite( $fp, 'Cats chase mice' );
                                @fclose( $fp );
                                if ( is_file( $test_file_logs ) ) {
                                    $directory_perm_logs = 'True';
                                } else {
                                    $directory_perm_logs = 'False';
                                }
                            } else {
                                $directory_perm_logs = 'Unknown';
                            }
                        } else {
                            $directory_perm = 'False';
                        }

                        // Check if the cron is enabled
                        if ( ! wp_next_scheduled( 'woosea_cron_hook' ) ) {
                            $cron_enabled = 'False';
                        } else {
                            $cron_enabled = 'True';
                        }

                        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
                            $cron_enabled = '<strong>False</strong>';
                        }

                        print '<table class="woo-product-feed-pro-table">';
                        print '<tr><td><strong>System check</strong></td><td><strong>Status</strong></td></tr>';
                        echo "<tr><td>WP-Cron enabled</td><td>$cron_enabled</td></tr>";
                        echo "<tr><td>PHP-version</td><td>$php_validation ($versions[PHP])</td></tr>";
                        echo "<tr><td>Product feed directory writable</td><td>$directory_perm</td></tr>";
                        echo "<tr><td>Product feed XML directory writable</td><td>$directory_perm_xml</td></tr>";
                        echo "<tr><td>Product feed CSV directory writable</td><td>$directory_perm_csv</td></tr>";
                        echo "<tr><td>Product feed TXT directory writable</td><td>$directory_perm_txt</td></tr>";
                        echo "<tr><td>Product feed TSV directory writable</td><td>$directory_perm_tsv</td></tr>";
                        echo "<tr><td>Product feed LOGS directory writable</td><td>$directory_perm_logs</td></tr>";
                        print '<tr><td colspan="2">&nbsp;</td></tr>';
                        print '</table>';

                        // Display the debugging information.
                        $debug_info_content = $notifications_obj->woosea_debug_informations( $versions, $product_numbers, $order_rows );
                        $debug_info_title   = __( 'System Report', 'woo-product-feed-pro' );

                        print '<div class="woo-product-feed-pro-debug-info">';
                        print '<button class="button copy-product-feed-pro-debug-info" type="button" data-clipboard-target="#woo-product-feed-pro-debug-info">Copy to clipboard</button>';
                        echo "<h3>{$debug_info_title}</h3>";
                        print '<p>' . __( 'Copy the below text and paste to the support team when requested to help us debug any systems issues with your feeds.', 'woo-product-feed-pro' ) . '</p>';
                        echo "<pre id=\"woo-product-feed-pro-debug-info\">{$debug_info_content}</pre>";
                        print '</div>';
                    } else {
                    ?>
                        <table class="woo-product-feed-pro-table">
                            <?php
                            if ( ! get_option( 'woosea_extra_attributes' ) ) {
                                $extra_attributes = array();
                            } else {
                                $extra_attributes = get_option( 'woosea_extra_attributes' );
                            }
                            print '<tr><td><strong>Attribute name</strong></td><td><strong>On / Off</strong></td></tr>';

                            $list = array(
                                'custom_attributes__woosea_brand'           => 'woosea brand',
                                'custom_attributes__woosea_gtin'            => 'woosea gtin',
                                'custom_attributes__woosea_ean'             => 'woosea ean',
                                'custom_attributes__woosea_mpn'             => 'woosea mpn',
                                'custom_attributes__woosea_optimized_title'         => 'woosea optimized title',
                                'custom_attributes__woosea_age_group'           => 'woosea age group',
                                'custom_attributes__woosea_color'           => 'woosea color',
                                'custom_attributes__woosea_condition'           => 'woosea condition',
                                'custom_attributes__woosea_cost_of_good_sold'       => 'woosea cost of good sold',
                                'custom_attributes__woosea_custom_field_0'      => 'woosea custom field 0',
                                'custom_attributes__woosea_custom_field_1'      => 'woosea custom field 1',
                                'custom_attributes__woosea_custom_field_2'      => 'woosea custom field 2',
                                'custom_attributes__woosea_custom_field_3'      => 'woosea custom field 3',
                                'custom_attributes__woosea_custom_field_4'      => 'woosea custom field 4',
                                'custom_attributes__woosea_energy_efficiency_class'     => 'woosea energy efficiency class',
                                'custom_attributes__woosea_exclude_product'         => 'woosea exclude product',
                                'custom_attributes__woosea_gender'          => 'woosea gender',
                                'custom_attributes__woosea_installment_amount'      => 'woosea installment amount',
                                'custom_attributes__woosea_installment_months'      => 'woosea installment months',
                                'custom_attributes__woosea_is_bundle'           => 'woosea is bundle',
                                'custom_attributes__woosea_is_promotion'        => 'woosea is promotion',
                                'custom_attributes__woosea_material'            => 'woosea material',
                                'custom_attributes__woosea_max_energy_efficiency_class' => 'woosea max energy efficiency class',
                                'custom_attributes__woosea_min_energy_efficiency_class' => 'woosea min energy efficiency class',
                                'custom_attributes__woosea_multipack'           => 'woosea multipack',
                                'custom_attributes__woosea_pattern'             => 'woosea pattern',
                                'custom_attributes__woosea_size'            => 'woosea size',
                                'custom_attributes__woosea_unit_pricing_base_measure'   => 'woosea unit pricing base measure',
                                'custom_attributes__woosea_unit_pricing_measure'    => 'woosea unit pricing measure',
                                'custom_attributes__woosea_upc'             => 'woosea upc',
                            );

                            foreach ( $list as $key => $value ) {
                                // Trim spaces before and after
                                $value = trim( $value );

                                if ( in_array( $value, $extra_attributes, true ) ) {
                                    $checked = 'checked';
                                } else {
                                    $checked = '';
                                }

                                if ( strpos( $key, 'woosea' ) ) {
                                    $value_display = str_replace( 'woosea', '', $value );

                                    echo "<tr id=\"$key\"><td><span>$value_display</span></td>";
                                    print '<td>';
                            ?>
                                    <label class="woo-product-feed-pro-switch">
                                        <input type="hidden" name="manage_attribute" value="<?php echo "$key"; ?>"><input type="checkbox" id="attribute_active" name="<?php echo "$value"; ?>" class="checkbox-field" value="<?php echo "$key"; ?>" <?php echo "$checked"; ?>>
                                        <div class="woo-product-feed-pro-slider round"></div>
                                    </label>
                            <?php
                                    print '</td>';
                                    print '</tr>';
                                }
                            }
                            ?>
                        </table>
                    <?php
                    }
                    ?>
                </div>
                <?php require_once WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'view-sidebar.php'; ?>
            </div>
        </tbody>
    </div>
</div>
