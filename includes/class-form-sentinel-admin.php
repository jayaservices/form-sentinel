<?php

defined( 'ABSPATH' ) || exit;

final class Form_Sentinel_Admin {
	private Form_Sentinel_Repository $repository;

	public function __construct( Form_Sentinel_Repository $repository ) {
		$this->repository = $repository;
	}

	public function register_hooks(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_form_sentinel_save_settings', array( $this, 'save_settings' ) );
	}

	public function register_menu(): void {
		add_menu_page(
			__( 'Form Sentinel', 'form-sentinel' ),
			__( 'Form Sentinel', 'form-sentinel' ),
			'manage_options',
			'form-sentinel',
			array( $this, 'render_page' ),
			'dashicons-shield-alt',
			58
		);
	}

	public function enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_form-sentinel' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'form-sentinel-admin', FORM_SENTINEL_URL . 'assets/admin.css', array(), FORM_SENTINEL_VERSION );
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to access this page.', 'form-sentinel' ) );
		}

		if ( isset( $_GET['event'] ) ) {
			$this->render_detail( absint( $_GET['event'] ) );
			return;
		}

		$status   = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$form_id  = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		$page     = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$result   = $this->repository->query( array( 'status' => $status, 'form_id' => $form_id ), $page );
		$counts   = $this->repository->count_by_status();
		$base_url = admin_url( 'admin.php?page=form-sentinel' );
		?>
		<div class="wrap form-sentinel-wrap">
			<h1><?php esc_html_e( 'Form Sentinel', 'form-sentinel' ); ?></h1>
			<p class="description"><?php esc_html_e( 'A technical success means WordPress accepted the email for sending. It does not prove inbox delivery.', 'form-sentinel' ); ?></p>

			<div class="form-sentinel-cards">
				<?php $this->render_card( __( 'Received', 'form-sentinel' ), array_sum( $counts ), 'neutral' ); ?>
				<?php $this->render_card( __( 'Accepted', 'form-sentinel' ), $counts['accepted'] ?? 0, 'success' ); ?>
				<?php $this->render_card( __( 'Failed', 'form-sentinel' ), $counts['failed'] ?? 0, 'danger' ); ?>
				<?php $this->render_card( __( 'Skipped', 'form-sentinel' ), $counts['skipped'] ?? 0, 'warning' ); ?>
			</div>

			<div class="form-sentinel-panel">
				<form method="get" class="form-sentinel-filters">
					<input type="hidden" name="page" value="form-sentinel">
					<label for="form-sentinel-status"><?php esc_html_e( 'Email status', 'form-sentinel' ); ?></label>
					<select id="form-sentinel-status" name="status">
						<option value=""><?php esc_html_e( 'All statuses', 'form-sentinel' ); ?></option>
						<?php foreach ( array( 'received', 'accepted', 'failed', 'skipped' ) as $option ) : ?>
							<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $status, $option ); ?>><?php echo esc_html( ucfirst( $option ) ); ?></option>
						<?php endforeach; ?>
					</select>
					<label for="form-sentinel-form-id"><?php esc_html_e( 'Form ID', 'form-sentinel' ); ?></label>
					<input id="form-sentinel-form-id" type="number" min="1" name="form_id" value="<?php echo esc_attr( $form_id ?: '' ); ?>">
					<button class="button"><?php esc_html_e( 'Filter', 'form-sentinel' ); ?></button>
					<a class="button-link" href="<?php echo esc_url( $base_url ); ?>"><?php esc_html_e( 'Reset', 'form-sentinel' ); ?></a>
				</form>

				<table class="widefat striped form-sentinel-table">
					<thead><tr>
						<th><?php esc_html_e( 'Date', 'form-sentinel' ); ?></th>
						<th><?php esc_html_e( 'Form', 'form-sentinel' ); ?></th>
						<th><?php esc_html_e( 'Status', 'form-sentinel' ); ?></th>
						<th><?php esc_html_e( 'Recipient', 'form-sentinel' ); ?></th>
						<th><?php esc_html_e( 'Action', 'form-sentinel' ); ?></th>
					</tr></thead>
					<tbody>
					<?php if ( ! $result['items'] ) : ?>
						<tr><td colspan="5"><?php esc_html_e( 'No submissions recorded yet.', 'form-sentinel' ); ?></td></tr>
					<?php else : foreach ( $result['items'] as $item ) : ?>
						<tr>
							<td><?php echo esc_html( get_date_from_gmt( $item->submitted_at, 'Y-m-d H:i:s' ) ); ?></td>
							<td><?php echo esc_html( $item->form_title ?: '#' . $item->form_id ); ?></td>
							<td><span class="form-sentinel-status status-<?php echo esc_attr( $item->mail_status ); ?>"><?php echo esc_html( $item->mail_status ); ?></span></td>
							<td><?php echo esc_html( $item->mail_recipient ?: '—' ); ?></td>
							<td><a href="<?php echo esc_url( add_query_arg( 'event', $item->id, $base_url ) ); ?>"><?php esc_html_e( 'View', 'form-sentinel' ); ?></a></td>
						</tr>
					<?php endforeach; endif; ?>
					</tbody>
				</table>
				<?php
				$total_pages = (int) ceil( $result['total'] / 20 );
				if ( $total_pages > 1 ) {
					echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => $page, 'total' => $total_pages ) ) );
				}
				?>
			</div>

			<div class="form-sentinel-panel">
				<h2><?php esc_html_e( 'Data retention', 'form-sentinel' ); ?></h2>
				<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
					<input type="hidden" name="action" value="form_sentinel_save_settings">
					<?php wp_nonce_field( 'form_sentinel_save_settings' ); ?>
					<label for="form-sentinel-retention"><?php esc_html_e( 'Delete submissions after', 'form-sentinel' ); ?></label>
					<input id="form-sentinel-retention" type="number" min="1" max="365" name="retention_days" value="<?php echo esc_attr( get_option( 'form_sentinel_retention_days', 30 ) ); ?>">
					<?php esc_html_e( 'days', 'form-sentinel' ); ?>
					<?php submit_button( __( 'Save settings', 'form-sentinel' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	public function save_settings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to perform this action.', 'form-sentinel' ) );
		}

		check_admin_referer( 'form_sentinel_save_settings' );
		$days = isset( $_POST['retention_days'] ) ? absint( $_POST['retention_days'] ) : 30;
		update_option( 'form_sentinel_retention_days', min( 365, max( 1, $days ) ) );

		wp_safe_redirect( add_query_arg( 'settings-updated', '1', admin_url( 'admin.php?page=form-sentinel' ) ) );
		exit;
	}

	private function render_detail( int $id ): void {
		$event = $this->repository->find( $id );

		if ( ! $event ) {
			wp_die( esc_html__( 'Submission not found.', 'form-sentinel' ) );
		}

		$payload = json_decode( $event->payload, true );
		?>
		<div class="wrap form-sentinel-wrap">
			<h1><?php echo esc_html( sprintf( __( 'Submission #%d', 'form-sentinel' ), $event->id ) ); ?></h1>
			<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=form-sentinel' ) ); ?>">&larr; <?php esc_html_e( 'Back to journal', 'form-sentinel' ); ?></a></p>
			<div class="form-sentinel-panel">
				<dl class="form-sentinel-detail">
					<dt><?php esc_html_e( 'Form', 'form-sentinel' ); ?></dt><dd><?php echo esc_html( $event->form_title . ' (#' . $event->form_id . ')' ); ?></dd>
					<dt><?php esc_html_e( 'Status', 'form-sentinel' ); ?></dt><dd><?php echo esc_html( $event->mail_status ); ?></dd>
					<dt><?php esc_html_e( 'Page', 'form-sentinel' ); ?></dt><dd><?php echo esc_html( $event->page_url ?: '—' ); ?></dd>
					<dt><?php esc_html_e( 'Recipient', 'form-sentinel' ); ?></dt><dd><?php echo esc_html( $event->mail_recipient ?: '—' ); ?></dd>
					<dt><?php esc_html_e( 'Error', 'form-sentinel' ); ?></dt><dd><?php echo esc_html( trim( $event->error_code . ' ' . $event->error_message ) ?: '—' ); ?></dd>
				</dl>
				<h2><?php esc_html_e( 'Saved fields', 'form-sentinel' ); ?></h2>
				<table class="widefat striped"><tbody>
				<?php foreach ( is_array( $payload ) ? $payload : array() as $key => $value ) : ?>
					<tr><th><?php echo esc_html( $key ); ?></th><td><?php echo esc_html( is_array( $value ) ? implode( ', ', $value ) : $value ); ?></td></tr>
				<?php endforeach; ?>
				</tbody></table>
			</div>
		</div>
		<?php
	}

	private function render_card( string $label, int $count, string $type ): void {
		printf(
			'<div class="form-sentinel-card card-%1$s"><span>%2$s</span><strong>%3$d</strong></div>',
			esc_attr( $type ),
			esc_html( $label ),
			$count
		);
	}
}
