<?php
/**
 * Settings: defaults, button types, retrieval and sanitization.
 *
 * @package XeVN_Floating_Contact_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings helper.
 */
class XVFCB_Settings {

	const OPTION   = 'xvfcb_settings';
	const META_KEY = '_xvfcb_hide';
	const MAX_BUTTONS = 12;
	const MAX_SETS    = 5;

	/**
	 * Cached settings.
	 *
	 * @var array|null
	 */
	private static $cache = null;

	/**
	 * Supported button types.
	 *
	 * @return array key => array( label, icon, color, kind, hint ).
	 */
	public static function types() {
		return array(
			'phone'     => array(
				'label' => __( 'Call', 'xevn-floating-contact-buttons' ),
				'icon'  => 'phone',
				'color' => '#2196f3',
				'kind'  => 'phone',
				'hint'  => __( 'Phone number, e.g. 0901234567 or +84901234567', 'xevn-floating-contact-buttons' ),
			),
			'zalo'      => array(
				'label' => __( 'Zalo', 'xevn-floating-contact-buttons' ),
				'icon'  => 'chat',
				'color' => '#0068ff',
				'kind'  => 'handle',
				'hint'  => __( 'Zalo phone number or full zalo.me link', 'xevn-floating-contact-buttons' ),
			),
			'messenger' => array(
				'label' => __( 'Messenger', 'xevn-floating-contact-buttons' ),
				'icon'  => 'chat-dots',
				'color' => '#0866ff',
				'kind'  => 'handle',
				'hint'  => __( 'Username of your Facebook page or profile (e.g. yourpage), or a full m.me link', 'xevn-floating-contact-buttons' ),
			),
			'viber'     => array(
				'label' => __( 'Viber', 'xevn-floating-contact-buttons' ),
				'icon'  => 'chat-phone',
				'color' => '#7360f2',
				'kind'  => 'phone',
				'hint'  => __( 'Phone number with country code, e.g. +84901234567', 'xevn-floating-contact-buttons' ),
			),
			'whatsapp'  => array(
				'label' => __( 'WhatsApp', 'xevn-floating-contact-buttons' ),
				'icon'  => 'chat-phone',
				'color' => '#25d366',
				'kind'  => 'phone',
				'hint'  => __( 'Phone number with country code, e.g. +84901234567', 'xevn-floating-contact-buttons' ),
			),
			'telegram'  => array(
				'label' => __( 'Telegram', 'xevn-floating-contact-buttons' ),
				'icon'  => 'plane',
				'color' => '#26a5e4',
				'kind'  => 'handle',
				'hint'  => __( 'Telegram username or full t.me link', 'xevn-floating-contact-buttons' ),
			),
			'facebook'  => array(
				'label' => __( 'Facebook', 'xevn-floating-contact-buttons' ),
				'icon'  => 'link',
				'color' => '#0866ff',
				'kind'  => 'handle',
				'hint'  => __( 'Facebook page username or full facebook.com link', 'xevn-floating-contact-buttons' ),
			),
			'tiktok'    => array(
				'label' => __( 'TikTok', 'xevn-floating-contact-buttons' ),
				'icon'  => 'link',
				'color' => '#000000',
				'kind'  => 'handle',
				'hint'  => __( 'TikTok username (e.g. @yourshop) or full tiktok.com link', 'xevn-floating-contact-buttons' ),
			),
			'youtube'   => array(
				'label' => __( 'YouTube', 'xevn-floating-contact-buttons' ),
				'icon'  => 'link',
				'color' => '#ff0000',
				'kind'  => 'handle',
				'hint'  => __( 'YouTube handle (e.g. @yourchannel) or full youtube.com link', 'xevn-floating-contact-buttons' ),
			),
			'instagram' => array(
				'label' => __( 'Instagram', 'xevn-floating-contact-buttons' ),
				'icon'  => 'link',
				'color' => '#ff0069',
				'kind'  => 'handle',
				'hint'  => __( 'Instagram username or full instagram.com link', 'xevn-floating-contact-buttons' ),
			),
			'email'     => array(
				'label' => __( 'Email', 'xevn-floating-contact-buttons' ),
				'icon'  => 'mail',
				'color' => '#f59e0b',
				'kind'  => 'email',
				'hint'  => __( 'Email address', 'xevn-floating-contact-buttons' ),
			),
			'sms'       => array(
				'label' => __( 'SMS', 'xevn-floating-contact-buttons' ),
				'icon'  => 'sms',
				'color' => '#10b981',
				'kind'  => 'phone',
				'hint'  => __( 'Phone number', 'xevn-floating-contact-buttons' ),
			),
			'map'       => array(
				'label' => __( 'Directions', 'xevn-floating-contact-buttons' ),
				'icon'  => 'pin',
				'color' => '#ef4444',
				'kind'  => 'url',
				'hint'  => __( 'Map link (e.g. a Google Maps share link)', 'xevn-floating-contact-buttons' ),
			),
			'link'      => array(
				'label' => __( 'Link', 'xevn-floating-contact-buttons' ),
				'icon'  => 'link',
				'color' => '#4b5563',
				'kind'  => 'url',
				'hint'  => __( 'Any URL, e.g. your quote request page', 'xevn-floating-contact-buttons' ),
			),
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'enabled'         => 1,
			'position'        => 'right',
			'offset_x'        => 16,
			'offset_y'        => 20,
			'size'            => 52,
			'size_mobile'     => 48,
			'gap'             => 10,
			'position_mobile' => 'right',
			'offset_x_mobile' => 16,
			'offset_y_mobile' => 20,
			'gap_mobile'      => 10,
			'layout'          => 'icon',
			'show_tooltip'    => 1,
			'breakpoint'      => 768,
			'z_index'         => 9999,
			'animation'       => 'shake',
			'new_tab'         => 1,
			'hide_front_page' => 0,
			'hide_ids'        => '',
			'hide_post_types' => array(),
			'tracking'        => 1,
			'event_name'      => 'xevn_contact_click',
			'gtag'            => 0,
			'language'        => 'auto',
			'mobile_style'    => 'float',
			'bar_bg'          => '#ffffff',
			'bar_text'        => '#374151',
			'bar_font'        => 12,
			'collapse'        => 'off',
			'collapse_label'  => '',
			'toggle_color'    => '#0a7cff',
			'desktop_call'    => 'popup',
			'reveal'          => 'immediate',
			'reveal_scroll'   => 300,
			'reveal_delay'    => 3,
			'hours_enabled'   => 0,
			'hours_week'      => self::default_week(),
			'sets'            => array(),
			'buttons'         => array(
				self::button_defaults( 'phone' ),
				self::button_defaults( 'zalo' ),
			),
		);
	}

	/**
	 * Default values for one button row.
	 *
	 * @param string $type Button type.
	 * @return array
	 */
	public static function button_defaults( $type = 'phone' ) {
		$types = self::types();
		$type  = isset( $types[ $type ] ) ? $type : 'phone';
		return array(
			'type'      => $type,
			'value'     => '',
			'label'     => '',
			'color'     => $types[ $type ]['color'],
			'icon'      => 'default',
			'icon_url'  => '',
			'message'   => '',
			'hours'     => 'always',
			'device'    => 'all',
			'attention' => ( 'phone' === $type ) ? 1 : 0,
			'featured'  => ( 'phone' === $type ) ? 1 : 0,
			'enabled'   => 1,
		);
	}

	/**
	 * Get saved settings merged with defaults.
	 *
	 * @return array
	 */
	public static function get() {
		if ( null === self::$cache ) {
			$saved = get_option( self::OPTION, array() );
			if ( ! is_array( $saved ) ) {
				$saved = array();
			}
			// Phone placement saved before it had its own settings: use the
			// computer values so the buttons stay where they were.
			foreach ( self::mobile_fallbacks() as $mobile => $desktop ) {
				if ( ! isset( $saved[ $mobile ] ) && isset( $saved[ $desktop ] ) ) {
					$saved[ $mobile ] = $saved[ $desktop ];
				}
			}
			self::$cache = array_merge( self::defaults(), $saved );
		}
		return self::$cache;
	}

	/**
	 * Phone settings and the computer setting they default to.
	 *
	 * @return array
	 */
	public static function mobile_fallbacks() {
		return array(
			'position_mobile' => 'position',
			'offset_x_mobile' => 'offset_x',
			'offset_y_mobile' => 'offset_y',
			'gap_mobile'      => 'gap',
		);
	}

	/**
	 * Clear cached settings (after saving).
	 */
	public static function flush() {
		self::$cache = null;
	}

	/**
	 * Integer within range.
	 *
	 * @param mixed $value Raw value.
	 * @param int   $min   Minimum.
	 * @param int   $max   Maximum.
	 * @param int   $def   Default.
	 * @return int
	 */
	private static function int_range( $value, $min, $max, $def ) {
		if ( ! is_numeric( $value ) ) {
			return $def;
		}
		return max( $min, min( $max, (int) $value ) );
	}

	/**
	 * Sanitize the whole settings array.
	 *
	 * @param mixed $input Raw input.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$def = self::defaults();
		$in  = is_array( $input ) ? $input : array();
		$out = array();

		// Settings files exported before the phone settings existed.
		foreach ( self::mobile_fallbacks() as $mobile => $desktop ) {
			if ( ! isset( $in[ $mobile ] ) && isset( $in[ $desktop ] ) ) {
				$in[ $mobile ] = $in[ $desktop ];
			}
		}

		$out['enabled']         = empty( $in['enabled'] ) ? 0 : 1;
		$out['position']        = ( isset( $in['position'] ) && 'left' === $in['position'] ) ? 'left' : 'right';
		$out['offset_x']        = self::int_range( isset( $in['offset_x'] ) ? $in['offset_x'] : null, 0, 200, $def['offset_x'] );
		$out['offset_y']        = self::int_range( isset( $in['offset_y'] ) ? $in['offset_y'] : null, 0, 400, $def['offset_y'] );
		$out['size']            = self::int_range( isset( $in['size'] ) ? $in['size'] : null, 36, 80, $def['size'] );
		$out['size_mobile']     = self::int_range( isset( $in['size_mobile'] ) ? $in['size_mobile'] : null, 32, 72, $def['size_mobile'] );
		$out['gap']             = self::int_range( isset( $in['gap'] ) ? $in['gap'] : null, 0, 40, $def['gap'] );
		$out['position_mobile'] = ( isset( $in['position_mobile'] ) && 'left' === $in['position_mobile'] ) ? 'left' : 'right';
		$out['offset_x_mobile'] = self::int_range( isset( $in['offset_x_mobile'] ) ? $in['offset_x_mobile'] : null, 0, 200, $def['offset_x_mobile'] );
		$out['offset_y_mobile'] = self::int_range( isset( $in['offset_y_mobile'] ) ? $in['offset_y_mobile'] : null, 0, 400, $def['offset_y_mobile'] );
		$out['gap_mobile']      = self::int_range( isset( $in['gap_mobile'] ) ? $in['gap_mobile'] : null, 0, 40, $def['gap_mobile'] );
		$out['layout']          = ( isset( $in['layout'] ) && 'label' === $in['layout'] ) ? 'label' : 'icon';
		$out['show_tooltip']    = empty( $in['show_tooltip'] ) ? 0 : 1;
		$out['breakpoint']      = self::int_range( isset( $in['breakpoint'] ) ? $in['breakpoint'] : null, 320, 1280, $def['breakpoint'] );
		$out['z_index']         = self::int_range( isset( $in['z_index'] ) ? $in['z_index'] : null, 1, 2147483000, $def['z_index'] );
		$out['animation']       = ( isset( $in['animation'] ) && in_array( $in['animation'], array( 'none', 'shake', 'pulse', 'bounce' ), true ) ) ? $in['animation'] : 'none';
		$out['new_tab']         = empty( $in['new_tab'] ) ? 0 : 1;
		$out['hide_front_page'] = empty( $in['hide_front_page'] ) ? 0 : 1;

		$ids = array();
		if ( isset( $in['hide_ids'] ) ) {
			foreach ( preg_split( '/[\s,;]+/', (string) $in['hide_ids'] ) as $id ) {
				$id = absint( $id );
				if ( $id ) {
					$ids[] = $id;
				}
			}
		}
		$out['hide_ids'] = implode( ', ', array_unique( $ids ) );

		$out['hide_post_types'] = array();
		if ( isset( $in['hide_post_types'] ) && is_array( $in['hide_post_types'] ) ) {
			foreach ( $in['hide_post_types'] as $pt ) {
				$pt = sanitize_key( $pt );
				if ( '' !== $pt && post_type_exists( $pt ) ) {
					$out['hide_post_types'][] = $pt;
				}
			}
		}

		$out['tracking']   = empty( $in['tracking'] ) ? 0 : 1;
		$event             = isset( $in['event_name'] ) ? preg_replace( '/[^A-Za-z0-9_]/', '', (string) $in['event_name'] ) : '';
		$out['event_name'] = ( '' !== $event ) ? substr( $event, 0, 40 ) : $def['event_name'];
		$out['gtag']       = empty( $in['gtag'] ) ? 0 : 1;
		$out['language']   = ( isset( $in['language'] ) && in_array( $in['language'], array( 'auto', 'vi', 'en' ), true ) ) ? $in['language'] : 'auto';

		$out['mobile_style']   = ( isset( $in['mobile_style'] ) && 'bar' === $in['mobile_style'] ) ? 'bar' : 'float';
		$bar_bg                = isset( $in['bar_bg'] ) ? sanitize_hex_color( $in['bar_bg'] ) : '';
		$out['bar_bg']         = $bar_bg ? $bar_bg : $def['bar_bg'];
		$bar_text              = isset( $in['bar_text'] ) ? sanitize_hex_color( $in['bar_text'] ) : '';
		$out['bar_text']       = $bar_text ? $bar_text : $def['bar_text'];
		$out['bar_font']       = self::int_range( isset( $in['bar_font'] ) ? $in['bar_font'] : null, 10, 18, $def['bar_font'] );
		if ( isset( $in['collapse_desktop'] ) || isset( $in['collapse_mobile'] ) ) {
			// Settings page: one choice per device.
			$group_desktop   = isset( $in['collapse_desktop'] ) && 'on' === $in['collapse_desktop'];
			$group_mobile    = isset( $in['collapse_mobile'] ) && 'on' === $in['collapse_mobile'];
			$out['collapse'] = $group_desktop ? ( $group_mobile ? 'always' : 'desktop' ) : ( $group_mobile ? 'mobile' : 'off' );
		} else {
			$out['collapse'] = ( isset( $in['collapse'] ) && in_array( $in['collapse'], array( 'off', 'mobile', 'desktop', 'always' ), true ) ) ? $in['collapse'] : 'off';
		}
		$out['collapse_label'] = isset( $in['collapse_label'] ) ? substr( sanitize_text_field( $in['collapse_label'] ), 0, 40 ) : '';
		$toggle                = isset( $in['toggle_color'] ) ? sanitize_hex_color( $in['toggle_color'] ) : '';
		$out['toggle_color']   = $toggle ? $toggle : $def['toggle_color'];
		$out['desktop_call']   = ( isset( $in['desktop_call'] ) && 'link' === $in['desktop_call'] ) ? 'link' : 'popup';

		$out['reveal']        = ( isset( $in['reveal'] ) && in_array( $in['reveal'], array( 'immediate', 'scroll', 'delay' ), true ) ) ? $in['reveal'] : 'immediate';
		$out['reveal_scroll'] = self::int_range( isset( $in['reveal_scroll'] ) ? $in['reveal_scroll'] : null, 0, 3000, $def['reveal_scroll'] );
		$out['reveal_delay']  = self::int_range( isset( $in['reveal_delay'] ) ? $in['reveal_delay'] : null, 0, 60, $def['reveal_delay'] );

		$out['hours_enabled'] = empty( $in['hours_enabled'] ) ? 0 : 1;
		$out['hours_week']    = self::sanitize_week( $in );

		$out['buttons'] = self::sanitize_buttons( isset( $in['buttons'] ) ? $in['buttons'] : array() );

		$out['sets'] = array();
		if ( isset( $in['sets'] ) && is_array( $in['sets'] ) ) {
			foreach ( $in['sets'] as $set ) {
				if ( count( $out['sets'] ) >= self::MAX_SETS ) {
					break;
				}
				if ( is_array( $set ) ) {
					$out['sets'][] = self::sanitize_set( $set, count( $out['sets'] ) + 1 );
				}
			}
		}

		self::flush();
		return $out;
	}

	/**
	 * Sanitize a list of button rows.
	 *
	 * @param mixed $rows Raw rows.
	 * @return array
	 */
	public static function sanitize_buttons( $rows ) {
		$out = array();
		if ( ! is_array( $rows ) ) {
			return $out;
		}
		$featured = false;
		foreach ( $rows as $row ) {
			if ( count( $out ) >= self::MAX_BUTTONS ) {
				break;
			}
			if ( is_array( $row ) ) {
				$b = self::sanitize_button( $row );
				// Only one featured button per list: keep the first one.
				if ( $b['featured'] ) {
					$b['featured'] = $featured ? 0 : 1;
					$featured      = true;
				}
				$out[] = $b;
			}
		}
		return $out;
	}

	/**
	 * Split a list typed by the user (new lines, commas or semicolons).
	 *
	 * @param mixed $value Raw text.
	 * @return array Trimmed, non-empty items.
	 */
	private static function split_list( $value ) {
		$items = preg_split( '/[\r\n,;]+/', (string) $value );
		return array_values( array_filter( array_map( 'trim', $items ), 'strlen' ) );
	}

	/**
	 * Sanitize one page-specific button set.
	 *
	 * @param array $set    Raw set.
	 * @param int   $number Position, used for the default name.
	 * @return array
	 */
	public static function sanitize_set( $set, $number ) {
		$name = isset( $set['name'] ) ? substr( sanitize_text_field( $set['name'] ), 0, 60 ) : '';
		if ( '' === $name ) {
			/* translators: %d: set number. */
			$name = sprintf( __( 'Set %d', 'xevn-floating-contact-buttons' ), $number );
		}

		$paths = array();
		foreach ( self::split_list( isset( $set['paths'] ) ? $set['paths'] : '' ) as $path ) {
			$path = strtolower( sanitize_text_field( $path ) );
			$path = preg_replace( '#^https?://[^/]+#', '', $path );
			if ( '' !== $path && count( $paths ) < 30 ) {
				$paths[] = substr( $path, 0, 200 );
			}
		}

		$ids = array();
		foreach ( self::split_list( isset( $set['ids'] ) ? $set['ids'] : '' ) as $id ) {
			if ( absint( $id ) ) {
				$ids[] = absint( $id );
			}
		}

		$terms = array();
		foreach ( self::split_list( isset( $set['terms'] ) ? $set['terms'] : '' ) as $term ) {
			$term = sanitize_title( $term );
			if ( '' !== $term && count( $terms ) < 30 ) {
				$terms[] = $term;
			}
		}

		return array(
			'name'    => $name,
			'enabled' => empty( $set['enabled'] ) ? 0 : 1,
			'paths'   => implode( "\n", array_unique( $paths ) ),
			'ids'     => implode( ', ', array_unique( $ids ) ),
			'terms'   => implode( ', ', array_unique( $terms ) ),
			'buttons' => self::sanitize_buttons( isset( $set['buttons'] ) ? $set['buttons'] : array() ),
		);
	}

	/**
	 * Sanitize one button row.
	 *
	 * @param array $row Raw row.
	 * @return array
	 */
	public static function sanitize_button( $row ) {
		$types = self::types();
		$type  = ( isset( $row['type'] ) && isset( $types[ $row['type'] ] ) ) ? $row['type'] : 'link';
		$b     = self::button_defaults( $type );

		$b['value']     = self::sanitize_value( $type, isset( $row['value'] ) ? $row['value'] : '' );
		$b['label']     = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
		$color          = isset( $row['color'] ) ? sanitize_hex_color( $row['color'] ) : '';
		$b['color']     = $color ? $color : $types[ $type ]['color'];
		$b['icon_url']  = isset( $row['icon_url'] ) ? esc_url_raw( trim( (string) $row['icon_url'] ) ) : '';
		$b['icon']      = ( isset( $row['icon'] ) && in_array( $row['icon'], array( 'default', 'mono', 'generic', 'custom' ), true ) ) ? $row['icon'] : 'default';
		if ( 'custom' === $b['icon'] && '' === $b['icon_url'] ) {
			$b['icon'] = 'default';
		}
		$b['message']   = isset( $row['message'] ) ? substr( sanitize_text_field( $row['message'] ), 0, 300 ) : '';
		$b['hours']     = ( isset( $row['hours'] ) && in_array( $row['hours'], array( 'always', 'open', 'closed' ), true ) ) ? $row['hours'] : 'always';
		$b['device']    = ( isset( $row['device'] ) && in_array( $row['device'], array( 'all', 'mobile', 'desktop' ), true ) ) ? $row['device'] : 'all';
		$b['attention'] = empty( $row['attention'] ) ? 0 : 1;
		$b['featured']  = empty( $row['featured'] ) ? 0 : 1;
		$b['enabled']   = empty( $row['enabled'] ) ? 0 : 1;

		return $b;
	}

	/**
	 * Readable text color (white or near-black) for a background color,
	 * following the WCAG 2 contrast formula. White is kept when it reaches
	 * 4.5:1; otherwise the color with the higher contrast is used.
	 *
	 * @param string $hex Background color (#rgb or #rrggbb).
	 * @return string
	 */
	public static function text_color( $hex ) {
		$hex = ltrim( (string) $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( ! preg_match( '/^[0-9a-f]{6}$/i', $hex ) ) {
			return '#ffffff';
		}
		$lum = 0;
		foreach ( array( 0.2126, 0.7152, 0.0722 ) as $i => $weight ) {
			$c    = hexdec( substr( $hex, $i * 2, 2 ) ) / 255;
			$c    = ( $c <= 0.03928 ) ? $c / 12.92 : pow( ( $c + 0.055 ) / 1.055, 2.4 );
			$lum += $weight * $c;
		}
		$dark_lum = 0.0103; // #111827.
		$white    = 1.05 / ( $lum + 0.05 );
		$dark     = ( $lum + 0.05 ) / ( $dark_lum + 0.05 );
		return ( $white >= 4.5 || $white >= $dark ) ? '#ffffff' : '#111827';
	}

	/**
	 * Default weekly schedule: Mon-Fri 08:00-17:30, Sat 08:00-12:00, Sun closed.
	 * Keys are day numbers (0 = Sunday).
	 *
	 * @return array
	 */
	public static function default_week() {
		$week = array();
		for ( $d = 0; $d <= 6; $d++ ) {
			$week[ $d ] = array(
				'on'    => ( 0 === $d ) ? 0 : 1,
				'start' => '08:00',
				'end'   => ( 6 === $d ) ? '12:00' : '17:30',
			);
		}
		return $week;
	}

	/**
	 * Sanitize the weekly schedule.
	 *
	 * Also converts the earlier single-schedule format (hours_days,
	 * hours_start, hours_end) used by pre-release test builds.
	 *
	 * @param array $in Raw settings input.
	 * @return array
	 */
	public static function sanitize_week( $in ) {
		$def  = self::default_week();
		$week = array();

		if ( ! isset( $in['hours_week'] ) && isset( $in['hours_days'] ) && is_array( $in['hours_days'] ) ) {
			$start = self::sanitize_time( isset( $in['hours_start'] ) ? $in['hours_start'] : '', '08:00' );
			$end   = self::sanitize_time( isset( $in['hours_end'] ) ? $in['hours_end'] : '', '17:30' );
			$days  = array_map( 'intval', $in['hours_days'] );
			for ( $d = 0; $d <= 6; $d++ ) {
				$week[ $d ] = array(
					'on'    => in_array( $d, $days, true ) ? 1 : 0,
					'start' => $start,
					'end'   => $end,
				);
			}
			return $week;
		}

		$raw = ( isset( $in['hours_week'] ) && is_array( $in['hours_week'] ) ) ? $in['hours_week'] : array();
		for ( $d = 0; $d <= 6; $d++ ) {
			$day        = ( isset( $raw[ $d ] ) && is_array( $raw[ $d ] ) ) ? $raw[ $d ] : array();
			$week[ $d ] = array(
				'on'    => empty( $day['on'] ) ? 0 : 1,
				'start' => self::sanitize_time( isset( $day['start'] ) ? $day['start'] : '', $def[ $d ]['start'] ),
				'end'   => self::sanitize_time( isset( $day['end'] ) ? $day['end'] : '', $def[ $d ]['end'] ),
			);
		}
		return $week;
	}

	/**
	 * Sanitize a HH:MM time.
	 *
	 * @param mixed  $value Raw value.
	 * @param string $def   Default.
	 * @return string
	 */
	public static function sanitize_time( $value, $def ) {
		if ( preg_match( '/^([01]?\d|2[0-3]):([0-5]\d)$/', trim( (string) $value ), $m ) ) {
			return sprintf( '%02d:%02d', (int) $m[1], (int) $m[2] );
		}
		return $def;
	}

	/**
	 * Types that support a pre-filled message.
	 *
	 * @return array
	 */
	public static function message_types() {
		return array( 'whatsapp', 'sms', 'email' );
	}

	/**
	 * Sanitize the contact value according to the button type.
	 *
	 * @param string $type  Button type.
	 * @param mixed  $value Raw value.
	 * @return string
	 */
	public static function sanitize_value( $type, $value ) {
		$types = self::types();
		$kind  = $types[ $type ]['kind'];
		$value = trim( sanitize_text_field( (string) $value ) );

		if ( '' === $value ) {
			return '';
		}

		switch ( $kind ) {
			case 'phone':
				return self::clean_phone( $value );
			case 'email':
				return sanitize_email( $value );
			case 'handle':
				if ( preg_match( '#^https?://#i', $value ) ) {
					return esc_url_raw( $value );
				}
				return preg_replace( '/[^A-Za-z0-9._+\-]/', '', $value );
			default:
				return esc_url_raw( $value );
		}
	}

	/**
	 * Keep digits and a leading plus sign.
	 *
	 * @param string $value Phone number.
	 * @return string
	 */
	public static function clean_phone( $value ) {
		$plus   = ( 0 === strpos( ltrim( $value ), '+' ) ) ? '+' : '';
		$digits = preg_replace( '/\D+/', '', $value );
		return ( '' === $digits ) ? '' : $plus . $digits;
	}

	/**
	 * Build the link for a button.
	 *
	 * @param array $b Sanitized button.
	 * @return string Empty when the value is missing.
	 */
	public static function build_url( $b ) {
		$v = isset( $b['value'] ) ? (string) $b['value'] : '';
		if ( '' === $v ) {
			return '';
		}
		$is_url = (bool) preg_match( '#^https?://#i', $v );
		$digits = ltrim( $v, '+' );
		$msg    = isset( $b['message'] ) ? trim( (string) $b['message'] ) : '';

		switch ( $b['type'] ) {
			case 'phone':
				return 'tel:' . $v;
			case 'sms':
				return 'sms:' . $v . ( '' !== $msg ? '?&body=' . rawurlencode( $msg ) : '' );
			case 'email':
				return 'mailto:' . $v . ( '' !== $msg ? '?body=' . rawurlencode( $msg ) : '' );
			case 'zalo':
				return $is_url ? $v : 'https://zalo.me/' . rawurlencode( $v );
			case 'messenger':
				return $is_url ? $v : 'https://m.me/' . rawurlencode( $v );
			case 'telegram':
				return $is_url ? $v : 'https://t.me/' . rawurlencode( ltrim( $v, '@' ) );
			case 'facebook':
				return $is_url ? $v : 'https://www.facebook.com/' . rawurlencode( $v );
			case 'tiktok':
				return $is_url ? $v : 'https://www.tiktok.com/@' . rawurlencode( ltrim( $v, '@' ) );
			case 'youtube':
				return $is_url ? $v : 'https://www.youtube.com/@' . rawurlencode( ltrim( $v, '@' ) );
			case 'instagram':
				return $is_url ? $v : 'https://www.instagram.com/' . rawurlencode( ltrim( $v, '@' ) ) . '/';
			case 'whatsapp':
				return 'https://wa.me/' . $digits . ( '' !== $msg ? '?text=' . rawurlencode( $msg ) : '' );
			case 'viber':
				return 'viber://chat?number=%2B' . $digits;
			default:
				return $v;
		}
	}
}
