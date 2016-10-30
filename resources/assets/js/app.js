jQuery.noConflict();
// Function so the toastr messages are getting flashed
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
		// Find the dom
		var $messageToastr = $("#messageToastr");
		// Check if their is a message
		if ($messageToastr.length > 0) {
			// Search for the settings
			var style = $messageToastr.data("style");
			var title = $messageToastr.find("span.title");
			var content = $messageToastr.find("span.content");
			
			// Show that toastr
			toastr[ style ](content, title);
		}
	});
	
})(jQuery);