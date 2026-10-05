<?php

namespace SimplyUmami;

/**
 * Class Widget
 *
 * @since 0.8.0
 */
class DashboardWidget {

	/**
	 * Widget constructor.
	 *
	 * @since 0.8.0
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$options = Options::get_options();
		if ( ! $options['enabled'] ) {
			return;
		}

		add_action( 'wp_dashboard_setup', array( $this, 'add_widget' ) );
	}

	/**
	 * Add the widget to the dashboard.
	 *
	 * @since 0.8.0
	 */
	public function add_widget() {
		wp_add_dashboard_widget(
			'umami_widget',
			'Umami Analytics',
			array( $this, 'render_widget' )
		);
	}

	/**
	 * Render the widget.
	 *
	 * @since 0.8.0
	 * @change 0.8.1 - Path changed in umami.
	 * @change 0.9.0 - Show live stats via API when configured.
	 */
	public function render_widget() {
		$options    = Options::get_options();
		$website_id = $options['website_id'];
		$host_url   = $options['host_url'];
		$script_url = $options['script_url'];

		// Check if the settings are configured.
		if ( empty( $website_id ) || empty( $script_url ) ) {
			echo wp_kses(
				__( 'Please configure the Umami Analytics settings.', 'simply-umami' ),
				array( 'p' => array() )
			);
			return;
		}

		// Try to fetch live stats via API.
		$this->render_api_stats( $options );

		// Always show the link to Umami.
		if ( empty( $host_url ) ) {
			$host_url = Options::get_script_base_url( $script_url );
		}
		$url = $host_url . '/websites/' . $website_id;
		echo '<p>';
		echo wp_kses(
			// translators: %s => Umami Admin page URL.
			sprintf( __( 'View full analytics on <a href="%s" target="_blank">Umami Analytics</a>', 'simply-umami' ), esc_url( $url ) ),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
			)
		);
		echo '</p>';
	}

	/**
	 * Render API-powered stats if credentials are configured.
	 *
	 * @param array $options Plugin options.
	 *
	 * @since 0.9.0
	 */
	private function render_api_stats( array $options ) {
		$has_api_key   = ! empty( $options['api_key'] );
		$has_api_creds = ! empty( $options['api_username'] ) && ! empty( $options['api_password'] );

		if ( ! $has_api_key && ! $has_api_creds ) {
			return;
		}

		// Use a transient to cache stats for 5 minutes.
		$cache_key = 'simply_umami_stats_' . md5( $options['website_id'] );
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			$this->render_stats_html( $cached );
			return;
		}

		$client = ApiClient::from_options();
		if ( null === $client ) {
			return;
		}

		// For self-hosted, login first.
		if ( ! $client->is_cloud() && $has_api_creds ) {
			if ( ! $client->login() ) {
				echo '<p><em>' . esc_html__( 'Could not connect to Umami API. Check your credentials.', 'simply-umami' ) . '</em></p>';
				return;
			}
		}

		// Fetch last 30 days of stats.
		$end_date   = (string) round( microtime( true ) * 1000 );
		$start_date = (string) ( (int) $end_date - 30 * DAY_IN_SECONDS * 1000 );
		$stats      = $client->get_stats( $start_date, $end_date );
		$active     = $client->get_active_visitors();

		if ( null === $stats ) {
			echo '<p><em>' . esc_html__( 'Could not fetch analytics data.', 'simply-umami' ) . '</em></p>';
			return;
		}

		$data = array(
			'pageviews' => (int) ( is_array( $stats['pageviews'] ?? null ) ? ( $stats['pageviews']['value'] ?? 0 ) : ( $stats['pageviews'] ?? 0 ) ),
			'visitors'  => (int) ( is_array( $stats['visitors'] ?? null ) ? ( $stats['visitors']['value'] ?? 0 ) : ( $stats['visitors'] ?? 0 ) ),
			'visits'    => (int) ( is_array( $stats['visits'] ?? null ) ? ( $stats['visits']['value'] ?? 0 ) : ( $stats['visits'] ?? 0 ) ),
			'bounces'   => (int) ( is_array( $stats['bounces'] ?? null ) ? ( $stats['bounces']['value'] ?? 0 ) : ( $stats['bounces'] ?? 0 ) ),
			'active'    => $active ?? 0,
		);

		set_transient( $cache_key, $data, 5 * MINUTE_IN_SECONDS );
		$this->render_stats_html( $data );
	}

	/**
	 * Output the stats HTML.
	 *
	 * @param array $data Stats data array.
	 *
	 * @since 0.9.0
	 */
	private function render_stats_html( array $data ) {
		?>
		<div class="simply-umami-stats" style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:12px;">
			<div style="text-align:center;padding:8px;background:#f0f0f1;border-radius:4px;">
				<div style="font-size:24px;font-weight:600;color:#1d2327;"><?php echo esc_html( number_format_i18n( $data['pageviews'] ) ); ?></div>
				<div style="font-size:12px;color:#50575e;"><?php esc_html_e( 'Pageviews', 'simply-umami' ); ?></div>
			</div>
			<div style="text-align:center;padding:8px;background:#f0f0f1;border-radius:4px;">
				<div style="font-size:24px;font-weight:600;color:#1d2327;"><?php echo esc_html( number_format_i18n( $data['visitors'] ) ); ?></div>
				<div style="font-size:12px;color:#50575e;"><?php esc_html_e( 'Visitors', 'simply-umami' ); ?></div>
			</div>
			<div style="text-align:center;padding:8px;background:#f0f0f1;border-radius:4px;">
				<div style="font-size:24px;font-weight:600;color:#2271b1;"><?php echo esc_html( number_format_i18n( $data['active'] ) ); ?></div>
				<div style="font-size:12px;color:#50575e;"><?php esc_html_e( 'Active Now', 'simply-umami' ); ?></div>
			</div>
		</div>
		<p style="font-size:12px;color:#787c82;margin:0 0 8px;">
			<?php
			echo esc_html(
				sprintf(
					// translators: %1$s visits, %2$s bounces.
					__( '%1$s visits, %2$s bounces in the last 30 days', 'simply-umami' ),
					number_format_i18n( $data['visits'] ),
					number_format_i18n( $data['bounces'] )
				)
			);
			?>
		</p>
		<?php
	}
}
