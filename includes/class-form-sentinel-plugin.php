<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Plugin {
	private static ?self $instance = null;
	private ?Form_Sentinel_Repository $repository = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function boot(): void {
		add_action( 'plugins_loaded', array( $this, 'load' ) );
		add_action( Form_Sentinel_Installer::CLEANUP_HOOK, array( $this, 'cleanup' ) );
	}

	public function load(): void {
		load_plugin_textdomain( 'form-sentinel', false, dirname( plugin_basename( FORM_SENTINEL_FILE ) ) . '/languages' );

		global $wpdb;
		Form_Sentinel_Installer::maybe_upgrade();
		$this->repository = new Form_Sentinel_Repository( $wpdb );

		$privacy = new Form_Sentinel_Privacy( $this->repository );
		$privacy->register_hooks();

		if ( class_exists( 'WPCF7_ContactForm' ) ) {
			$tracker = new Form_Sentinel_Tracker( $this->repository );
			$tracker->register_hooks();
		} else {
			add_action( 'admin_notices', array( $this, 'missing_dependency_notice' ) );
		}

		if ( is_admin() ) {
			$admin = new Form_Sentinel_Admin( $this->repository );
			$admin->register_hooks();
		}
	}

	public function cleanup(): void {
		if ( ! $this->repository ) {
			global $wpdb;
			$this->repository = new Form_Sentinel_Repository( $wpdb );
		}

		$days = max( 1, (int) get_option( 'form_sentinel_retention_days', 30 ) );
		$this->repository->delete_older_than( $days );
	}

	public function missing_dependency_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'Form Sentinel requires Contact Form 7 to record submissions.', 'form-sentinel' );
		echo '</p></div>';
	}
}
