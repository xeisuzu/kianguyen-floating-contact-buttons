<?php
/**
 * Optional built-in Vietnamese strings.
 *
 * Community translations from translate.wordpress.org always take priority in
 * "auto" mode. The built-in Vietnamese strings are only used when the site
 * language is Vietnamese and a string has no community translation yet, or
 * when the administrator explicitly picks Vietnamese.
 *
 * @package Kianguyen_Floating_Contact_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Language handling.
 */
class XVFCB_I18n {

	const DOMAIN = 'kianguyen-floating-contact-buttons';

	/**
	 * Built-in Vietnamese strings, loaded on demand.
	 *
	 * @var array|null
	 */
	private static $vi = null;

	/**
	 * Register the gettext filter.
	 */
	public static function init() {
		add_filter( 'gettext_' . self::DOMAIN, array( __CLASS__, 'filter' ), 10, 2 );
	}

	/**
	 * Current language mode: auto, vi or en.
	 *
	 * Read straight from the option to avoid recursion through
	 * XVFCB_Settings::types(), which itself uses translated strings.
	 *
	 * @return string
	 */
	public static function mode() {
		$opt  = get_option( 'xvfcb_settings', array() );
		$mode = ( is_array( $opt ) && isset( $opt['language'] ) ) ? $opt['language'] : 'auto';
		return in_array( $mode, array( 'auto', 'vi', 'en' ), true ) ? $mode : 'auto';
	}

	/**
	 * Filter translations for this plugin's text domain.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original English text.
	 * @return string
	 */
	public static function filter( $translation, $text ) {
		$mode = self::mode();

		if ( 'en' === $mode ) {
			return $text;
		}

		if ( 'auto' === $mode ) {
			if ( $translation !== $text ) {
				return $translation;
			}
			$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
			if ( 0 !== strpos( (string) $locale, 'vi' ) ) {
				return $translation;
			}
		}

		if ( null === self::$vi ) {
			$strings  = include XVFCB_DIR . 'includes/strings-vi.php';
			self::$vi = is_array( $strings ) ? $strings : array();
		}

		return isset( self::$vi[ $text ] ) ? self::$vi[ $text ] : $translation;
	}
}
