jQuery(function($){
	$('#autofill').click(function(){
		$.cookie('setting_autofill', +$(this).is(':checked') , { expires: 3650 });
		window.location.reload(true);
	});
});
