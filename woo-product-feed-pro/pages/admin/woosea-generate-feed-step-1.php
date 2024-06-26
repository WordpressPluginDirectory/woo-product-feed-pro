<?php
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

delete_option( 'woosea_cat_mapping' );

/**
 * Create notification object
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications();
$notifications_box = $notifications_obj->get_admin_notifications( '1', 'false' );

/**
 * Update or get project configuration
 */
$nonce = wp_create_nonce( 'woosea_ajax_nonce' );

/**
 * Update project configuration
 */
if ( array_key_exists( 'project_hash', $_GET ) ) {
    $project        = WooSEA_Update_Project::get_project_data( sanitize_text_field( $_GET['project_hash'] ) );
    $channel_data   = WooSEA_Update_Project::get_channel_data( sanitize_text_field( $_GET['channel_hash'] ) );
    $manage_project = 'yes';

    if ( isset( $project['WPML'] ) ) {
        if ( ( is_plugin_active( 'sitepress-multilingual-cms' ) ) || ( function_exists( 'icl_object_id' ) ) ) {
            if ( ! class_exists( 'Polylang' ) ) {
                // Get WPML language
                global $sitepress;
                $lang = $project['WPML'];
                $sitepress->switch_lang( $lang );
            }
        }
    }
} else {
    // Sanitize values in multi-dimensional POST array
    if ( is_array( $_POST ) ) {
        foreach ( $_POST as $p_key => $p_value ) {
            if ( is_array( $p_value ) ) {
                foreach ( $p_value as $pp_key => $pp_value ) {
                    if ( is_array( $pp_value ) ) {
                        foreach ( $pp_value as $ppp_key => $ppp_value ) {
                            $_POST[ $p_key ][ $pp_key ][ $ppp_key ] = sanitize_text_field( $ppp_value );
                        }
                    }
                }
            } else {
                $_POST[ $p_key ] = sanitize_text_field( $p_value );
            }
        }
    } else {
        $_POST = array();
    }
    $project      = WooSEA_Update_Project::update_project( $_POST );
    $channel_data = WooSEA_Update_Project::get_channel_data( sanitize_text_field( $_POST['channel_hash'] ) );

    if ( isset( $project['WPML'] ) ) {
        if ( function_exists( 'icl_object_id' ) ) {
            if ( ! class_exists( 'Polylang' ) ) {
                // Get WPML language
                global $sitepress;
                $lang = $project['WPML'];
                $sitepress->switch_lang( $lang );
            }
        }
    }
}

function woosea_hierarchical_term_tree( $category, $prev_mapped ) {
    $r = '';

    $args = array(
        'parent'        => $category,
        'hide_empty'    => false,
        'no_found_rows' => true,
    );

    $next          = get_terms( 'product_cat', $args );
    $nr_categories = count( $next );
    $yo            = 0;

    if ( $next ) {
        foreach ( $next as $sub_category ) {
            ++$yo;
            $x               = $sub_category->term_id;
            $woo_category    = $sub_category->name;
            $woo_category_id = $sub_category->term_id;

            $mapped_category     = '';
            $mapped_active_class = 'input-field-large';
            $woo_category        = preg_replace( '/&amp;/', '&', $woo_category );
            $woo_category        = preg_replace( '/"/', '&quot;', $woo_category );

            // Check if mapping is in place
            if ( ( array_key_exists( $x, $prev_mapped ) ) || ( array_key_exists( $woo_category, $prev_mapped ) ) ) {
                if ( array_key_exists( $x, $prev_mapped ) ) {
                    $mapped_category = $prev_mapped[ $x ];
                } elseif ( array_key_exists( $woo_category, $prev_mapped ) ) {
                    $mapped_category = $prev_mapped[ $x ];
                } else {
                    $mapped_category = $woo_category;
                }
                $mapped_active_class = 'input-field-large-active';
            }

            // These are main categories
            if ( $sub_category->parent == 0 ) {
                $args = array(
                    'parent'        => $sub_category->term_id,
                    'hide_empty'    => false,
                    'no_found_rows' => true,
                );

                $subcat     = get_terms( 'product_cat', $args );
                $nr_subcats = count( $subcat );

                $r .= '<tr class="catmapping">';
                $r .= "<td><input type=\"hidden\" name=\"mappings[$x][rowCount]\" value=\"$x\"><input type=\"hidden\" name=\"mappings[$x][categoryId]\" value=\"$woo_category_id\"><input type=\"hidden\" name=\"mappings[$x][criteria]\" class=\"input-field-large\" id=\"$woo_category_id\" value=\"$woo_category\">$woo_category ($sub_category->count)</td>";
                $r .= "<td><div id=\"the-basics-$x\"><input type=\"text\" name=\"mappings[$x][map_to_category]\" class=\"$mapped_active_class js-typeahead js-autosuggest autocomplete_$x\" value=\"$mapped_category\"></div></td>";
                if ( ( $yo == $nr_categories ) && ( $nr_subcats == 0 ) ) {
                    $r .= "<td><span class=\"copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
                } elseif ( $nr_subcats > 0 ) {
                    $r .= "<td><span class=\"dashicons dashicons-arrow-down copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to subcategories\"></span><span class=\"dashicons dashicons-arrow-down-alt copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
                } else {
                    $r .= "<td><span class=\"dashicons dashicons-arrow-down-alt copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
                }
                $r .= '</tr>';
            } else {
                $r .= '<tr class="catmapping">';
                $r .= "<td><input type=\"hidden\" name=\"mappings[$x][rowCount]\" value=\"$x\"><input type=\"hidden\" name=\"mappings[$x][categoryId]\" value=\"$woo_category_id\"><input type=\"hidden\" name=\"mappings[$x][criteria]\" class=\"input-field-large\" id=\"$woo_category_id\" value=\"$woo_category\">-- $woo_category ($sub_category->count)</td>";
                $r .= "<td><div id=\"the-basics-$x\"><input type=\"text\" name=\"mappings[$x][map_to_category]\" class=\"$mapped_active_class js-typeahead js-autosuggest autocomplete_$x mother_$sub_category->parent\" value=\"$mapped_category\"></div></td>";
                $r .= "<td><span class=\"copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
                $r .= '</tr>';
            }
            $r .= $sub_category->term_id !== 0 ? woosea_hierarchical_term_tree( $sub_category->term_id, $prev_mapped ) : null;
        }
    }

    $allowed_tags = array(
        'tr'    => array(
            'class' => array(),
        ),
        'td'    => array(),
        'input' => array(
            'type'  => array(),
            'name'  => array(),
            'value' => array(),
            'class' => array(),
            'id'    => array(),
        ),
        'span'  => array(
            'class' => array(),
            'style' => array(),
            'title' => array(),
        ),
        'div'   => array(
            'id' => array(),
        ),
        '>'     => array(),
        '&'     => array(),
    );
    return wp_kses_normalize_entities( $r, $allowed_tags );
}
?>

<div class="wrap">
    <div class="woo-product-feed-pro-form-style-2">
        <div class="woo-product-feed-pro-form-style-2-heading">
            <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( WOOCOMMERCESEA_PLUGIN_URL . '/images/adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-pro' ); ?>"></a> 
            <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank" class="logo-upgrade">Upgrade to Elite</a>
            <h1 class="title"><?php esc_html_e( 'Category mapping', 'woo-product-feed-pro' ); ?></h1>
        </div>

        <div class="<?php echo esc_attr( $notifications_box['message_type'] ); ?>">
            <p><?php echo wp_kses_post( $notifications_box['message'] ); ?></p>
        </div>

        <div class="woo-product-feed-pro-table-wrapper">
            <div class="woo-product-feed-pro-table-left">

                <table id="woosea-ajax-mapping-table" class="woo-product-feed-pro-table" border="1">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Your category', 'woo-product-feed-pro' ); ?> <i>(<?php esc_html_e( 'Number of products', 'woo-product-feed-pro' ); ?>)</i></th>
                            <th><?php echo "$channel_data[name]"; ?> <?php esc_html_e( 'category', 'woo-product-feed-pro' ); ?></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody class="woo-product-feed-pro-body">
                        <?php
                        // Get already mapped categories
                        $prev_mapped = array();
                        if ( isset( $project['mappings'] ) ) {
                            foreach ( $project['mappings'] as $map_key => $map_value ) {
                                if ( strlen( $map_value['map_to_category'] ) > 0 ) {
                                    $map_value['criteria']                   = str_replace( '\\', '', $map_value['criteria'] );
                                    $prev_mapped[ $map_value['categoryId'] ] = $map_value['map_to_category'];
                                    // $prev_mapped[$map_value['criteria']] = $map_value['map_to_category'];
                                }
                            }
                        }

                        // Display mapping form
                        echo woosea_hierarchical_term_tree( 0, $prev_mapped );
                        ?>
                    </tbody>

                    <form action="" method="post">
                        <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>

                        <tr>
                            <td colspan="3">
                                <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo "$project[channel_hash]"; ?>">
                                <?php
                                if ( isset( $manage_project ) ) {
                                ?>
                                    <input type="hidden" name="project_update" id="project_update" value="yes" />
                                    <input type="hidden" id="project_hash" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                    <input type="hidden" name="step" value="100">
                                    <input type="submit" value="Save mappings" />
                                <?php
                                } else {
                                ?>
                                    <input type="hidden" id="project_hash" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                    <input type="hidden" name="step" value="4">
                                    <input type="submit" value="Save mappings" />
                                <?php
                                }
                                ?>
                            </td>
                        </tr>

                    </form>

                </table>
            </div>

            <div class="woo-product-feed-pro-table-right">
                <table class="woo-product-feed-pro-table">
                    <tr>
                        <td><strong><?php esc_html_e( 'Why upgrade to Elite?', 'woo-product-feed-pro' ); ?></strong></td>
                    </tr>
                    <tr>
                        <td>
                            <?php esc_html_e( 'Enjoy all privileges of our Elite features and priority support and upgrade to the Elite version of our plugin now!', 'woo-product-feed-pro' ); ?>
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
                                <a href="https://adtribes.io/pro-vs-elite/?utm_source=pfp&utm_medium=page1&utm_campaign=why-upgrade-box" target="_blank"><?php esc_html_e( 'Upgrade to Elite here!', 'woo-product-feed-pro' ); ?></a>
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
                                <li><strong><a href="https://adtribes.io/support/?utm_source=pfp&utm_medium=page1&utm_campaign=faq" target="_blank"><?php esc_html_e( 'Frequently Asked Questions', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong><a href="https://www.youtube.com/channel/UCXp1NsK-G_w0XzkfHW-NZCw" target="_blank"><?php esc_html_e( 'YouTube tutorials', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong><a href="https://adtribes.io/tutorials/?utm_source=pfp&utm_medium=page1&utm_campaign=tutorials" target="_blank"><?php esc_html_e( 'Tutorials', 'woo-product-feed-pro' ); ?></a></strong></li>
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
                                <li><strong>1. <a href="https://adtribes.io/setting-up-your-first-google-shopping-product-feed/?utm_source=pfp&utm_medium=page1&utm_campaign=first shopping feed" target="_blank"><?php esc_html_e( 'Create a Google Shopping feed', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>2. <a href="https://adtribes.io/feature-product-data-manipulation/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=product_data_manipulation" target="_blank"><?php esc_html_e( 'Product data manipulation', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>3. <a href="https://adtribes.io/how-to-create-filters-for-your-product-feed/?utm_source=pfp&utm_medium=page1&utm_campaign=how to create filters" target="_blank"><?php esc_html_e( 'How to create filters for your product feed', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>4. <a href="https://adtribes.io/how-to-create-rules/?utm_source=pfp&utm_medium=page1&utm_campaign=how to create rules" target="_blank"><?php esc_html_e( 'How to set rules for your product feed', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>5. <a href="https://adtribes.io/add-gtin-mpn-upc-ean-product-condition-optimised-title-and-brand-attributes/?utm_source=pfp&utm_medium=page1&utm_campaign=adding fields" target="_blank"><?php esc_html_e( 'Adding GTIN, Brand, MPN and more', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>6. <a href="https://adtribes.io/woocommerce-structured-data-bug/?utm_source=pfp&utm_medium=page1&utm_campaign=structured data bug" target="_blank"><?php esc_html_e( 'WooCommerce structured data markup bug', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>7. <a href="https://adtribes.io/wpml-support/?utm_source=pfp&utm_medium=page1&utm_campaign=wpml support" target="_blank"><?php esc_html_e( 'Enable WPML support', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>8. <a href="https://adtribes.io/aelia-currency-switcher-feature/?utm_source=pfp&utm_medium=page1&utm_campaign=aelia support" target="_blank"><?php esc_html_e( 'Enable Aelia currency switcher support', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>9. <a href="https://adtribes.io/help-my-feed-processing-is-stuck/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=feed stuck" target="_blank"><?php esc_html_e( 'Help, my feed is stuck!', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>10. <a href="https://adtribes.io/help-i-have-none-or-less-products-in-my-product-feed-than-expected/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=too few products" target="_blank"><?php esc_html_e( 'Help, my feed has no or too few products!', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>11. <a href="https://adtribes.io/polylang-support-product-feeds/?utm_source=pfp&utm_medium=manage-feed&utm_campaign=polylang support" target="_blank"><?php esc_html_e( 'How to use the Polylang feature', 'woo-product-feed-pro' ); ?></a></strong></li>
                                <li><strong>12. <a href="https://adtribes.io/curcy-currency-switcher-feature/?utm_source=pfp&utm_medium=page1&utm_campaign=curcy support" target="_blank"><?php esc_html_e( 'Enable Curcy currency switcher support', 'woo-product-feed-pro' ); ?></a></strong></li>
                            </ul>
                        </td>
                    </tr>
                </table><br />
            </div>
        </div>
    </div>
</div>
