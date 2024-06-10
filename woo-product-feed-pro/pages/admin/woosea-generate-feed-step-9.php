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

$add_manipulation_support = get_option( 'add_manipulation_support' );

/**
 * Create notification object
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications();
$notifications_box = $notifications_obj->get_admin_notifications( '15', 'false' );

/**
 * Create product attribute object
 */
$attributes_obj = new WooSEA_Attributes();
$attributes     = $attributes_obj->get_product_attributes();

/**
 * Update or get project configuration
 */
$nonce = wp_create_nonce( 'woosea_ajax_nonce' );

/**
 * Update or get project configuration
 */
if ( array_key_exists( 'project_hash', $_GET ) ) {
        $project      = WooSEA_Update_Project::get_project_data( sanitize_text_field( $_GET['project_hash'] ) );
        $channel_data = WooSEA_Update_Project::get_channel_data( sanitize_text_field( $_GET['channel_hash'] ) );
        $count_rules  = 0;
    if ( isset( $project['field_manipulation'] ) ) {
        $count_rules = count( $project['field_manipulation'] );
    }
    $manage_project = 'yes';
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
    $project          = WooSEA_Update_Project::update_project( sanitize_text_field( $_POST ) );
        $channel_data = WooSEA_Update_Project::get_channel_data( sanitize_text_field( $_POST['channel_hash'] ) );
    $count_rules      = 0;
}
?>
    <div class="wrap">
        <div class="woo-product-feed-pro-form-style-2">
            <div class="woo-product-feed-pro-form-style-2-heading">
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( WOOCOMMERCESEA_PLUGIN_URL . '/images/adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-pro' ); ?>"></a> 
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank" class="logo-upgrade">Upgrade to Elite</a>
                <h1 class="title"><?php esc_html_e( 'Product data manipulation', 'woo-product-feed-pro' ); ?></h1>
            </div>

            <div class="<?php echo esc_attr( $notifications_box['message_type'] ); ?>">
                    <p><?php echo wp_kses_post( $notifications_box['message'] ); ?></p>
            </div>

            <form id="fieldmanipulation" method="post">
            <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>
            <table class="woo-product-feed-pro-table" id="woosea-ajax-table" border="1">
                <thead>
                            <tr>
                                <th></th>
                        <th><?php esc_html_e( 'Product type', 'woo-product-feed-pro' ); ?></th>
                        <th><?php esc_html_e( 'Field', 'woo-product-feed-pro' ); ?></th>
                        <th><?php esc_html_e( 'Becomes', 'woo-product-feed-pro' ); ?></th>
                        <th></th>
                            </tr>
                    </thead>
                
                <tbody class="woo-product-feed-pro-body">
                <?php
                if ( isset( $project['field_manipulation'] ) ) {

                    $product_types = array(
                        'all'      => 'Simple and variable',
                        'simple'   => 'Simple',
                        'variable' => 'Variable',
                    );

                                    foreach ( $project['field_manipulation'] as $manipulation_key => $manipulation_array ) {
                    ?>
                        <tr class="rowCount">
                                                    <td valign="top">
                                <input type="hidden" name="field_manipulation[<?php echo "$manipulation_key"; ?>][rowCount]" value="<?php echo "$manipulation_key"; ?>"><input type="checkbox" name="record" class="checkbox-field">
                            </td>
                                            <td valign="top">
                                        <select name="field_manipulation[<?php echo "$manipulation_key"; ?>][product_type]" class="select-field">
                                                                    <?php
                                                                        foreach ( $product_types as $k => $v ) {
                                                                            if ( isset( $project['field_manipulation'][ $manipulation_key ]['product_type'] ) && ( $project['field_manipulation'][ $manipulation_key ]['product_type'] == $k ) ) {
                                                                                    echo "<option value=\"$k\" selected>$v</option>";
                                                                                } else {
                                                                                        echo "<option value=\"$k\">$v</option>";
                                                                                }
                                                                        }
                                                                        ?>
                                                                </select>
                            </td>
                            <td valign="top">
                                                            <select name="field_manipulation[<?php echo "$manipulation_key"; ?>][attribute]" class="select-field">
                                                                    <?php
                                                                        foreach ( $attributes as $k => $v ) {
                                                                            if ( isset( $project['field_manipulation'][ $manipulation_key ]['attribute'] ) && ( $project['field_manipulation'][ $manipulation_key ]['attribute'] == $k ) ) {
                                                                                    echo "<option value=\"$k\" selected>$v</option>";
                                                                                } else {
                                                                                        echo "<option value=\"$k\">$v</option>";
                                                                                }
                                                                        }
                                                                        ?>
                                                                </select>
                                                    </td>
                            <td valign="top" class="becomes_fields_<?php echo "$manipulation_key"; ?>">
                                <?php
                                                                        foreach ( $project['field_manipulation'][ $manipulation_key ]['becomes'] as $k => $v ) {
                                        echo "<select name=\"field_manipulation[$manipulation_key][becomes][$k][attribute]\" class=\"select_field\">";
                                        foreach ( $attributes as $ak => $av ) {
                                                                                if ( isset( $project['field_manipulation'][ $manipulation_key ]['becomes'][ $k ]['attribute'] ) && ( $project['field_manipulation'][ $manipulation_key ]['becomes'][ $k ]['attribute'] == $ak ) ) {
                                                                                        echo "<option value=\"$ak\" selected>$av</option>";
                                                                                    } else {
                                                                                            echo "<option value=\"$ak\">$av</option>";
                                                                                    }
                                                                            }
                                        print '</select>';
                                        print '</br>';
                                    }
                                ?>
                            </td>
                            <td>
                                <span class="dashicons dashicons-plus field_extra field_manipulation_extra_<?php echo "$manipulation_key"; ?>" style="display: inline-block;" title="Add an attribute to this field"></span>
                            </td>
                        </tr>
                    <?php
                    }
                }
                ?>
                </tbody>                    
                
                <tbody>

                <tr class="rules-buttons">
                    <td colspan="8">
                                                <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo "$project[channel_hash]"; ?>">
                        <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                        <input type="hidden" name="woosea_page" value="field_manipulation">
                                        <input type="hidden" name="step" value="100">
                                        <input type="button" class="delete-row" value="- Delete">&nbsp;<input type="button" class="add-field-manipulation" value="+ Add field manipulation">&nbsp;<input type="submit" id="savebutton" value="Save">
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
