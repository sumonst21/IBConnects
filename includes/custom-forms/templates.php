<?php
/**
 * Underscore templates
 */
?>
<!-- formbuilder template -->
<script id="tmpl-app-formbuilder" type="text/html">
	<div class="toolbar">
		<div id="{{ data.id }}-control-box" class="button-wrap">
			<a href="#" id="toggle-all">{{{ l10n.collapse_all }}}</a>
			<# _.each( data._fieldTypes, function( type ) { #>
			<a href="#" data-type="{{ type.type }}" class="button">{{ type.title }}</a>
			<# }); #>
		</div>
	</div><!-- .toolbar -->
	<ul id="{{ data.id }}" class="frmb {{ data.id }}"></ul>
</script>

<!-- field row template -->
<script id="tmpl-app-form-field" type="text/html">
	<li class="postbox" data-field-type="{{ data.type }}" data-field-id="{{ data.id }}">
		<div class="handlediv"><br></div>
		<h3>{{{ data.title }}}</h3>
		<div class="frm-holder">
			<div class="frm-elements">
				<# _.each( data.props, function( property ) { #>
				<div class="frm-group">
					<label class="options">{{{ property.label }}}</label>
					<div class="fields property" data-prop="{{ property.type }}">
						<# property.fieldID = data.id; #>
						{{{ property.html( property ) }}}
						<# if ( property.tip ) { #>
						<div class="field-tip">{{{ property.tip }}}</div>
						<# } #>
					</div><!-- .fields -->
				</div><!-- .frm-group -->
				<# }); #>
				<a href="#" class="delete" tabindex="-1" title="{{ l10n.remove }}">
					<span class="dashicons dashicons-dismiss"></span>
				</a>
			</div><!-- .frm-elements -->
		</div><!-- .frm-holder -->
	</li>
</script>

<script id="tmpl-app-field-property-input" type="text/html">
	<# var input_type = data.input_type ? data.input_type : 'text'; #>
	<# var extra = data.extra ? data.extra : ''; #>
	<input type="{{ input_type }}" class="prop-{{ data.type }}" value="{{ data.value }}" {{{ extra }}}/>
</script>

<script id="tmpl-app-field-property-checkbox" type="text/html">
	<# var checked = data.value == 1 ? 'checked="checked"' : '' #>
	<input type="checkbox" value="1" class="prop-{{ data.type }}" {{{ checked }}}/>
</script>

<script id="tmpl-app-field-property-options" type="text/html">
	<# _.each( data.value, function( value ) { #>
		<# var checked = value.baseline == 1 ? 'checked="checked"' : ''; #>
		<div class="option-row">
			<input type="{{ data.input }}" tabindex="-1" class="prop-{{ data.type }}-val" name="{{ data.fieldID }}" {{{ checked }}}/>
			<input type="text" value="{{ value.value }}" class="prop-{{ data.type }}-label" />
			<a href="#" class="add add_opt" tabindex="-1"><span class="dashicons dashicons-plus"></span></a>
			<a href="#" class="remove" tabindex="-1"><span class="dashicons dashicons-no"></span></a>
		</div>
	<# }); #>
</script>

<script id="tmpl-app-field-property-select" type="text/html">
	<select name="{{ data.fieldID }}" class="prop-{{ data.type }}">
		<# _.each( data.opts, function( option ) { #>
			<# var selected = option.name == data.value ? 'selected="selected"' : ''; #>
			<option value="{{ option.name }}" {{{ selected }}}>{{{ option.label }}}</option>
		<# }); #>
	</select>
</script>