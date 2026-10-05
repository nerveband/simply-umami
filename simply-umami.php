<?php
/**
 * Plugin Name: Simply Umami
 * Description: Simple, privacy-focused Umami Analytics integration for WordPress.
 * Version: 1.1.1
 * Author: Ashraf Ali
 * Author URI: https://ashrafali.net
 * Plugin URI: https://github.com/nerveband/simply-umami
 * License: GPLv3 or later
 * Text Domain: simply-umami
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * Based on Integrate Umami by Ancocodet (https://github.com/Ancocodet/wp-umami).
 *
 * @package Simply Umami
 */

namespace SimplyUmami;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';

define( 'SIMPLY_UMAMI_VERSION', '1.1.1' );
define( 'SIMPLY_UMAMI_BASE_FILE', __FILE__ );

/**
 * Init plugin.
 *
 * @since 0.1.0
 */
function init() {
	new Manager();
	new Settings();
}

\add_action( 'plugins_loaded', 'SimplyUmami\init' );
