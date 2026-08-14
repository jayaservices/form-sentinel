<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Privacy {
	private Form_Sentinel_Repository $repository;

	public function __construct( Form_Sentinel_Repository $repository ) {
		$this->repository = $repository;
	}

	public function register_hooks(): void {
		add_filter( 'wp_privacy_personal_data_exporters', array( $this, 'register_exporter' ) );
		add_filter( 'wp_privacy_personal_data_erasers', array( $this, 'register_eraser' ) );
	}

	public function register_exporter( array $exporters ): array {
		$exporters['form-sentinel'] = array(
			'exporter_friendly_name' => __( 'Form Sentinel submissions', 'form-sentinel' ),
			'callback' => array( $this, 'export' ),
		);
		return $exporters;
	}

	public function register_eraser( array $erasers ): array {
		$erasers['form-sentinel'] = array(
			'eraser_friendly_name' => __( 'Form Sentinel submissions', 'form-sentinel' ),
			'callback' => array( $this, 'erase' ),
		);
		return $erasers;
	}

	public function export( string $email_address, int $page = 1 ): array {
		if ( $page > 1 ) {
			return array( 'data' => array(), 'done' => true );
		}
		$data = array();
		foreach ( $this->repository->find_by_email( $email_address ) as $event ) {
			$data[] = array(
				'group_id' => 'form-sentinel-submissions',
				'group_label' => __( 'Form Sentinel submissions', 'form-sentinel' ),
				'item_id' => 'form-sentinel-' . $event->id,
				'data' => array(
					array( 'name' => __( 'Form', 'form-sentinel' ), 'value' => $event->form_title ),
					array( 'name' => __( 'Submitted', 'form-sentinel' ), 'value' => $event->submitted_at ),
					array( 'name' => __( 'Email status', 'form-sentinel' ), 'value' => $event->mail_status ),
					array( 'name' => __( 'Saved fields', 'form-sentinel' ), 'value' => wp_json_encode( json_decode( $event->payload, true ), JSON_UNESCAPED_UNICODE ) ),
				),
			);
		}
		return array( 'data' => $data, 'done' => true );
	}

	public function erase( string $email_address, int $page = 1 ): array {
		if ( $page > 1 ) {
			return array( 'items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true );
		}
		$ids = array_map( static fn( $event ) => (int) $event->id, $this->repository->find_by_email( $email_address ) );
		$removed = $this->repository->delete( $ids );
		return array(
			'items_removed' => $removed > 0,
			'items_retained' => false,
			'messages' => array(),
			'done' => true,
		);
	}
}
