jQuery(function($){
	$('.index_target').click(function(e){
		e.preventDefault();
		parent.location = (''+this.href).replace('pager.php', '');
	});
});
