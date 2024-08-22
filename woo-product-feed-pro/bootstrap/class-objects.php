<?php
/**
 * Class objects instance list.
 *
 * @since   13.3.3
 * @package AdTribes\PFP
 */

use AdTribes\PFP\Classes\WP_Admin;
use AdTribes\PFP\Classes\Product_Feed_Admin;
use AdTribes\PFP\Classes\Cron;
use AdTribes\PFP\Classes\Heartbeat;
use AdTribes\PFP\Classes\Marketing;
use AdTribes\PFP\Post_Types\Product_Feed_Post_Type;

defined( 'ABSPATH' ) || exit;

return array(
    new Product_Feed_Admin(),
    new Cron(),
    new Heartbeat(),
    new Product_Feed_Post_Type(),
    new WP_Admin(),
    new Marketing(),
);
