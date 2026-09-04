/**
 * Post to X media picker - vanilla JS (no jQuery dependency), using wp.media
 * directly.
 *
 * Replaces the pre-2.0.0 plugin's assets/jquery/jquery.js, which contained
 * four near-identical jQuery/thickbox media-uploader handlers - one
 * hand-copied per image slot (1-4). This single, parameterised handler
 * reads which slot was clicked from the button's data-azrcrv-e-media-slot
 * attribute (set on the wrapping <div> for each slot in
 * functions-metabox-to-x.php) instead.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media ) {
		return;
	}

	var mediaContainer = document.querySelector( '[data-azrcrv-e-no-image]' );
	var noImageUrl      = mediaContainer ? mediaContainer.getAttribute( 'data-azrcrv-e-no-image' ) : null;

	/**
	 * Find the slot number (1-4) for a clicked button.
	 *
	 * @param {HTMLElement} button The clicked button.
	 * @return {string|null} The slot number, or null if not found.
	 */
	function getSlot( button ) {
		var wrapper = button.closest( '[data-azrcrv-e-media-slot]' );
		return wrapper ? wrapper.getAttribute( 'data-azrcrv-e-media-slot' ) : null;
	}

	document.addEventListener( 'click', function ( e ) {

		var uploadButton = e.target.closest( '.azrcrv-e-upload-image' );
		var removeButton = e.target.closest( '.azrcrv-e-remove-image' );

		if ( uploadButton ) {
			e.preventDefault();

			var slot = getSlot( uploadButton );
			if ( ! slot ) {
				return;
			}

			var frame = wp.media( {
				title: uploadButton.value,
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				var image      = document.getElementById( 'tweet-image-' + slot );
				var input      = document.getElementById( 'tweet-selected-image-' + slot );

				if ( image ) {
					image.src = attachment.url;
				}
				if ( input ) {
					input.value = attachment.url;
				}
			} );

			frame.open();
			return;
		}

		if ( removeButton ) {
			e.preventDefault();

			var removeSlot = getSlot( removeButton );
			if ( ! removeSlot ) {
				return;
			}

			var image = document.getElementById( 'tweet-image-' + removeSlot );
			var input = document.getElementById( 'tweet-selected-image-' + removeSlot );

			if ( image && noImageUrl ) {
				image.src = noImageUrl;
			}
			if ( input ) {
				input.value = '';
			}
		}
	} );

} );
