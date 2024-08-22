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

        $project_hash = sanitize_text_field( $_POST['project_hash'] );
        $feed         = new Product_Feed( $project_hash );
        if ( ! $feed->id ) {
            wp_send_json_error( __( 'Product feed not found.', 'woo-product-feed-pro' ) );
        }

        $proc_perc = 0;
        if ( $feed->status === 'ready' ) {
            $proc_perc = 100;
        } elseif ( $feed->status === 'not run yet' ) {
            $proc_perc = 999;
        } elseif ( $feed->status === 'processing' ) {
            $proc_perc = $feed->get_processing_percentage();
        }

        $response = array(
            'project_hash' => $project_hash,
            'running'      => $feed->status,
            'proc_perc'    => $proc_perc,
        );

        wp_send_json_success( apply_filters( 'adt_product_feed_processing_status_response', $response, $feed ) );
    }

    /**
     * Get product feed check processing.
     *
     * @since 13.3.5
     * @access public
     *
     * @return void
     */
    public function get_product_feed_check_processing() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $product_feeds_query = new Product_Feed_Query(
            array(
                'post_status'    => array( 'draft', 'publish' ),
                'posts_per_page' => -1,
            ),
            'edit'
        );

        $processing = 'false';
        if ( $product_feeds_query->have_posts() ) {
            foreach ( $product_feeds_query->get_posts() as $product_feed ) {
                if ( in_array( $product_feed->status, array( 'true', 'processing', 'stopped', 'not run yet' ), true ) ) {
                    $processing = 'true';
                    break;
                }
            }
        }

        $response = array(
            'processing' => $processing,
        );

        wp_send_json_success( apply_filters( 'adt_product_feed_check_processing_response', $response ) );
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 13.3.5
     */
    public function run() {
        add_action( 'wp_ajax_woosea_project_processing_status', array( $this, 'get_product_feed_processing_status' ) );
        add_action( 'wp_ajax_woosea_check_processing', array( $this, 'get_product_feed_check_processing' ) );
    }
}
