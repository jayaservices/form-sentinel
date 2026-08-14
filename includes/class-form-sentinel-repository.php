<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Repository {
	private wpdb $wpdb;
	private string $table;

	public function __construct( wpdb $wpdb ) {
		$this->wpdb  = $wpdb;
		$this->table = $wpdb->prefix . 'form_sentinel_events';
	}

	public function insert( array $event ): int {
		$now = current_time( 'mysql', true );
		$timeline = array(
			array(
				'status' => 'received',
				'at'     => $now,
				'message' => __( 'Form Sentinel received the Contact Form 7 submission.', 'form-sentinel' ),
			),
		);

		$inserted = $this->wpdb->insert(
			$this->table,
			array(
				'form_id'        => (int) ( $event['form_id'] ?? 0 ),
				'form_title'     => (string) ( $event['form_title'] ?? '' ),
				'page_url'       => (string) ( $event['page_url'] ?? '' ),
				'payload'        => wp_json_encode( $event['payload'] ?? array(), JSON_UNESCAPED_UNICODE ),
				'mail_status'    => 'received',
				'mail_recipient' => '',
				'error_code'     => '',
				'error_message'  => '',
				'timeline'       => wp_json_encode( $timeline, JSON_UNESCAPED_UNICODE ),
				'submitted_at'   => $now,
				'updated_at'     => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return false === $inserted ? 0 : (int) $this->wpdb->insert_id;
	}

	public function mark( int $id, string $status, array $context = array() ): bool {
		if ( $id < 1 ) {
			return false;
		}

		$event = $this->find( $id );
		if ( ! $event ) {
			return false;
		}

		$now = current_time( 'mysql', true );
		$data    = array(
			'mail_status' => $status,
			'updated_at'  => $now,
		);
		$formats = array( '%s', '%s' );

		foreach ( array( 'recipient' => 'mail_recipient', 'error_code' => 'error_code', 'error_message' => 'error_message' ) as $context_key => $column ) {
			if ( array_key_exists( $context_key, $context ) ) {
				$data[ $column ] = (string) $context[ $context_key ];
				$formats[]       = '%s';
			}
		}

		$timeline   = json_decode( (string) $event->timeline, true );
		$timeline   = is_array( $timeline ) ? $timeline : array();
		$message    = (string) ( $context['error_message'] ?? '' );
		$last_event = end( $timeline );
		if ( ! is_array( $last_event ) || $status !== ( $last_event['status'] ?? '' ) || $message !== ( $last_event['message'] ?? '' ) ) {
			$timeline[] = array( 'status' => $status, 'at' => $now, 'message' => $message );
		}
		$data['timeline'] = wp_json_encode( $timeline, JSON_UNESCAPED_UNICODE );
		$formats[] = '%s';

		$updated = $this->wpdb->update(
			$this->table,
			$data,
			array( 'id' => $id ),
			$formats,
			array( '%d' )
		);

		return false !== $updated;
	}

	public function find( int $id ): ?object {
		$sql = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id );
		$row = $this->wpdb->get_row( $sql );

		return $row ?: null;
	}

	public function query( array $filters, int $page = 1, int $per_page = 20 ): array {
		$where  = array( '1 = 1' );
		$params = array();

		if ( ! empty( $filters['status'] ) ) {
			$where[]  = 'mail_status = %s';
			$params[] = $filters['status'];
		}

		if ( ! empty( $filters['form_id'] ) ) {
			$where[]  = 'form_id = %d';
			$params[] = (int) $filters['form_id'];
		}

		$where_sql = implode( ' AND ', $where );
		$count_sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where_sql}";
		$list_sql  = "SELECT * FROM {$this->table} WHERE {$where_sql} ORDER BY submitted_at DESC, id DESC LIMIT %d OFFSET %d";
		$offset    = max( 0, ( $page - 1 ) * $per_page );

		$count_query = $params ? $this->wpdb->prepare( $count_sql, ...$params ) : $count_sql;
		$list_params = array_merge( $params, array( $per_page, $offset ) );
		$list_query  = $this->wpdb->prepare( $list_sql, ...$list_params );

		return array(
			'items' => $this->wpdb->get_results( $list_query ),
			'total' => (int) $this->wpdb->get_var( $count_query ),
		);
	}

	public function count_by_status(): array {
		$rows   = $this->wpdb->get_results( "SELECT mail_status, COUNT(*) AS total FROM {$this->table} GROUP BY mail_status", ARRAY_A );
		$counts = array();

		foreach ( $rows as $row ) {
			$counts[ $row['mail_status'] ] = (int) $row['total'];
		}

		return $counts;
	}

	public function delete( array $ids ): int {
		$ids = array_values( array_filter( array_map( 'absint', $ids ) ) );
		if ( ! $ids ) {
			return 0;
		}

		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );
		$sql          = $this->wpdb->prepare( "DELETE FROM {$this->table} WHERE id IN ({$placeholders})", ...$ids );
		$result       = $this->wpdb->query( $sql );

		return false === $result ? 0 : (int) $result;
	}

	public function all( array $filters = array() ): array {
		return $this->query( $filters, 1, 5000 )['items'];
	}

	public function find_by_email( string $email, int $limit = 500 ): array {
		$email = strtolower( sanitize_email( $email ) );
		if ( '' === $email ) {
			return array();
		}

		$like = '%' . $this->wpdb->esc_like( $email ) . '%';
		$sql  = $this->wpdb->prepare( "SELECT * FROM {$this->table} WHERE payload LIKE %s ORDER BY submitted_at DESC LIMIT %d", $like, $limit );

		return $this->wpdb->get_results( $sql );
	}

	public function delete_older_than( int $days ): int {
		$days      = max( 1, $days );
		$threshold = gmdate( 'Y-m-d H:i:s', time() - ( DAY_IN_SECONDS * $days ) );
		$sql       = $this->wpdb->prepare( "DELETE FROM {$this->table} WHERE submitted_at < %s", $threshold );
		$result    = $this->wpdb->query( $sql );

		return false === $result ? 0 : (int) $result;
	}
}
