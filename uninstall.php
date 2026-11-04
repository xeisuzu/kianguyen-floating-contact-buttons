<?php
/**
 * Remove plugin data on uninstall.
 *
 * @package XeVN_Floating_Contact_Buttons
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'xvfcb_settings' );
delete_post_meta_by_key( '_xvfcb_hide' );
