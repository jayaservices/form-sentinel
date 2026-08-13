<?php

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}form_sentinel_events" );
delete_option( 'form_sentinel_db_version' );
delete_option( 'form_sentinel_retention_days' );

$timestamp = wp_next_scheduled( 'form_sentinel_daily_cleanup' );
if ( $timestamp ) {
	wp_unschedule_event( $timestamp, 'form_sentinel_daily_cleanup' );
}
