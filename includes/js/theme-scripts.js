/**
 * JobRoller theme jQuery functions
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

	/* Single Job Apply Validate */

	$('#apply_form .main_form').validate({
		rules: {
		  // simple rule, converted to {required:true}
		  name: "required",
		  // compound rule
		  your_email: {
			required: true,
			email: true
		  }
		}
	  }
	);

	/* Register Form Validate */

	$('#login-form').validate({
		rules: {
		  // simple rule, converted to {required:true}
		  name: "required",
		  // compound rule
		  user_email: {
			required: true,
			email: true
		  }
		}
	  });


	/* Load more sponsored results */

	$(document.body).on('click', 'a.more_sponsored_results', function(){
		var link = $(this);
		var source = $(this).attr('source');
		var tax = '';
		var term = '';

		$(link).fadeOut('fast');

		if ($(link).is('.front_page')) {
			load = 'more_front_results';
		} else if ($(link).is('.filter')) {
			if ( $(link).attr('tax') && $(link).attr('tax').length > 0 ) {
				tax = $(link).attr('tax');
				term = $(link).attr('term');
			}
			load = 'more_filter_results';
		} else if ($(link).is('.search_page')) {
			load = 'search';
		} else {
			load = '';
		}

		var callback = $(this).attr("callback");

		var data = {
			// get more sponsored results
			action:   callback,
			security: jobroller_params.get_sponsored_results_nonce,
			page:				$(link).attr('rel'),
			load:				load,
			tax:				tax,
			term:				term
		};

		$.post( jobroller_params.ajax_url, data, function(response) {

			if ( ! response ) {
				$('.more_sponsored_results').replaceWith('<span>'+jobroller_params.no_more_results+'</span>');
				return;
			}

			$('ol.sponsored_results[source="' + source + '"]').append(response);
			var current = parseInt($(link).attr('rel'));
			$(link).attr('rel', (current + 1)).fadeIn();

			$('html, body').animate({
			    scrollTop: $("li#more-" + current).offset().top
			}, 500);

		});

		return false;

	});

	/* Search Geo Location */
	function clientside_geo_lookup() {

		var address_string = $('#near').val();
		if (address_string) {

			var geo = new google.maps.Geocoder();
			geo.geocode({'address' : address_string}, function(results, status){

			    latitude 			= results[0].geometry.location.lat();
			    longitude 			= results[0].geometry.location.lng();
			    north_east_lat		= results[0].geometry.bounds.getNorthEast().lat();
			    south_west_lat		= results[0].geometry.bounds.getSouthWest().lat();
			    north_east_lng		= results[0].geometry.bounds.getNorthEast().lng();
			    south_west_lng		= results[0].geometry.bounds.getSouthWest().lng();

			    full_address 	= results[0]['formatted_address'];

			    $('input#field_longitude').val( longitude );
				$('input#field_latitude').val( latitude );
				$('input#field_full_address').val( full_address );
				$('input#field_north_east_lat').val( north_east_lat );
				$('input#field_south_west_lat').val( south_west_lat );
				$('input#field_north_east_lng').val( north_east_lng );
				$('input#field_south_west_lng').val( south_west_lng );

			});
		}
		$('#searchform').unbind('submit');
		setTimeout("jQuery('#searchform').submit();", 100);
		return false;
	}
	$('#searchform').bind('submit', function() {
		return clientside_geo_lookup();
	});

	/* Placeholder fallback */
	$('[placeholder]').not("input[class*='tag-input-']").defaultValue();

	/* Tag input */
	$('.tag-input-commas').tag( {separator: ','} );

    /* Apply for job slider */
    $('#share_form, #apply_form:not(.open)').hide();

    if ( $('#apply_form').is('.open') ) {
		$('a.apply_online').addClass('active');
	}

    $('a.apply_online').click(function(){
    	$('#job_map').slideUp();
        $('#share_form').slideUp();
        $('#apply_form').slideToggle();
        $('a.share').removeClass('active');
        $(this).toggleClass('active');
        return false;
    });

    $('a.share').click(function(){
    	$('#job_map').slideUp();
        $('#apply_form').slideUp();
        $('#share_form').slideToggle();
        $('a.apply_online').removeClass('active');
        $(this).toggleClass('active');
        return false;
    });

    // Show single job apply and print section
    $('ul.section_footer').show();

    // add jquery lazy load for images
    $('img:not(.load)').lazyload({
        effect:'fadeIn',
        placeholder: jobroller_params.lazyload_placeholder
    });

	$('textarea.grow').autogrow();

	// qTips
	if ( $.isFunction( $.fn.qtip ) ) {
		$('h1.resume-title span, .resume_header img').qtip({
			content: {
				text: $('.user_prefs_wrap')
			},
			position: {
				my: 'bottom center',
				at: 'center'
			}
		});
		$('ol.resumes li').qtip({
			position: {
				my: 'bottom center',
				at: 'center'
			}
		});
	}

	function check_pass_strength () {

		var pass = $('#pass1').val();
		var pass2 = $('#pass2').val();
		var user = $('#user_login').val();

		$('#pass-strength-result').removeClass('short bad good strong');
		if ( ! pass ) {
			$('#pass-strength-result').html( jobroller_params.si_empty );
			return;
		}

		var strength = passwordStrength(pass, user, pass2);

		if ( 2 == strength ) {
			$('#pass-strength-result').addClass('bad').html( jobroller_params.si_bad );
		} else if ( 3 == strength ) {
			$('#pass-strength-result').addClass('good').html( jobroller_params.si_good );
		} else if ( 4 == strength ) {
			$('#pass-strength-result').addClass('strong').html( jobroller_params.si_strong );
		} else if ( 5 == strength ) {
			 $('#pass-strength-result').addClass('short').html( jobroller_params.si_mismatch );
		 } else {
			$('#pass-strength-result').addClass('short').html( jobroller_params.si_short );
		}

	}

	$('#pass1, #pass2').val('').keyup( check_pass_strength );

	try{convertEntities(jobroller_params);}catch(e){};

	/* Footables for smaller screens */
	$('.footable').footable();

	/* Gateways */

	$('form.gateway').addClass('custom');

	var button_text = $('.section_content .row form button').text();

	$('.section_content .row form button').parents('form').addClass('main_form');
	$('.section_content .row form button').replaceWith('<input type="submit" class="submit" value="' + button_text.trim() + '">');

	/* Tabs */

	var url = window.location.hash;
	var hash = url.substring( url.indexOf("#") + 1 );

	if ( hash ) {

		if ( undefined !== $('a[href="#'+hash+'"]').html() ) {

			$('a[href="#'+hash+'"]').trigger('click');

			$('html,body').animate( {scrollTop: $('a[href=#'+hash+']').offset().top-100} );
		}

	}

	/* enables smooth scroll to the top */
	if ( $.isFunction( $.fn.smoothScroll ) ) {
		$('.top a').smoothScroll();
	}

	/* Functions */

	/* Load sponsored results on the background - async */
	function load_async_sponsored_results () {

		$('div.async_sponsored_results').each( function() {

				var link = $(this);
				var load = 'front_results';
				var tax = '';
				var term = '';

				// front page sponsored results
				if ($(link).length > 0) {
					if ( $(link).attr('tax') && $(link).attr('tax').length > 0 ) {
						load = 'filter_results';
						tax = $(link).attr('tax');
						term = $(link).attr('term');
					}
				} else
					return;

				var callback = $(this).attr("callback");

				var data = {
					action: 	callback,
					security: 	jobroller_params.get_sponsored_results_nonce,
					load:		load,
					tax:		tax,
					term:		term
				};

				$.post( jobroller_params.ajax_url, data, function(response) {

					$(link).html(response);
					$(link).fadeIn();

				});

		});
	}

	/* Init sponsored results async load */
	load_async_sponsored_results();
});