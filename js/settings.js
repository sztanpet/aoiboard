jQuery(function($){
	$('#autofill').click(function(){
		$.cookie('setting_autofill', +$(this).attr('checked'), { expires: 3650 });
		window.location.reload(true);
	});
});
