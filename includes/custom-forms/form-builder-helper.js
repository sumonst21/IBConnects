(function($) {
  $.fn.enableCheckboxRangeSelection = function() {
    var lastCheckbox = null;
    var $spec = this;
    $spec.unbind("click.checkboxrange");
    $spec.bind("click.checkboxrange", function(e) {
      if (lastCheckbox != null && (e.shiftKey || e.metaKey)) {
        $spec.slice(
          Math.min($spec.index(lastCheckbox), $spec.index(e.target)),
          Math.max($spec.index(lastCheckbox), $spec.index(e.target)) + 1
        ).attr({checked: e.target.checked ? true : false});
      }
      lastCheckbox = e.target;
    });
  };
})(jQuery);

jQuery(document).ready(function() {
	jQuery('.categorychecklist input[type="checkbox"]').click(function(){
		if ( children = jQuery(this).closest('li').find('ul.children') ) {
			if ( jQuery(this).attr('checked') ) {
				children.find('input[type=checkbox]').attr('checked', true);
			} else {
				children.find('input[type=checkbox]').attr('checked', false);
			}
		}
	});

	jQuery('.categorychecklist input[type="checkbox"]').enableCheckboxRangeSelection();
	
});