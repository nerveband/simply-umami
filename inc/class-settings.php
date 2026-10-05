<?php
namespace SimplyUmami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 *
 * @since 0.1.0
 */
class Settings {

	/**
	 * Settings constructor.
	 *
	 * @since 0.1.0
	 * @change 0.3.3 Fix an issue with hook calls.
	 * @change 0.5.0 Added plugin action links.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'register_styles' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
			add_action( 'admin_menu', array( $this, 'add_page' ) );
		}
		add_action( 'plugin_action_links_simply-umami/simply-umami.php', array( $this, 'plugin_actions' ) );
	}

	/**
	 * Add plugin actions.
	 *
	 * @param array $links Current link values.
	 *
	 * @since 0.5.0
	 *
	 * @return array Manipulated array of links.
	 */
	public function plugin_actions( array $links ): array {
		$url = esc_url(
			add_query_arg(
				'page',
				'simply-umami',
				get_admin_url() . 'options-general.php'
			)
		);

		$settings_link = "<a href='{$url}'>" . __( 'Settings', 'simply-umami' ) . '</a>';

		$links[] = $settings_link;

		return $links;
	}

	/**
	 * Register styles.
	 *
	 * @since 0.4.0
	 */
	public function register_styles() {
		wp_register_style(
			'simply-umami-styles',
			plugins_url( 'css/simply-umami.css', SIMPLY_UMAMI_BASE_FILE ),
			array(),
			SIMPLY_UMAMI_VERSION
		);
	}

	/**
	 * Enqueue styles.
	 *
	 * @since 0.4.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'simply-umami-styles' );
	}

	/**
	 * Register settings
	 *
	 * @since 0.1.0
	 * @change 0.6.1 - Changed the option group name.
	 */
	public function register_settings() {
		register_setting(
			'simply_umami',
			'simply_umami_options',
			array( $this, 'validate_options' )
		);
	}

	/**
	 * Add umami settings page.
	 *
	 * @since 0.1.0
	 * @change 0.4.0 - Changed page title.
	 * @change 0.5.0 Change page name to plugin slug.
	 */
	public function add_page() {
		$page = add_options_page(
			__( 'Simply Umami', 'simply-umami' ),
			__( 'Simply Umami', 'simply-umami' ),
			'manage_options',
			'simply-umami',
			array( $this, 'render_options_page' )
		);

		add_action( "admin_print_styles-{$page}", array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Option validation and sanitization.
	 *
	 * @param array $data The data to validate.
	 *
	 * @since 0.1.0
	 * @change 0.2.1
	 * @change 0.4.1 - Fix bug with host url option.
	 *
	 * @return array The validated data.
	 */
	public function validate_options( array $data ): array {
		if ( empty( $data ) ) {
			return array();
		}

		// Sanitize before_send: only allow valid JS identifier patterns.
		$before_send = sanitize_text_field( $data['before_send'] ?? '' );
		if ( '' !== $before_send && ! preg_match( '/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', $before_send ) ) {
			$before_send = '';
		}
		$credentials = $data['fetch_credentials'] ?? 'omit';
		if ( ! in_array( $credentials, array( 'omit', 'same-origin', 'include' ), true ) ) {
			$credentials = 'omit';
		}

		return array(
			'enabled'           => (int) ( $data['enabled'] ?? false ),
			'script_url'        => esc_url_raw( trim( $data['script_url'] ?? '' ) ),
			'website_id'        => sanitize_text_field( trim( $data['website_id'] ?? '' ) ),
			'host_url'          => esc_url_raw( trim( $data['host_url'] ?? '' ) ),
			'use_host_url'      => (int) ( $data['use_host_url'] ?? false ),
			'ignore_admins'     => (int) ( $data['ignore_admins'] ?? false ),
			'auto_track'        => (int) ( $data['auto_track'] ?? false ),
			'do_not_track'      => (int) ( $data['do_not_track'] ?? false ),
			'track_comments'    => (int) ( $data['track_comments'] ?? false ),
			'tag'               => sanitize_text_field( $data['tag'] ?? '' ),
			'domains'           => sanitize_text_field( $data['domains'] ?? '' ),
			'exclude_search'    => (int) ( $data['exclude_search'] ?? false ),
			'exclude_hash'      => (int) ( $data['exclude_hash'] ?? false ),
			'before_send'       => $before_send,
			'auto_pageview'     => (int) ( $data['auto_pageview'] ?? false ),
			'performance'       => (int) ( $data['performance'] ?? false ),
			'distinct_id'       => sanitize_text_field( trim( $data['distinct_id'] ?? '' ) ),
			'fetch_credentials' => $credentials,
			'recorder_enabled'  => (int) ( $data['recorder_enabled'] ?? false ),
			'recorder_url'      => esc_url_raw( trim( $data['recorder_url'] ?? '' ) ),
			'api_key'           => sanitize_text_field( $data['api_key'] ?? '' ),
			'api_username'      => sanitize_text_field( $data['api_username'] ?? '' ),
			'api_password'      => sanitize_text_field( $data['api_password'] ?? '' ),
		);
	}

	/**
	 * Render settings page.
	 *
	 * @since 0.1.0
	 * @change 0.4.0 - Changed page title.
	 */
	public function render_options_page() {
		$options = Options::get_options();
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
			<div class="wrap" id="simply-umami">
				<header class="simply-umami-header">
					<img src="<?php echo esc_url( plugins_url( 'css/icon.png', SIMPLY_UMAMI_BASE_FILE ) ); ?>" alt="" width="48" height="48" />
					<div>
						<h1><?php esc_html_e( 'Simply Umami', 'simply-umami' ); ?></h1>
						<p><?php esc_html_e( 'Tracking and dashboard settings', 'simply-umami' ); ?></p>
					</div>
					<span class="simply-umami-status">
						<?php
						if ( ! $options['enabled'] ) {
							esc_html_e( 'Tracking off', 'simply-umami' );
						} elseif ( empty( $options['script_url'] ) || empty( $options['website_id'] ) ) {
							esc_html_e( 'Setup incomplete', 'simply-umami' );
						} else {
							esc_html_e( 'Tracking configured', 'simply-umami' );
						}
						?>
					</span>
				</header>
				<hr class="wp-header-end" />
				<?php include 'templates/settings-page.php'; ?>
			</div>
		<?php
	}
}
