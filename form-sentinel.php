<?php
/**
 * Plugin Name:       Form Sentinel
 * Description:       Records Contact Form 7 submissions and shows whether WordPress accepted or rejected the related email.
 * Version:           0.1.0-alpha
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * Requires Plugins:  contact-form-7
 * Author:            Yassine Boumehdi
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       form-sentinel
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'FORM_SENTINEL_VERSION', '0.1.0-alpha' );
define( 'FORM_SENTINEL_FILE', __FILE__ );
define( 'FORM_SENTINEL_PATH', plugin_dir_path( __FILE__ ) );
define( 'FORM_SENTINEL_URL', plugin_dir_url( __FILE__ ) );

require_once FORM_SENTINEL_PATH . 'includes/class-form-sentinel-installer.php';
require_once FORM_SENTINEL_PATH . 'includes/class-form-sentinel-repository.php';
require_once FORM_SENTINEL_PATH . 'includes/class-form-sentinel-tracker.php';
require_once FORM_SENTINEL_PATH . 'includes/class-form-sentinel-admin.php';
require_once FORM_SENTINEL_PATH . 'includes/class-form-sentinel-plugin.php';

register_activation_hook( __FILE__, array( 'Form_Sentinel_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Form_Sentinel_Installer', 'deactivate' ) );

Form_Sentinel_Plugin::instance()->boot();
