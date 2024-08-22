<?php
/**
 * This class is called to:
 * - retrieve channel settings and configuration, function: get_channel_data
 * - update project configuration during steps, function: update_project
 * - add project configuration to cron option and clear current project config
 */
class WooSEA_Update_Project {

    public $channel_data;
    public $channel_update;
    private $project_config;
    private $project_hash;

    /**
     * Get generic channel information
     */
        public static function get_channel_data( $channel_hash ) {
        $channel_statics = get_option( 'channel_statics' );

        foreach ( $channel_statics as $key => $val ) {

            foreach ( $val as $k => $v ) {
                if ( $channel_hash === $v['channel_hash'] ) {
                    $channel_data = $v;
                }
            }
        }
        return $channel_data;
    }

    public static function update_project( $project_data ) {
        check_ajax_referer( 'woosea_ajax_nonce', 'security' );

        if ( ! array_key_exists( 'project_hash', $project_data ) ) {
                    $upload_dir    = wp_upload_dir();
                    $external_base = $upload_dir['baseurl'];
                    $external_path = $external_base . '/woo-product-feed-pro/' . $project_data['fileformat'];
            $channel_statics       = get_option( 'channel_statics' );

            foreach ( $channel_statics as $key => $val ) {

                foreach ( $val as $k => $v ) {
                    if ( $project_data['channel_hash'] == $v['channel_hash'] ) {
                        $project_fill = array_merge( $v, $project_data );

                        // New code to create the project hash so dependency on openSSL is removed
                        $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $pieces   = array();
                        $length   = 32;
                        $max      = mb_strlen( $keyspace, '8bit' ) - 1;
                        for ( $i = 0; $i < $length; ++$i ) {
                                $pieces [] = $keyspace[ random_int( 0, $max ) ];
                            }
                            $project_fill['project_hash'] = implode( '', $pieces );
                        $project_fill['filename']         = $project_fill['project_hash'];
                        $project_fill['external_file']    = $external_path . '/' . sanitize_file_name( $project_fill['filename'] ) . '.' . $project_fill['fileformat'];
                        $project_fill['query_log']        = $external_base . '/woo-product-feed-pro/logs/query.log';
                        $project_fill['query_output_log'] = $external_base . '/woo-product-feed-pro/logs/query_output.log';
                    }
                }
            }
                    update_option( ADT_OPTION_TEMP_PRODUCT_FEED, $project_fill, false );
        } else {
            $project_temp = get_option( ADT_OPTION_TEMP_PRODUCT_FEED );
            if ( is_array( $project_temp ) ) {
                    $project_fill = array_merge( $project_temp, $project_data );
            } else {
                    $project_fill = $project_data;
            }
            update_option( ADT_OPTION_TEMP_PRODUCT_FEED, $project_fill, false );
        }
        return $project_fill;
    }
}
