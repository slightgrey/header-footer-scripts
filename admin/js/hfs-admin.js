/* Header Footer Scripts — Admin JS */
(function ( $ ) {
	'use strict';

	/**
	 * Auto-resize a textarea to fit its content.
	 */
	function autoResize( textarea ) {
		textarea.style.height = 'auto';
		textarea.style.height = ( textarea.scrollHeight + 2 ) + 'px';
	}

	/**
	 * Initialise auto-resize on all code editors in the page.
	 */
	function initAutoResize() {
		document.querySelectorAll( '.hfs-code-editor' ).forEach( function ( el ) {
			autoResize( el );
			el.addEventListener( 'input', function () {
				autoResize( el );
			} );
		} );
	}

	/**
	 * Keep the toggle row's visual state in sync with checkbox state.
	 */
	function initToggleRows() {
		document.querySelectorAll( '.hfs-toggle-row' ).forEach( function ( row ) {
			var checkbox = row.querySelector( 'input[type="checkbox"]' );
			if ( ! checkbox ) {
				return;
			}

			function updateState() {
				if ( checkbox.checked ) {
					row.classList.add( 'is-active' );
				} else {
					row.classList.remove( 'is-active' );
				}
			}

			updateState();
			checkbox.addEventListener( 'change', updateState );
		} );
	}

	$( document ).ready( function () {
		initAutoResize();
		initToggleRows();
	} );

}( jQuery ) );
