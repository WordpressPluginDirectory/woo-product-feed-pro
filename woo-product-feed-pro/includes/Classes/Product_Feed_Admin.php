<?php
/**
 * Author: Rymera Web Co.
 *
 * @package AdTribes\PFP\Classes
 */

namespace AdTribes\PFP\Classes;

use AdTribes\PFP\Abstracts\Abstract_Class;
use AdTribes\PFP\Factories\Product_Feed;
use AdTribes\PFP\Helpers\Product_Feed_Helper;

use function Avifinfo\read;

/**
 * Product Feed Admin class.
 *
 * @since 13.3.5
 */
class Product_Feed_Admin extends Abstract_Class {

    /***************************************************************************
     * Actions
     * **************************************************************************
     */

    /**
     * Create product feed.
     *
     * This method is used to create the product feed after generating the products from the legacy code base.
     *
     * @since 13.3.5
     * @access public
     *
     * @param array $project_data Project data from the legacy code base.
     * @param array $post_data    Post data from the legacy code base.
     */
    public function create_product_feed( $project_data, $post_data ) {
        if ( ! wp_verify_nonce( $post_data['_wpnonce'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        // Get the total amount of products in the feed.
        if ( isset( $project_data['product_variations'] ) && 'on' === $project_data['product_variations'] ) {
            $project_data['nr_products'] = Product_Feed_Helper::get_total_published_products( true );
        } else {
            $project_data['nr_products'] = Product_Feed_Helper::get_total_published_products();
        }

        $product_feed = new Product_Feed();
        $country_code = isset( $project_data['countries'] ) ? Product_Feed_Helper::get_code_from_legacy_country_name( $project_data['countries'] ) : '';
        $product_feed->set_props(
            array(
                'title'                                  => $project_data['projectname'] ?? '',
                'status'                                 => 'processing',
                'country'                                => $country_code,
                'channel_hash'                           => $project_data['channel_hash'] ?? '',
                'file_name'                              => $project_data['filename'] ?? '',
                'file_format'                            => $project_data['fileformat'] ?? '',
                'delimiter'                              => $project_data['delimiter'] ?? '',
                'refresh_interval'                       => $project_data['cron'] ?? '',
                'include_product_variations'             => isset( $project_data['product_variations'] ) && 'on' === $project_data['product_variations'] ? 'yes' : 'no',
                'only_include_default_product_variation' => isset( $project_data['default_variations'] ) && 'on' === $project_data['default_variations'] ? 'yes' : 'no',
                'only_include_lowest_product_variation'  => isset( $project_data['lowest_price_variations'] ) && 'on' === $project_data['lowest_price_variations'] ? 'yes' : 'no',
                'create_preview'                         => isset( $project_data['preview_feed'] ) && 'on' === $project_data['preview_feed'] ? 'yes' : 'no',
                'refresh_only_when_product_changed'      => isset( $project_data['products_changed'] ) && 'on' === $project_data['products_changed'] ? 'yes' : 'no',
                'attributes'                             => $project_data['attributes'] ?? array(),
                'mappings'                               => $project_data['mappings'] ?? array(),
                'filters'                                => $project_data['rules'] ?? array(),
                'rules'                                  => $project_data['rules2'] ?? array(),
                'products_count'                         => $project_data['nr_products'] ?? 0,
                'total_products_processed'               => $project_data['nr_products_processed'] ?? 0,
                'utm_enabled'                            => isset( $project_data['utm_on'] ) && 'on' === $project_data['utm_on'] ? 'yes' : 'no',
                'utm_source'                             => $project_data['utm_source'] ?? '',
                'utm_medium'                             => $project_data['utm_medium'] ?? '',
                'utm_campaign'                           => $project_data['utm_campaign'] ?? '',
                'utm_term'                               => $project_data['utm_term'] ?? '',
                'utm_content'                            => $project_data['utm_content'] ?? '',
                'utm_total_product_orders_lookback'      => $project_data['total_product_orders_lookback'] ?? '',
                'legacy_project_hash'                    => $project_data['project_hash'] ?? '',
            )
        );
        $product_feed->save();

        /**
         * Run the product feed batch processing.
         * This is the legacy code base processing logic.
         */
        woosea_continue_batch( $product_feed->id );
    }

    /**
     * Edit product feed.
     *
     * This method is used to edit the product feed after generating the products from the legacy code base.
     *
     * @since 13.3.5
     * @access public
     *
     * @param array $post_data Post data from the legacy code base.
     */
    public function edit_product_feed( $post_data ) {
        if ( ! wp_verify_nonce( $post_data['_wpnonce'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $product_feed = new Product_Feed( $post_data['project_hash'] );
        if ( $product_feed->id ) {
            $props_to_update = array();
            switch ( $_GET['step'] ) {
                case 0: // General settings.
                    $props_to_update = array(
                        'title'                      => $post_data['projectname'] ?? '',
                        'country'                    => isset( $post_data['countries'] ) ? Product_Feed_Helper::get_code_from_legacy_country_name( $post_data['countries'] ) : '',
                        'file_format'                => $post_data['fileformat'] ?? '',
                        'delimiter'                  => $post_data['delimiter'] ?? '',
                        'refresh_interval'           => $post_data['cron'] ?? '',
                        'include_product_variations' => isset( $post_data['product_variations'] ) && 'on' === $post_data['product_variations'] ? 'yes' : 'no',
                        'only_include_default_product_variation' => isset( $post_data['default_variations'] ) && 'on' === $post_data['default_variations'] ? 'yes' : 'no',
                        'only_include_lowest_product_variation' => isset( $post_data['lowest_price_variations'] ) && 'on' === $post_data['lowest_price_variations'] ? 'yes' : 'no',
                        'create_preview'             => isset( $post_data['preview_feed'] ) && 'on' === $post_data['preview_feed'] ? 'yes' : 'no',
                        'refresh_only_when_product_changed' => isset( $post_data['products_changed'] ) && 'on' === $post_data['products_changed'] ? 'yes' : 'no',
                    );
                    break;
                case 4: // Filters and rules.
                    $props_to_update = array(
                        'filters' => $post_data['rules'] ?? array(),
                        'rules'   => $post_data['rules2'] ?? array(),
                    );
                    break;
                case 5: // Conversion & Google Analytics settings.
                    $props_to_update = array(
                        'utm_enabled'  => isset( $post_data['utm_on'] ) && 'on' === $post_data['utm_on'] ? true : false,
                        'utm_source'   => $post_data['utm_source'] ?? '',
                        'utm_medium'   => $post_data['utm_medium'] ?? '',
                        'utm_campaign' => $post_data['utm_campaign'] ?? '',
                        'utm_term'     => $post_data['utm_term'] ?? '',
                        'utm_content'  => $post_data['utm_content'] ?? '',
                        'utm_total_product_orders_lookback' => $post_data['total_product_orders_lookback'] ?? '',
                    );
                    break;
                case 7: // Field mapping.
                    $props_to_update = array(
                        'attributes' => $post_data['attributes'] ?? array(),
                    );
                    break;
            }
            $product_feed->set_props( $props_to_update );
            $product_feed->save();
        }
    }

    /**
     * Update product feed.
     *
     * This method is used to update the product feed after generating the products from the legacy code base.
     *
     * @since 13.3.5
     * @access public
     *
     * @param int $feed_id     Feed ID.
     * @param int $batch_size  Offset step size.
     */
    public function update_product_feed( $feed_id, $batch_size ) {
        $feed = new Product_Feed( $feed_id );
        if ( ! $feed->id ) {
            return false;
        }

        // User would like to see a preview of their feed, retrieve only 5 products by default.
        $preview_count = $feed->create_preview ? apply_filters( 'adt_product_feed_preview_products', 5, $feed ) : null;

        // Get total of published products to process.
        $published_products = $preview_count ? $preview_count : Product_Feed_Helper::get_total_published_products( $feed->include_product_variations );

        /**
         * Filter the total number of products to process.
         *
         * @since 13.3.5
         *
         * @param int $published_products Total number of published products to process.
         * @param \AdTribes\PFP\Factories\Product_Feed $feed The product feed instance.
         */
        $published_products = apply_filters( 'adt_product_feed_total_published_products', $published_products, $feed );

        // Update the feed with the total number of products.
        $feed->products_count = $published_products;

        /**
         * Batch processing.
         *
         * If the batch size is less than the total number of published products, then we need to create a batch.
         * The batching logic is from the legacy code base as it's has the batch size.
         * We need to refactor this logic so it's not stupid.
         */
        if ( $feed->total_products_processed >= $published_products || $batch_size >= $published_products ) { // End of processing.
            $upload_dir = wp_upload_dir();
            $base       = $upload_dir['basedir'];
            $path       = $base . '/woo-product-feed-pro/' . $feed->file_format;
            $tmp_file   = $path . '/' . sanitize_file_name( $feed->file_name ) . '_tmp.' . $feed->file_format;
            $new_file   = $path . '/' . sanitize_file_name( $feed->file_name ) . '.' . $feed->file_format;

            // Move the temporary file to the final file.
            if ( copy( $tmp_file, $new_file ) ) {
                wp_delete_file( $tmp_file );
            }

            // In 2 minutes from now check the amount of products in the feed and update the history count.
            wp_schedule_single_event( time() + 120, 'woosea_update_project_stats', array( $feed->id ) );

            // Set status to ready.
            $feed->status = 'ready';

            // Set counters back to 0.
            $feed->total_products_processed = 0;

            // Set last updated date and time.
            $feed->last_updated = gmdate( 'd M Y H:i' );
        } else { // Continue processing.
            // Set the next scheduled event.
            if ( ! wp_next_scheduled( 'woosea_create_batch_event', array( $feed->id ) ) ) {
                wp_schedule_single_event( time() + 2, 'woosea_create_batch_event', array( $feed->id ) );
            }

            $feed->status = 'processing';
            // Update current processing status and numbers.
            $feed->total_products_processed = $total_products_processed + $batch_size;
        }

        $feed->save();
    }


    /***************************************************************************
     * AJAX Actions
     * **************************************************************************
     */

    /**
     * Update product feed status.
     *
     * This method is used to update the product feed status after generating the products from the legacy code base.
     *
     * @since 13.3.5
     * @access public
     */
    public function ajax_update_product_feed_status() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $project_hash = sanitize_text_field( $_POST['project_hash'] );
        $is_publish   = sanitize_text_field( $_POST['active'] );

        $feed = new Product_Feed( $project_hash );
        if ( ! $feed->id ) {
            wp_send_json_error( __( 'Product feed not found.', 'woo-product-feed-pro' ) );
        }

        // Remove file if set to draft.
        if ( 'true' !== $is_publish ) {
            $feed->remove_file();
        } else {
            // Remove cache.
            Product_Feed_Helper::disable_cache();

            /**
             * Run the product feed batch processing.
             * This is the legacy code base processing logic.
             */
            woosea_continue_batch( $feed->id );
        }

        $feed->post_status = 'true' === $is_publish ? 'publish' : 'draft';
        $feed->save();

        $response = array(
            'project_hash' => $project_hash,
            'status'       => $feed->post_status,
        );

        wp_send_json_success( apply_filters( 'adt_product_feed_status_response', $response, $feed ) );
    }

    /**
     * Clone product feed.
     *
     * @since 13.3.5
     * @access public
     *
     * @return void
     */
    public function ajax_clone_product_feed() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $original_feed = new Product_Feed( sanitize_text_field( $_POST['project_hash'] ) );
        if ( ! $original_feed->id ) {
            wp_send_json_error( __( 'Product feed not found.', 'woo-product-feed-pro' ) );
        }

        // Generate a new project hash for the cloned feed.
        $project_hash = Product_Feed_Helper::generate_legacy_project_hash();

        $feed = new Product_Feed();
        $feed->set_props(
            apply_filters(
                'adt_clone_product_feed_props',
                array(
                    'title'                             => 'Copy ' . $original_feed->title,
                    'status'                            => 'not run yet',
                    'country'                           => $original_feed->country,
                    'channel_hash'                      => $original_feed->channel_hash,
                    'file_format'                       => $original_feed->file_format,
                    'delimiter'                         => $original_feed->delimiter,
                    'refresh_interval'                  => $original_feed->refresh_interval,
                    'refresh_only_when_product_changed' => $original_feed->refresh_only_when_product_changed,
                    'create_preview'                    => $original_feed->create_preview,
                    'include_product_variations'        => $original_feed->include_product_variations,
                    'only_include_default_product_variation' => $original_feed->only_include_default_product_variation,
                    'only_include_lowest_product_variation' => $original_feed->only_include_lowest_product_variation,
                    'products_count'                    => $original_feed->products_count,
                    'total_products_processed'          => $original_feed->total_products_processed,
                    'utm_enabled'                       => $original_feed->utm_enabled,
                    'utm_source'                        => $original_feed->utm_source,
                    'utm_medium'                        => $original_feed->utm_medium,
                    'utm_campaign'                      => $original_feed->utm_campaign,
                    'utm_term'                          => $original_feed->utm_term,
                    'utm_content'                       => $original_feed->utm_content,
                    'utm_total_product_orders_lookback' => $original_feed->utm_total_product_orders_lookback,
                    'attributes'                        => $original_feed->attributes,
                    'mappings'                          => $original_feed->mappings,
                    'rules'                             => $original_feed->rules,
                    'filters'                           => $original_feed->filters,
                    'legacy_project_hash'               => $project_hash, // Backward compatibility.
                    'file_name'                         => $project_hash, // Backward compatibility.
                )
            )
        );

        /**
         * Filter the cloned product feed.
         *
         * @since 13.3.5
         *
         * @param Product_Feed_Factory $feed           The cloned product feed.
         * @param Product_Feed_Factory $original_feed  The original product feed.
         */
        apply_filters( 'adt_clone_product_feed_before_save', $feed, $original_feed );

        $feed->save();

        $response = array(
            'project_hash'  => $feed->legacy_project_hash,
            'channel'       => $feed->channel_hash,
            'projectname'   => $feed->title,
            'fileformat'    => $feed->file_format,
            'interval'      => $feed->refresh_interval,
            'external_file' => $feed->get_file_url(),
            'copy_status'   => true,  // Do not start processing, user wants to make changes to the copied project.
        );

        wp_send_json_success( apply_filters( 'adt_clone_product_feed_response', $response, $feed ) );
    }

    /**
     * Cancel product feed.
     *
     * @since 13.3.5
     * @access public
     *
     * @return void
     */
    public function ajax_cancel_product_feed() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $project_hash = sanitize_text_field( $_POST['project_hash'] );

        $feed = new Product_Feed( $project_hash );
        if ( ! $feed->id ) {
            wp_send_json_error( __( 'Product feed not found.', 'woo-product-feed-pro' ) );
        }

        do_action( 'adt_before_cancel_product_feed', $feed );

        $feed->total_products_processed = 0;
        $feed->status                   = 'stopped';
        $feed->last_updated             = gmdate( 'd M Y H:i' );
        $feed->save();

        /**
         * Legacy code base.
         * In 1 minute from now check the amount of products in the feed and update the history count.
         */
        wp_schedule_single_event( time() + 60, 'woosea_update_project_stats', array( $feed->id ) );

        do_action( 'adt_after_cancel_product_feed', $feed );

        wp_send_json_success( __( 'Product feed process has been cancelled.', 'woo-product-feed-pro' ) );
    }

    /**
     * Refresh product feed.
     *
     * @since 13.3.5
     * @access public
     */
    public function ajax_refresh_product_feed() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $project_hash = sanitize_text_field( $_POST['project_hash'] );

        $feed = new Product_Feed( $project_hash );
        if ( ! $feed->id ) {
            wp_send_json_error( __( 'Product feed not found.', 'woo-product-feed-pro' ) );
        }

        // Remove cache.
        Product_Feed_Helper::disable_cache();

        /**
         * Run the product feed batch processing.
         * This is the legacy code base processing logic.
         */
        woosea_continue_batch( $feed->id );

        wp_send_json_success( __( 'Product feed has been refreshed.', 'woo-product-feed-pro' ) );
    }

    /**
     * Delete product feed.
     *
     * @since 13.3.5
     * @access public
     *
     * @return void
     */
    public function ajax_delete_product_feed() {
        if ( ! wp_verify_nonce( $_REQUEST['security'], 'woosea_ajax_nonce' ) ) {
            wp_send_json_error( __( 'Invalid security token', 'woo-product-feed-pro' ) );
        }

        if ( ! Product_Feed_Helper::is_current_user_allowed() ) {
            wp_send_json_error( __( 'You do not have permission to manage product feed.', 'woo-product-feed-pro' ) );
        }

        $project_hash = sanitize_text_field( $_POST['project_hash'] );

        $feed = new Product_Feed( $project_hash );
        if ( ! $feed->id ) {
            wp_send_json_error( __( 'Product feed not found.', 'woo-product-feed-pro' ) );
        }

        do_action( 'adt_before_delete_product_feed', $feed );

        $feed->delete();

        do_action( 'adt_after_delete_product_feed', $feed );

        wp_send_json_success( __( 'Product feed has been deleted.', 'woo-product-feed-pro' ) );
    }

    /**
     * Run the class
     *
     * @codeCoverageIgnore
     * @since 13.3.3
     */
    public function run() {
        add_action( 'adt_create_product_feed', array( $this, 'create_product_feed' ), 10, 2 );
        add_action( 'adt_edit_product_feed', array( $this, 'edit_product_feed' ), 10, 2 );
        add_action( 'adt_after_product_feed_generation', array( $this, 'update_product_feed' ), 10, 2 );

        // AJAX actions.
        add_action( 'wp_ajax_woosea_project_status', array( $this, 'ajax_update_product_feed_status' ) );
        add_action( 'wp_ajax_woosea_project_copy', array( $this, 'ajax_clone_product_feed' ) );
        add_action( 'wp_ajax_woosea_project_cancel', array( $this, 'ajax_cancel_product_feed' ) );
        add_action( 'wp_ajax_woosea_project_refresh', array( $this, 'ajax_refresh_product_feed' ) );
        add_action( 'wp_ajax_woosea_project_delete', array( $this, 'ajax_delete_product_feed' ) );
    }
}
