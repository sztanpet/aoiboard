jQuery(function($){
	$('#autofill').click(function(){
		$.cookie('setting_autofill', +$(this).is(':checked') , { expires: 3650 });
		window.location.reload(true);
	});
});
!function( $ ){

  var d = 'a.menu, .dropdown-toggle'

  function clearMenus() {
    $(d).parent('li').removeClass('open')
  }

  $(function () {
    $('html').bind("click", clearMenus)
    $('body').dropdown( '[data-dropdown] a.menu, [data-dropdown] .dropdown-toggle' )
  })

  /* DROPDOWN PLUGIN DEFINITION
   * ========================== */

  $.fn.dropdown = function ( selector ) {
    return this.each(function () {
      $(this).delegate(selector || d, 'click', function (e) {
        var li = $(this).parent('li')
          , isActive = li.hasClass('open')

        clearMenus()
        !isActive && li.toggleClass('open')
        return false
      })
    })
  }

}( window.jQuery || window.ender );

/*jslint browser: true */ /*global jQuery: true */

/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

// TODO JsDoc

/**
 * Create a cookie with the given key and value and other optional parameters.
 *
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Set the value of a cookie.
 * @example $.cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a cookie with all available options.
 * @example $.cookie('the_cookie', 'the_value');
 * @desc Create a session cookie.
 * @example $.cookie('the_cookie', null);
 * @desc Delete a cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the cookie was set.
 *
 * @param String key The key of the cookie.
 * @param String value The value of the cookie.
 * @param Object options An object literal containing key/value pairs to provide optional cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the cookie will be deleted.
 *                             If set to null or omitted, the cookie will be a session cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the cookie (default: path of page that created the cookie).
 * @option String domain The value of the domain attribute of the cookie (default: domain of page that created the cookie).
 * @option Boolean secure If true, the secure attribute of the cookie will be set and the cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a cookie with the given key.
 *
 * @example $.cookie('the_cookie');
 * @desc Get the value of a cookie.
 *
 * @param String key The key of the cookie.
 * @return The value of the cookie.
 * @type String
 *
 * @name $.cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
jQuery.cookie = function (key, value, options) {
    
    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }
        
        value = String(value);
        
        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

jQuery(function($){
	$('#autofill').click(function(){
		$.cookie('setting_autofill', +$(this).is(':checked') , { expires: 3650 });
		window.location.reload(true);
	});
});

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
});
AUTOFILLER = (function($){
	return function(opts){
		
		var limit,
			page_load_ajax_running,
			query,
			source,
			items,
			last_loaded_page,
			prev_last_loaded_page,
			win,
			viewport_height,
			scroll_threshold,
			timer,
			pager,
			pager_source,
			pager_query,
			pager_refresh_interval,
			page_loader_interval,
			last_loaded_pager_page,
			pages_loaded_so_far = 0,
			visited_pages,
			re = {
				on_page_load:null,
				on_bottom:null,
				query: query,
				current_page:null
			};

		function refresh_last_loaded_page(root){
			if (last_loaded_page !== undefined) {
				prev_last_loaded_page = last_loaded_page;
			}
			last_loaded_page = root.find(opts.item).last().attr('data-page');
			return last_loaded_page;
		}


		function check_position(e) {
			function distance_to_bottom() {
				var scroll_height = $(document).height();
					scroll_top = win.scrollTop();

				return scroll_height - (viewport_height + scroll_top);
			}

			if (distance_to_bottom() <= scroll_threshold) {
				load_page();
			}
		}

		function decorate_with_days(page_start) {
			var item_bottom = page_start.offset().top + page_start.height(),
				next_item = page_start.next(),
				params = $.parseJSON(items.attr('data-query')),
				prev_item = page_start.prev();
			
			while (prev_item.is(opts.item) && (item_bottom - (prev_item.offset().top + prev_item.height()) === 0)) {
				prev_item.addClass('prev_page_row');
				prev_item = prev_item.prev();
			}
			while (next_item.is(opts.item) && (item_bottom - (next_item.offset().top + next_item.height()) === 0)) {
				next_item.addClass('next_page_row');
				next_item = next_item.next();
			}

			$.extend(params, {page: +prev_item.attr('data-page') - 1});

			$('<div class="page_banner"><div class="inner">'+
				'<span class="letter">p</span>'+
				'<span class="letter">a</span>'+
				'<span class="letter">g</span>'+
				'<span class="letter">e</span>'+
				'<a href="?'+$.param(params)+'" class="num">'+page_start.attr('data-page')+'</a>'+
				'</div></div>')
			.appendTo(prev_item.next());
		}

		function load_page() {
			var get_params = $.extend({}, query, {page: +last_loaded_page - 1});

			if ((prev_last_loaded_page === last_loaded_page && prev_last_loaded_page !== undefined && last_loaded_page !== undefined) || get_params.page === -1) {
				if (re.on_bottom) {
					on_bottom(last_loaded_page);
				}
				return true;
			}
			
			if (page_load_ajax_running) {
				return true;
			}

			if (re.on_page_load) {
				on_page_load(get_params);
			}

			page_load_ajax_running = true;
			$.ajax({
				url: source, 
				data: get_params,
				success: function(resp){
					items.append(resp);
					refresh_last_loaded_page(items);
					decorate_with_days(items.find('.page_start').last());
					page_load_ajax_running = false;
					pages_loaded_so_far += 1;
					if (pages_loaded_so_far === +opts.max_loaded_pages) {
						window.clearInterval(page_loader_interval);
						items.parent().append('<p class="center"><a class="btn primary more_content" href="?'+$.param($.extend({page: +last_loaded_page - 1}, query))+'">Load more pages</a></p>');
					}
					$('body').trigger('autofiller.page_loaded', [last_loaded_page]);
					check_position();
				},
				timeout:5000,
				error: function() {
					page_load_ajax_running = false;	
				}}
			);
		}

		function what_page_are_we_on(){
			var start_items = items.find(opts.item+'.page_start').add(items.find(opts.item).eq(0)),
				scroll_top = win.scrollTop() - (+start_items.eq(0).find('.border').height()),
				current_item,
				i, n_items = start_items.size(),
				distances = [];

			for (i = 0; i < n_items; ++i) {
				current_item = start_items.eq(i);
				distances.push({
					distance: Math.abs(scroll_top - +current_item.offset().top),
					page: current_item.attr('data-page')
				});
			}
			distances.sort(function(lhs, rhs){
				 return lhs.distance - rhs.distance;
			});
			re.current_page = distances[0].page;
			return distances[0].page;
		}

		function presist_visited_pages(pages){
			pages = pages.sort(function(a,b){ return (+b) - (+a)});
			$.cookie('visited_pages', pages.join('|'), {expires: 3650});
		}


		function refresh_pager(){
			var page = what_page_are_we_on();
			if (last_loaded_page === undefined) {
				last_loaded_page = page;
				return;
			}
			if (last_loaded_pager_page !== page) {
			$.get(pager_source+'?'+$.param($.extend({page: page}, pager_query)), function(resp){
					var pager_parent = pager.parent();
					pager_parent.append(resp);
					pager.remove();
					pager = pager_parent.find('.pager').eq(0);
					refresh_pager();
				});
				last_loaded_pager_page = page;
				if ($.inArray(page, visited_pages) === -1) {
					visited_pages.push(page);
				}
				presist_visited_pages(visited_pages);
			}
		}

		// init the filler
		win = $(window);
		pager = $(opts.pager);
		pager_source = pager.attr('data-source');
		items = $(opts.items);
		source = items.attr('data-source');
		query = $.parseJSON(items.attr('data-query'));
		pager_query = $.parseJSON(pager.attr('data-query'));
		limit = items.find(opts.item).size();
		viewport_height = win.height();
		scroll_threshold = opts.scroll_threshold;
		refresh_last_loaded_page(items);
		re.current_page = items.attr('data-page');
		visited_pages = $.cookie('visited_pages') ? $.cookie('visited_pages').split('|') : [];

		check_position();
		refresh_pager();

		page_loader_interval = window.setInterval(check_position, opts.check_interval || 1000);
		pager_refresh_interval = window.setInterval(refresh_pager, opts.check_interval || 1000);

		return re;
	};
})(jQuery);
jQuery(function(){
	if (DO_AUTOFILL) {
		window.autofiller = AUTOFILLER({
			items: '#images',
			item: '.image',
			pager: '.pager',
			scroll_threshold: 600,
			check_interval: 500,
			max_loaded_pages: 20
		});
	}
});
