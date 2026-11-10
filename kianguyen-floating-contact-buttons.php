<?php
/**
 * Plugin Name:       Kianguyen Floating Contact Buttons
 * Description:       Floating contact buttons for call, Zalo, Messenger, WhatsApp, Viber, Telegram, Facebook, TikTok, YouTube, Instagram, email, SMS and maps, with full-color logos, grouping, working hours and Google Tag Manager click tracking.
 * Version:           1.0.0
 * Requires at least: 5.5
 * Requires PHP:      7.4
 * Author:            kianguyen
 * Author URI:        https://xeisuzuvn.com
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kianguyen-floating-contact-buttons
 *
 * @package Kianguyen_Floating_Contact_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'XVFCB_VERSION', '1.0.0' );
define( 'XVFCB_FILE', __FILE__ );
define( 'XVFCB_DIR', plugin_dir_path( __FILE__ ) );
define( 'XVFCB_URL', plugin_dir_url( __FILE__ ) );
define( 'XVFCB_DONATE_URL', 'https://xeisuzuvn.com/donate/' );

/**
 * Version string for a bundled asset: plugin version plus the file's
 * modification time, so browsers and caches load a changed file right away.
 *
 * @param string $rel Path relative to the plugin folder.
 * @return string
 */
function xvfcb_asset_ver( $rel ) {
	$file = XVFCB_DIR . $rel;
	return file_exists( $file ) ? XVFCB_VERSION . '.' . filemtime( $file ) : XVFCB_VERSION;
}

require_once XVFCB_DIR . 'includes/class-xvfcb-settings.php';
require_once XVFCB_DIR . 'includes/class-xvfcb-i18n.php';
require_once XVFCB_DIR . 'includes/class-xvfcb-icons.php';
require_once XVFCB_DIR . 'includes/class-xvfcb-frontend.php';

XVFCB_I18n::init();
XVFCB_Frontend::init();

if ( is_admin() ) {
	require_once XVFCB_DIR . 'includes/class-xvfcb-admin.php';
	XVFCB_Admin::init();
}
