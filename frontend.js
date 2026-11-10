/**
 * Kianguyen Floating Contact Buttons - front end.
 *
 * - Group toggle (collapse buttons into one "Contact" button).
 * - Desktop call box: shows the phone number with a copy button instead of
 *   opening a tel: link that desktops cannot handle.
 * - Working hours: shows or hides buttons by the site's time zone. Runs in the
 *   browser so it stays correct with full-page caching.
 * - {url} and {title} placeholders in pre-filled messages.
 * - Click tracking: pushes events to window.dataLayer (and gtag() if enabled).
 *   Nothing is sent anywhere by this script itself.
 */
( function () {
	'use strict';

	// Runs before the buttons are parsed (the script is printed earlier in the
	// footer), so a delayed reveal can hide them without a flash.
	document.documentElement.classList.add( 'xvfcb-js' );

	var DAY = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };

	function readConfig( box ) {
		try {
			return JSON.parse( box.getAttribute( 'data-xvfcb-config' ) || '{}' ) || {};
		} catch ( e ) {
			return {};
		}
	}

	function track( config, link, type ) {
		if ( ! config.track ) {
			return;
		}
		var eventName = config.event || 'contact_button_click';
		var data = {
			button_type: type || link.getAttribute( 'data-xvfcb-type' ) || '',
			button_label: link.getAttribute( 'data-xvfcb-label' ) || '',
			button_url: link.getAttribute( 'href' ) || '',
			page_location: window.location.href,
			button_set: config.set || 'default'
		};
		var payload = { event: eventName };
		for ( var key in data ) {
			if ( Object.prototype.hasOwnProperty.call( data, key ) ) {
				payload[ key ] = data[ key ];
			}
		}
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push( payload );
		if ( config.gtag && typeof window.gtag === 'function' ) {
			window.gtag( 'event', eventName, data );
		}
	}

	/* ---------- Working hours ---------- */

	function toMinutes( hhmm ) {
		var p = String( hhmm || '' ).split( ':' );
		return ( parseInt( p[ 0 ], 10 ) || 0 ) * 60 + ( parseInt( p[ 1 ], 10 ) || 0 );
	}

	function nowInSiteTime( h ) {
		if ( h.tz && window.Intl && Intl.DateTimeFormat ) {
			try {
				var parts = new Intl.DateTimeFormat( 'en-US', {
					timeZone: h.tz,
					weekday: 'short',
					hour: '2-digit',
					minute: '2-digit',
					hourCycle: 'h23'
				} ).formatToParts( new Date() );
				var out = {};
				parts.forEach( function ( part ) {
					out[ part.type ] = part.value;
				} );
				if ( out.weekday in DAY ) {
					return { day: DAY[ out.weekday ], min: ( parseInt( out.hour, 10 ) % 24 ) * 60 + parseInt( out.minute, 10 ) };
				}
			} catch ( e ) {
				// Fall back to the fixed offset below.
			}
		}
		var d = new Date( Date.now() + ( h.offset || 0 ) * 60000 );
		return { day: d.getUTCDay(), min: d.getUTCHours() * 60 + d.getUTCMinutes() };
	}

	function isOpen( h ) {
		var now = nowInSiteTime( h );
		var week = h.week || {};
		var today = week[ String( now.day ) ];
		var prev = week[ String( ( now.day + 6 ) % 7 ) ];
		var s, e;
		if ( today ) {
			s = toMinutes( today[ 0 ] );
			e = toMinutes( today[ 1 ] );
			if ( s === e ) {
				return true; // Open all day.
			}
			if ( s < e && now.min >= s && now.min < e ) {
				return true;
			}
			if ( s > e && now.min >= s ) {
				return true; // Overnight shift starting today.
			}
		}
		if ( prev ) {
			s = toMinutes( prev[ 0 ] );
			e = toMinutes( prev[ 1 ] );
			if ( s > e && now.min < e ) {
				return true; // Overnight shift from yesterday.
			}
		}
		return false;
	}

	function applyHours( box, config ) {
		var h = config.hours || {};
		if ( ! h.on ) {
			return;
		}
		var open = isOpen( h );
		var links = box.querySelectorAll( '[data-xvfcb-hours]' );
		for ( var i = 0; i < links.length; i++ ) {
			var rule = links[ i ].getAttribute( 'data-xvfcb-hours' );
			var hide = ( 'open' === rule && ! open ) || ( 'closed' === rule && open );
			links[ i ].classList.toggle( 'xvfcb-off', hide );
		}
		var anyLeft = box.querySelectorAll( '.xvfcb__list .xvfcb__btn:not(.xvfcb-off)' ).length > 0;
		box.classList.toggle( 'xvfcb-empty', ! anyLeft );
	}

	/* ---------- Message placeholders ---------- */

	function fillPlaceholders( box ) {
		var links = box.querySelectorAll( '[data-xvfcb-msg]' );
		var url = encodeURIComponent( window.location.href );
		var title = encodeURIComponent( document.title || '' );
		for ( var i = 0; i < links.length; i++ ) {
			var href = links[ i ].getAttribute( 'href' ) || '';
			href = href.replace( /%7Burl%7D/gi, url ).replace( /%7Btitle%7D/gi, title );
			links[ i ].setAttribute( 'href', href );
		}
	}

	/* ---------- Desktop call box ---------- */

	var pop = null;

	function closePop() {
		if ( pop && pop.parentNode ) {
			pop.parentNode.removeChild( pop );
		}
		pop = null;
	}

	function copyText( text, done ) {
		var finished = false;
		function finish( useFallback ) {
			if ( finished ) {
				return;
			}
			finished = true;
			if ( useFallback ) {
				fallbackCopy( text );
			}
			done();
		}
		if ( navigator.clipboard && window.isSecureContext ) {
			// Some browsers keep the promise pending while asking for permission.
			window.setTimeout( function () {
				finish( true );
			}, 600 );
			navigator.clipboard.writeText( text ).then( function () {
				finish( false );
			}, function () {
				finish( true );
			} );
			return;
		}
		finish( true );
	}

	function fallbackCopy( text ) {
		var area = document.createElement( 'textarea' );
		area.value = text;
		area.setAttribute( 'readonly', '' );
		area.style.position = 'fixed';
		area.style.opacity = '0';
		document.body.appendChild( area );
		area.select();
		try {
			document.execCommand( 'copy' );
		} catch ( e ) {
			// Nothing else to try.
		}
		document.body.removeChild( area );
	}

	function isDesktop( config ) {
		if ( ! window.matchMedia ) {
			return false;
		}
		return window.matchMedia( '(min-width: ' + ( config.bp || 768 ) + 'px)' ).matches &&
			window.matchMedia( '(hover: hover) and (pointer: fine)' ).matches;
	}

	function openPop( box, link, config ) {
		closePop();
		var t = config.i18n || {};
		var number = link.getAttribute( 'data-xvfcb-number' ) || '';
		var rect = link.getBoundingClientRect();
		var right = box.classList.contains( 'xvfcb--right' );

		pop = document.createElement( 'div' );
		pop.className = 'xvfcb-pop';
		pop.setAttribute( 'role', 'dialog' );
		pop.setAttribute( 'aria-label', link.getAttribute( 'data-xvfcb-label' ) || '' );
		pop.style.setProperty( '--xvfcb-color', link.style.getPropertyValue( '--xvfcb-color' ) || '#e02424' );
		pop.style.setProperty( '--xvfcb-text', link.style.getPropertyValue( '--xvfcb-text' ) || '#fff' );
		pop.style.zIndex = String( ( parseInt( getComputedStyle( box ).zIndex, 10 ) || 9999 ) + 1 );

		var label = document.createElement( 'div' );
		label.className = 'xvfcb-pop__label';
		label.textContent = link.getAttribute( 'data-xvfcb-label' ) || '';

		var num = document.createElement( 'a' );
		num.className = 'xvfcb-pop__number';
		num.href = link.getAttribute( 'href' ) || '#';
		num.textContent = number;

		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'xvfcb-pop__copy';
		btn.textContent = t.copy || 'Copy';
		btn.addEventListener( 'click', function () {
			copyText( number, function () {
				btn.textContent = t.copied || 'Copied!';
			} );
			track( config, link, 'phone_copy' );
		} );

		var close = document.createElement( 'button' );
		close.type = 'button';
		close.className = 'xvfcb-pop__close';
		close.setAttribute( 'aria-label', t.close || 'Close' );
		close.innerHTML = '&times;';
		close.addEventListener( 'click', closePop );

		pop.appendChild( close );
		pop.appendChild( label );
		pop.appendChild( num );
		pop.appendChild( btn );
		document.body.appendChild( pop );

		var top = rect.top + rect.height / 2 - pop.offsetHeight / 2;
		top = Math.max( 8, Math.min( top, window.innerHeight - pop.offsetHeight - 8 ) );
		pop.style.top = top + 'px';
		if ( right ) {
			pop.style.right = ( window.innerWidth - rect.left + 12 ) + 'px';
		} else {
			pop.style.left = ( rect.right + 12 ) + 'px';
		}
		btn.focus();
	}

	/* ---------- Group toggle ---------- */

	function setOpen( box, open ) {
		box.classList.toggle( 'is-open', open );
		var toggle = box.querySelector( '.xvfcb__toggle' );
		if ( toggle ) {
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		}
	}

	/* ---------- Delayed reveal ---------- */

	function setupReveal( box, config ) {
		var r = config.reveal || {};
		if ( ! box.classList.contains( 'xvfcb--waiting' ) ) {
			return;
		}
		var done = false;
		function show() {
			if ( done ) {
				return;
			}
			done = true;
			box.classList.remove( 'xvfcb--waiting' );
			window.removeEventListener( 'scroll', onScroll );
		}
		function onScroll() {
			if ( ( window.pageYOffset || document.documentElement.scrollTop || 0 ) >= ( r.scroll || 0 ) ) {
				show();
			}
		}
		if ( 'scroll' === r.mode ) {
			// Short pages that cannot scroll far enough still get the buttons.
			var maxScroll = Math.max( 0, document.documentElement.scrollHeight - window.innerHeight );
			if ( maxScroll < ( r.scroll || 0 ) ) {
				show();
				return;
			}
			window.addEventListener( 'scroll', onScroll, { passive: true } );
			onScroll();
		} else {
			window.setTimeout( show, ( r.delay || 0 ) * 1000 );
		}
	}

	/* ---------- Init ---------- */

	function init( box ) {
		var config = readConfig( box );
		box.classList.add( 'xvfcb--js' );

		setupReveal( box, config );
		fillPlaceholders( box );
		applyHours( box, config );
		if ( config.hours && config.hours.on ) {
			window.setInterval( function () {
				applyHours( box, config );
			}, 60000 );
		}

		box.addEventListener( 'click', function ( event ) {
			var target = event.target;
			if ( ! target || ! target.closest ) {
				return;
			}
			var toggle = target.closest( '.xvfcb__toggle' );
			if ( toggle ) {
				setOpen( box, ! box.classList.contains( 'is-open' ) );
				return;
			}
			var link = target.closest( 'a.xvfcb__btn' );
			if ( ! link ) {
				return;
			}
			track( config, link );
			if ( 'phone' === link.getAttribute( 'data-xvfcb-type' ) && 'popup' === config.call && isDesktop( config ) ) {
				event.preventDefault();
				openPop( box, link, config );
			}
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( box.contains( event.target ) || ( pop && pop.contains( event.target ) ) ) {
				return;
			}
			closePop();
			setOpen( box, false );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key || 'Esc' === event.key ) {
				closePop();
				setOpen( box, false );
			}
		} );
	}

	function start() {
		var boxes = document.querySelectorAll( '.xvfcb' );
		for ( var i = 0; i < boxes.length; i++ ) {
			init( boxes[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
