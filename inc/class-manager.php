<?php
namespace SimplyUmami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Manager
 *
 * @since 0.1.0
 */
class Manager {

	/**
	 * Script data attributes indexed by enqueue handle.
	 *
	 * @var array
	 */
	private $script_attributes = array();


	/**
	 * Manager constructor.
	 *
	 * @since 0.1.0
	 * @change 0.6.0 - Add filter for comment form submit button.
	 */
	public function __construct() {
		$options = Options::get_options();
		if ( $options['enabled'] && isset( $options['script_url'] ) && isset( $options['website_id'] ) && ! is_admin() ) {
			if ( ! empty( $options['website_id'] ) && ! empty( $options['script_url'] ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_filter( 'script_loader_tag', array( $this, 'filter_script_tag' ), 10, 2 );
			}
		}

		new DashboardWidget();
	}



	/**
	 * Enqueue the current Umami tracker and optional heatmap/replay recorder.
	 */
	public function enqueue_scripts() {
		$options = Options::get_options();
		if ( ! $options['enabled'] || ( $options['ignore_admins'] === 1 && current_user_can( 'manage_options' ) ) ) {
			return;
		}
		if ( ! apply_filters( 'simply_umami_tracking_allowed', true, $options ) ) {
			return;
		}

		$script_url = trim( $options['script_url'] ?? '' );
		$website_id = trim( $options['website_id'] ?? '' );
		if ( '' === $script_url || '' === $website_id ) {
			return;
		}

		$attrs = array(
			'data-website-id'        => $website_id,
			'data-fetch-credentials' => $options['fetch_credentials'],
		);
		foreach ( array( 'tag', 'domains', 'before_send', 'distinct_id' ) as $key ) {
			if ( ! empty( $options[ $key ] ) ) {
				$attrs[ 'data-' . str_replace( '_', '-', $key ) ] = trim( $options[ $key ] );
			}
		}
		foreach ( array( 'do_not_track', 'exclude_search', 'exclude_hash', 'performance' ) as $key ) {
			if ( ! empty( $options[ $key ] ) ) {
				$attrs[ 'data-' . str_replace( '_', '-', $key ) ] = 'true';
			}
		}
		foreach ( array( 'auto_track', 'auto_pageview' ) as $key ) {
			if ( empty( $options[ $key ] ) ) {
				$attrs[ 'data-' . str_replace( '_', '-', $key ) ] = 'false';
			}
		}
		if ( ! empty( $options['host_url'] ) && ! empty( $options['use_host_url'] ) ) {
			$attrs['data-host-url'] = trim( $options['host_url'] );
		}
		$attrs = apply_filters( 'simply_umami_tracker_attributes', $attrs, $options );

		$this->script_attributes['simply-umami-tracker'] = $attrs;
		// Service-managed URLs must retain their own query parameters and cache policy.
		wp_enqueue_script( 'simply-umami-tracker', $script_url, array(), null, true );
		if ( apply_filters( 'simply_umami_recorder_enabled', (bool) $options['recorder_enabled'], $options ) ) {
			$recorder_url = trim( $options['recorder_url'] );
			if ( '' === $recorder_url ) {
				$recorder_url = Options::get_script_base_url( $script_url ) . '/recorder.js';
			}
			$this->script_attributes['simply-umami-recorder'] = array(
				'data-website-id' => $attrs['data-website-id'],
			);
			if ( ! empty( $attrs['data-host-url'] ) ) {
				$this->script_attributes['simply-umami-recorder']['data-host-url'] = $attrs['data-host-url'];
			}
			wp_enqueue_script( 'simply-umami-recorder', $recorder_url, array( 'simply-umami-tracker' ), null, true );
		}
		if ( ! empty( $options['track_comments'] ) ) {
			wp_enqueue_script( 'simply-umami-comments', plugin_dir_url( SIMPLY_UMAMI_BASE_FILE ) . 'js/comments.js', array( 'simply-umami-tracker' ), SIMPLY_UMAMI_VERSION, true );
		}
	}

	/**
	 * Add data attributes while preserving other script-tag filters.
	 *
	 * Defer is added here to support WordPress versions before 6.3.
	 *
	 * @param string $tag Existing script tag.
	 * @param string $handle Script enqueue handle.
	 * @return string Filtered script tag.
	 */
	public function filter_script_tag( string $tag, string $handle ): string {
		if ( ! isset( $this->script_attributes[ $handle ] ) ) {
			return $tag;
		}
		$attributes = '';
		foreach ( $this->script_attributes[ $handle ] as $name => $value ) {
			if ( preg_match( '/^data-[a-z0-9-]+$/D', $name ) && is_scalar( $value ) ) {
				$attributes .= ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
			}
		}
		return str_replace( '<script ', '<script defer' . $attributes . ' ', $tag );
	}
}
