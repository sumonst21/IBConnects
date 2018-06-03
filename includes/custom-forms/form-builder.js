/**
 * jQuery Form Builder Plugin
 * Copyright (c) 2009 Mike Botsko, Botsko.net LLC (http://www.botsko.net)
 * http://www.botsko.net/blog/2009/04/jquery-form-builder-plugin/
 * Originally designed for AspenMSM, a CMS product from Trellis Development
 * Licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * Copyright notice and license must remain intact for legal use
 *
 * Forked by AppThemes on 09/09/2011
 */

/* global $, jQuery, l10n, validateL10n */
(function ($) {

	$.widget( "appthemes.formbuilder", {

		options : {},
		id      : '',

		_create : function () {
			this._trigger( 'init', null, this );
			this._initProperties();
			// hook to add new or change existing properties
			this._trigger( 'props', null, this );
			this._initFieldTypes();
			// hook to add new or change existing field types
			this._trigger( 'fields', null, this );
			this.id = 'frmb-' + $('ul[id^=frmb-]').length++;
			this._build_form();
		},

		_fieldProperty : {
			type     : '',
			label    : '',
			tip      : '',
			value    : '',
			required : false,
			html     : function () {},
			getData  : function ( context ) {
				return $( context ).find( ".prop-" + this.type ).val();
			}
		},

		_field : {
			type  : '',
			title : '',
			tip   : '',
			props : {},
			getData : function ( context ) {
				return {
					id    : $( context ).data( 'field-id' ),
					type  : this.type,
					props : {}
				};
			}
		},

		addFiledType : function( type, options ) {
			var proto;
			if ( ! this._fieldTypes[type] ) {
				proto = this._field;
			} else {
				proto = this._fieldTypes[type];
			}
			this._fieldTypes[type] = $.extend( true, {}, proto, { type : type }, options );
		},

		addPropertyType : function( type, options ) {
			this._propertyTypes[type] = $.extend( true, {}, this._fieldProperty, { type : type }, options );
		},

		_initFieldTypes : function() {

			var fileProps = { extensions : $.extend( {}, this._propertyTypes.extensions ) };
			var selectProps = { options : $.extend( {}, this._propertyTypes.options ) };
			var checkboxProps = $.extend( {},
				selectProps,
				{ options : $.extend( {},
					this._propertyTypes.options, { input : 'checkbox' }
				) }
			);

			// text
			this.addFiledType( 'input_text', {
				title : l10n.input_text
			} );

			this.addFiledType( 'number', {
				title : l10n.number
			} );

			this.addFiledType( 'url', {
				title : 'URL'
			} );

			this.addFiledType( 'email', {
				title : 'Email'
			} );

			// textarea
			this.addFiledType( 'textarea', {
				title : l10n.textarea
			} );

			// file
			this.addFiledType( 'file', {
				title : l10n.file,
				props : fileProps
			} );

			// select
			this.addFiledType( 'select', {
				title : l10n.select,
				props : selectProps
			} );

			// radio
			this.addFiledType( 'radio', {
				title : l10n.radio,
				props : selectProps
			} );

			// checkbox
			this.addFiledType( 'checkbox', {
				title : l10n.checkbox,
				props : checkboxProps
			} );
		},

		_initProperties : function() {

			// field Label property
			this.addPropertyType( 'label', {
				label    : l10n.label,
				required : true,
				html     : wp.template( "app-field-property-input" )
			} );

			// field Reqired property
			this.addPropertyType( 'required', {
				label   : l10n.required,
				html    : wp.template( "app-field-property-checkbox" ),
				getData : function( context ) {
					return $( context ).find( ".prop-" + this.type ).prop( "checked" ) ? 1 : 0;
				}
			} );

			// File field extensions property
			this.addPropertyType( 'extensions', {
				label    : l10n.file_extensions,
				required : true,
				tip      : l10n.file_tip,
				value    : 'pdf',
				html     : wp.template( "app-field-property-input" )
			} );

			// field options property
			this.addPropertyType( 'options', {
				input    : 'radio',
				required : true,
				label    : l10n.select_options,
				value    : {
					0 : {
						value    : '',
						baseline : ''
					}
				},
				html     : wp.template( "app-field-property-options" ),
				getData  : function( context ) {
					var options = [];
					$( context ).children().each( function() {
						var option = {};
						option.baseline = $( this ).find( '.prop-options-val' ).prop( "checked" ) ? 1 : 0;
						option.value = $( this ).find( '.prop-options-label' ).val();
						options.push( option );
					} );
					return options;
				}
			} );

			// field Tooltip property
			this.addPropertyType( 'tip', {
				label : l10n.tooltip,
				html  : wp.template( "app-field-property-input" )
			} );

			// add global properties to all field types
			this._field.props = {
				required : $.extend( {}, this._propertyTypes.required ),
				label    : $.extend( {}, this._propertyTypes.label ),
				tip      : $.extend( {}, this._propertyTypes.tip )
			};
		},

		_fieldTypes : {},
		_propertyTypes : {},

		_validate : function(e) {
			var frmb = this;
			$('label.error').remove();
			$('.frmb .frm-elements .property').each(function() {
				var field_type = $( this ).closest( "li.postbox" ).data('field-type');
				var prop       = frmb._fieldTypes[field_type]['props'][ $(this).data('prop') ];
				var required   = prop['required'];

				$(this).find('input[type=text]').each(function() {
					var valid = true;
					$(this).css({backgroundColor:'#fff'});
					if ( $(this).val() == '' && required ) {
						$(this).css({backgroundColor:'#ffdada'});
						$(this).parent().append('<label for="'+ $(this).attr('id') +'" class="error">'+ validateL10n.required +'</label>');
						valid = false;
					}
					if ( ! valid ) {
						e.preventDefault();
					}
				});
			});

			this._trigger( 'validate', null, { frmb:this, event:e } );
		},

		_build_form : function() {

			var tpl = wp.template( "app-formbuilder" );
			var formbuilder = this;

			$( $.trim( tpl( this ) ) ).appendTo( this.element );

			var ul_obj    = $( '#' + this.id );
			var toggleAll = $( '#toggle-all' );

			var fields = this._parseForm( $.parseJSON( $('#app-form-data').val() ) );

			for ( var id in fields ) {
				if ( fields.hasOwnProperty( id ) ) {
					this._newField( fields[id] ).appendTo( ul_obj );
				}
			}

			this.element.closest('form').submit(function(e) {
				var data = $('#app-form-data');
				data.val( formbuilder._serializeForm( ul_obj, data.attr('name') ) );
				$('.frmb input[type=text]').on('input focusout', function(e) {formbuilder._validate(e);});
				formbuilder._validate(e);
			});

			$( '.button-wrap > .button' ).click( function( e ) {

				var field  = $.extend( true, {}, formbuilder._fieldTypes[ $(this).data('type') ] );
				var output = formbuilder
					._newField( field )
					.appendTo( ul_obj )
					.css( { backgroundColor:'#ceb' } )
					.fadeIn( 300, function() {
						$( this )
							.animate( { backgroundColor: '#fff' }, 1000 )
							.find( '.frm-group input:text' )
							.first()
							.focus();
					});

				// This solves the scrollTo dependency
				$( 'html, body' ).animate( {
					scrollTop: output.offset().top
				}, 300 );

				e.preventDefault();
			});

			// handle field delete rows
			$('.postbox').on('click', 'a.remove', function(e) {
				var i = $(this).closest('.fields');
				// must have at least one field
				if ( i.children().length <= 1 )
					return false;
				$(this).parent('div').remove();
				e.preventDefault();
			});

			// toggle all boxes
			toggleAll.click(function(e) {
				$(this).text($(this).text() === l10n.collapse_all ? l10n.expand_all : l10n.collapse_all);
				$('div.frm-holder').toggle();
				$('li.postbox').toggleClass('closed');
				e.preventDefault();
			});

			// handle field display/hide
			$('.frmb').on('click', '.handlediv', function(e) {
				$(this).toggleClass('closed');
				$(this).siblings('.frm-holder').toggle();
				e.preventDefault();
			});

			// handle delete field
			$('.frmb').on('click', 'a.delete', function(e) {
				if (confirm($(this).attr('title'))) {
					$(this).closest('li').css( {backgroundColor:'#FFAAAA'} ).fadeOut(350, function(){
						$(this).remove();
					});
				}
				e.preventDefault();
			});

			// add new select option field
			$('.frmb').on('click', '.add_opt', function(e) {
				var
					fieldRow  = $( this ).closest( "li.postbox" ),
					fieldType = fieldRow.data( 'field-type' ),
					field     = formbuilder._fieldTypes[ fieldType ],
					options   = field.props.options;

				options.fieldID = fieldRow.data( 'field-id' );
				$(this).parent().after( options.html( options ) );
				e.preventDefault();
			});
		},

		_newField : function( field ) {
			var tpl = wp.template( "app-form-field" );
			return $( $.trim( tpl( field ) ) );
		},

		_parseForm : function( json ) {
			var
				fields = {},
				types  = this._fieldTypes;

			// Parse json
			$(json).each( function () {
				var field;

				field = $.extend( true, {}, types[ this.type ], { id : this.id } );

				for ( var prop in field.props ) {
					if ( field.props.hasOwnProperty( prop ) ) {
						field.props[ prop ].value = this.props[ prop ];
					}
				}

				fields[ this.id ] = field;
			});

			return fields;
		},

		_serializeForm : function( ul, prepend ) {
			var
				fieldsData  = [],
				formData    = {};
				formbuilder = this;

			ul.children().each(function() {
				var
					fieldType = $(this).data('field-type'),
					filedObj  = formbuilder._fieldTypes[ fieldType ],
					data      = filedObj.getData( this );

				$( this ).find( '.property' ).each( function() {
					var
						propType = $(this).data('prop'),
						propObj  = formbuilder._propertyTypes[ propType ];

					data.props[ propType ] = propObj.getData( this );
				} );

				fieldsData.push( data );
			});

			formData[ prepend ] = fieldsData;
			return decodeURIComponent( $.param( formData ) );
		}

	} );
}(jQuery));