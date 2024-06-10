<?php
/**
 * Author: Rymera Web Co
 *
 * @package AdTribes\PFP\Factories
 */

namespace AdTribes\PFP\Factories;

use AdTribes\PFP\Abstracts\Abstract_Class;

/**
 * Class Admin_Notice
 */
class Admin_Notice extends Abstract_Class {

    /**
     * Holds the admin notice message.
     *
     * @since 13.3.3
     * @access protected
     *
     * @var string The admin notice message.
     */
    protected $message;

    /**
     * Holds the admin notice type.
     *
     * @since 13.3.3
     * @access protected
     *
     * @var string The admin notice type.
     */
    protected $type;

    /**
     * Holds the type of message. Either 'string' or 'html'.
     *
     * @since 13.3.3
     * @access protected
     *
     * @var string The message type.
     */
    protected string $message_type;

    /**
     * Constructor.
     *
     * @since 13.3.3
     * @access public
     *
     * @param string $message      The admin notice message.
     * @param string $type         The admin notice type.
     * @param string $message_type string The message type. Either 'string' or 'html'.
     */
    public function __construct( $message, $type = 'error', $message_type = 'string' ) {

        $this->message      = $message;
        $this->type         = $type;
        $this->message_type = $message_type;
    }

    /**
     * Run the class.
     *
     * @since 13.3.3
     * @access public
     */
    public function run() {

        if ( did_action( 'admin_notices' ) ) {
            $this->add_notice();
        } else {
            add_action( 'admin_notices', array( $this, 'add_notice' ) );
        }
    }

    /**
     * Renders admin notice.
     *
     * @since 13.3.3
     * @access public
     */
    public function add_notice() {

        $message_id   = 'woo-sea-' . md5( $this->message );
        $type         = $this->type;
        $message_type = $this->message_type;
        $message      = $this->message;

        include WOOCOMMERCESEA_VIEWS_ROOT_PATH . 'notices/view-admin-notice.php';
    }
}
