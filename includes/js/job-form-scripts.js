/*
 * JobRoller job form jQuery functions
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

	var validator = $("#submit_form").validate({
		ignore: '',
		errorClass: 'error',
		errorElement: 'label',
		rules: {
		  post_content: {
			required: function() {
				// help jquery validator check if the hidden text area is empty or not by looking at the related iframe content
				var content = $('#post_content_ifr').contents().find('body').html();
				return undefined === content || content.indexOf('data-mce-bogus') >= 0;
			},
		  }
		},
		errorPlacement: function( error, element ) {

			if ( 'textarea' == element.prop('type') ) {
				// make sure the error message is displayed after the container
				error.addClass('wp_editor_error')
				error.appendTo( element.closest('.wp_editor_wrapper') );
				error.closest('.wp_editor_wrapper').addClass('error wp_editor_error');
			} else {
				error.insertAfter(element); // default error placement.
			}

		},
		showErrors: function( errorMap, errorList ) {

			// remove error marker in wp_editor() textareas
			$('.wp_editor_error label.error').remove();
			$('.wp_editor_error').removeClass('error wp_editor_error');

			this.defaultShowErrors();
		},

	});

	// init the validator on load if the posted data contains empty required fields
	if ( undefined != $('.wp_editor_empty').html() ) {
		validator.form();
	}

	function loadFormFields() {
		var data = {
			action: 'app-render-job-form',
			job_category: $(this).val()
		};

		$('#job-form-custom-fields').html('<img class="loading-custom-fields" src = "' + JR_i18n.loading_img + '"> ' + JR_i18n.loading_msg );

		$.post( JR_i18n.ajaxurl, data, function(response) {
			$( '#job-form-custom-fields' ).html( response );
		});
	}

	$('#job_term_cat')
		.change(loadFormFields)
		.find( 'option' ).eq(0).val(''); // needed for jQuery.validate()

});