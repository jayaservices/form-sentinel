<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Mail_Test {
	/** @return array{success: bool, recipient: string, message: string} */
	public function send_to_current_administrator(): array {
		$user = wp_get_current_user();
		$recipient = $user && $user->user_email ? sanitize_email( $user->user_email ) : '';
		if ( ! is_email( $recipient ) ) {
			return array( 'success' => false, 'recipient' => '', 'message' => __( 'The current administrator account has no valid email address.', 'form-sentinel' ) );
		}

		$subject = sprintf( __( '[%s] Form Sentinel email test', 'form-sentinel' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) );
		$message = __( "This is a technical mail test sent by Form Sentinel. No form submission or customer data was created.\n\nReceiving this email confirms that the configured WordPress mail transport accepted this test; it does not validate a specific Contact Form 7 form.", 'form-sentinel' );
		$sent = wp_mail( $recipient, $subject, $message, array( 'Content-Type: text/plain; charset=UTF-8', 'X-Form-Sentinel-Test: 1' ) );

		return array(
			'success' => (bool) $sent,
			'recipient' => $recipient,
			'message' => $sent ? __( 'WordPress accepted the technical test email for sending.', 'form-sentinel' ) : __( 'WordPress rejected the technical test email. Check the mail plugin and PHP error log.', 'form-sentinel' ),
		);
	}
}
