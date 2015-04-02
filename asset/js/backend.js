jQuery(function($) {
	
	// Choosing channel on the post-channel edit/add form
	$('#cmvl-choose-channel input[type=radio]').change(function() {
		var channel = $(this).parents('figure').first();
		$('#title').val(channel.data('name'));
		$('#title-prompt-text').hide();
		var description = JSON.parse(channel.data('description'));
		if (!description) description = '';
		$('#content').val(description);
		if (tinyMCE.activeEditor) {
			tinyMCE.activeEditor.setContent(description.replace("\n", '<br>'));
		}
	});
	
	
	// After submit post-channel edit/add form
	$('form#post').submit(function(e) {
		var setError = function(msg) {
			e.preventDefault();
			e.stopPropagation();
			alert(msg);
		};
		// Force to choose channel.
		if ($('#cmvl-choose-channel input[type=radio]').length > 0 && $('#cmvl-choose-channel input[type=radio]:checked').length == 0) {
			setError('Please choose the Vimeo channel.');
		}
		// Force to choose at least one category for channel.
		else if ($('#cmvl_categorychecklist input[type=checkbox]').length > 0 && $('#cmvl_categorychecklist input[type=checkbox]:checked').length == 0) {
			setError('Please select at least one category.');
		}
	});
	
	
	$('.cmvl-settings-tabs a').click(function() {
		var match = this.href.match(/\#tab\-([^\#]+)$/);
		$('#settings .settings-category.current').removeClass('current');
		$('#settings .settings-category-'+ match[1]).addClass('current');
		$('.cmvl-settings-tabs a.current').removeClass('current');
		$('.cmvl-settings-tabs a[href=#tab-'+ match[1] +']').addClass('current');
		this.blur();
	});
	if (location.hash.length > 0) {
		$('.cmvl-settings-tabs a[href='+ location.hash +']').click();
	} else {
		$('.cmvl-settings-tabs li:first-child a').click();
	}
	
	
	$('.cmvl-mp-cost-add').click(function() {
		var button = $(this);
		var p = button.parents('p').first();
		p.before(button.data('template').replace(/\%s/g, ''));
		p.prev().find('.cmvl-mp-cost-remove').click(mpCostRemove);
		return false;
	});
	
	var mpCostRemove = function() {
		var button = $(this);
		button.parents('div').first().remove();
		return false;
	};
	$('.cmvl-mp-cost-remove').click(mpCostRemove);
	
});