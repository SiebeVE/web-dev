jQuery.noConflict();
(function ( $ ) {
	toastr.options = {
		"closeButton": true,
		"debug": false,
		"newestOnTop": false,
		"progressBar": false,
		"positionClass": "toast-top-right",
		"preventDuplicates": false,
		"onclick": null,
		"showDuration": "300",
		"hideDuration": "1000",
		"timeOut": "3000",
		"extendedTimeOut": "1000",
		"showEasing": "swing",
		"hideEasing": "linear",
		"showMethod": "fadeIn",
		"hideMethod": "fadeOut"
	};
	
	$(function () {
		var $messageToastr = $("#messageToastr");
		if ($messageToastr.length > 0) {
			var style = $messageToastr.data("style");
			var title = $messageToastr.find("span.title");
			var content = $messageToastr.find("span.content");
			
			toastr[ style ](content, title);
			// $messageToastr.remove();
		}
	});
	
})(jQuery);