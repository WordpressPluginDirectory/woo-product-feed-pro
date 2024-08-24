<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFP\Classes
 */

namespace AdTribes\PFP\Classes;

use AdTribes\PFP\Abstracts\Abstract_Class;
use AdTribes\PFP\Factories\Product_Feed_Query;
use AdTribes\PFP\Factories\Product_Feed;
use AdTribes\PFP\Helpers\Product_Feed_Helper;

/**
 * Heartbeat class.
 *
 * @since 13.3.5
 */
class Heartbeat extends Abstract_Class {

    /**
     * Get product feed processing status.
     *
     * @since 13.3.5
     * @access public
     *
     * @return void
     */
    public function get_product_feed_processing_status() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $project_hashes = array_map( 'sanitize_text_field', $_POST['project_hashes'] );
        $response       = array();

        foreach ( $project_hashes as $project_hash ) {
            $feed = new Product_Feed( $project_hash );

            if ( ! $feed->id ) {
                continue;
            }

            $proc_perc = 0;
            if ( 'ready' === $feed->status ) {
                $proc_perc = 100;
            } elseif ( 'not run yet' === $feed->status ) {
                $proc_perc = 999;
            } elseif ( 'processing' === $feed->status ) {
                $proc_perc = $feed->get_processing_percentage();

                // If the feed is processing and the percentage is not 100 and there is no cron job scheduled for woosae_update_project_stats, schedule it.
                if ( 100 !== $proc_perc && ! wp_next_scheduled( 'woosea_create_batch_event', array( $feed->id ) ) ) {
                    // Schedule the cron job to update the project stats.
                    wp_schedule_single_event( time(), 'woosea_create_batch_event', array( $feed->id ) );
                    spawn_cron( time() );
                }
            }

            $response[] = array(
                'hash'      => $project_hash,
                'running'   => $feed->status,
                'proc_perc' => $proc_perc,
            );
        }

        if ( empty( $response ) ) {
            wp_send_json_error( __( 'Product feed(s) not found.', 'woo-product-feed-pro' ) );
        }

        wp_send_json_success( apply_filters( 'adt_product_feed_processing_status_response', $response, $feed ) );
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 13.3.5
     */
    public function run() {
        add_action( 'wp_ajax_woosea_project_processing_status', array( $this, 'get_product_feed_processing_status' ) );
    }
}
