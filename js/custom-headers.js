jQuery( document ).ready( function( $ ) {

	/* === Begin color picker JS. === */

	/* Add the WordPress color picker to our custom color input. */
	$( '.che-wp-color-picker' ).wpColorPicker();

	/* Hide the "Color" label. */
	$( 'label[for="che-header-text-color"]' ).hide();


	/* Show the color picker if the we're displaying header text. */
	if ( 'show' == $( '#che-header-text-show' ).val() || 'default-show' == $( '#che-header-text-show' ).val() ) {
		$( '.che-header-text-color-section' ).show();
	}else {
		$( '.che-header-text-color-section' ).hide();
	}

	/* Show/hide the color picker based on what option the user selects for displayin the header text. */
	$( '#che-header-text-show' ).change(
		function() {
			if ( 'show' == $( this ).val() || 'default-show' == $( this ).val() ) {
				$( '.che-header-text-color-section' ).show();
			} else {
				$( '.che-header-text-color-section' ).hide();
			}
		}
	);

	/* === End the color picker JS. === */

	/* === Begin header image JS. === */

	/* If the header <img> source has a value, show it.  Otherwise, hide. */
	if ( $( '.che-header-image-url' ).attr( 'src' ) ) {
		$( '.che-header-image-url' ).show();
	} else {
		$( '.che-header-image-url' ).hide();
	}

	/* If there's a value for the header image input. */
	if ( $( 'input#che-header-image' ).val() ) {

		/* Hide the 'set header image' link. */
		$( '.che-add-media-text' ).hide();

		/* Show the 'remove header image' link, the image. */
		$( '.che-remove-media, .che-header-image-url' ).show();
	}

	/* Else, if there's not a value for the header image input. */
	else {

		/* Show the 'set header image' link. */
		$( '.che-add-media-text' ).show();

		/* Hide the 'remove header image' link, the image. */
		$( '.che-remove-media, .che-header-image-url' ).hide();
	}

	/* When the 'remove header image' link is clicked. */
	$( '.che-remove-media' ).click(
		function( j ) {

			/* Prevent the default link behavior. */
			j.preventDefault();

			/* Set the header image input value to nothing. */
			$( '#che-header-image' ).val( '' );

			/* Show the 'set header image' link. */
			$( '.che-add-media-text' ).show();

			/* Hide the 'remove header image' link, the image. */
			$( '.che-remove-media, .che-header-image-url, .che-errors' ).hide();
		}
	);

	/*
	 * The following code deals with the custom media modal frame for the header image.  It is a 
	 * modified version of Thomas Griffin's New Media Image Uploader example plugin.
	 *
	 * @link      https://github.com/thomasgriffin/New-Media-Image-Uploader
	 * @license   http://www.opensource.org/licenses/gpl-license.php
	 * @author    Thomas Griffin <thomas@thomasgriffinmedia.com>
	 * @copyright Copyright 2013 Thomas Griffin
	 */

	/* Prepare the variable that holds our custom media manager. */
	var che_custom_headers_frame;

	/* When the 'set header image' link is clicked. */
	$( '.che-add-media' ).click( 

		function( j ) {

			/* Prevent the default link behavior. */
			j.preventDefault();

			/* If the frame already exists, open it. */
			if ( che_custom_headers_frame ) {
				che_custom_headers_frame.open();
				return;
			}

			/* Creates a custom media frame. */
			che_custom_headers_frame = wp.media.frames.che_custom_headers_frame = wp.media( 
				{
					className: 'media-frame',            // Custom CSS class name
					frame:     'select',                 // Frame type (post, select)
					multiple:  false,                   // Allow selection of multiple images
					title:     che_custom_headers.title, // Custom frame title

					library: {
						type: 'image' // Media types allowed
					},

					button: {
						text:  che_custom_headers.button // Custom insert button text
					}
				}
			);

			/*
			 * The following handles the image data and sending it back to the meta box once an 
			 * an image has been selected via the media frame.
			 */
			che_custom_headers_frame.on( 'select', 

				function() {

					/* Construct a JSON representation of the model. */
					var media_attachment = che_custom_headers_frame.state().get( 'selection' ).toJSON();

					/* If the custom header image size is available, use it. */
					/* Note the 'width' is contrained by $content_width. */
					if ( media_attachment[0].sizes.che_header_image ) {
						var che_media_url    = media_attachment[0].sizes.che_header_image.url;
						var che_media_width  = media_attachment[0].sizes.che_header_image.width;
						var che_media_height = media_attachment[0].sizes.che_header_image.height;
					}

					/* Else, use the full size b/c it will always be available. */
					else {
						var che_media_url    = media_attachment[0].sizes.full.url;
						var che_media_width  = media_attachment[0].sizes.full.width;
						var che_media_height = media_attachment[0].sizes.full.height;
					}

					/* === Begin image dimensions error checks. === */

					var che_errors = '';

					/*
					 * Note that we must use the "full" size width in some error checks 
					 * b/c I haven't found a way around WordPress constraining the image 
					 * size via the $content_width global. This means that the error 
					 * checking isn't 100%, but it should do fine for the most part since 
					 * we're using a custom image size. If not, the error checking is good 
					 * on the PHP side once the data is saved.
					 */
					if ( che_custom_headers.min_width > media_attachment[0].sizes.full.width && che_custom_headers.min_height > che_media_height ) {
						che_errors = che_custom_headers.min_width_height_error;
					}

					else if ( che_custom_headers.max_width < che_media_width && che_custom_headers.max_height < che_media_height ) {
						che_errors = che_custom_headers.max_width_height_error;
					}

					else if ( che_custom_headers.min_width > media_attachment[0].sizes.full.width ) {
						che_errors = che_custom_headers.min_width_error;
					}

					else if ( che_custom_headers.min_height > che_media_height ) {
						che_errors = che_custom_headers.min_height_error;
					}

					else if ( che_custom_headers.max_width < che_media_width ) {
						che_errors = che_custom_headers.max_width_error;
					}

					else if ( che_custom_headers.max_height < che_media_height ) {
						che_errors = che_custom_headers.max_height_error;
					}

					/* If there are error strings, show them. */
					if ( che_errors ) {
						$( '.che-errors p' ).text( che_errors );
						$( '.che-errors' ).show();
					}

					/* If no error strings, make sure the errors <div> is hidden. */
					else {
						$( '.che-errors' ).hide();
					}

					/* === End image dimensions error checks. === */

					/* Add the image attachment ID to our hidden form field. */
					$( '#che-header-image').val( media_attachment[0].id );

					/* Change the 'src' attribute so the image will display in the meta box. */
					$( '.che-header-image-url' ).attr( 'src', che_media_url );

					/* Hides the add header link. */
					$( '.che-add-media-text' ).hide();

					/* Displays the header image and remove header link. */
					$( '.che-header-image-url, .che-remove-media' ).show();
				}
			);

			/* Open up the frame. */
			che_custom_headers_frame.open();
		}
	);

	/* === End header image JS. === */
});