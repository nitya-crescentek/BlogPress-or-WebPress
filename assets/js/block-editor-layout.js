/**
 * Keeps the block editor canvas as wide as the post's content area on the
 * front end, following the sidebar, container and page builder choices in
 * the Layout meta box as they change.
 *
 * This replaces the content width plugin in assets/dist/block-editor.js,
 * which predates container layouts.
 *
 * @package WebPress
 */
( function( wp, data ) {
	'use strict';

	if ( ! wp || ! wp.data || ! data ) {
		return;
	}

	if ( wp.plugins && wp.plugins.getPlugin && wp.plugins.getPlugin( 'webpress-content-width' ) ) {
		wp.plugins.unregisterPlugin( 'webpress-content-width' );
	}

	// Layout meta box fields, mapped to the saved values they start from.
	var fields = {
		'webpress-sidebar-layout': 'sidebarLayout',
		'webpress-container-layout': 'containerLayout',
		'_webpress-full-width-content': 'contentAreaType',
	};

	var isScheduled = false;

	/**
	 * Get a layout value from its meta box field, or the saved value when the
	 * meta box isn't on the screen.
	 *
	 * @param {string} fieldId The field's element ID.
	 * @return {string} The value. Empty means use the default.
	 */
	function getValue( fieldId ) {
		var field = document.getElementById( fieldId );

		return field ? field.value : data[ fields[ fieldId ] ];
	}

	/**
	 * Work out the content width, mirroring the front end.
	 *
	 * @return {string} A CSS length.
	 */
	function getContentWidth() {
		var sidebarLayout = getValue( 'webpress-sidebar-layout' ) || data.defaultSidebarLayout;
		var containerLayout = getValue( 'webpress-container-layout' ) || data.defaultContainerLayout;
		var contentAreaType = getValue( '_webpress-full-width-content' );
		var sidebarCount = 2;
		var width;

		if ( 'true' === contentAreaType || 'full-width' === containerLayout ) {
			return '100%';
		}

		if ( 'no-sidebar' === sidebarLayout ) {
			sidebarCount = 0;
		} else if ( 'right-sidebar' === sidebarLayout || 'left-sidebar' === sidebarLayout ) {
			sidebarCount = 1;
		}

		width = Number( data.containerWidth ) * ( 100 - ( Number( data.rightSidebarWidth ) * sidebarCount ) ) / 100;

		if ( 'narrow' === containerLayout ) {
			width = Math.min( Number( data.narrowContainerWidth ), width );
		}

		// Page builder mode removes the content padding.
		if ( 'contained' !== contentAreaType ) {
			width -= parseInt( data.contentPaddingLeft, 10 ) + parseInt( data.contentPaddingRight, 10 );
		}

		return Math.round( width ) + 'px';
	}

	/**
	 * Set the width on the editor canvas, which may be inside an iframe.
	 */
	function applyContentWidth() {
		var canvas = document.querySelector( 'iframe[name="editor-canvas"]' );
		var canvasDocument = canvas && canvas.contentDocument ? canvas.contentDocument : document;
		var wrapper = canvasDocument.querySelector( '.editor-styles-wrapper' );
		var width;

		isScheduled = false;

		if ( canvas && 'complete' !== canvasDocument.readyState ) {
			canvas.addEventListener( 'load', scheduleUpdate, { once: true } );
		}

		if ( ! wrapper ) {
			return;
		}

		width = getContentWidth();

		if ( wrapper.style.getPropertyValue( '--content-width' ) !== width ) {
			wrapper.style.setProperty( '--content-width', width );
		}
	}

	/**
	 * Update on the next frame, so a burst of changes only updates once.
	 */
	function scheduleUpdate() {
		if ( isScheduled ) {
			return;
		}

		isScheduled = true;
		window.requestAnimationFrame( applyContentWidth );
	}

	wp.domReady( function() {
		scheduleUpdate();

		document.addEventListener( 'change', function( event ) {
			if ( event.target && fields.hasOwnProperty( event.target.id ) ) {
				scheduleUpdate();
			}
		} );

		// The editor rebuilds the canvas when switching device previews or editor modes.
		wp.data.subscribe( scheduleUpdate );
	} );
}( window.wp, window.webpressBlockEditor ) );
