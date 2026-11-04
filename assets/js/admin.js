/**
 * XeVN Floating Contact Buttons - settings page.
 */
( function ( $ ) {
	'use strict';

	var cfg = window.xvfcbAdmin || {};
	var $sets = $( '#xvfcb-sets' );
	var $rows = $sets; // Row events are delegated for every button list.
	var counter = Date.now();

	function rowTitle( $row ) {
		var label = $.trim( $row.find( '.xvfcb-label' ).val() );
		if ( ! label ) {
			label = $row.find( '.xvfcb-type option:selected' ).text();
		}
		$row.find( '.xvfcb-row__title' ).text( label );
	}

	function setSwatch( $row, color ) {
		$row.find( '.xvfcb-row__swatch' ).css( 'background', color );
	}

	function updateIcon( $row ) {
		var type = $row.find( '.xvfcb-type' ).val();
		var style = $row.find( '.xvfcb-icon-style' ).val();
		var url = $.trim( $row.find( '.xvfcb-icon-url' ).val() );
		var $swatch = $row.find( '.xvfcb-row__swatch' ).empty();

		$row.find( '.xvfcb-icon-upload' ).prop( 'hidden', 'custom' !== style );
		updateColorField( $row );

		if ( 'custom' === style && url ) {
			$( '<img alt="">' ).attr( 'src', url ).appendTo( $swatch );
			return;
		}
		var set = ( 'generic' === style ) ? cfg.generic : ( ( 'mono' === style ) ? cfg.mono : cfg.icons );
		if ( set && set[ type ] ) {
			$swatch.html( set[ type ] );
		}
		$swatch.toggleClass( 'is-color-logo', 'default' === style && !! ( cfg.icons && cfg.generic && cfg.icons[ type ] !== cfg.generic[ type ] ) );
	}

	function isBrand( type ) {
		return !! ( cfg.icons && cfg.generic && cfg.icons[ type ] && cfg.icons[ type ] !== cfg.generic[ type ] );
	}

	// Show the color picker only when the color is visible on the site.
	function updateColorField( $row ) {
		var layout = $( '#xvfcb-layout' ).val();
		var type = $row.find( '.xvfcb-type' ).val();
		var style = $row.find( '.xvfcb-icon-style' ).val();
		var url = $.trim( $row.find( '.xvfcb-icon-url' ).val() );
		var hidesColor = ( 'custom' === style && url ) || ( 'default' === style && isBrand( type ) );
		$row.find( '.xvfcb-color-field' ).prop( 'hidden', 'label' !== layout && hidesColor );
	}

	function initColor( $row ) {
		$row.find( '.xvfcb-color' ).wpColorPicker( {
			change: function ( event, ui ) {
				setSwatch( $row, ui.color.toString() );
				window.setTimeout( schedulePreview, 0 );
			},
			clear: function () {
				setSwatch( $row, $row.find( '.xvfcb-color' ).data( 'default-color' ) );
			}
		} );
	}

	$sets.find( '.xvfcb-row' ).each( function () {
		initColor( $( this ) );
	} );

	$( '.xvfcb-color-global' ).wpColorPicker( {
		change: function () {
			window.setTimeout( schedulePreview, 0 );
		}
	} );

	$( '#xvfcb-layout' ).on( 'change', function () {
		$sets.find( '.xvfcb-row' ).each( function () {
			updateColorField( $( this ) );
		} );
	} );

	$( '.xvfcb-week' ).on( 'change', '.xvfcb-day-on', function () {
		$( this ).closest( 'tr' ).toggleClass( 'is-closed', ! this.checked );
	} );

	$( '#xvfcb-reveal' ).on( 'change', function () {
		$( '.xvfcb-reveal-scroll' ).prop( 'hidden', 'scroll' !== $( this ).val() );
		$( '.xvfcb-reveal-delay' ).prop( 'hidden', 'delay' !== $( this ).val() );
	} );

	$( '#xvfcb-mobile-style' ).on( 'change', function () {
		var bar = 'bar' === $( this ).val();
		$( '.xvfcb-bar-field' ).prop( 'hidden', ! bar );
		$( '.xvfcb-float-field' ).prop( 'hidden', bar );
		$( '.xvfcb-featured-field' ).prop( 'hidden', ! bar );
		updateGroupField();
	} );

	// Only one featured button per list (works like a radio button).
	$sets.on( 'change', '.xvfcb-featured-input', function () {
		if ( this.checked ) {
			$( this ).closest( '.xvfcb-rowlist' ).find( '.xvfcb-featured-input' ).not( this ).prop( 'checked', false );
		}
	} );

	// Sliders: show the current value next to each one.
	$( '.xvfcb-wrap' ).on( 'input change', '.xvfcb-range input[type="range"]', function () {
		$( this ).siblings( '.xvfcb-range__value' ).text( $( this ).val() + ( $( this ).data( 'unit' ) || '' ) );
	} );

	// Group button label and color: shown when grouping is used on any device
	// (phones in the bar style never group).
	function updateGroupField() {
		var onDesktop = 'on' === $( '#xvfcb-collapse-desktop' ).val();
		var onMobile = 'on' === $( '#xvfcb-collapse-mobile' ).val() && 'bar' !== $( '#xvfcb-mobile-style' ).val();
		$( '.xvfcb-group-field' ).prop( 'hidden', ! onDesktop && ! onMobile );
	}

	$( '.xvfcb-collapse' ).on( 'change', updateGroupField );
	updateGroupField();

	function newRow( $list ) {
		counter += 1;
		var html = $( '#tmpl-xvfcb-row' ).html()
			.replace( /__base__/g, String( $list.data( 'base' ) ) )
			.replace( /__u__/g, 'n' )
			.replace( /__i__/g, String( counter ) );
		var $row = $( $.parseHTML( $.trim( html ) ) );
		$list.append( $row );
		return $row;
	}

	$sets.on( 'click', '.xvfcb-add', function () {
		var $list = $( this ).closest( '.xvfcb-panel' ).find( '.xvfcb-rowlist' );
		if ( cfg.max && $list.children( '.xvfcb-row' ).length >= cfg.max ) {
			window.alert( cfg.maxReached );
			return;
		}
		var $row = newRow( $list );
		initColor( $row );
		$row.find( '.xvfcb-value' ).trigger( 'focus' );
		schedulePreview();
	} );

	/* ---------- Page-specific sets ---------- */

	function fieldKey( el ) {
		var m = String( el.name || '' ).match( /\[([a-z_]+)\]$/ );
		return m ? m[ 1 ] : '';
	}

	function rowData( $row ) {
		var data = {};
		$row.find( '[name]' ).each( function () {
			var key = fieldKey( this );
			if ( key ) {
				data[ key ] = ( 'checkbox' === this.type ) ? this.checked : $( this ).val();
			}
		} );
		return data;
	}

	function refreshRow( $row ) {
		var type = $row.find( '.xvfcb-type' ).val();
		var $color = $row.find( '.xvfcb-color' );
		var def = ( cfg.colors && cfg.colors[ type ] ) || '#4b5563';
		$row.find( '.xvfcb-value' ).attr( 'placeholder', ( cfg.hints && cfg.hints[ type ] ) || '' );
		$row.find( '.xvfcb-label' ).attr( 'placeholder', $row.find( '.xvfcb-type option:selected' ).text() );
		$color.data( 'default-color', def ).attr( 'data-default-color', def );
		setSwatch( $row, $color.val() || def );
		$row.toggleClass( 'is-disabled', ! $row.find( '.xvfcb-row__toggle input' ).is( ':checked' ) );
		$row.find( '.xvfcb-message' ).prop( 'hidden', ( cfg.messageTypes || [] ).indexOf( type ) === -1 );
		rowTitle( $row );
		updateIcon( $row );
	}

	function activate( panel ) {
		$sets.find( '.xvfcb-tab' ).removeClass( 'is-active' ).attr( 'aria-selected', 'false' )
			.filter( '[data-panel="' + panel + '"]' ).addClass( 'is-active' ).attr( 'aria-selected', 'true' );
		$sets.find( '.xvfcb-panel' ).removeClass( 'is-active' ).prop( 'hidden', true )
			.filter( '[data-panel="' + panel + '"]' ).addClass( 'is-active' ).prop( 'hidden', false );
		schedulePreview();
	}

	function updateAddTab() {
		var max = parseInt( $sets.data( 'max-sets' ), 10 ) || 5;
		$sets.find( '.xvfcb-tab-add' ).prop( 'hidden', $sets.find( '.xvfcb-panel[data-panel^="set-"]' ).length >= max );
	}

	$sets.on( 'click', '.xvfcb-tab', function () {
		activate( $( this ).data( 'panel' ) );
	} );

	$sets.on( 'click', '.xvfcb-tab-add', function () {
		counter += 1;
		var k = 'n' + counter;
		var $panel = $( $.parseHTML( $.trim( $( '#tmpl-xvfcb-set' ).html().replace( /__k__/g, k ) ) ) );
		var $tab = $( '<button type="button" class="xvfcb-tab" role="tab"></button>' )
			.attr( 'data-panel', 'set-' + k ).text( cfg.newSet || 'New set' );
		$( this ).before( $tab );
		$sets.append( $panel );
		activate( 'set-' + k );
		updateAddTab();
		$panel.find( '.xvfcb-set-name' ).trigger( 'focus' );
	} );

	$sets.on( 'input', '.xvfcb-set-name', function () {
		var panel = $( this ).closest( '.xvfcb-panel' ).data( 'panel' );
		$sets.find( '.xvfcb-tab[data-panel="' + panel + '"]' ).text( $.trim( $( this ).val() ) || cfg.newSet || 'New set' );
	} );

	$sets.on( 'click', '.xvfcb-remove-set', function () {
		if ( ! window.confirm( cfg.confirmSet ) ) {
			return;
		}
		var $panel = $( this ).closest( '.xvfcb-panel' );
		$sets.find( '.xvfcb-tab[data-panel="' + $panel.data( 'panel' ) + '"]' ).remove();
		$panel.remove();
		activate( 'default' );
		updateAddTab();
	} );

	$sets.on( 'click', '.xvfcb-copy-default', function () {
		var $list = $( this ).closest( '.xvfcb-panel' ).find( '.xvfcb-rowlist' );
		$( '#xvfcb-rows' ).children( '.xvfcb-row' ).each( function () {
			if ( cfg.max && $list.children( '.xvfcb-row' ).length >= cfg.max ) {
				return false;
			}
			var data = rowData( $( this ) );
			var $row = newRow( $list );
			$row.find( '[name]' ).each( function () {
				var key = fieldKey( this );
				if ( ! ( key in data ) ) {
					return;
				}
				if ( 'checkbox' === this.type ) {
					this.checked = !! data[ key ];
				} else {
					$( this ).val( data[ key ] );
				}
			} );
			initColor( $row );
			refreshRow( $row );
		} );
		schedulePreview();
	} );

	updateAddTab();

	$rows.on( 'click', '.xvfcb-remove', function () {
		if ( window.confirm( cfg.confirmDel ) ) {
			$( this ).closest( '.xvfcb-row' ).remove();
		}
	} );

	$rows.on( 'click', '.xvfcb-up', function () {
		var $row = $( this ).closest( '.xvfcb-row' );
		$row.prev( '.xvfcb-row' ).before( $row );
	} );

	$rows.on( 'click', '.xvfcb-down', function () {
		var $row = $( this ).closest( '.xvfcb-row' );
		$row.next( '.xvfcb-row' ).after( $row );
	} );

	$rows.on( 'change', '.xvfcb-row__toggle input', function () {
		$( this ).closest( '.xvfcb-row' ).toggleClass( 'is-disabled', ! this.checked );
	} );

	$rows.on( 'input', '.xvfcb-label', function () {
		rowTitle( $( this ).closest( '.xvfcb-row' ) );
	} );

	$rows.on( 'change', '.xvfcb-type', function () {
		var $row = $( this ).closest( '.xvfcb-row' );
		var type = $( this ).val();
		var $color = $row.find( '.xvfcb-color' );
		var oldDefault = String( $color.data( 'default-color' ) || '' ).toLowerCase();
		var newDefault = ( cfg.colors && cfg.colors[ type ] ) || '#4b5563';

		$row.find( '.xvfcb-value' ).attr( 'placeholder', ( cfg.hints && cfg.hints[ type ] ) || '' );
		$row.find( '.xvfcb-label' ).attr( 'placeholder', $( this ).find( 'option:selected' ).text() );

		$color.data( 'default-color', newDefault ).attr( 'data-default-color', newDefault );
		if ( String( $color.val() ).toLowerCase() === oldDefault || ! $color.val() ) {
			$color.wpColorPicker( 'color', newDefault );
			setSwatch( $row, newDefault );
		}
		rowTitle( $row );
		updateIcon( $row );
		$row.find( '.xvfcb-message' ).prop( 'hidden', ( cfg.messageTypes || [] ).indexOf( type ) === -1 );
	} );

	$rows.on( 'change', '.xvfcb-icon-style', function () {
		var $row = $( this ).closest( '.xvfcb-row' );
		updateIcon( $row );
		if ( 'custom' === $( this ).val() && ! $.trim( $row.find( '.xvfcb-icon-url' ).val() ) ) {
			$row.find( '.xvfcb-pick-icon' ).trigger( 'click' );
		}
	} );

	$rows.on( 'change input', '.xvfcb-icon-url', function () {
		updateIcon( $( this ).closest( '.xvfcb-row' ) );
	} );

	$rows.on( 'click', '.xvfcb-pick-icon', function ( event ) {
		event.preventDefault();
		if ( ! window.wp || ! window.wp.media ) {
			return;
		}
		var $input = $( this ).siblings( '.xvfcb-icon-url' );
		var frame = window.wp.media( {
			title: cfg.chooseIcon,
			button: { text: cfg.useIcon },
			library: { type: 'image' },
			multiple: false
		} );
		frame.on( 'select', function () {
			var file = frame.state().get( 'selection' ).first().toJSON();
			$input.val( file.url ).trigger( 'change' );
		} );
		frame.open();
	} );

	/* ---------- Live preview ---------- */

	// Remembered per browser (only a convenience; the page works without it).
	function remember( key, value ) {
		try {
			if ( undefined === value ) {
				return window.localStorage.getItem( 'xvfcb_' + key );
			}
			window.localStorage.setItem( 'xvfcb_' + key, value );
		} catch ( e ) {
			// Storage blocked: keep the defaults.
		}
		return null;
	}

	var previewMode = 'mobile' === remember( 'device' ) ? 'mobile' : 'desktop';
	var previewHidden = '1' === remember( 'preview_hidden' );
	var previewTimer = null;
	var $preview = $( '<div id="xvfcb-preview" class="xvfcb xvfcb--js xvfcb-is-preview" role="group"></div>' ).appendTo( 'body' );

	function field( name ) {
		return $( '[name="xvfcb_settings[' + name + ']"]' );
	}

	function textColor( hex ) {
		var h = String( hex || '' ).replace( '#', '' );
		if ( 3 === h.length ) {
			h = h[ 0 ] + h[ 0 ] + h[ 1 ] + h[ 1 ] + h[ 2 ] + h[ 2 ];
		}
		if ( ! /^[0-9a-f]{6}$/i.test( h ) ) {
			return '#ffffff';
		}
		var lum = 0;
		[ 0.2126, 0.7152, 0.0722 ].forEach( function ( w, i ) {
			var c = parseInt( h.substr( i * 2, 2 ), 16 ) / 255;
			c = c <= 0.03928 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 );
			lum += w * c;
		} );
		var white = 1.05 / ( lum + 0.05 );
		var dark = ( lum + 0.05 ) / 0.0603;
		return ( white >= 4.5 || white >= dark ) ? '#ffffff' : '#111827';
	}

	function iconMarkup( type, style, url ) {
		if ( 'custom' === style && url ) {
			return $( '<img alt="" width="24" height="24">' ).attr( 'src', url );
		}
		var set = ( 'generic' === style ) ? cfg.generic : ( ( 'mono' === style ) ? cfg.mono : cfg.icons );
		return ( set && set[ type ] ) ? set[ type ] : '';
	}

	function renderPreview() {
		previewTimer = null;
		$preview.empty();
		if ( previewHidden ) {
			$preview.hide();
			return;
		}
		var num = function ( name, def ) {
			var v = parseInt( field( name ).val(), 10 );
			return isNaN( v ) ? def : v;
		};
		var phone = 'mobile' === previewMode;
		var position = field( phone ? 'position_mobile' : 'position' ).val() || 'right';
		var layout = field( 'layout' ).val() || 'icon';
		var animation = field( 'animation' ).val() || 'none';
		var collapse = 'on' === field( phone ? 'collapse_mobile' : 'collapse_desktop' ).val() ? 'always' : 'off';
		var tooltip = ! phone && field( 'show_tooltip' ).is( ':checked' );
		var bar = phone && 'bar' === field( 'mobile_style' ).val();

		var classes = [ 'xvfcb', 'xvfcb--js', 'xvfcb-is-preview', 'xvfcb-pv-' + previewMode,
			'xvfcb--' + position, 'xvfcb--layout-' + layout, 'xvfcb--anim-' + animation, 'xvfcb--collapse-' + collapse ];
		if ( tooltip && 'icon' === layout ) {
			classes.push( 'xvfcb--tooltip' );
		}
		if ( bar ) {
			classes.push( 'xvfcb--bar' );
		}
		var wasOpen = $preview.hasClass( 'is-open' );
		$preview.attr( 'class', classes.join( ' ' ) ).toggleClass( 'is-open', wasOpen ).show();
		$preview.attr( 'aria-label', cfg.contactUs || '' );
		var el = $preview[ 0 ];
		el.style.setProperty( '--xvfcb-size', ( 'mobile' === previewMode ? num( 'size_mobile', 48 ) : num( 'size', 52 ) ) + 'px' );
		el.style.setProperty( '--xvfcb-x', num( phone ? 'offset_x_mobile' : 'offset_x', 16 ) + 'px' );
		el.style.setProperty( '--xvfcb-y', num( phone ? 'offset_y_mobile' : 'offset_y', 20 ) + 'px' );
		el.style.setProperty( '--xvfcb-gap', num( phone ? 'gap_mobile' : 'gap', 10 ) + 'px' );
		el.style.setProperty( '--xvfcb-bar-bg', field( 'bar_bg' ).val() || '#ffffff' );
		el.style.setProperty( '--xvfcb-bar-text', field( 'bar_text' ).val() || '#374151' );
		el.style.setProperty( '--xvfcb-bar-font', ( parseInt( field( 'bar_font' ).val(), 10 ) || 12 ) + 'px' );
		var featuredDone = false;
		var items = [];

		var $list = $( '<div class="xvfcb__list"></div>' ).appendTo( $preview );
		$sets.find( '.xvfcb-panel.is-active .xvfcb-rowlist' ).children( '.xvfcb-row' ).each( function () {
			var $r = $( this );
			if ( ! $r.find( '.xvfcb-row__toggle input' ).is( ':checked' ) ) {
				return;
			}
			var type = $r.find( '.xvfcb-type' ).val();
			var style = $r.find( '.xvfcb-icon-style' ).val();
			var url = $.trim( $r.find( '.xvfcb-icon-url' ).val() );
			var color = $r.find( '.xvfcb-color' ).val() || ( cfg.colors && cfg.colors[ type ] ) || '#4b5563';
			var label = $.trim( $r.find( '.xvfcb-label' ).val() ) || ( cfg.labels && cfg.labels[ type ] ) || type;
			var device = $r.find( '[name$="[device]"]' ).val() || 'all';
			var empty = ! $.trim( $r.find( '.xvfcb-value' ).val() );
			var custom = 'custom' === style && url;

			var $a = $( '<a href="#" class="xvfcb__btn"></a>' )
				.addClass( 'xvfcb__btn--' + type + ' xvfcb-dev-' + device )
				.toggleClass( 'xvfcb-attn', $r.find( '[name$="[attention]"]' ).is( ':checked' ) )
				.toggleClass( 'xvfcb-custom-icon', !! custom )
				.toggleClass( 'xvfcb-color-logo', ! custom && 'default' === style && isBrand( type ) )
				.toggleClass( 'xvfcb-pv-empty', empty )
				.toggleClass( 'xvfcb-featured', ( function () {
					var f = ! featuredDone && $r.find( '.xvfcb-featured-input' ).is( ':checked' );
					featuredDone = featuredDone || f;
					return f;
				}() ) )
				.attr( { 'aria-label': label, title: empty ? cfg.emptyNote : label } );
			$a[ 0 ].style.setProperty( '--xvfcb-color', color );
			$a[ 0 ].style.setProperty( '--xvfcb-text', textColor( color ) );
			$( '<span class="xvfcb__icon"></span>' ).append( iconMarkup( type, style, url ) ).appendTo( $a );
			$( '<span class="xvfcb__label"></span>' ).text( label ).appendTo( $a );
			$list.append( $a );
			items.push( $a );
		} );

		// Featured button in the middle of the bar (same rule as on the site).
		var others = items.filter( function ( $x ) {
			return ! $x.hasClass( 'xvfcb-featured' );
		} );
		var feat = items.filter( function ( $x ) {
			return $x.hasClass( 'xvfcb-featured' );
		} );
		if ( feat.length ) {
			others.splice( Math.floor( others.length / 2 ), 0, feat[ 0 ] );
		}
		others.forEach( function ( $x, i ) {
			$x[ 0 ].style.setProperty( '--xvfcb-order', i );
		} );
		el.style.setProperty( '--xvfcb-count', Math.max( 1, items.length ) );

		if ( 'off' !== collapse && ! bar ) {
			var tColor = field( 'toggle_color' ).val() || '#0a7cff';
			var tLabel = $.trim( field( 'collapse_label' ).val() ) || cfg.contactUs || '';
			var $t = $( '<button type="button" class="xvfcb__btn xvfcb__toggle"></button>' )
				.toggleClass( 'xvfcb-attn', 'none' !== animation )
				.attr( 'aria-label', tLabel );
			$t[ 0 ].style.setProperty( '--xvfcb-color', tColor );
			$t[ 0 ].style.setProperty( '--xvfcb-text', textColor( tColor ) );
			$( '<span class="xvfcb__icon xvfcb__toggle-open"></span>' ).html( cfg.toggleIcon || '' ).appendTo( $t );
			$( '<span class="xvfcb__icon xvfcb__toggle-close"></span>' ).html( cfg.closeIcon || '' ).appendTo( $t );
			$( '<span class="xvfcb__label"></span>' ).text( tLabel ).appendTo( $t );
			$preview.append( $t );
		}
	}

	function schedulePreview() {
		if ( previewTimer ) {
			window.clearTimeout( previewTimer );
		}
		previewTimer = window.setTimeout( renderPreview, 60 );
	}

	$preview.on( 'click', function ( event ) {
		event.preventDefault();
		if ( $( event.target ).closest( '.xvfcb__toggle' ).length ) {
			$preview.toggleClass( 'is-open' );
		}
	} );

	/* ---------- Device switch and preview toggle ---------- */

	function setDevice( device ) {
		previewMode = ( 'mobile' === device ) ? 'mobile' : 'desktop';
		$( '[data-xvfcb-device]' ).each( function () {
			var on = $( this ).data( 'xvfcb-device' ) === previewMode;
			$( this ).toggleClass( 'is-active', on ).attr( 'aria-pressed', on ? 'true' : 'false' );
		} );
		$( '[data-xvfcb-panel]' ).each( function () {
			$( this ).prop( 'hidden', $( this ).data( 'xvfcb-panel' ) !== previewMode );
		} );
		remember( 'device', previewMode );
		renderPreview();
	}

	function setPreviewHidden( hidden ) {
		previewHidden = !! hidden;
		var $eye = $( '[data-xvfcb-eye]' );
		$eye.attr( 'aria-pressed', previewHidden ? 'true' : 'false' ).toggleClass( 'is-active', previewHidden );
		$eye.find( '.dashicons' ).toggleClass( 'dashicons-hidden', ! previewHidden ).toggleClass( 'dashicons-visibility', previewHidden );
		$eye.find( '.xvfcb-eye__text' ).text( previewHidden ? ( cfg.showPreview || 'Show preview' ) : ( cfg.hidePreview || 'Hide preview' ) );
		remember( 'preview_hidden', previewHidden ? '1' : '0' );
		renderPreview();
	}

	$( '.xvfcb-wrap' ).on( 'click', '[data-xvfcb-device]', function () {
		setDevice( $( this ).data( 'xvfcb-device' ) );
	} );

	$( '[data-xvfcb-eye]' ).on( 'click', function () {
		setPreviewHidden( ! previewHidden );
	} );

	setDevice( previewMode );
	setPreviewHidden( previewHidden );

	$( '.xvfcb-wrap form[action="options.php"]' ).on( 'input change', schedulePreview );
	$rows.on( 'click', '.xvfcb-up, .xvfcb-down, .xvfcb-remove', schedulePreview );
	renderPreview();
}( jQuery ) );
