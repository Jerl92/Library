	
function rs_save_for_later($) {
    
    $.fn.ready();
	'use strict';

	/**
	 * Remove All from Saved for Later
	 */
	$("#scan-book-isbn").keypress(function(event){
		if (event.keyCode === 13) {
		event.preventDefault();

		var $this = $(this);
		nonce = $this.data('nonce');
				object_id = {userid:$("#scan-userid").val(), isbn:$("#scan-book-isbn").val()}

			$.ajax({
				type: 'post',
				url: scan_ajax_url,
				dataType: 'JSON',
				data: {
					'object_id': object_id,
					'action': 'scan_book'
				},
				success: function(data) {
					if (data[0] == -1){
						$("#scan-userid").val($("#scan-book-isbn").val());
						$("#scan-book-isbn").val('');
						get_user_info(data[1]);
					} else {
						console.log(data);
						$(".book-name").html(data);
						rs_save_for_btn($);
						get_rent($);
						$("#scan-book-isbn").val('');
					}
				},
				error: function(error) {
					console.log(error);
				}
			});
		}
	});
}

	jQuery(document).ready(function($) {
		rs_save_for_later($);
	});