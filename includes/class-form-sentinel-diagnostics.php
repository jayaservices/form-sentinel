<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Diagnostics {
	/** @return array<int, array<string, mixed>> */
	public function scan(): array {
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return array();
		}

		$forms = WPCF7_ContactForm::find( array( 'posts_per_page' => -1 ) );
		$results = array();
		foreach ( $forms as $form ) {
			if ( is_object( $form ) && method_exists( $form, 'prop' ) ) {
				$results[] = $this->diagnose_form( $form );
			}
		}

		return $results;
	}

	/** @return array<string, mixed> */
	private function diagnose_form( object $form ): array {
		$mail = (array) $form->prop( 'mail' );
		$values = implode( "\n", array_filter( $mail, 'is_scalar' ) );
		$defined = $this->defined_tags( $form );
		$used = $this->mail_tags( $values );
		$issues = array();
		$sender = (string) ( $mail['sender'] ?? '' );
		$sender_email = $this->email_from_value( $sender );

		if ( '' === trim( $sender ) ) {
			$issues[] = array( 'level' => 'error', 'message' => __( 'The CF7 From field is empty.', 'form-sentinel' ) );
		} elseif ( '' === $sender_email && ! $this->mail_tags( $sender ) ) {
			$issues[] = array( 'level' => 'error', 'message' => __( 'The CF7 From field does not contain a valid email address or mail tag.', 'form-sentinel' ) );
		} elseif ( $sender_email && ! $this->is_local_domain( $sender_email ) ) {
			$issues[] = array( 'level' => 'warning', 'message' => sprintf( __( 'The CF7 From address uses the external domain %s. Use an address from this site domain to reduce spoofing and deliverability issues.', 'form-sentinel' ), substr( strrchr( $sender_email, '@' ), 1 ) ) );
		}

		foreach ( array_diff( $used, $defined, array( '_site_title', '_site_url', '_remote_ip', '_user_agent', '_url', '_post_id', '_post_name', '_post_title', '_post_url', '_post_author', '_post_author_email' ) ) as $tag ) {
			$issues[] = array( 'level' => 'error', 'message' => sprintf( __( 'Mail tag [%s] is used in the mail template but is not defined in this form.', 'form-sentinel' ), $tag ) );
		}

		foreach ( array_diff( $defined, $used ) as $tag ) {
			$issues[] = array( 'level' => 'info', 'message' => sprintf( __( 'Form field [%s] is not used in the mail template. This is informational; it may be intentional.', 'form-sentinel' ), $tag ) );
		}

		if ( $form->prop( 'demo_mode' ) ) {
			$issues[] = array( 'level' => 'warning', 'message' => __( 'CF7 demo mode is enabled: Form Sentinel will record submissions as skipped and CF7 will not send email.', 'form-sentinel' ) );
		}

		return array(
			'id' => (int) $form->id(),
			'title' => (string) $form->title(),
			'issues' => $issues,
		);
	}

	/** @return string[] */
	private function defined_tags( object $form ): array {
		if ( ! method_exists( $form, 'scan_form_tags' ) ) {
			return array();
		}
		$names = array();
		foreach ( (array) $form->scan_form_tags() as $tag ) {
			if ( is_object( $tag ) && ! empty( $tag->name ) ) {
				$names[] = (string) $tag->name;
			}
		}
		return array_values( array_unique( $names ) );
	}

	/** @return string[] */
	private function mail_tags( string $value ): array {
		preg_match_all( '/\[([a-zA-Z0-9_\-]+)(?:\s[^\]]*)?\]/', $value, $matches );
		return array_values( array_unique( $matches[1] ?? array() ) );
	}

	private function email_from_value( string $value ): string {
		if ( preg_match( '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $value, $matches ) ) {
			return sanitize_email( $matches[0] );
		}
		return '';
	}

	private function is_local_domain( string $email ): bool {
		$domain = strtolower( (string) substr( strrchr( $email, '@' ), 1 ) );
		$site_domain = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		return $domain === $site_domain || str_ends_with( $site_domain, '.' . $domain ) || str_ends_with( $domain, '.' . $site_domain );
	}
}
