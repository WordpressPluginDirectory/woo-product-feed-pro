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
 * Product Feed Cron class.
 *
 * @since 13.3.5
 */
class Cron extends Abstract_Class {

    /***************************************************************************
     * Cron actions
     * **************************************************************************
     */

    /**
     * Register own cron hook(s), it will execute the woosea_create_all_feeds that will generate all feeds on scheduled event.
     *
     * This is the legacy cron job to run the product feeds at a certain time.
     * The cron is running every hour and will check if the feed needs to be updated at that time.
     * ( daily, twicedaily, hourly, no refresh ) are the options.
     * The base is at hour 07 and 19. x_x
     * We will refactor this to individual cron jobs for each feed using Action Scheduler.
     *
     * @since 13.3.5
     * @access public
     */
    public function run_product_feed_cron() {
        $product_feeds_query = new Product_Feed_Query(
            array(
                'post_status'    => array( 'publish' ),
                'posts_per_page' => -1,
            ),
            'edit'
        );

        if ( $product_feeds_query->have_posts() ) {
            // Make sure content of feeds is not being cached.
            Product_Feed_Helper::disable_cache();

            // Get the current hour.
            $hour = gmdate( 'H' );

            foreach ( $product_feeds_query->get_posts() as $product_feed ) {

                if ( ! $product_feed instanceof Product_Feed ) {
                    continue;
                }

                $interval = $product_feed->refresh_interval;

                if ( ( $interval == 'daily' ) && ( $hour == 07 ) ||
                    ( $interval == 'twicedaily' ) && ( $hour == 19 || $hour == 07 ) ||
                    ( $interval == 'twicedaily' || $interval == 'daily' ) && ( $product_feed->status == 'processing' ) || // Re-start daily and twicedaily projects that are hanging. (not sure what this means, but we keep it here)
                    ( $interval == 'hourly' )
                ) {
                    woosea_continue_batch( $product_feed->id );
                } elseif ( ( $interval == 'no refresh' ) && ( $hour == 26 ) ) {
                    // It is never hour 26, so this project will never refresh. (Seriusly?!!)
                }
            }
        }
    }

    /**
     * Set project history: amount of products in the feed.
     *
     * @param string $project_hash The project hash.
     **/
    public function update_project_history( $id ) {
        $feed = new Product_Feed( $id );
        if ( ! $feed->id ) {
            return;
        }

        // Filter the amount of history products in the system report.
        $max_history_products = apply_filters( 'adt_product_feed_max_history_products', 10 );

        $products_count = 0;
        $file           = $feed->get_file_path();
        $file_format    = $feed->file_format;
        $products_count = file_exists( $file ) ? $this->get_product_counts_from_file( $file, $file_format, $feed ) : 0;

        $feed->add_history_product( $products_count );
        $feed->save();
    }

    /**
     * Get the amount of products in the feed file.
     *
     * @param string       $file        The file path.
     * @param string       $file_format The file format.
     * @param Product_Feed $feed        The feed data object.
     *
     * @return int The amount of products in the feed file.
     */
    private function get_product_counts_from_file( $file, $file_format, $feed ) {
        $products_count = 0;

        switch ( $file_format ) {
            case 'xml':
                $xml          = simplexml_load_file( $file, 'SimpleXMLElement', LIBXML_NOCDATA );
                $feed_channel = $feed->get_channel();

                if ( $feed_channel['name'] == 'Yandex' ) {
                    $products_count = isset( $xml->offers->offer ) ? count( $xml->offers->offer ) : 0;
                } elseif ( $feed_channel['taxonomy'] == 'none' ) {
                    $products_count = is_countable( $xml->product ) ? count( $xml->product ) : 0;
                } else {
                    $products_count = count( $xml->channel->item );
                }

                break;
            case 'csv':
            case 'txt':
            case 'tsv':
                $products_count = count( file( $file ) ) - 1; // -1 for the header.
                break;
        }

        /**
         * Filter the amount of history products in the system report.
         *
         * @since 13.3.5
         *
         * @param int          $products_count The amount of products in the feed file.
         * @param string       $file           The file path.
         * @param string       $file_format    The file format.
         * @param Product_Feed $feed           The feed data object.
         */
        return apply_filters( 'adt_product_feed_history_count', $products_count, $file, $file_format, $feed );
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 13.3.5
     */
    public function run() {
        add_action( 'woosea_cron_hook', array( $this, 'run_product_feed_cron' ), 1, 1 );
        add_action( 'woosea_update_project_stats', array( $this, 'update_project_history' ), 1, 1 );
    }
}
