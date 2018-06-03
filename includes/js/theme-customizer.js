( function($) {

	// update colors for the currently selected color scheme
	update_customizer_colors( customizer_params['color_scheme'] );

	// Update the color defaults in real time...
	wp.customize( 'jr_options[jr_child_theme]', function( value ) {
		value.bind( function( new_color_scheme ) {
			update_customizer_colors( new_color_scheme );
		} );
	} );

	function update_customizer_colors( color_scheme ) {

		var settings = new Array(
			'jr_top_nav_bgcolor',
			'jr_top_nav_links_color',
			'jr_top_nav_hover_bgcolor',
			'jr_top_nav_sep_color',
			'jr_header_bgcolor',
			'jr_buttons_color',
			'jr_buttons_nav_link_color',
			'jr_buttons_hover_bgcolor',
			'jr_buttons_selected_bgcolor',
			'jr_links_color',
			'jr_footer_bgcolor',
			'jr_footer_text_color',
			'jr_footer_links_color',
			'jr_footer_titles_color',
			'jr_footer_sep_color'
		);

		for ( i = 0; i < settings.length; i++ ) {

			var val = customizer_params['colors'][ color_scheme ];
			val = val[ settings[i] ];

			$('#customize-control-' + settings[i] + ' .wp-color-result').css( 'background-color', val );
			$('#customize-control-' + settings[i] + ' .color-picker-hex.wp-color-picker').val( val );
			$('#customize-control-' + settings[i] + ' .color-picker-hex.wp-color-picker').attr( 'data-default-color', val ).trigger('change');
		}

	}

} )( jQuery );
