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
			$.ajax({
				url: source, 
				data: get_params,
				success: function(resp){
					items.append(resp);
					refresh_last_loaded_page(items);
					decorate_with_days(items.find('.page_start:last'));
					page_load_ajax_running = false;
					pages_loaded_so_far += 1;
					if (pages_loaded_so_far === +opts.max_loaded_pages) {
						window.clearInterval(page_loader_interval);
						items.parent().append('<a class="more_content" href="?'+$.param($.extend({page: +last_loaded_page - 1}, query))+'">Load more pages</a>');
					}
					$('body').trigger('autofiller.page_loaded', [last_loaded_page]);
				},
				timeout:5000,
				error: function() {
					page_load_ajax_running = false;	
				}}
			);
		}

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
				pager.load(pager_source+'?'+$.param($.extend({page: page}, pager_query)));
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

		page_loader_interval = window.setInterval(check_position, opts.check_interval || 1000);
		pager_refresh_interval = window.setInterval(refresh_pager, opts.check_interval || 1000);

		return re;
	};
})(jQuery);
jQuery(function(){
	window.autofiller = AUTOFILLER({
		items: '#images',
		item: '.image',
		pager: '.pager:first',
		scroll_threshold:400,
		check_interval: 500,
		max_loaded_pages: 13
	});
});
