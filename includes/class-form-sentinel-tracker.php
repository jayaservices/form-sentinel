<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Tracker {
	private Form_Sentinel_Repository $repository;
	private int $active_event_id = 0;
	private string $active_status = '';

	public function __construct( Form_Sentinel_Repository $repository ) {
		$this->repository = $repository;
	}

	public function register_hooks(): void {
		add_action( 'wpcf7_before_send_mail', array( $this, 'capture_submission' ), 10, 3 );
		add_filter( 'wpcf7_skip_mail', array( $this, 'capture_skipped_mail' ), PHP_INT_MAX, 2 );
		add_action( 'wp_mail_succeeded', array( $this, 'capture_mail_success' ) );
		add_action( 'wp_mail_failed', array( $this, 'capture_mail_failure' ) );
		add_action( 'wpcf7_mail_sent', array( $this, 'finalize_success' ) );
		add_action( 'wpcf7_mail_failed', array( $this, 'finalize_failure' ) );
	}

	public function capture_submission( $contact_form, &$abort = false, $submission = null ): void {
		if ( ! $submission && class_exists( 'WPCF7_Submission' ) ) {
			$submission = WPCF7_Submission::get_instance();
		}

		if ( ! $submission || ! is_object( $contact_form ) ) {
			return;
		}

		$posted_data = method_exists( $submission, 'get_posted_data' ) ? $submission->get_posted_data() : array();
		$container   = method_exists( $submission, 'get_meta' ) ? $submission->get_meta( 'container_post_id' ) : 0;

		$this->active_event_id = $this->repository->insert(
			array(
				'form_id'    => method_exists( $contact_form, 'id' ) ? $contact_form->id() : 0,
				'form_title' => method_exists( $contact_form, 'title' ) ? $contact_form->title() : '',
				'page_url'   => $container ? get_permalink( (int) $container ) : wp_get_referer(),
				'payload'    => $this->sanitize_payload( is_array( $posted_data ) ? $posted_data : array() ),
			)
		);
		$this->active_status = $this->active_event_id ? 'received' : '';
	}

	public function capture_skipped_mail( $skip_mail, $contact_form ): bool {
		if ( $skip_mail && $this->active_event_id ) {
			$this->repository->mark(
				$this->active_event_id,
				'skipped',
				array( 'error_message' => __( 'Contact Form 7 skipped the email. Check demo mode, skip_mail, or another filter.', 'form-sentinel' ) )
			);
			$this->active_status = 'skipped';
		}

		return (bool) $skip_mail;
	}

	public function capture_mail_success( array $mail_data ): void {
		if ( ! $this->active_event_id ) {
			return;
		}

		$this->repository->mark(
			$this->active_event_id,
			'accepted',
			array( 'recipient' => $this->normalize_recipient( $mail_data['to'] ?? '' ) )
		);
		$this->active_status = 'accepted';
	}

	public function capture_mail_failure( WP_Error $error ): void {
		if ( ! $this->active_event_id ) {
			return;
		}

		$data = $error->get_error_data();

		$this->repository->mark(
			$this->active_event_id,
			'failed',
			array(
				'recipient'     => is_array( $data ) ? $this->normalize_recipient( $data['to'] ?? '' ) : '',
				'error_code'    => $error->get_error_code(),
				'error_message' => $error->get_error_message(),
			)
		);
		$this->active_status = 'failed';
	}

	public function finalize_success(): void {
		if ( $this->active_event_id && 'skipped' !== $this->active_status ) {
			$this->repository->mark( $this->active_event_id, 'accepted' );
		}

		$this->active_event_id = 0;
		$this->active_status   = '';
	}

	public function finalize_failure(): void {
		if ( $this->active_event_id ) {
			$this->repository->mark(
				$this->active_event_id,
				'failed',
				array( 'error_message' => __( 'Contact Form 7 reported an email sending failure.', 'form-sentinel' ) )
			);
			$this->active_event_id = 0;
			$this->active_status   = '';
		}
	}

	private function sanitize_payload( array $payload ): array {
		$masked = array();
		$deny   = (array) apply_filters(
			'form_sentinel_sensitive_field_patterns',
			array( 'password', 'passwd', 'pass', 'secret', 'token', 'card', 'credit', 'cvv', 'cvc' )
		);

		foreach ( $payload as $key => $value ) {
			$field_name = strtolower( (string) $key );
			$is_private = false;

			foreach ( $deny as $pattern ) {
				if ( '' !== $pattern && str_contains( $field_name, strtolower( (string) $pattern ) ) ) {
					$is_private = true;
					break;
				}
			}

			if ( $is_private ) {
				$masked[ $key ] = '[masked]';
				continue;
			}

			if ( is_array( $value ) ) {
				$masked[ $key ] = array_map( 'sanitize_text_field', $value );
			} else {
				$masked[ $key ] = sanitize_textarea_field( (string) $value );
			}
		}

		return $masked;
	}

	private function normalize_recipient( $recipient ): string {
		if ( is_array( $recipient ) ) {
			$recipient = implode( ', ', array_map( 'sanitize_email', $recipient ) );
		}

		return sanitize_text_field( (string) $recipient );
	}
}
