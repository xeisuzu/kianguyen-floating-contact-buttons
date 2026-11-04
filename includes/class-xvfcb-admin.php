<?php
/**
 * Admin: settings page and per-post meta box.
 *
 * @package XeVN_Floating_Contact_Buttons
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin screens.
 */
class XVFCB_Admin {

	const PAGE  = 'xevn-floating-contact-buttons';
	const GROUP = 'xvfcb_group';

	/**
	 * Settings page hook suffix.
	 *
	 * @var string
	 */
	private static $hook = '';

	/**
	 * Hook into WordPress.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ), 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( XVFCB_FILE ), array( __CLASS__, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'row_meta' ), 10, 2 );
		add_action( 'admin_post_xvfcb_export', array( __CLASS__, 'export' ) );
		add_action( 'admin_post_xvfcb_import', array( __CLASS__, 'import' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
	}

	/**
	 * Register the option.
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			XVFCB_Settings::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'XVFCB_Settings', 'sanitize' ),
				'default'           => XVFCB_Settings::defaults(),
			)
		);
	}

	/**
	 * Add the settings page.
	 */
	public static function menu() {
		self::$hook = add_options_page(
			'XeVN Floating Contact Buttons',
			__( 'Contact Buttons', 'xevn-floating-contact-buttons' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'page' )
		);
	}

	/**
	 * Settings link on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=' . self::PAGE );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'xevn-floating-contact-buttons' ) . '</a>' );
		return $links;
	}

	/**
	 * Donate link in the plugin row on the Plugins screen.
	 *
	 * @param array  $meta Row meta links.
	 * @param string $file Plugin file.
	 * @return array
	 */
	public static function row_meta( $meta, $file ) {
		if ( plugin_basename( XVFCB_FILE ) === $file ) {
			$meta[] = '<a href="' . esc_url( XVFCB_DONATE_URL ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Donate', 'xevn-floating-contact-buttons' ) . '</a>';
		}
		return $meta;
	}

	/**
	 * Enqueue admin assets on the settings page only.
	 *
	 * @param string $hook Current admin page.
	 */
	public static function assets( $hook ) {
		if ( $hook !== self::$hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'xvfcb-frontend', XVFCB_URL . 'assets/css/frontend.css', array(), xvfcb_asset_ver( 'assets/css/frontend.css' ) );
		wp_enqueue_style( 'xvfcb-bar', XVFCB_URL . 'assets/css/bar.css', array( 'xvfcb-frontend' ), xvfcb_asset_ver( 'assets/css/bar.css' ) );
		wp_enqueue_style( 'xvfcb-admin', XVFCB_URL . 'assets/css/admin.css', array( 'xvfcb-frontend', 'xvfcb-bar' ), xvfcb_asset_ver( 'assets/css/admin.css' ) );
		wp_enqueue_script( 'xvfcb-admin', XVFCB_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), xvfcb_asset_ver( 'assets/js/admin.js' ), true );

		$hints   = array();
		$colors  = array();
		$icons   = array();
		$generic = array();
		$mono    = array();
		foreach ( XVFCB_Settings::types() as $key => $type ) {
			$hints[ $key ]   = $type['hint'];
			$colors[ $key ]  = $type['color'];
			$icons[ $key ]   = XVFCB_Icons::for_type( $key, 'default' );
			$mono[ $key ]    = XVFCB_Icons::for_type( $key, 'mono' );
			$generic[ $key ] = XVFCB_Icons::for_type( $key, 'generic' );
		}
		wp_localize_script(
			'xvfcb-admin',
			'xvfcbAdmin',
			array(
				'hints'       => $hints,
				'colors'      => $colors,
				'icons'       => $icons,
				'generic'     => $generic,
				'mono'        => $mono,
				'messageTypes' => XVFCB_Settings::message_types(),
				'labels'       => wp_list_pluck( XVFCB_Settings::types(), 'label' ),
				'toggleIcon'   => XVFCB_Icons::svg( 'chat-dots' ),
				'closeIcon'    => XVFCB_Icons::svg( 'close' ),
				'contactUs'    => __( 'Contact us', 'xevn-floating-contact-buttons' ),
				'emptyNote'    => __( 'Not shown on the website until you enter a phone number, username or link.', 'xevn-floating-contact-buttons' ),
				'max'         => XVFCB_Settings::MAX_BUTTONS,
				'chooseIcon'  => __( 'Choose icon image', 'xevn-floating-contact-buttons' ),
				'useIcon'     => __( 'Use this image', 'xevn-floating-contact-buttons' ),
				'confirmDel'  => __( 'Remove this button?', 'xevn-floating-contact-buttons' ),
				'confirmSet'  => __( 'Delete this set and its buttons?', 'xevn-floating-contact-buttons' ),
				'newSet'      => __( 'New set', 'xevn-floating-contact-buttons' ),
				'maxReached'  => __( 'Maximum number of buttons reached.', 'xevn-floating-contact-buttons' ),
			)
		);
	}


	/**
	 * Whether the button color is visible on the site for this button.
	 *
	 * Full-color logos and uploaded images replace the round colored
	 * background, so their color only matters in the "icon with label" style.
	 *
	 * @param array $b Button data.
	 * @return bool
	 */
	private static function color_matters( $b ) {
		$s = XVFCB_Settings::get();
		if ( 'label' === $s['layout'] ) {
			return true;
		}
		if ( 'custom' === $b['icon'] && '' !== $b['icon_url'] ) {
			return false;
		}
		return ! XVFCB_Icons::is_color( $b['type'], $b['icon'] );
	}

	/**
	 * Print a slider with its current value.
	 *
	 * @param string $key   Setting key.
	 * @param string $label Small label before the slider ('' for none).
	 * @param int    $min   Minimum.
	 * @param int    $max   Maximum.
	 * @param int    $step  Step.
	 * @param string $unit  Unit shown after the value.
	 * @param int    $value Current value.
	 * @param string $id    Optional element ID.
	 */
	private static function range( $key, $label, $min, $max, $step, $unit, $value, $id = '' ) {
		$id = ( '' !== $id ) ? $id : 'xvfcb-r-' . $key;
		?>
		<span class="xvfcb-range">
			<?php if ( '' !== $label ) : ?>
				<label class="xvfcb-range__name" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $label ); ?></label>
			<?php endif; ?>
			<input id="<?php echo esc_attr( $id ); ?>" type="range" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>" name="<?php echo esc_attr( self::name( $key ) ); ?>" value="<?php echo esc_attr( $value ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>">
			<output class="xvfcb-range__value" for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $value . $unit ); ?></output>
		</span>
		<?php
	}

	/**
	 * Field name helper.
	 *
	 * @param string $key Setting key.
	 * @return string
	 */
	private static function name( $key ) {
		return XVFCB_Settings::OPTION . '[' . $key . ']';
	}

	/**
	 * Render one button row.
	 *
	 * @param string|int $index  Row index or template placeholder.
	 * @param array      $b      Button data.
	 * @param string     $prefix Field name prefix for the list.
	 * @param string     $uid    Unique part for element IDs.
	 */
	private static function row( $index, $b, $prefix = 'xvfcb_settings[buttons]', $uid = 'd' ) {
		$types = XVFCB_Settings::types();
		$b     = array_merge( XVFCB_Settings::button_defaults( isset( $b['type'] ) ? $b['type'] : 'phone' ), $b );
		$base  = $prefix . '[' . $index . ']';
		$id    = 'xvfcb-b-' . $uid . '-' . $index;
		?>
		<div class="xvfcb-row<?php echo $b['enabled'] ? '' : ' is-disabled'; ?>">
			<div class="xvfcb-row__head">
				<span class="xvfcb-row__swatch<?php echo XVFCB_Icons::is_color( $b['type'], $b['icon'] ) ? ' is-color-logo' : ''; ?>" style="background:<?php echo esc_attr( $b['color'] ); ?>">
					<?php
					if ( 'custom' === $b['icon'] && '' !== $b['icon_url'] ) {
						printf( '<img src="%s" alt="">', esc_url( $b['icon_url'] ) );
					} else {
						echo wp_kses( XVFCB_Icons::for_type( $b['type'], $b['icon'] ), XVFCB_Icons::kses() );
					}
					?>
				</span>
				<strong class="xvfcb-row__title"><?php echo esc_html( '' !== $b['label'] ? $b['label'] : $types[ $b['type'] ]['label'] ); ?></strong>
				<label class="xvfcb-row__toggle">
					<input type="checkbox" name="<?php echo esc_attr( $base ); ?>[enabled]" value="1" <?php checked( $b['enabled'], 1 ); ?>>
					<?php esc_html_e( 'Enabled', 'xevn-floating-contact-buttons' ); ?>
				</label>
				<span class="xvfcb-row__tools">
					<button type="button" class="button-link xvfcb-up" aria-label="<?php esc_attr_e( 'Move up', 'xevn-floating-contact-buttons' ); ?>">&#9650;</button>
					<button type="button" class="button-link xvfcb-down" aria-label="<?php esc_attr_e( 'Move down', 'xevn-floating-contact-buttons' ); ?>">&#9660;</button>
					<button type="button" class="button-link button-link-delete xvfcb-remove"><?php esc_html_e( 'Remove', 'xevn-floating-contact-buttons' ); ?></button>
				</span>
			</div>
			<div class="xvfcb-row__grid">
				<p>
					<label for="<?php echo esc_attr( $id ); ?>-type"><?php esc_html_e( 'Type', 'xevn-floating-contact-buttons' ); ?></label>
					<select id="<?php echo esc_attr( $id ); ?>-type" class="xvfcb-type" name="<?php echo esc_attr( $base ); ?>[type]">
						<?php foreach ( $types as $key => $type ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $b['type'], $key ); ?>><?php echo esc_html( $type['label'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="xvfcb-wide">
					<label for="<?php echo esc_attr( $id ); ?>-value"><?php esc_html_e( 'Phone / username / link', 'xevn-floating-contact-buttons' ); ?></label>
					<input id="<?php echo esc_attr( $id ); ?>-value" class="regular-text xvfcb-value" type="text" name="<?php echo esc_attr( $base ); ?>[value]" value="<?php echo esc_attr( $b['value'] ); ?>" placeholder="<?php echo esc_attr( $types[ $b['type'] ]['hint'] ); ?>">
				</p>
				<p>
					<label for="<?php echo esc_attr( $id ); ?>-label"><?php esc_html_e( 'Label', 'xevn-floating-contact-buttons' ); ?></label>
					<input id="<?php echo esc_attr( $id ); ?>-label" class="xvfcb-label" type="text" name="<?php echo esc_attr( $base ); ?>[label]" value="<?php echo esc_attr( $b['label'] ); ?>" placeholder="<?php echo esc_attr( $types[ $b['type'] ]['label'] ); ?>">
				</p>
				<p class="xvfcb-color-field"<?php echo self::color_matters( $b ) ? '' : ' hidden'; ?>>
					<label><?php esc_html_e( 'Color', 'xevn-floating-contact-buttons' ); ?></label>
					<input class="xvfcb-color" type="text" name="<?php echo esc_attr( $base ); ?>[color]" value="<?php echo esc_attr( $b['color'] ); ?>" data-default-color="<?php echo esc_attr( $types[ $b['type'] ]['color'] ); ?>">
				</p>
				<p>
					<label for="<?php echo esc_attr( $id ); ?>-device"><?php esc_html_e( 'Show on', 'xevn-floating-contact-buttons' ); ?></label>
					<select id="<?php echo esc_attr( $id ); ?>-device" name="<?php echo esc_attr( $base ); ?>[device]">
						<option value="all" <?php selected( $b['device'], 'all' ); ?>><?php esc_html_e( 'All devices', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="mobile" <?php selected( $b['device'], 'mobile' ); ?>><?php esc_html_e( 'Mobile only', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="desktop" <?php selected( $b['device'], 'desktop' ); ?>><?php esc_html_e( 'Desktop only', 'xevn-floating-contact-buttons' ); ?></option>
					</select>
				</p>
				<p>
					<label for="<?php echo esc_attr( $id ); ?>-iconstyle"><?php esc_html_e( 'Icon', 'xevn-floating-contact-buttons' ); ?></label>
					<select id="<?php echo esc_attr( $id ); ?>-iconstyle" class="xvfcb-icon-style" name="<?php echo esc_attr( $base ); ?>[icon]">
						<option value="default" <?php selected( $b['icon'], 'default' ); ?>><?php esc_html_e( 'Default (full-color logo)', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="mono" <?php selected( $b['icon'], 'mono' ); ?>><?php esc_html_e( 'White logo on button color', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="generic" <?php selected( $b['icon'], 'generic' ); ?>><?php esc_html_e( 'Simple icon', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="custom" <?php selected( $b['icon'], 'custom' ); ?>><?php esc_html_e( 'Uploaded image', 'xevn-floating-contact-buttons' ); ?></option>
					</select>
				</p>
				<p class="xvfcb-wide xvfcb-icon-upload"<?php echo ( 'custom' === $b['icon'] ) ? '' : ' hidden'; ?>>
					<label for="<?php echo esc_attr( $id ); ?>-icon"><?php esc_html_e( 'Icon image', 'xevn-floating-contact-buttons' ); ?></label>
					<span class="xvfcb-icon-field">
						<input id="<?php echo esc_attr( $id ); ?>-icon" class="regular-text xvfcb-icon-url" type="url" name="<?php echo esc_attr( $base ); ?>[icon_url]" value="<?php echo esc_attr( $b['icon_url'] ); ?>" placeholder="https://">
						<button type="button" class="button xvfcb-pick-icon"><?php esc_html_e( 'Upload / choose', 'xevn-floating-contact-buttons' ); ?></button>
					</span>
				</p>
				<p>
					<label for="<?php echo esc_attr( $id ); ?>-hours"><?php esc_html_e( 'Working hours', 'xevn-floating-contact-buttons' ); ?></label>
					<select id="<?php echo esc_attr( $id ); ?>-hours" name="<?php echo esc_attr( $base ); ?>[hours]">
						<option value="always" <?php selected( $b['hours'], 'always' ); ?>><?php esc_html_e( 'Show at any time', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="open" <?php selected( $b['hours'], 'open' ); ?>><?php esc_html_e( 'Only during working hours', 'xevn-floating-contact-buttons' ); ?></option>
						<option value="closed" <?php selected( $b['hours'], 'closed' ); ?>><?php esc_html_e( 'Only outside working hours', 'xevn-floating-contact-buttons' ); ?></option>
					</select>
				</p>
				<p class="xvfcb-wide xvfcb-message"<?php echo in_array( $b['type'], XVFCB_Settings::message_types(), true ) ? '' : ' hidden'; ?>>
					<label for="<?php echo esc_attr( $id ); ?>-message"><?php esc_html_e( 'Pre-filled message (optional)', 'xevn-floating-contact-buttons' ); ?></label>
					<input id="<?php echo esc_attr( $id ); ?>-message" class="regular-text" type="text" maxlength="300" name="<?php echo esc_attr( $base ); ?>[message]" value="<?php echo esc_attr( $b['message'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. I would like a quote for: {title}', 'xevn-floating-contact-buttons' ); ?>">
					<span class="description"><?php esc_html_e( 'Use {title} for the page title and {url} for the page link.', 'xevn-floating-contact-buttons' ); ?></span>
				</p>
				<p>
					<label class="xvfcb-inline">
						<input type="checkbox" name="<?php echo esc_attr( $base ); ?>[attention]" value="1" <?php checked( $b['attention'], 1 ); ?>>
						<?php esc_html_e( 'Attention effect', 'xevn-floating-contact-buttons' ); ?>
					</label>
					<label class="xvfcb-inline xvfcb-featured-field"<?php echo ( 'bar' === XVFCB_Settings::get()['mobile_style'] ) ? '' : ' hidden'; ?> title="<?php esc_attr_e( 'Only one button per set can be featured. Choosing another one switches it automatically.', 'xevn-floating-contact-buttons' ); ?>">
						<input type="checkbox" class="xvfcb-featured-input" name="<?php echo esc_attr( $base ); ?>[featured]" value="1" <?php checked( $b['featured'], 1 ); ?>>
						<?php esc_html_e( 'Featured in the middle of the phone bar', 'xevn-floating-contact-buttons' ); ?>
					</label>
				</p>
			</div>
		</div>
		<?php
	}

	/**
	 * Render one page-specific set panel.
	 *
	 * @param int|string $k   Set index or template placeholder.
	 * @param array      $set Set data.
	 */
	private static function set_panel( $k, $set ) {
		$set    = array_merge(
			array(
				'name'    => '',
				'enabled' => 1,
				'paths'   => '',
				'ids'     => '',
				'terms'   => '',
				'buttons' => array(),
			),
			(array) $set
		);
		$prefix = XVFCB_Settings::OPTION . '[sets][' . $k . ']';
		$id     = 'xvfcb-set-' . $k;
		?>
		<div class="xvfcb-panel" data-panel="set-<?php echo esc_attr( $k ); ?>" hidden>
			<table class="form-table xvfcb-set-rules" role="presentation">
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $id ); ?>-name"><?php esc_html_e( 'Set name', 'xevn-floating-contact-buttons' ); ?></label></th>
					<td>
						<input id="<?php echo esc_attr( $id ); ?>-name" class="regular-text xvfcb-set-name" type="text" name="<?php echo esc_attr( $prefix ); ?>[name]" value="<?php echo esc_attr( $set['name'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Product pages', 'xevn-floating-contact-buttons' ); ?>">
						<label class="xvfcb-set-enabled"><input type="checkbox" name="<?php echo esc_attr( $prefix ); ?>[enabled]" value="1" <?php checked( $set['enabled'], 1 ); ?>> <?php esc_html_e( 'Enabled', 'xevn-floating-contact-buttons' ); ?></label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $id ); ?>-paths"><?php esc_html_e( 'Page links containing', 'xevn-floating-contact-buttons' ); ?></label></th>
					<td>
						<textarea id="<?php echo esc_attr( $id ); ?>-paths" class="large-text code" rows="3" name="<?php echo esc_attr( $prefix ); ?>[paths]" placeholder="/products/&#10;/services/"><?php echo esc_textarea( $set['paths'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'One per line. The set is used when the page address contains this text, e.g. /products/ matches every page under /products/.', 'xevn-floating-contact-buttons' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $id ); ?>-ids"><?php esc_html_e( 'Post or page IDs', 'xevn-floating-contact-buttons' ); ?></label></th>
					<td><input id="<?php echo esc_attr( $id ); ?>-ids" class="regular-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[ids]" value="<?php echo esc_attr( $set['ids'] ); ?>" placeholder="12, 345"></td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $id ); ?>-terms"><?php esc_html_e( 'Category slugs', 'xevn-floating-contact-buttons' ); ?></label></th>
					<td>
						<input id="<?php echo esc_attr( $id ); ?>-terms" class="regular-text" type="text" name="<?php echo esc_attr( $prefix ); ?>[terms]" value="<?php echo esc_attr( $set['terms'] ); ?>" placeholder="products, services">
						<p class="description"><?php esc_html_e( 'Category, tag or product category slugs, separated by commas. Matches the category page and every post in it.', 'xevn-floating-contact-buttons' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="description"><?php esc_html_e( 'A page uses the first enabled set that matches any of its rules; otherwise it uses the Default buttons.', 'xevn-floating-contact-buttons' ); ?></p>
			<div class="xvfcb-rowlist" data-base="<?php echo esc_attr( $prefix . '[buttons]' ); ?>">
				<?php
				foreach ( (array) $set['buttons'] as $i => $b ) {
					if ( is_array( $b ) ) {
						self::row( (int) $i, $b, $prefix . '[buttons]', 's' . $k );
					}
				}
				?>
			</div>
			<p class="xvfcb-set-actions">
				<button type="button" class="button xvfcb-add"><?php esc_html_e( 'Add button', 'xevn-floating-contact-buttons' ); ?></button>
				<button type="button" class="button xvfcb-copy-default"><?php esc_html_e( 'Copy buttons from Default', 'xevn-floating-contact-buttons' ); ?></button>
				<button type="button" class="button-link button-link-delete xvfcb-remove-set"><?php esc_html_e( 'Delete this set', 'xevn-floating-contact-buttons' ); ?></button>
			</p>
		</div>
		<?php
	}

	/**
	 * Render the settings page.
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$s          = XVFCB_Settings::get();
		$post_types = get_post_types( array( 'public' => true ), 'objects' );
		unset( $post_types['attachment'] );
		?>
		<div class="wrap xvfcb-wrap">
			<h1>XeVN Floating Contact Buttons</h1>

			<div class="xvfcb-preview-bar">
				<span class="xvfcb-preview-bar__title"><?php esc_html_e( 'Live preview', 'xevn-floating-contact-buttons' ); ?></span>
				<span class="xvfcb-seg" role="group" aria-label="<?php esc_attr_e( 'Live preview', 'xevn-floating-contact-buttons' ); ?>">
					<button type="button" class="xvfcb-seg__btn" data-xvfcb-preview="mobile" aria-pressed="false"><?php esc_html_e( 'Phone', 'xevn-floating-contact-buttons' ); ?></button>
					<button type="button" class="xvfcb-seg__btn is-active" data-xvfcb-preview="desktop" aria-pressed="true"><?php esc_html_e( 'Computer', 'xevn-floating-contact-buttons' ); ?></button>
					<button type="button" class="xvfcb-seg__btn" data-xvfcb-preview="off" aria-pressed="false"><?php esc_html_e( 'Hide', 'xevn-floating-contact-buttons' ); ?></button>
				</span>
				<span class="xvfcb-preview-bar__hint"><?php esc_html_e( 'The buttons appear in the corner of this page and update as you edit. Click "Save Changes" to apply them to the website. Working hours and delayed showing are not simulated.', 'xevn-floating-contact-buttons' ); ?></span>
			</div>

			<form method="post" action="options.php">
				<?php settings_fields( self::GROUP ); ?>

				<h2><?php esc_html_e( 'Buttons', 'xevn-floating-contact-buttons' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Buttons are shown from top to bottom in this order. A button without a phone number, username or link is not shown.', 'xevn-floating-contact-buttons' ); ?></p>

				<div id="xvfcb-sets" data-max-sets="<?php echo esc_attr( XVFCB_Settings::MAX_SETS ); ?>">
					<div class="xvfcb-tabs" role="tablist">
						<button type="button" class="xvfcb-tab is-active" role="tab" data-panel="default"><?php esc_html_e( 'Default', 'xevn-floating-contact-buttons' ); ?></button>
						<?php foreach ( (array) $s['sets'] as $k => $set ) : ?>
							<button type="button" class="xvfcb-tab" role="tab" data-panel="set-<?php echo esc_attr( $k ); ?>"><?php echo esc_html( $set['name'] ); ?></button>
						<?php endforeach; ?>
						<button type="button" class="xvfcb-tab-add"><?php esc_html_e( '+ Add a set for specific pages', 'xevn-floating-contact-buttons' ); ?></button>
					</div>

					<div class="xvfcb-panel is-active" data-panel="default">
						<p class="description"><?php esc_html_e( 'Used on every page that does not match one of the page-specific sets.', 'xevn-floating-contact-buttons' ); ?></p>
						<div class="xvfcb-rowlist" id="xvfcb-rows" data-base="<?php echo esc_attr( XVFCB_Settings::OPTION . '[buttons]' ); ?>">
							<?php
							foreach ( (array) $s['buttons'] as $i => $b ) {
								if ( is_array( $b ) ) {
									self::row( (int) $i, $b );
								}
							}
							?>
						</div>
						<p><button type="button" class="button xvfcb-add" id="xvfcb-add"><?php esc_html_e( 'Add button', 'xevn-floating-contact-buttons' ); ?></button></p>
					</div>

					<?php
					foreach ( (array) $s['sets'] as $k => $set ) {
						self::set_panel( (int) $k, $set );
					}
					?>
				</div>
				<script type="text/html" id="tmpl-xvfcb-row"><?php self::row( '__i__', XVFCB_Settings::button_defaults( 'phone' ), '__base__', '__u__' ); ?></script>
				<script type="text/html" id="tmpl-xvfcb-set"><?php self::set_panel( '__k__', array() ); ?></script>

				<h2><?php esc_html_e( 'Appearance', 'xevn-floating-contact-buttons' ); ?></h2>

				<label class="xvfcb-master">
					<input type="checkbox" name="<?php echo esc_attr( self::name( 'enabled' ) ); ?>" value="1" <?php checked( $s['enabled'], 1 ); ?>>
					<span><strong><?php esc_html_e( 'Show buttons', 'xevn-floating-contact-buttons' ); ?></strong> &mdash; <?php esc_html_e( 'Display the floating buttons on the website', 'xevn-floating-contact-buttons' ); ?></span>
				</label>

				<div class="xvfcb-cards">
					<section class="xvfcb-card">
						<h3 class="xvfcb-card__title"><?php esc_html_e( 'Position & size', 'xevn-floating-contact-buttons' ); ?></h3>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-position"><?php esc_html_e( 'Position', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-position" name="<?php echo esc_attr( self::name( 'position' ) ); ?>">
									<option value="right" <?php selected( $s['position'], 'right' ); ?>><?php esc_html_e( 'Bottom right', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="left" <?php selected( $s['position'], 'left' ); ?>><?php esc_html_e( 'Bottom left', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
							</div>
						</div>
						<div class="xvfcb-field">
							<span class="xvfcb-field__label"><?php esc_html_e( 'Distance from edges', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control">
								<?php self::range( 'offset_x', __( 'Side', 'xevn-floating-contact-buttons' ), 0, 200, 1, 'px', $s['offset_x'] ); ?>
								<?php self::range( 'offset_y', __( 'Bottom', 'xevn-floating-contact-buttons' ), 0, 400, 1, 'px', $s['offset_y'] ); ?>
							</div>
						</div>
						<div class="xvfcb-field">
							<span class="xvfcb-field__label"><?php esc_html_e( 'Button size', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control">
								<?php self::range( 'size', __( 'Computer', 'xevn-floating-contact-buttons' ), 36, 80, 1, 'px', $s['size'] ); ?>
								<?php self::range( 'size_mobile', __( 'Phone', 'xevn-floating-contact-buttons' ), 32, 72, 1, 'px', $s['size_mobile'] ); ?>
								<p class="description"><?php esc_html_e( 'The phone size applies to screens narrower than the mobile breakpoint, in both the floating style and the bottom bar (the featured button is drawn larger automatically).', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
						<div class="xvfcb-field">
							<span class="xvfcb-field__label"><?php esc_html_e( 'Spacing', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control">
								<?php self::range( 'gap', '', 0, 40, 1, 'px', $s['gap'] ); ?>
							</div>
						</div>
					</section>

					<section class="xvfcb-card">
						<h3 class="xvfcb-card__title"><?php esc_html_e( 'Button style', 'xevn-floating-contact-buttons' ); ?></h3>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-layout"><?php esc_html_e( 'Style', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-layout" name="<?php echo esc_attr( self::name( 'layout' ) ); ?>">
									<option value="icon" <?php selected( $s['layout'], 'icon' ); ?>><?php esc_html_e( 'Round icon', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="label" <?php selected( $s['layout'], 'label' ); ?>><?php esc_html_e( 'Icon with label', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
								<label class="xvfcb-check"><input type="checkbox" name="<?php echo esc_attr( self::name( 'show_tooltip' ) ); ?>" value="1" <?php checked( $s['show_tooltip'], 1 ); ?>> <?php esc_html_e( 'Show the label as a tooltip on hover (round icon style)', 'xevn-floating-contact-buttons' ); ?></label>
							</div>
						</div>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-animation"><?php esc_html_e( 'Attention effect', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-animation" name="<?php echo esc_attr( self::name( 'animation' ) ); ?>">
									<option value="none" <?php selected( $s['animation'], 'none' ); ?>><?php esc_html_e( 'None', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="shake" <?php selected( $s['animation'], 'shake' ); ?>><?php esc_html_e( 'Ringing icon', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="pulse" <?php selected( $s['animation'], 'pulse' ); ?>><?php esc_html_e( 'Pulse ring', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="bounce" <?php selected( $s['animation'], 'bounce' ); ?>><?php esc_html_e( 'Bounce', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Applied only to buttons with "Attention effect" ticked. Turned off automatically for visitors who prefer reduced motion.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
					</section>

					<section class="xvfcb-card">
						<h3 class="xvfcb-card__title"><?php esc_html_e( 'On phones', 'xevn-floating-contact-buttons' ); ?></h3>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-mobile-style"><?php esc_html_e( 'Style on phones', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-mobile-style" name="<?php echo esc_attr( self::name( 'mobile_style' ) ); ?>">
									<option value="float" <?php selected( $s['mobile_style'], 'float' ); ?>><?php esc_html_e( 'Floating buttons in the corner', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="bar" <?php selected( $s['mobile_style'], 'bar' ); ?>><?php esc_html_e( 'Horizontal bar at the bottom of the screen', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'The bar shows every button side by side with its label. Choose one button as "Featured" to make it larger and raised in the middle; choosing another one switches it automatically, so there is never more than one. Computers keep the floating buttons. Grouping is not used in the bar.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
						<div class="xvfcb-field xvfcb-bar-field"<?php echo ( 'bar' === $s['mobile_style'] ) ? '' : ' hidden'; ?>>
							<span class="xvfcb-field__label"><?php esc_html_e( 'Bar', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control">
								<div class="xvfcb-colors">
									<span class="xvfcb-bar-color">
										<span class="xvfcb-mini-label"><?php esc_html_e( 'Bar background', 'xevn-floating-contact-buttons' ); ?></span>
										<input class="xvfcb-color-global" type="text" name="<?php echo esc_attr( self::name( 'bar_bg' ) ); ?>" value="<?php echo esc_attr( $s['bar_bg'] ); ?>" data-default-color="#ffffff">
									</span>
									<span class="xvfcb-bar-color">
										<span class="xvfcb-mini-label"><?php esc_html_e( 'Label color', 'xevn-floating-contact-buttons' ); ?></span>
										<input class="xvfcb-color-global" type="text" name="<?php echo esc_attr( self::name( 'bar_text' ) ); ?>" value="<?php echo esc_attr( $s['bar_text'] ); ?>" data-default-color="#374151">
									</span>
								</div>
								<?php self::range( 'bar_font', __( 'Label font size', 'xevn-floating-contact-buttons' ), 10, 18, 1, 'px', $s['bar_font'], 'xvfcb-bar-font' ); ?>
							</div>
						</div>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-collapse"><?php esc_html_e( 'Group buttons', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-collapse" name="<?php echo esc_attr( self::name( 'collapse' ) ); ?>">
									<option value="off" <?php selected( $s['collapse'], 'off' ); ?>><?php esc_html_e( 'Off - always show all buttons', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="mobile" <?php selected( $s['collapse'], 'mobile' ); ?>><?php esc_html_e( 'On mobile only', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="always" <?php selected( $s['collapse'], 'always' ); ?>><?php esc_html_e( 'On all devices', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Shows a single contact button; visitors tap it to open the list. Useful when you have many buttons.', 'xevn-floating-contact-buttons' ); ?></p>
								<div class="xvfcb-colors xvfcb-group-field"<?php echo ( 'off' === $s['collapse'] ) ? ' hidden' : ''; ?>>
									<span class="xvfcb-bar-color">
										<span class="xvfcb-mini-label"><?php esc_html_e( 'Group button label', 'xevn-floating-contact-buttons' ); ?></span>
										<input type="text" class="regular-text" name="<?php echo esc_attr( self::name( 'collapse_label' ) ); ?>" value="<?php echo esc_attr( $s['collapse_label'] ); ?>" placeholder="<?php esc_attr_e( 'Contact us', 'xevn-floating-contact-buttons' ); ?>">
									</span>
									<span class="xvfcb-bar-color">
										<span class="xvfcb-mini-label"><?php esc_html_e( 'Group button color', 'xevn-floating-contact-buttons' ); ?></span>
										<input class="xvfcb-color-global" type="text" name="<?php echo esc_attr( self::name( 'toggle_color' ) ); ?>" value="<?php echo esc_attr( $s['toggle_color'] ); ?>" data-default-color="#0a7cff">
									</span>
								</div>
							</div>
						</div>
					</section>

					<section class="xvfcb-card">
						<h3 class="xvfcb-card__title"><?php esc_html_e( 'Timing & behaviour', 'xevn-floating-contact-buttons' ); ?></h3>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-reveal"><?php esc_html_e( 'When to show', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-reveal" name="<?php echo esc_attr( self::name( 'reveal' ) ); ?>">
									<option value="immediate" <?php selected( $s['reveal'], 'immediate' ); ?>><?php esc_html_e( 'Right away', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="scroll" <?php selected( $s['reveal'], 'scroll' ); ?>><?php esc_html_e( 'After the visitor scrolls', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="delay" <?php selected( $s['reveal'], 'delay' ); ?>><?php esc_html_e( 'After a few seconds', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
								<div class="xvfcb-reveal-scroll"<?php echo ( 'scroll' === $s['reveal'] ) ? '' : ' hidden'; ?>>
									<?php self::range( 'reveal_scroll', __( 'Scroll distance', 'xevn-floating-contact-buttons' ), 0, 3000, 10, 'px', $s['reveal_scroll'] ); ?>
								</div>
								<div class="xvfcb-reveal-delay"<?php echo ( 'delay' === $s['reveal'] ) ? '' : ' hidden'; ?>>
									<?php self::range( 'reveal_delay', __( 'Delay', 'xevn-floating-contact-buttons' ), 0, 60, 1, 's', $s['reveal_delay'] ); ?>
								</div>
								<p class="description"><?php esc_html_e( 'Keeps the top of the page clear on first view. Pages too short to scroll show the buttons right away.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-desktop-call"><?php esc_html_e( 'Call button on computers', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<select id="xvfcb-desktop-call" name="<?php echo esc_attr( self::name( 'desktop_call' ) ); ?>">
									<option value="popup" <?php selected( $s['desktop_call'], 'popup' ); ?>><?php esc_html_e( 'Show the number with a copy button', 'xevn-floating-contact-buttons' ); ?></option>
									<option value="link" <?php selected( $s['desktop_call'], 'link' ); ?>><?php esc_html_e( 'Open the tel: link (like on phones)', 'xevn-floating-contact-buttons' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Most computers cannot place calls, so showing the number lets visitors dial it on their phone.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
						<div class="xvfcb-field">
							<span class="xvfcb-field__label"><?php esc_html_e( 'Links', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control">
								<label class="xvfcb-check"><input type="checkbox" name="<?php echo esc_attr( self::name( 'new_tab' ) ); ?>" value="1" <?php checked( $s['new_tab'], 1 ); ?>> <?php esc_html_e( 'Open web links (Zalo, Messenger, maps...) in a new tab', 'xevn-floating-contact-buttons' ); ?></label>
							</div>
						</div>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-z"><?php esc_html_e( 'Layer order (z-index)', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<input id="xvfcb-z" class="small-text" type="number" min="1" name="<?php echo esc_attr( self::name( 'z_index' ) ); ?>" value="<?php echo esc_attr( $s['z_index'] ); ?>">
								<p class="description"><?php esc_html_e( 'Raise it only if another element covers the buttons.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
					</section>
				</div>

				<h2><?php esc_html_e( 'Working hours', 'xevn-floating-contact-buttons' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Use working hours', 'xevn-floating-contact-buttons' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( self::name( 'hours_enabled' ) ); ?>" value="1" <?php checked( $s['hours_enabled'], 1 ); ?>> <?php esc_html_e( 'Show or hide buttons by working hours', 'xevn-floating-contact-buttons' ); ?></label>
							<p class="description">
								<?php
								/* translators: %s: site time zone, e.g. "Asia/Ho_Chi_Minh". */
								echo esc_html( sprintf( __( 'Choose per button: any time, only during or only outside working hours. Uses the site time zone (%s) and is checked in the visitor\'s browser, so it works with page caching.', 'xevn-floating-contact-buttons' ), function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : 'UTC' ) );
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Weekly schedule', 'xevn-floating-contact-buttons' ); ?></th>
						<td>
							<table class="xvfcb-week" role="presentation">
								<?php
								$day_names = array(
									1 => __( 'Monday', 'xevn-floating-contact-buttons' ),
									2 => __( 'Tuesday', 'xevn-floating-contact-buttons' ),
									3 => __( 'Wednesday', 'xevn-floating-contact-buttons' ),
									4 => __( 'Thursday', 'xevn-floating-contact-buttons' ),
									5 => __( 'Friday', 'xevn-floating-contact-buttons' ),
									6 => __( 'Saturday', 'xevn-floating-contact-buttons' ),
									0 => __( 'Sunday', 'xevn-floating-contact-buttons' ),
								);
								$week      = XVFCB_Settings::sanitize_week( array( 'hours_week' => $s['hours_week'] ) );
								foreach ( $day_names as $num => $day_name ) :
									$row  = $week[ $num ];
									$base = self::name( 'hours_week' ) . '[' . $num . ']';
									?>
									<tr class="<?php echo $row['on'] ? '' : 'is-closed'; ?>">
										<td><label><input type="checkbox" class="xvfcb-day-on" name="<?php echo esc_attr( $base ); ?>[on]" value="1" <?php checked( $row['on'], 1 ); ?>> <?php echo esc_html( $day_name ); ?></label></td>
										<td>
											<input type="time" aria-label="<?php echo esc_attr( $day_name . ' - ' . __( 'From', 'xevn-floating-contact-buttons' ) ); ?>" name="<?php echo esc_attr( $base ); ?>[start]" value="<?php echo esc_attr( $row['start'] ); ?>">
											&ndash;
											<input type="time" aria-label="<?php echo esc_attr( $day_name . ' - ' . __( 'To', 'xevn-floating-contact-buttons' ) ); ?>" name="<?php echo esc_attr( $base ); ?>[end]" value="<?php echo esc_attr( $row['end'] ); ?>">
										</td>
										<td class="xvfcb-day-closed"><?php esc_html_e( 'Closed', 'xevn-floating-contact-buttons' ); ?></td>
									</tr>
								<?php endforeach; ?>
							</table>
							<p class="description"><?php esc_html_e( 'Untick a day to mark it closed. A closing time earlier than the opening time means the shift runs past midnight. Same opening and closing time means open all day.', 'xevn-floating-contact-buttons' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Visibility', 'xevn-floating-contact-buttons' ); ?></h2>
				<div class="xvfcb-cards">
					<section class="xvfcb-card">
						<h3 class="xvfcb-card__title"><?php esc_html_e( 'Phones and computers', 'xevn-floating-contact-buttons' ); ?></h3>
						<div class="xvfcb-field">
							<span class="xvfcb-field__label"><?php esc_html_e( 'Mobile breakpoint', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control">
								<?php self::range( 'breakpoint', '', 320, 1280, 4, 'px', $s['breakpoint'] ); ?>
								<p class="description"><?php esc_html_e( 'Screens narrower than this width count as mobile. Detection uses CSS only, so it works with page caching.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
					</section>

					<section class="xvfcb-card">
						<h3 class="xvfcb-card__title"><?php esc_html_e( 'Pages without buttons', 'xevn-floating-contact-buttons' ); ?></h3>
						<div class="xvfcb-field">
							<span class="xvfcb-field__label"><?php esc_html_e( 'Hide on', 'xevn-floating-contact-buttons' ); ?></span>
							<div class="xvfcb-field__control xvfcb-checklist">
								<label class="xvfcb-check"><input type="checkbox" name="<?php echo esc_attr( self::name( 'hide_front_page' ) ); ?>" value="1" <?php checked( $s['hide_front_page'], 1 ); ?>> <?php esc_html_e( 'Front page', 'xevn-floating-contact-buttons' ); ?></label>
								<?php foreach ( $post_types as $pt ) : ?>
									<label class="xvfcb-check"><input type="checkbox" name="<?php echo esc_attr( self::name( 'hide_post_types' ) ); ?>[]" value="<?php echo esc_attr( $pt->name ); ?>" <?php checked( in_array( $pt->name, (array) $s['hide_post_types'], true ) ); ?>>
									<?php
									/* translators: %s: post type name, e.g. "Pages". */
									echo esc_html( sprintf( __( 'All single %s', 'xevn-floating-contact-buttons' ), $pt->labels->name ) );
									?>
									</label>
								<?php endforeach; ?>
							</div>
						</div>
						<div class="xvfcb-field">
							<label class="xvfcb-field__label" for="xvfcb-hide-ids"><?php esc_html_e( 'Hide on these IDs', 'xevn-floating-contact-buttons' ); ?></label>
							<div class="xvfcb-field__control">
								<input id="xvfcb-hide-ids" class="regular-text" type="text" name="<?php echo esc_attr( self::name( 'hide_ids' ) ); ?>" value="<?php echo esc_attr( $s['hide_ids'] ); ?>" placeholder="12, 345">
								<p class="description"><?php esc_html_e( 'Post or page IDs separated by commas. You can also tick "Hide contact buttons" in the sidebar when editing a post or page.', 'xevn-floating-contact-buttons' ); ?></p>
							</div>
						</div>
					</section>
				</div>

				<h2><?php esc_html_e( 'Click tracking', 'xevn-floating-contact-buttons' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Google Tag Manager', 'xevn-floating-contact-buttons' ); ?></th>
						<td>
							<label><input type="checkbox" name="<?php echo esc_attr( self::name( 'tracking' ) ); ?>" value="1" <?php checked( $s['tracking'], 1 ); ?>> <?php esc_html_e( 'Push a dataLayer event when a button is clicked', 'xevn-floating-contact-buttons' ); ?></label>
							<p class="description"><?php esc_html_e( 'Event fields: button_type, button_label, button_url, page_location. The plugin itself does not send data to any external service.', 'xevn-floating-contact-buttons' ); ?></p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="xvfcb-event"><?php esc_html_e( 'Event name', 'xevn-floating-contact-buttons' ); ?></label></th>
						<td><input id="xvfcb-event" class="regular-text" type="text" name="<?php echo esc_attr( self::name( 'event_name' ) ); ?>" value="<?php echo esc_attr( $s['event_name'] ); ?>"></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'gtag.js', 'xevn-floating-contact-buttons' ); ?></th>
						<td><label><input type="checkbox" name="<?php echo esc_attr( self::name( 'gtag' ) ); ?>" value="1" <?php checked( $s['gtag'], 1 ); ?>> <?php esc_html_e( 'Also call gtag() with the same event if gtag.js is already on the page', 'xevn-floating-contact-buttons' ); ?></label></td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Language', 'xevn-floating-contact-buttons' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="xvfcb-language"><?php esc_html_e( 'Display language', 'xevn-floating-contact-buttons' ); ?></label></th>
						<td>
							<select id="xvfcb-language" name="<?php echo esc_attr( self::name( 'language' ) ); ?>">
								<option value="auto" <?php selected( $s['language'], 'auto' ); ?>><?php esc_html_e( 'Same as website', 'xevn-floating-contact-buttons' ); ?></option>
								<option value="vi" <?php selected( $s['language'], 'vi' ); ?>>Tiếng Việt</option>
								<option value="en" <?php selected( $s['language'], 'en' ); ?>>English</option>
							</select>
							<p class="description"><?php esc_html_e( 'Applies to this settings page and to the default button labels.', 'xevn-floating-contact-buttons' ); ?></p>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<p class="xvfcb-donate">
				<?php esc_html_e( 'This plugin is free. If it helps your business, you can support its development.', 'xevn-floating-contact-buttons' ); ?>
				<a href="<?php echo esc_url( XVFCB_DONATE_URL ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Donate', 'xevn-floating-contact-buttons' ); ?></a>
			</p>

			<h2><?php esc_html_e( 'Export / import settings', 'xevn-floating-contact-buttons' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Copy these settings to another website: export a .json file here, then import it on the other site. Importing replaces all current settings.', 'xevn-floating-contact-buttons' ); ?></p>
			<div class="xvfcb-tools">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="xvfcb_export">
					<?php wp_nonce_field( 'xvfcb_export', 'xvfcb_export_nonce' ); ?>
					<?php submit_button( __( 'Export settings', 'xevn-floating-contact-buttons' ), 'secondary', 'submit', false ); ?>
				</form>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="xvfcb_import">
					<?php wp_nonce_field( 'xvfcb_import', 'xvfcb_import_nonce' ); ?>
					<input type="file" name="xvfcb_file" accept=".json,application/json" required>
					<?php submit_button( __( 'Import settings', 'xevn-floating-contact-buttons' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Download the current settings as a JSON file.
	 */
	public static function export() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'xevn-floating-contact-buttons' ), 403 );
		}
		check_admin_referer( 'xvfcb_export', 'xvfcb_export_nonce' );

		$data = array(
			'plugin'   => 'xevn-floating-contact-buttons',
			'version'  => XVFCB_VERSION,
			'exported' => gmdate( 'c' ),
			'settings' => XVFCB_Settings::get(),
		);
		$file = 'xevn-contact-buttons-' . gmdate( 'Y-m-d' ) . '.json';

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $file . '"' );
		echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON file download, not HTML.
		exit;
	}

	/**
	 * Import settings from an uploaded JSON file.
	 */
	public static function import() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'xevn-floating-contact-buttons' ), 403 );
		}
		check_admin_referer( 'xvfcb_import', 'xvfcb_import_nonce' );

		$back   = admin_url( 'options-general.php?page=' . self::PAGE );
		$result = 'error';

		if ( isset( $_FILES['xvfcb_file']['tmp_name'], $_FILES['xvfcb_file']['size'], $_FILES['xvfcb_file']['error'] )
			&& UPLOAD_ERR_OK === (int) $_FILES['xvfcb_file']['error']
			&& (int) $_FILES['xvfcb_file']['size'] > 0
			&& (int) $_FILES['xvfcb_file']['size'] <= 200 * KB_IN_BYTES ) {
			$tmp = sanitize_text_field( wp_unslash( $_FILES['xvfcb_file']['tmp_name'] ) );
			if ( is_uploaded_file( $tmp ) ) {
				$json = json_decode( (string) file_get_contents( $tmp ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Reading the uploaded temporary file.
				if ( is_array( $json ) && isset( $json['plugin'], $json['settings'] ) && 'xevn-floating-contact-buttons' === $json['plugin'] && is_array( $json['settings'] ) ) {
					update_option( XVFCB_Settings::OPTION, XVFCB_Settings::sanitize( $json['settings'] ) );
					$result = 'ok';
				}
			}
		}

		wp_safe_redirect( add_query_arg( 'xvfcb_import', $result, $back ) );
		exit;
	}

	/**
	 * Result notice after an import.
	 */
	public static function notices() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag set by our own redirect.
		if ( ! isset( $_GET['page'], $_GET['xvfcb_import'] ) || self::PAGE !== sanitize_key( wp_unslash( $_GET['page'] ) ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display flag.
		$ok = 'ok' === sanitize_key( wp_unslash( $_GET['xvfcb_import'] ) );
		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $ok ? 'success' : 'error' ),
			esc_html( $ok ? __( 'Settings imported.', 'xevn-floating-contact-buttons' ) : __( 'Import failed: please choose a settings file exported by this plugin.', 'xevn-floating-contact-buttons' ) )
		);
	}

	/**
	 * Register the per-post meta box.
	 */
	public static function add_meta_box() {
		$post_types = get_post_types( array( 'public' => true ) );
		unset( $post_types['attachment'] );
		add_meta_box(
			'xvfcb-hide',
			__( 'Contact buttons', 'xevn-floating-contact-buttons' ),
			array( __CLASS__, 'meta_box' ),
			array_values( $post_types ),
			'side',
			'low'
		);
	}

	/**
	 * Meta box content.
	 *
	 * @param WP_Post $post Current post.
	 */
	public static function meta_box( $post ) {
		wp_nonce_field( 'xvfcb_hide_' . $post->ID, 'xvfcb_hide_nonce' );
		$hidden = (bool) get_post_meta( $post->ID, XVFCB_Settings::META_KEY, true );
		?>
		<label>
			<input type="checkbox" name="xvfcb_hide" value="1" <?php checked( $hidden ); ?>>
			<?php esc_html_e( 'Hide contact buttons on this page', 'xevn-floating-contact-buttons' ); ?>
		</label>
		<?php
	}

	/**
	 * Save the meta box value.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public static function save_meta_box( $post_id, $post ) {
		if ( ! isset( $_POST['xvfcb_hide_nonce'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['xvfcb_hide_nonce'] ) ), 'xvfcb_hide_' . $post_id ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( ! empty( $_POST['xvfcb_hide'] ) ) {
			update_post_meta( $post_id, XVFCB_Settings::META_KEY, 1 );
		} else {
			delete_post_meta( $post_id, XVFCB_Settings::META_KEY );
		}
	}
}
