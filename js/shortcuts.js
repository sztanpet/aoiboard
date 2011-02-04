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

jQuery(function($){
	$('.hide').click( function() {
		var id = $(this).parents('div.image').attr('data-id'),
				ids = $.cookie('hideimages');
		
		if ( !ids )
			$.cookie('hideimages', id, {expires: 3650});
		else
			$.cookie('hideimages', ids + '|' + id, {expires: 3650});
		
		$(this).parents('div.image').hide();
		return false;
	});
	updateHideImages();
});

function updateHideImages() {
	
	var ids = $.cookie('hideimages');
	if (ids) {
		ids = ids.split('|');
		if ( ids.length ) {
			
			var currentids = {};
			$('.image').each( function() {
				currentids[ $(this).attr('data-id') ] = $(this);
			});
			
			for( i = 0, j = ids.length; i < j; i++ ) {
				if ( currentids[ ids[ i ] ] )
					currentids[ ids[ i ] ].hide();
			}
		}
	}
	
}