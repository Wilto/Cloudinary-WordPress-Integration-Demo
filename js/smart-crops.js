/* global $, jQuery, ajaxurl */

$( function() {
	var $body = $( 'body' ),
		attachmentId, imageSrc, nonceName, nonceValue,
		$smartCropButton, $focalPointButton, $resetButton, $spinner, $originalImage, $thumbsContainer,
		cl = cloudinary.CloudinaryJQuery.new({ cloud_name: "wilto" });

		cl.init();

	disableSmartCrops();
	$body.on( 'click', '.js--select-attachment', disableSmartCrops );

	$body.on( 'click', '[data-show-thumbnails]', function( e ) {
		var $revealButton = $( this );
		e.preventDefault();

		$revealButton.hide();

		revealThumbnails( $revealButton );
	});

	$body.on( 'click', '[data-use-smart-crop]', function( e ) {
		e.preventDefault();
		$smartCropButton.blur();

		toggleSmartCrop( true );
	});

	$body.on( 'click', '[data-select-focal-point]', function( e ) {
		e.preventDefault();
		$focalPointButton.blur();

		toggleFocalPointSelection();
	});

	$body.on( 'click', '[data-reset-smart-crops]', function( e ) {
		e.preventDefault();

		resetSmartCrops();
	});

	/**
	 * Diable Smart Crops feature.
	 *
	 * @return {void}
	 */
	function disableSmartCrops() {
		if ( ! $body.hasClass( 'upload-php' ) ) {
			$( '.compat-field-pmc_smart_crops' ).remove();
		}
	}

	/**
	 * Reveal thumbnails.
	 *
	 * Move the thumbnails container to a better place and show it right after.
	 * Add a spinner element used when AJAX requests are made.
	 *
	 * @return {void}
	 */
	function revealThumbnails( $button ) {
		var $parent = $button.closest( '.compat-attachment-fields' ),
			$nonce = $parent.find( '#smart-crops-nonce' ),
			$editAttachmentButton = $( '.edit-attachment' );

		$thumbsContainer  = $( '.smart-crop-thumbs-container' );
		$smartCropButton  = $parent.find( '[data-use-smart-crop]' );
		$focalPointButton = $parent.find( '[data-select-focal-point]' );
		$resetButton      = $parent.find( '[data-reset-smart-crops]' );
		$spinner          = $parent.find( '.spinner' );
		$originalImage    = $( '.details-image' );
		attachmentId      = $thumbsContainer.data( 'attachmentId' );
		imageSrc          = $thumbsContainer.data( 'attachmentSrc' );

		// Nonce data.
		nonceName  = $nonce.attr( 'name' );
		nonceValue = $nonce.val();

		// Reposition thumbs and buttons.
		$thumbsContainer.detach();
		$parent.after( $thumbsContainer.removeClass( 'hidden' ) );

		$smartCropButton.detach().removeClass( 'hidden' );
		$focalPointButton.detach().removeClass( 'hidden' );
		$resetButton.detach();
		$spinner.detach();

		$editAttachmentButton.after( $smartCropButton );
		$smartCropButton.after( $focalPointButton );
		$focalPointButton.after( $resetButton );
		$resetButton.after( $spinner );

		if ( $thumbsContainer.hasClass( 'has-cropping-parameters' ) ) {
			$resetButton.removeClass( 'hidden' );
		}
	}

	/**
	 * Toggle smart crop.
	 *
	 * Update thumbnail URLs to toggle smart crop and trigger AJAX request to save change in the back-end.
	 *
	 * @param {boolean} enableSmartCrop
	 * @return {void}
	 */
	function toggleSmartCrop( enableSmartCrop ) {
		var cropParams = enableSmartCrop ? 'auto' : '';

		updateThumbnailsURL( cropParams );
		$resetButton.removeClass( 'hidden' );

		sendAjaxRequest({
			action: 'toggle_smart_crop',
			attachment_id: attachmentId,
			smart_crop_state: enableSmartCrop ? 1 : 0
		});
	}

	/**
	 * Reset cropping.
	 *
	 * @return {void}
	 */
	function resetSmartCrops() {
		updateThumbnailsURL( '' );
		$resetButton.addClass( 'hidden' );

		sendAjaxRequest({
			action: 'reset_smart_crops',
			attachment_id: attachmentId
		});
	}

	/**
	 * Toggle focal point selection mode.
	 *
	 * @return {void}
	 */
	function toggleFocalPointSelection() {
		$originalImage.toggleClass( 'select-focal-point' );
		if ( $originalImage.hasClass( 'select-focal-point' ) ) {
			$originalImage.one( 'click', selectFocalPoint );
		} else {
			$originalImage.off( 'click', selectFocalPoint );
		}
	}

	/**
	 * Handle focal point selection event.
	 *
	 * @param {object} e Event object.
	 * @return {void}
	 */
	function selectFocalPoint( e ) {
		var offsetLeft = Math.round( e.offsetX / $originalImage.width() * 100 ),
			offsetTop = Math.round( e.offsetY / $originalImage.height() * 100 );

		updateThumbnailsURL( ',offset-x' + offsetLeft + ',offset-y' + offsetLeft );
		$resetButton.removeClass( 'hidden' );
		toggleFocalPointSelection();

		sendAjaxRequest({
			action: 'set_focal_point',
			attachment_id: attachmentId,
			focal_point_offset_left: offsetLeft,
			focal_point_offset_top: offsetTop
		});
	}

	/**
	 * Build Fastly API compliant image URL.
	 *
	 * @param {string} cropParams
	 * @return {void}
	 */
	function updateThumbnailsURL( cropParams ) {
		$thumbsContainer.find( 'img' ).each( function( index, img ) {
			var w = img.naturalWidth,
				h = img.naturalHeight,
				cid = img.getAttribute( "data-cloudinary" ),
				crop = cropParams ? { radius: 200, crop: "fill", gravity: cropParams } : {};

			img.src = cl.url( cid, crop );
		});
	}
	/**
	 * Send AJAX request.
	 *
	 * @param {object} params
	 */
	function sendAjaxRequest( params ) {
		$spinner.addClass( 'is-active' );

		params[ nonceName ] = nonceValue;

		jQuery.post( ajaxurl, params, function() {
			$spinner.removeClass( 'is-active' );
		});
	}
});
