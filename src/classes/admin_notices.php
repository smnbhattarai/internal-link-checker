<?php

namespace ROCKET_WP_CRAWLER;

class Rocket_Wpc_Admin_Notice {
	/**
	 * Allowed values for message type are error, info, success, warning
	 *
	 * @var $message_type
	 */
	public $message_type;

	/**
	 * Message to be displayed to user
	 *
	 * @var $message
	 */
	public $message;

	/**
	 * Prepare class with necessary construct.
	 *
	 * @param string $message Message to be displayed to user.
	 * @param string $message_type message type are error, info, success, warning.
	 */
	public function __construct( $message, $message_type = 'info' ) {
		$this->message_type = $message_type;
		$this->message      = $message;

		add_action( 'admin_notices', array( $this, 'render_message' ) );
	}

	/**
	 * Form admin notices to rendering
	 */
	public function render_message() {
		echo '<div class="notice notice-' . esc_html( $this->message_type ) . ' is-dismissible">
			  <p><strong>' . esc_html( strtoupper( $this->message_type ) ) . ':</strong> ' . esc_html( $this->message ) . '</p>
			  </div>';
	}
}
