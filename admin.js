jQuery(document).ready(function($){
		$('.dll-link-btn').on('click' , function(e){
		// e.preventDefault();
		var	wdpfa_product_link = $(this).attr('data-dll-dir'),
			wdpfa_ajax_img = $(this).siblings('.ajax-loader');
			wdpfa_ajax_img.show();
			setTimeout(function(){wdpfa_ajax_img.hide();},1000);

			
	});
});