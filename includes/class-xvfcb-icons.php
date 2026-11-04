<?php
/**
 * Inline SVG icons.
 *
 * Generic icons are drawn for this plugin (GPLv2 or later). Brand logos come
 * from Simple Icons (CC0 1.0, see includes/brand-icons.php). Site owners can
 * also upload their own icon image per button.
 *
 * @package XeVN_Floating_Contact_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Icon registry.
 */
class XVFCB_Icons {

	/**
	 * Types with a full-color icon drawn for this plugin (no brand shape).
	 */
	const DRAWN = array( 'map' );

	/**
	 * Path data keyed by icon name (24x24 view box, filled shapes).
	 *
	 * @return array
	 */
	private static function paths() {
		return array(
			'phone'      => 'M6.6 2.8c.5-.3 1.2-.2 1.6.3l2.3 3c.4.5.4 1.2 0 1.7L9.2 9.4a12.3 12.3 0 0 0 5.4 5.4l1.6-1.3c.5-.4 1.2-.4 1.7 0l3 2.3c.5.4.6 1.1.3 1.6l-1 1.8c-.6 1-1.8 1.6-3 1.4C10.6 19.5 4.5 13.4 3.4 6.8c-.2-1.2.4-2.4 1.4-3z',
			'chat'       => 'M12 3C6.8 3 2.7 6.6 2.7 11c0 2.5 1.3 4.7 3.4 6.2L5.4 21l4.1-2.2c.8.2 1.6.3 2.5.3 5.2 0 9.3-3.6 9.3-8.1S17.2 3 12 3z',
			'chat-dots'  => 'M12 3C6.8 3 2.7 6.6 2.7 11c0 2.5 1.3 4.7 3.4 6.2L5.4 21l4.1-2.2c.8.2 1.6.3 2.5.3 5.2 0 9.3-3.6 9.3-8.1S17.2 3 12 3zM7.6 12.4a1.4 1.4 0 1 1 0-2.8 1.4 1.4 0 0 1 0 2.8zm4.4 0a1.4 1.4 0 1 1 0-2.8 1.4 1.4 0 0 1 0 2.8zm4.4 0a1.4 1.4 0 1 1 0-2.8 1.4 1.4 0 0 1 0 2.8z',
			'chat-phone' => 'M12 2.8C6.9 2.8 2.8 6.5 2.8 11c0 2.4 1.2 4.6 3.1 6.1L5.2 21.2l4.3-2.3c.8.2 1.6.3 2.5.3 5.1 0 9.2-3.7 9.2-8.2S17.1 2.8 12 2.8zm3.9 11.1-.6.9c-.3.4-.9.7-1.5.6-2.8-.5-5.2-2.9-5.7-5.7-.1-.6.2-1.2.6-1.5l.9-.6c.3-.2.7-.1.9.2l1 1.4c.2.3.2.7 0 .9l-.6.7c.6 1.1 1.5 2 2.6 2.6l.7-.6c.3-.2.7-.2.9 0l1.4 1c.3.2.4.6.2.9z',
			'plane'      => 'M21.2 3.3c.6-.2 1.1.3.9.9l-3.3 15.3c-.2.8-1.1 1.1-1.7.6l-4.6-3.5-2.4 2.3c-.3.3-.8.1-.8-.3l.1-3.4 8-7.4c.2-.2 0-.5-.3-.3L7.3 13.6 2.9 12.2c-.8-.3-.8-1.4 0-1.7z',
			'mail'       => 'M3 5.5h18c.6 0 1 .4 1 1v11c0 .6-.4 1-1 1H3c-.6 0-1-.4-1-1v-11c0-.6.4-1 1-1zm1.2 2.3v.3l7.3 5c.3.2.7.2 1 0l7.3-5v-.3c0-.3-.3-.4-.5-.3L12 12 4.7 7.5c-.2-.1-.5 0-.5.3z',
			'sms'        => 'M4 3.5h16c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H9.5L5 21v-3.5H4c-1.1 0-2-.9-2-2v-10c0-1.1.9-2 2-2zm3 4.3v1.6h10V7.8zm0 3.6v1.6h7v-1.6z',
			'pin'        => 'M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.7A2.7 2.7 0 1 1 12 6.3a2.7 2.7 0 0 1 0 5.4z',
			'close'      => 'M6.3 4.9 12 10.6l5.7-5.7a1 1 0 1 1 1.4 1.4L13.4 12l5.7 5.7a1 1 0 1 1-1.4 1.4L12 13.4l-5.7 5.7a1 1 0 1 1-1.4-1.4l5.7-5.7-5.7-5.7a1 1 0 0 1 1.4-1.4z',
			'link'       => 'M10.6 13.4a1 1 0 0 1 0-1.4l3.5-3.5a1 1 0 1 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zM8 20a5 5 0 0 1-3.5-8.5l2.2-2.2a1 1 0 1 1 1.4 1.4l-2.2 2.2a3 3 0 0 0 4.2 4.2l2.2-2.2a1 1 0 1 1 1.4 1.4l-2.2 2.2A5 5 0 0 1 8 20zm9.3-5.3a1 1 0 0 1-.7-1.7l2.2-2.2a3 3 0 0 0-4.2-4.2l-2.2 2.2a1 1 0 1 1-1.4-1.4l2.2-2.2a5 5 0 0 1 7 7L18 14.4a1 1 0 0 1-.7.3z',
		);
	}

	/**
	 * Brand logo path data keyed by button type (loaded on demand).
	 *
	 * @var array|null
	 */
	private static $brands = null;

	/**
	 * Brand logo paths.
	 *
	 * @return array
	 */
	private static function brands() {
		if ( null === self::$brands ) {
			$data         = include XVFCB_DIR . 'includes/brand-icons.php';
			self::$brands = is_array( $data ) ? $data : array();
		}
		return self::$brands;
	}

	/**
	 * Whether a brand logo exists for a button type.
	 *
	 * @param string $type Button type.
	 * @return bool
	 */
	public static function has_brand( $type ) {
		$brands = self::brands();
		return isset( $brands[ $type ] );
	}

	/**
	 * Wrap path data in an SVG element.
	 *
	 * @param string $d Path data.
	 * @return string
	 */
	private static function wrap( $d ) {
		return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . esc_attr( $d ) . '"/></svg>';
	}

	/**
	 * Return an inline SVG for a generic icon name.
	 *
	 * @param string $name Icon name.
	 * @return string Safe SVG markup.
	 */
	public static function svg( $name ) {
		if ( 'phone' === $name ) {
			// Handset with signal waves, drawn for this plugin.
			$hand = self::paths();
			return '<svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">'
				. '<path fill="currentColor" transform="translate(-2.3 1.6) scale(1.02)" d="' . esc_attr( $hand['phone'] ) . '"/>'
				. '<path fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" d="M14.2 7.9a2.1 2.1 0 0 1 2.1 2.1M14.2 4.6a5.4 5.4 0 0 1 5.4 5.4M14.2 1.3a8.7 8.7 0 0 1 8.7 8.7"/>'
				. '</svg>';
		}
		$paths = self::paths();
		return self::wrap( isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['link'] );
	}

	/**
	 * Whether a button type gets the full-color app-style logo.
	 *
	 * @param string $type  Button type.
	 * @param string $style Icon style.
	 * @return bool
	 */
	public static function is_color( $type, $style ) {
		return 'default' === $style && ( self::has_brand( $type ) || in_array( $type, self::DRAWN, true ) );
	}

	/**
	 * Icon for a button type.
	 *
	 * Styles: "default" = full-color logo (brand types) or simple icon,
	 * "mono" = white logo on the button color, "generic" = simple icon.
	 *
	 * @param string $type  Button type.
	 * @param string $style Icon style.
	 * @return string Safe SVG markup.
	 */
	public static function for_type( $type, $style = 'default' ) {
		$brands = self::brands();
		if ( 'default' === $style && ( isset( $brands[ $type ] ) || in_array( $type, self::DRAWN, true ) ) ) {
			return self::color( $type );
		}
		if ( 'mono' === $style && isset( $brands[ $type ] ) ) {
			return self::wrap( $brands[ $type ] );
		}
		$types = XVFCB_Settings::types();
		$name  = isset( $types[ $type ] ) ? $types[ $type ]['icon'] : 'link';
		return self::svg( $name );
	}

	/**
	 * Counter for unique gradient IDs on one page.
	 *
	 * @var int
	 */
	private static $uid = 0;

	/**
	 * A brand path placed in the 48x48 canvas, scaled around the center.
	 *
	 * @param string $d     Path data (24x24).
	 * @param float  $scale Scale relative to the full 48px canvas (1 = full).
	 * @param string $fill  Fill value.
	 * @param float  $dx    Extra X offset.
	 * @param float  $dy    Extra Y offset.
	 * @return string
	 */
	private static function place( $d, $scale, $fill, $dx = 0, $dy = 0 ) {
		$k = 2 * $scale;
		$t = 24 - 12 * $k;
		return sprintf(
			'<path fill="%1$s" transform="translate(%2$s %3$s) scale(%4$s)" d="%5$s"/>',
			esc_attr( $fill ),
			esc_attr( round( $t + $dx, 3 ) ),
			esc_attr( round( $t + $dy, 3 ) ),
			esc_attr( round( $k, 4 ) ),
			esc_attr( $d )
		);
	}

	/**
	 * Full-color, app-style logo (48x48) for a brand button type.
	 *
	 * @param string $type Button type.
	 * @return string SVG markup.
	 */
	public static function color( $type ) {
		$brands = self::brands();
		if ( ! isset( $brands[ $type ] ) && ! in_array( $type, self::DRAWN, true ) ) {
			return '';
		}
		$d   = isset( $brands[ $type ] ) ? $brands[ $type ] : '';
		$id  = 'xvfcb-g' . ( ++self::$uid );
		$url = 'url(#' . $id . ')';
		$in  = '';

		switch ( $type ) {
			case 'zalo':
				// Round badge: blue ring with a thin inner line, a round white chat
				// bubble with its tail on the left, and the Zalo wordmark
				// (Simple Icons, CC0) centred inside.
				$in = '<circle cx="24" cy="24" r="24" fill="#0068ff"/>'
					. '<circle cx="24" cy="24" r="22.2" fill="none" stroke="#fff" stroke-width="0.9"/>'
					. '<circle cx="24" cy="24" r="17" fill="#fff"/>'
					. '<path fill="#fff" d="M7.4 23.6c.1 3.2-.6 6.1-2.1 8.6 2.7-.4 5.1-1.5 7.2-3.1z"/>'
					. str_replace( '<path ', '<path stroke="#0068ff" stroke-width="0.35" stroke-linejoin="round" ', self::place( $d, 0.6, '#0068ff', 0.6, 0 ) );
				break;
			case 'map':
				// Map badge drawn for this plugin: land, river, roads and a pin.
				$in = '<defs><clipPath id="' . $id . '"><circle cx="24" cy="24" r="24"/></clipPath></defs>'
					. '<g clip-path="' . $url . '">'
					. '<rect width="48" height="48" fill="#dff2e1"/>'
					. '<path fill="#9ed8a6" d="M0 30c8-3 14 2 22 0s10-9 26-8v26H0z"/>'
					. '<path fill="#4fa8f5" d="M-2 40c10-6 18-2 26-6s14-12 26-12v6c-10 0-14 8-24 12s-18 1-28 7z"/>'
					. '<path fill="#fff" d="M30 -2h6L20 50h-6z"/>'
					. '<path fill="#ffc531" d="M-2 14l52 10v5L-2 19z"/>'
					. '</g>'
					. '<path fill="#e53935" d="M24 7.5c-6.1 0-10.5 4.5-10.5 10.2 0 7.3 9 16 9.9 16.9.3.3.9.3 1.2 0 .9-.9 9.9-9.6 9.9-16.9 0-5.7-4.4-10.2-10.5-10.2z"/>'
					. '<circle cx="24" cy="17.8" r="4" fill="#fff"/>';
				break;
			case 'telegram':
				$in = '<defs><linearGradient id="' . $id . '" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#2aabee"/><stop offset="1" stop-color="#229ed9"/></linearGradient></defs>'
					. '<circle cx="24" cy="24" r="22" fill="#fff"/>'
					. self::place( $d, 1, $url );
				break;
			case 'facebook':
				$in = '<circle cx="24" cy="24" r="22" fill="#fff"/>' . self::place( $d, 1, '#0866ff' );
				break;
			case 'messenger':
				$in = '<defs><radialGradient id="' . $id . '" cx="0.19" cy="0.99" r="1.09"><stop offset="0" stop-color="#0099ff"/><stop offset="0.61" stop-color="#a033ff"/><stop offset="0.93" stop-color="#ff5280"/><stop offset="1" stop-color="#ff7061"/></radialGradient></defs>'
					. '<circle cx="24" cy="23" r="19" fill="#fff"/>'
					. self::place( $d, 1, $url );
				break;
			case 'whatsapp':
				$in = '<circle cx="24" cy="24" r="24" fill="#25d366"/>' . self::place( $d, 0.6, '#fff' );
				break;
			case 'viber':
				$in = '<circle cx="24" cy="24" r="24" fill="#7360f2"/>' . self::place( $d, 0.6, '#fff' );
				break;
			case 'tiktok':
				$in = '<circle cx="24" cy="24" r="24" fill="#000"/>'
					. self::place( $d, 0.52, '#25f4ee', -0.9, -0.9 )
					. self::place( $d, 0.52, '#fe2c55', 0.9, 0.9 )
					. self::place( $d, 0.52, '#fff' );
				break;
			case 'youtube':
				$in = '<rect x="15" y="14" width="18" height="20" fill="#fff"/>' . self::place( $d, 1, '#ff0000' );
				break;
			case 'instagram':
				$in = '<defs><radialGradient id="' . $id . '" cx="0.3" cy="1.07" r="1.3"><stop offset="0" stop-color="#fdf497"/><stop offset="0.05" stop-color="#fdf497"/><stop offset="0.45" stop-color="#fd5949"/><stop offset="0.6" stop-color="#d6249f"/><stop offset="0.9" stop-color="#285aeb"/></radialGradient></defs>'
					. '<rect width="48" height="48" rx="12" fill="' . $url . '"/>'
					. self::place( $d, 0.58, '#fff' );
				break;
		}

		return '<svg viewBox="0 0 48 48" width="48" height="48" aria-hidden="true" focusable="false">' . $in . '</svg>';
	}

	/**
	 * Allowed tags and attributes for all icon markup (for wp_kses()).
	 *
	 * @return array
	 */
	public static function kses() {
		$paint = array(
			'fill'            => true,
			'd'               => true,
			'transform'       => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
		);
		return array(
			'svg'            => array(
				'viewbox'     => true,
				'width'       => true,
				'height'      => true,
				'aria-hidden' => true,
				'focusable'   => true,
			),
			'defs'           => array(),
			'lineargradient' => array(
				'id' => true,
				'x1' => true,
				'y1' => true,
				'x2' => true,
				'y2' => true,
			),
			'radialgradient' => array(
				'id' => true,
				'cx' => true,
				'cy' => true,
				'r'  => true,
			),
			'stop'           => array(
				'offset'     => true,
				'stop-color' => true,
			),
			'path'           => $paint,
			'ellipse'        => array(
				'cx'   => true,
				'cy'   => true,
				'rx'   => true,
				'ry'   => true,
				'fill' => true,
			),
			'circle'         => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'fill'         => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
			'clippath'       => array(
				'id' => true,
			),
			'g'              => array(
				'clip-path' => true,
			),
			'rect'           => array(
				'x'      => true,
				'y'      => true,
				'width'  => true,
				'height' => true,
				'rx'     => true,
				'fill'   => true,
			),
		);
	}
}
