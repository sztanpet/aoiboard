pageScroller = (function($){
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
			pager_refresh_interval,
			page_loader_interval,
			last_loaded_pager_page,
			pages_loaded_so_far = 0,
			re = {
				on_page_load:null,
				on_bottom:null
			};

		function refresh_last_loaded_page(root){
			if (last_loaded_page !== undefined) {
				prev_last_loaded_page = last_loaded_page;
			}
			last_loaded_page = root.find(opts.item+':last').attr('data-page');
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
			$.get(source, get_params, function(resp){
				items.append(resp);
				refresh_last_loaded_page(items);
				decorate_with_days(items.find('.page_start:last'));
				page_load_ajax_running = false;
				pages_loaded_so_far += 1;
				if (pages_loaded_so_far === +opts.max_loaded_pages) {
					window.clearInterval(page_loader_interval);
					items.parent().append('<a class="more_content" href="?'+$.param($.extend({page: +last_loaded_page - 1}, query))+'">Load more pages</a>');
				}
			}); 
		}

		function refresh_pager(){
			function what_page_are_we_on(){
				var start_items = items.find(opts.item+'.page_start').add(items.find(opts.item+':first')),
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
				return distances[0].page;
			}
			
			var page = what_page_are_we_on();
			if (last_loaded_page === undefined) {
				last_loaded_page = page;
				return;
			}
			if (last_loaded_pager_page !== page) {
				pager.attr('src', pager_source+'?'+$.param($.extend({page: page}, query)));
				last_loaded_pager_page = page;
			}
		}

		// init the scroller
		win = $(window);
		pager = $(opts.pager);
		pager_source = pager.attr('data-source');
		items = $(opts.items);
		source = items.attr('data-source');
		query = $.parseJSON(items.attr('data-query'));
		limit = items.find(opts.item).size();
		viewport_height = win.height();
		scroll_threshold = opts.scroll_threshold;
		refresh_last_loaded_page(items);

		page_loader_interval = window.setInterval(check_position, opts.check_interval || 1000);
		pager_refresh_interval = window.setInterval(refresh_pager, opts.check_interval || 1000);

		return re;
	};
})(jQuery);
jQuery(function(){
	var scroller = pageScroller({
		items: '#images',
		item: '.image',
		pager: '.pager:first',
		scroll_threshold:400,
		check_interval: 500,
		max_loaded_pages: 13
	});
});
