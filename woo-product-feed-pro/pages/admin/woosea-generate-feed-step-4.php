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

/**
 * Create notification object
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications();
$notifications_box = $notifications_obj->get_admin_notifications( '4', 'false' );

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
    if ( isset( $project['rules'] ) ) {
        $count_rules = count( $project['rules'] );
    }

    $count_rules2 = 0;
    if ( isset( $project['rules2'] ) ) {
        $count_rules2 = count( $project['rules2'] );
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

    $project          = WooSEA_Update_Project::update_project( $_POST );
        $channel_data = WooSEA_Update_Project::get_channel_data( sanitize_text_field( $_POST['channel_hash'] ) );
    $count_rules      = 0;
    $count_rules2     = 0;
}
?>
    <div class="wrap">
        <div class="woo-product-feed-pro-form-style-2">
            <div class="woo-product-feed-pro-form-style-2-heading">
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank"><img class="logo" src="<?php echo esc_attr( WOOCOMMERCESEA_PLUGIN_URL . '/images/adt-logo.png' ); ?>" alt="<?php esc_attr_e( 'AdTribes', 'woo-product-feed-pro' ); ?>"></a> 
                <a href="https://adtribes.io/?utm_source=pfp&utm_medium=logo&utm_campaign=adminpagelogo" target="_blank" class="logo-upgrade">Upgrade to Elite</a>
                <h1 class="title"><?php esc_html_e( 'Feed filters and rules', 'woo-product-feed-pro' ); ?></h1>
            </div>

            <div class="<?php echo esc_attr( $notifications_box['message_type'] ); ?>">
                <p><?php echo wp_kses_post( $notifications_box['message'] ); ?></p>
            </div>
            <form id="rulesandfilters" method="post">
            <?php wp_nonce_field( 'woosea_ajax_nonce' ); ?>

            <table class="woo-product-feed-pro-table" id="woosea-ajax-table" border="1">
                <thead>
                            <tr>
                                <th></th>
                        <th><?php esc_html_e( 'Type', 'woo-product-feed-pro' ); ?></th>
                                <th><?php esc_html_e( 'IF', 'woo-product-feed-pro' ); ?></th>
                                <th><?php esc_html_e( 'Condition', 'woo-product-feed-pro' ); ?></th>
                                <th><?php esc_html_e( 'Value', 'woo-product-feed-pro' ); ?></th>
                        <th><?php esc_html_e( 'CS', 'woo-product-feed-pro' ); ?></th>
                                <th><?php esc_html_e( 'Then', 'woo-product-feed-pro' ); ?></th>
                        <th><?php esc_html_e( 'IS', 'woo-product-feed-pro' ); ?></th>
                            </tr>
                    </thead>
      
                <?php
                // if(isset($project['rules'])){
                    print '<tbody class="woo-product-feed-pro-body">';
                    if ( isset( $project['rules'] ) ) {
                        foreach ( $project['rules'] as $rule_key => $rule_array ) {

                            if ( isset( $project['rules'][ $rule_key ]['criteria'] ) ) {
                                $criteria = $project['rules'][ $rule_key ]['criteria'];
                            } else {
                                $criteria = '';
                            }
                            ?>
                            <tr class="rowCount">
                                        <td><input type="hidden" name="rules[<?php echo "$rule_key"; ?>][rowCount]" value="<?php echo "$rule_key"; ?>"><input type="checkbox" name="record" class="checkbox-field"></td>
                                        <td><i><?php esc_html_e( 'Filter', 'woo-product-feed-pro' ); ?></i></td>
                                <td>
                                    <select name="rules[<?php echo "$rule_key"; ?>][attribute]" class="select-field">
                                        <option></option>
                                        <?php
                                        foreach ( $attributes as $k => $v ) {
                                            if ( isset( $project['rules'][ $rule_key ]['attribute'] ) && ( $project['rules'][ $rule_key ]['attribute'] == $k ) ) {
                                                echo "<option value=\"$k\" selected>$v</option>";
                                            } else {
                                                echo "<option value=\"$k\">$v</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                        <td>
                                    <select name="rules[<?php echo "$rule_key"; ?>][condition]" class="select-field">
                                        <?php
                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == 'contains' ) ) {
                                            print '<option value="contains" selected>contains</option>';
                                        } else {
                                            print '<option value="contains">contains</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == 'containsnot' ) ) {
                                            echo "<option value=\"containsnot\" selected>doesn't contain</option>";
                                        } else {
                                            echo "<option value=\"containsnot\">doesn't contain</option>";
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == '=' ) ) {
                                            print '<option value="=" selected>is equal to</option>';
                                        } else {
                                            print '<option value="=">is equal to</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == '!=' ) ) {
                                            print '<option value="!=" selected>is not equal to</option>';
                                        } else {
                                            print '<option value="!=">is not equal to</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == '>' ) ) {
                                            print '<option value=">" selected>is greater than</option>';
                                        } else {
                                            print '<option value=">">is greater than</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == '>=' ) ) {
                                            print '<option value=">=" selected>is greater or equal to</option>';
                                        } else {
                                            print '<option value=">=">is greater or equal to</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == '<' ) ) {
                                            print '<option value="<" selected>is less than</option>';
                                        } else {
                                            print '<option value="<">is less than</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == '=<' ) ) {
                                            print '<option value="=<" selected>is less or equal to</option>';
                                        } else {
                                            print '<option value="=<">is less or equal to</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == 'empty' ) ) {
                                            print '<option value="empty" selected>is empty</option>';
                                        } else {
                                            print '<option value="empty">is empty</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['condition'] ) && ( $project['rules'][ $rule_key ]['condition'] == 'notempty' ) ) {
                                            print '<option value="notempty" selected>is not empty</option>';
                                        } else {
                                            print '<option value="notempty">is not empty</option>';
                                        }
                                        ?>
                                    </select>   
                                </td>
                                <td>
                                    <div style="display: block;">
                                        <input type="text" id="rulevalue" name="rules[<?php echo "$rule_key"; ?>][criteria]" class="input-field-large" value='<?php print $criteria; ?>'>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    if ( isset( $project['rules'][ $rule_key ]['cs'] ) ) {
                                        echo "<input type=\"checkbox\" name=\"rules[$rule_key][cs]\" class=\"checkbox-field\" alt=\"Case sensitive\" checked>";
                                    } else {
                                        echo "<input type=\"checkbox\" name=\"rules[$rule_key][cs]\" class=\"checkbox-field\" alt=\"Case sensitive\">";
                                    }
                                    ?>
                                </td>
                                        <td>
                                    <select name="rules[<?php echo "$rule_key"; ?>][than]" class="select-field">
                                        <optgroup label='Action'>Action:
                                        <?php
                                        if ( isset( $project['rules'][ $rule_key ]['than'] ) && ( $project['rules'][ $rule_key ]['than'] == 'exclude' ) ) {
                                            print '<option value="exclude" selected> Exclude</option>';
                                        } else {
                                            print '<option value="exclude"> Exclude</option>';
                                        }

                                        if ( isset( $project['rules'][ $rule_key ]['than'] ) && ( $project['rules'][ $rule_key ]['than'] == 'include_only' ) ) {
                                            print '<option value="include_only" selected> Include only</option>';
                                        } else {
                                            print '<option value="include_only"> Include only</option>';
                                        }
                                        ?>
                                        </optgroup>
                                    </select>
                                </td>
                                <td>&nbsp;</td>
                            </tr>
                        <?php
                        }
                    }

                    // RULES SECTION

                    if ( isset( $project['rules2'] ) ) {

                        foreach ( $project['rules2'] as $rule2_key => $rule2_array ) {

                            if ( isset( $project['rules2'][ $rule2_key ]['criteria'] ) ) {
                                $criteria = $project['rules2'][ $rule2_key ]['criteria'];
                            } else {
                                $criteria = '';
                            }
                            if ( isset( $project['rules2'][ $rule2_key ]['newvalue'] ) ) {
                                $newvalue = $project['rules2'][ $rule2_key ]['newvalue'];
                            } else {
                                $newvalue = '';
                            }
                            ?>
                                <tr class="rowCount">
                                        <td><input type="hidden" name="rules2[<?php echo "$rule2_key"; ?>][rowCount]" value="<?php echo "$rule2_key"; ?>"><input type="checkbox" name="record" class="checkbox-field"></td>
                                        <td><i><?php esc_html_e( 'Rule', 'woo-product-feed-pro' ); ?></i></td>
                                <td>
                                    <select name="rules2[<?php echo "$rule2_key"; ?>][attribute]" class="select-field">
                                        <option></option>
                                        <?php
                                        foreach ( $attributes as $k => $v ) {
                                            if ( isset( $project['rules2'][ $rule2_key ]['attribute'] ) && ( $project['rules2'][ $rule2_key ]['attribute'] == $k ) ) {
                                                echo "<option value=\"$k\" selected>$v</option>";
                                            } else {
                                                echo "<option value=\"$k\">$v</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                        <td>
                                    <select name="rules2[<?php echo "$rule2_key"; ?>][condition]" class="select-field">
                                        <?php
                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'contains' ) ) {
                                            print '<option value="contains" selected>contains</option>';
                                        } else {
                                            print '<option value="contains">contains</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'containsnot' ) ) {
                                            echo "<option value=\"containsnot\" selected>doesn't contain</option>";
                                        } else {
                                            echo "<option value=\"containsnot\">doesn't contain</option>";
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == '=' ) ) {
                                            print '<option value="=" selected>is equal to</option>';
                                        } else {
                                            print '<option value="=">is equal to</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == '!=' ) ) {
                                            print '<option value="!=" selected>is not equal to</option>';
                                        } else {
                                            print '<option value="!=">is not equal to</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == '>' ) ) {
                                            print '<option value=">" selected>is greater than</option>';
                                        } else {
                                            print '<option value=">">is greater than</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == '>=' ) ) {
                                            print '<option value=">=" selected>is greater or equal to</option>';
                                        } else {
                                            print '<option value=">=">is greater or equal to</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == '<' ) ) {
                                            print '<option value="<" selected>is less than</option>';
                                        } else {
                                            print '<option value="<">is less than</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == '=<' ) ) {
                                            print '<option value="=<" selected>is less or equal to</option>';
                                        } else {
                                            print '<option value="=<">is less or equal to</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'empty' ) ) {
                                            print '<option value="empty" selected>is empty</option>';
                                        } else {
                                            print '<option value="empty">is empty</option>';
                                        }

                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'notempty' ) ) {
                                            print '<option value="notempty" selected>is not empty</option>';
                                        } else {
                                            print '<option value="notempty">is not empty</option>';
                                        }

                                        // Data manipulators
                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'multiply' ) ) {
                                            print '<option value="multiply" selected>multiply</option>';
                                        } else {
                                            print '<option value="multiply">multiply</option>';
                                        }
                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'divide' ) ) {
                                            print '<option value="divide" selected>divide</option>';
                                        } else {
                                            print '<option value="divide">divide</option>';
                                        }
                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'plus' ) ) {
                                            print '<option value="plus" selected>plus</option>';
                                        } else {
                                            print '<option value="plus">plus</option>';
                                        }
                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'minus' ) ) {
                                            print '<option value="minus" selected>minus</option>';
                                        } else {
                                            print '<option value="minus">minus</option>';
                                        }
                                        if ( isset( $project['rules2'][ $rule2_key ]['condition'] ) && ( $project['rules2'][ $rule2_key ]['condition'] == 'findreplace' ) ) {
                                            print '<option value="findreplace" selected>find and replace</option>';
                                        } else {
                                            print '<option value="findreplace">find and replace</option>';
                                        }
                                        ?>
                                    </select>   
                                </td>
                                <td>
                                    <div style="display: block;">
                                        <input type="text" id="rulevalue" name="rules2[<?php echo "$rule2_key"; ?>][criteria]" class="input-field-large" value='<?php print $criteria; ?>'>
                                    </div>
                                </td>
                                <?php
                                    $manipulators = array( 'multiply', 'divide', 'plus', 'minus' );
                                    if ( in_array( $project['rules2'][ $rule2_key ]['condition'], $manipulators, true ) ) {
                                        print '<td colspan=3></td>';
                                    } else {
                                    ?>
                                    <td>
                                        <?php
                                        if ( isset( $project['rules2'][ $rule2_key ]['cs'] ) ) {
                                            echo "<input type=\"checkbox\" name=\"rules2[$rule2_key][cs]\" class=\"checkbox-field\" alt=\"Case sensitive\" checked>";
                                        } else {
                                            echo "<input type=\"checkbox\" name=\"rules2[$rule2_key][cs]\" class=\"checkbox-field\" alt=\"Case sensitive\">";
                                        }
                                        ?>
                                    </td>
                                            <td>
                                        <select name="rules2[<?php echo "$rule2_key"; ?>][than_attribute]" class="select-field" style="width:150px;">
                                            <option></option>
                                            <?php
                                            foreach ( $attributes as $k => $v ) {
                                                if ( isset( $project['rules2'][ $rule2_key ]['than_attribute'] ) && ( $project['rules2'][ $rule2_key ]['than_attribute'] == $k ) ) {
                                                    echo "<option value=\"$k\" selected>$v</option>";
                                                } else {
                                                    echo "<option value=\"$k\">$v</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>
                                    <td><input type="text" name="rules2[<?php echo "$rule2_key"; ?>][newvalue]" class="input-field-large" value="<?php echo "$newvalue"; ?>"></td>
                                <?php
                                    }
                                ?>
                            </tr>
                        <?php
                        }
                    }
                    print '</tbody>';
                // }
                ?>
                <tbody>
                <tr class="rules-buttons">
                    <td colspan="8">

                                                <input type="hidden" id="channel_hash" name="channel_hash" value="<?php echo "$project[channel_hash]"; ?>">
                                                <?php
                                                if ( isset( $manage_project ) ) {
                                                ?>
                            <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                            <input type="hidden" name="woosea_page" value="filters_rules">
                                            <input type="hidden" name="step" value="100">
                                            <input type="button" class="delete-row" value="- Delete">&nbsp;<input type="button" class="add-filter" value="+ Add filter">&nbsp;<input type="button" class="add-rule" value="+ Add rule">&nbsp;<input type="submit" id="savebutton" value="Save">
                        <?php
                        } else {
                        ?>
                            <input type="hidden" name="project_hash" value="<?php echo "$project[project_hash]"; ?>">
                                            <input type="hidden" name="step" value="5">
                                            <input type="button" class="delete-row" value="- Delete">&nbsp;<input type="button" class="add-filter" value="+ Add filter">&nbsp;<input type="button" class="add-rule" value="+ Add rule">&nbsp;<input type="submit" id="savebutton" value="Continue">
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
