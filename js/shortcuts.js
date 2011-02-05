jQuery(function($){
	var base_url = window.location.hostname + window.location.path,
	content = $('.paged_content'),
	source = content.attr('data-source') == 'index.php' ? '' : content.attr('data-source'), // prevent generating /index.php?... urls instead of /?...
	query = content.attr('data-query') ? $.parseJSON(content.attr('data-query')) : {};

	delete query.magic;

	function build_url(direction){
		var re = source + '?';
		if (window.autofiller) {
			re += $.param($.extend({page: (+window.autofiller.current_page) + direction}, query));
		} else {
			re += $.param($.extend({page: (+content.attr('data-page')) + direction}, query));
		}
		return re;
	}

	$(document).keypress(function(e){
		if (e.keyCode === 39) {
			 window.location = build_url(+1);
		}
		if (e.keyCode === 37) {
			 window.location = build_url(-1);
		}
	});
});

jQuery(function($){
	function hide_image(image) {
		image.find('img').attr('src', './img/hidden.png');
		image.find('.hide').hide();
	}
	function replace_hidden_images() {
		$('#image_'+($.cookie('hideimages') || '').split('|').join(',#image_')).each(function(i, el){
			hide_image($(el));
		});
	}
	$('body').bind('autofiller.page_loaded', replace_hidden_images);

	$('body').delegate('.hide', 'click', function(){
		var id = $(this).parents('div.image').attr('data-id'),
			ids = $.cookie('hideimages');

		if (!ids) {
			$.cookie('hideimages', id, {expires: 3650});
		} else {
			$.cookie('hideimages', ids + '|' + id, {expires: 3650});
		}

		hide_image($(this).parents('div.image'));
		return false;
	});
	
	replace_hidden_images();
});
