<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Installer {
	public const DB_VERSION = '1';
	public const CLEANUP_HOOK = 'form_sentinel_daily_cleanup';

	public static function activate(): void {
		self::create_table();

		add_option( 'form_sentinel_db_version', self::DB_VERSION );
		add_option( 'form_sentinel_retention_days', 30 );

		if ( ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CLEANUP_HOOK );
		}
	}

	public static function deactivate(): void {
		$timestamp = wp_next_scheduled( self::CLEANUP_HOOK );

		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CLEANUP_HOOK );
		}
	}

	private static function create_table(): void {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name      = $wpdb->prefix . 'form_sentinel_events';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			form_id bigint(20) unsigned NOT NULL DEFAULT 0,
			form_title varchar(255) NOT NULL DEFAULT '',
			page_url text NULL,
			payload longtext NULL,
			mail_status varchar(20) NOT NULL DEFAULT 'received',
			mail_recipient text NULL,
			error_code varchar(100) NULL,
			error_message text NULL,
			submitted_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id),
			KEY mail_status (mail_status),
			KEY submitted_at (submitted_at)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
