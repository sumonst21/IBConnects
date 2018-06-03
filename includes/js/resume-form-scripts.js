/*
 * JobRoller resume form jQuery functions
 * Written by AppThemes
 *
 * Copyright (c) 2010 AppThemes (http://appthemes.com)
 *
 * Built for use with the jQuery library
 * http://jquery.com
 *
 * Version 1.0
 *
 * Left .js uncompressed so it's easier to customize
 */

jQuery(document).ready(function($) {

	$.validator.messages.required = JR_i18n.required_msg;

	$("#submit_form").validate();

	// init the validator on load if the posted data contains empty required fields
	if ( undefined != $('.wp_editor_empty').html() ) {
		validator.form();
	}

	function loadFormFields() {
		var data = {
			action: 'app-render-resume-form',
			category: $(this).val()
		};

		$('#resume-form-custom-fields').html('<img class="loading-custom-fields" src = "' + JR_i18n.loading_img + '"> ' + JR_i18n.loading_msg );

		$.post( JR_i18n.ajaxurl, data, function(response) {
			console.log('response = ' , response);
			$( '#resume-form-custom-fields' ).html( response );
		});
	}

	$('#resume_cat')
		.change(loadFormFields)
		.find('option').eq(0).val(''); // needed for jQuery.validate()

});