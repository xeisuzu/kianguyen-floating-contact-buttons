<?php
/**
 * Front-end output.
 *
 * Everything is rendered server side and filtered with CSS media queries, so
 * the output is safe for full-page caches (no AJAX, no nonces, no user agent
 * sniffing).
 *
 * @package Kianguyen_Floating_Contact_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end renderer.
 */
class XVFCB_Frontend {

	/**
	 * Cached visibility decision for the current request.
	 *
	 * @var bool|null
	 */
	private static $show = null;

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render' ), 50 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	/**
	 * Buttons that are enabled and have a usable link.
	 *
	 * @return array
	 */
	public static function visible_buttons() {
		$set     = self::active_set();
		$buttons = $set['buttons'];
		$out     = array();
		if ( empty( $buttons ) || ! is_array( $buttons ) ) {
			return $out;
		}
		foreach ( $buttons as $b ) {
			if ( ! is_array( $b ) ) {
				continue;
			}
			$b = array_merge( XVFCB_Settings::button_defaults( isset( $b['type'] ) ? $b['type'] : 'link' ), $b );
			if ( empty( $b['enabled'] ) ) {
				continue;
			}
			$url = XVFCB_Settings::build_url( $b );
			if ( '' === $url ) {
				continue;
			}
			$b['url'] = $url;
			$out[]    = $b;
		}
		return $out;
	}

	/**
	 * Cached active set for this request.
	 *
	 * @var array|null
	 */
	private static $set = null;

	/**
	 * The button set for the current page: the first enabled page-specific
	 * set whose rules match, otherwise the default buttons.
	 *
	 * @return array With keys "name" ('' for the default set) and "buttons".
	 */
	public static function active_set() {
		if ( null !== self::$set ) {
			return self::$set;
		}
		$s         = XVFCB_Settings::get();
		self::$set = array(
			'name'    => '',
			'buttons' => (array) $s['buttons'],
		);
		if ( ! empty( $s['sets'] ) && is_array( $s['sets'] ) ) {
			$path   = self::request_path();
			$object = get_queried_object();
			foreach ( $s['sets'] as $set ) {
				if ( is_array( $set ) && ! empty( $set['enabled'] ) && self::set_matches( $set, $path, $object ) ) {
					self::$set = array(
						'name'    => (string) $set['name'],
						'buttons' => isset( $set['buttons'] ) ? (array) $set['buttons'] : array(),
					);
					break;
				}
			}
		}
		/**
		 * Filter the button set used on the current page.
		 *
		 * @param array $set Array with "name" and "buttons".
		 */
		self::$set = apply_filters( 'xvfcb_active_set', self::$set );
		return self::$set;
	}

	/**
	 * Lower-case, decoded path of the current request (no query string).
	 *
	 * @return string
	 */
	private static function request_path() {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return '';
		}
		$uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$path = wp_parse_url( $uri, PHP_URL_PATH );
		return strtolower( rawurldecode( (string) $path ) );
	}

	/**
	 * Whether a set's rules match the current page.
	 *
	 * @param array  $set    Sanitized set.
	 * @param string $path   Current path.
	 * @param mixed  $object Queried object.
	 * @return bool
	 */
	private static function set_matches( $set, $path, $object ) {
		if ( '' !== $path && ! empty( $set['paths'] ) ) {
			foreach ( explode( "\n", (string) $set['paths'] ) as $needle ) {
				$needle = trim( $needle );
				if ( '' !== $needle && false !== strpos( $path, $needle ) ) {
					return true;
				}
			}
		}

		$ids = array_filter( array_map( 'absint', explode( ',', (string) $set['ids'] ) ) );
		if ( $ids && $object instanceof WP_Post && in_array( (int) $object->ID, $ids, true ) ) {
			return true;
		}

		$terms = array_filter( array_map( 'trim', explode( ',', (string) $set['terms'] ) ) );
		if ( $terms ) {
			if ( $object instanceof WP_Term && in_array( $object->slug, $terms, true ) ) {
				return true;
			}
			if ( $object instanceof WP_Post ) {
				$slugs = wp_get_object_terms( $object->ID, get_object_taxonomies( $object->post_type ), array( 'fields' => 'slugs' ) );
				if ( is_array( $slugs ) && array_intersect( $terms, $slugs ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Whether the buttons should be shown on the current request.
	 *
	 * @return bool
	 */
	public static function should_show() {
		if ( null !== self::$show ) {
			return self::$show;
		}

		$s    = XVFCB_Settings::get();
		$show = ! empty( $s['enabled'] ) && ! is_admin() && ! is_feed() && ! is_embed();

		if ( $show && ! empty( $s['hide_front_page'] ) && is_front_page() ) {
			$show = false;
		}

		if ( $show ) {
			$object = get_queried_object();
			if ( $object instanceof WP_Post ) {
				$hide_ids = array_filter( array_map( 'absint', explode( ',', (string) $s['hide_ids'] ) ) );
				if ( in_array( (int) $object->ID, $hide_ids, true ) ) {
					$show = false;
				} elseif ( get_post_meta( $object->ID, XVFCB_Settings::META_KEY, true ) ) {
					$show = false;
				} elseif ( is_singular() && ! empty( $s['hide_post_types'] ) && in_array( $object->post_type, (array) $s['hide_post_types'], true ) ) {
					$show = false;
				}
			}
		}

		if ( $show && ! self::visible_buttons() ) {
			$show = false;
		}

		/**
		 * Filter whether the floating contact buttons are shown.
		 *
		 * @param bool $show Whether to show the buttons.
		 */
		self::$show = (bool) apply_filters( 'xvfcb_show_buttons', $show );
		return self::$show;
	}

	/**
	 * Mark the body when the phone bar is used, so page content can make room for it.
	 *
	 * @param array $classes Body classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		$s = XVFCB_Settings::get();
		if ( 'bar' === $s['mobile_style'] && self::should_show() ) {
			$classes[] = 'xvfcb-has-bar';
		}
		return $classes;
	}

	/**
	 * Enqueue assets only when buttons are shown.
	 */
	public static function enqueue() {
		if ( ! self::should_show() ) {
			return;
		}
		$s  = XVFCB_Settings::get();
		$bp = (int) $s['breakpoint'];
		$mq_mobile  = '@media (max-width:' . ( $bp - 1 ) . 'px)';
		$mq_desktop = '@media (min-width:' . $bp . 'px)';

		wp_enqueue_style( 'xvfcb-frontend', XVFCB_URL . 'assets/css/frontend.css', array(), xvfcb_asset_ver( 'assets/css/frontend.css' ) );

		$css  = $mq_mobile . '{.xvfcb .xvfcb-dev-desktop{display:none!important}}';
		$css .= $mq_desktop . '{.xvfcb .xvfcb-dev-mobile{display:none!important}}';
		// Phone size, distance from the edges and spacing (override the inline computer values).
		$css .= $mq_mobile . '{.xvfcb{--xvfcb-size:var(--xvfcb-size-m)!important;--xvfcb-x:var(--xvfcb-x-m)!important;--xvfcb-y:var(--xvfcb-y-m)!important;--xvfcb-gap:var(--xvfcb-gap-m)!important}';
		// Phone corner (floating style only; the bar spans the full width).
		$css .= '.xvfcb.xvfcb--m-right{right:var(--xvfcb-x,16px);left:auto;align-items:flex-end}.xvfcb.xvfcb--m-left{left:var(--xvfcb-x,16px);right:auto;align-items:flex-start}}';
		if ( 'bar' === $s['mobile_style'] ) {
			// Room kept under the page content: bar padding + icon + gap + one label line.
			$space = (int) $s['size_mobile'] + 16 + (int) ceil( (int) $s['bar_font'] * 1.2 );
			$css  .= $mq_mobile . '{body.xvfcb-has-bar{--xvfcb-bar-space:' . $space . 'px}}';
		}
		$css .= $mq_mobile . '{.xvfcb--js.xvfcb--collapse-mobile .xvfcb__toggle{display:inline-flex}.xvfcb--js.xvfcb--collapse-mobile:not(.is-open) .xvfcb__list{display:none}}';
		$css .= $mq_desktop . '{.xvfcb--js.xvfcb--collapse-desktop .xvfcb__toggle{display:inline-flex}.xvfcb--js.xvfcb--collapse-desktop:not(.is-open) .xvfcb__list{display:none}}';
		wp_add_inline_style( 'xvfcb-frontend', $css );

		if ( 'bar' === $s['mobile_style'] ) {
			wp_enqueue_style( 'xvfcb-bar', XVFCB_URL . 'assets/css/bar.css', array( 'xvfcb-frontend' ), xvfcb_asset_ver( 'assets/css/bar.css' ), '(max-width: ' . ( $bp - 1 ) . 'px)' );
		}

		wp_enqueue_script( 'xvfcb-frontend', XVFCB_URL . 'assets/js/frontend.js', array(), xvfcb_asset_ver( 'assets/js/frontend.js' ), true );
	}

	/**
	 * Front-end configuration passed to the script.
	 *
	 * @param array $s Settings.
	 * @return array
	 */
	private static function config( $s ) {
		$tz = function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : '';
		return array(
			'track' => ! empty( $s['tracking'] ),
			'set'   => ( '' !== self::active_set()['name'] ) ? self::active_set()['name'] : 'default',
			'event' => (string) $s['event_name'],
			'gtag'  => ! empty( $s['gtag'] ),
			'bp'    => (int) $s['breakpoint'],
			'call'  => (string) $s['desktop_call'],
			'reveal' => array(
				'mode'   => (string) $s['reveal'],
				'scroll' => (int) $s['reveal_scroll'],
				'delay'  => (int) $s['reveal_delay'],
			),
			'hours' => array(
				'on'     => ! empty( $s['hours_enabled'] ),
				'week'   => self::week_config( $s['hours_week'] ),
				'tz'     => ( false !== strpos( $tz, '/' ) || 'UTC' === $tz ) ? $tz : '',
				'offset' => (int) round( (float) get_option( 'gmt_offset', 0 ) * 60 ),
			),
			'i18n'  => array(
				'copy'   => __( 'Copy number', 'kianguyen-floating-contact-buttons' ),
				'copied' => __( 'Copied!', 'kianguyen-floating-contact-buttons' ),
				'close'  => __( 'Close', 'kianguyen-floating-contact-buttons' ),
			),
		);
	}

	/**
	 * Compact weekly schedule for the script: day number => [start, end],
	 * open days only.
	 *
	 * @param array $week Sanitized schedule.
	 * @return array
	 */
	private static function week_config( $week ) {
		$out = array();
		foreach ( (array) $week as $day => $row ) {
			if ( ! empty( $row['on'] ) ) {
				$out[ (string) (int) $day ] = array( (string) $row['start'], (string) $row['end'] );
			}
		}
		return (object) $out;
	}

	/**
	 * Order of the buttons in the phone bar: the first featured button goes
	 * to the middle, the others keep their order around it.
	 *
	 * @param array $buttons Visible buttons.
	 * @return array Keys "featured" (index or -1) and "order" (index => position).
	 */
	public static function bar_order( $buttons ) {
		$featured = -1;
		foreach ( array_values( $buttons ) as $i => $b ) {
			if ( ! empty( $b['featured'] ) ) {
				$featured = $i;
				break;
			}
		}
		$order  = array();
		$others = array();
		foreach ( array_keys( array_values( $buttons ) ) as $i ) {
			if ( $i !== $featured ) {
				$others[] = $i;
			}
		}
		if ( $featured >= 0 ) {
			array_splice( $others, (int) floor( count( $others ) / 2 ), 0, array( $featured ) );
		}
		foreach ( $others as $position => $i ) {
			$order[ $i ] = $position;
		}
		ksort( $order );
		return array(
			'featured' => $featured,
			'order'    => $order,
		);
	}

	/**
	 * Print the buttons in the footer.
	 */
	public static function render() {
		if ( ! self::should_show() ) {
			return;
		}

		$s       = XVFCB_Settings::get();
		$buttons = self::visible_buttons();
		$types   = XVFCB_Settings::types();

		$classes = array(
			'xvfcb',
			'xvfcb--' . $s['position'],
			'xvfcb--layout-' . $s['layout'],
			'xvfcb--anim-' . $s['animation'],
			'xvfcb--collapse-' . $s['collapse'],
		);
		if ( 'bar' === $s['mobile_style'] ) {
			$classes[] = 'xvfcb--bar';
		} else {
			$classes[] = 'xvfcb--m-' . ( ( 'left' === $s['position_mobile'] ) ? 'left' : 'right' );
		}
		if ( 'immediate' !== $s['reveal'] ) {
			$classes[] = 'xvfcb--waiting';
		}
		if ( ! empty( $s['show_tooltip'] ) && 'icon' === $s['layout'] ) {
			$classes[] = 'xvfcb--tooltip';
		}

		$style = sprintf(
			'--xvfcb-size:%1$dpx;--xvfcb-x:%2$dpx;--xvfcb-y:%3$dpx;--xvfcb-gap:%4$dpx;--xvfcb-z:%5$d;--xvfcb-bar-bg:%6$s;--xvfcb-bar-text:%7$s;--xvfcb-bar-font:%8$dpx;--xvfcb-size-m:%9$dpx;--xvfcb-count:%10$d;--xvfcb-x-m:%11$dpx;--xvfcb-y-m:%12$dpx;--xvfcb-gap-m:%13$dpx;',
			(int) $s['size'],
			(int) $s['offset_x'],
			(int) $s['offset_y'],
			(int) $s['gap'],
			(int) $s['z_index'],
			$s['bar_bg'],
			$s['bar_text'],
			(int) $s['bar_font'],
			(int) $s['size_mobile'],
			max( 1, count( $buttons ) ),
			(int) $s['offset_x_mobile'],
			(int) $s['offset_y_mobile'],
			(int) $s['gap_mobile']
		);

		$order = self::bar_order( $buttons );

		$protocols = array_merge( wp_allowed_protocols(), array( 'viber' ) );
		$group     = __( 'Contact us', 'kianguyen-floating-contact-buttons' );

		echo "\n<!-- Kianguyen Floating Contact Buttons -->\n";
		printf(
			'<div class="%1$s" style="%2$s" role="group" aria-label="%3$s" data-xvfcb-config="%4$s">',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $style ),
			esc_attr( $group ),
			esc_attr( wp_json_encode( self::config( $s ) ) )
		);
		echo '<div class="xvfcb__list" id="xvfcb-list">';

		foreach ( $buttons as $pos => $b ) {
			$type  = $b['type'];
			$label = ( '' !== $b['label'] ) ? $b['label'] : $types[ $type ]['label'];
			$url   = $b['url'];

			$btn_classes = array( 'xvfcb__btn', 'xvfcb__btn--' . $type, 'xvfcb-dev-' . $b['device'] );
			if ( ! empty( $b['attention'] ) ) {
				$btn_classes[] = 'xvfcb-attn';
			}
			if ( $pos === $order['featured'] ) {
				$btn_classes[] = 'xvfcb-featured';
			}
			if ( 'custom' === $b['icon'] && '' !== $b['icon_url'] ) {
				$btn_classes[] = 'xvfcb-custom-icon';
			} elseif ( XVFCB_Icons::is_color( $type, $b['icon'] ) ) {
				$btn_classes[] = 'xvfcb-color-logo';
			}

			$attrs = array(
				'class'            => implode( ' ', $btn_classes ),
				'style'            => '--xvfcb-color:' . $b['color'] . ';--xvfcb-text:' . XVFCB_Settings::text_color( $b['color'] ) . ';--xvfcb-order:' . $order['order'][ $pos ],
				'aria-label'       => $label,
				'data-xvfcb-type'  => $type,
				'data-xvfcb-label' => $label,
			);
			if ( 'phone' === $type ) {
				$attrs['data-xvfcb-number'] = $b['value'];
			}
			if ( 'always' !== $b['hours'] ) {
				$attrs['data-xvfcb-hours'] = $b['hours'];
			}
			if ( '' !== $b['message'] && in_array( $type, XVFCB_Settings::message_types(), true ) ) {
				$attrs['data-xvfcb-msg'] = '1';
			}
			if ( ! empty( $s['new_tab'] ) && preg_match( '#^https?://#i', $url ) ) {
				$attrs['target'] = '_blank';
				$attrs['rel']    = 'noopener noreferrer';
			}

			echo '<a href="' . esc_url( $url, $protocols ) . '"';
			foreach ( $attrs as $name => $value ) {
				echo ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
			}
			echo '>';

			echo '<span class="xvfcb__icon">';
			if ( 'custom' === $b['icon'] && '' !== $b['icon_url'] ) {
				printf( '<img src="%s" alt="" width="24" height="24" loading="lazy" decoding="async">', esc_url( $b['icon_url'] ) );
			} else {
				echo wp_kses( XVFCB_Icons::for_type( $type, $b['icon'] ), XVFCB_Icons::kses() );
			}
			echo '</span>';
			printf( '<span class="xvfcb__label">%s</span>', esc_html( $label ) );
			echo '</a>';
		}

		echo '</div>';

		if ( 'off' !== $s['collapse'] ) {
			$toggle_label = ( '' !== $s['collapse_label'] ) ? $s['collapse_label'] : $group;
			printf(
				'<button type="button" class="xvfcb__btn xvfcb__toggle%1$s" style="--xvfcb-color:%2$s;--xvfcb-text:%4$s" aria-expanded="false" aria-controls="xvfcb-list" aria-label="%3$s" data-xvfcb-label="%3$s">',
				esc_attr( ( 'none' !== $s['animation'] ) ? ' xvfcb-attn' : '' ),
				esc_attr( $s['toggle_color'] ),
				esc_attr( $toggle_label ),
				esc_attr( XVFCB_Settings::text_color( $s['toggle_color'] ) )
			);
			echo '<span class="xvfcb__icon xvfcb__toggle-open">' . wp_kses( XVFCB_Icons::svg( 'chat-dots' ), XVFCB_Icons::kses() ) . '</span>';
			echo '<span class="xvfcb__icon xvfcb__toggle-close">' . wp_kses( XVFCB_Icons::svg( 'close' ), XVFCB_Icons::kses() ) . '</span>';
			printf( '<span class="xvfcb__label">%s</span>', esc_html( $toggle_label ) );
			echo '</button>';
		}

		echo "</div>\n";
	}
}
