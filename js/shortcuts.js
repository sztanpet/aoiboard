jQuery(function($){
	var base_url = window.location.hostname + window.location.path,
	content = $('.paged_content'),
	source = content.attr('data-source') == 'index.php' ? '' : content.attr('data-source'), // prevent generating /index.php?... urls instead of /?...
	query = content.attr('data-query') ? $.parseJSON(content.attr('data-query')) : {};


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
