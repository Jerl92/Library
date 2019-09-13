function rs_save_for_user($) {
    
    $.fn.ready();
	'use strict';

	$("#scan-userid").keypress(function(event){
		if (event.keyCode == 13) {
		event.preventDefault();

		var $this = $(this);
		nonce = $this.data('nonce');
				object_id = $("#scan-userid").val();

				$.ajax({
				type: 'post',
				url: user_ajax_url,
				dataType: 'JSON',
				data: {
					'object_id': object_id,
					'action': 'user_info'
				},
				success: function(data) {
					console.log(data);
					$(".user-info").html(data);
					$("#scan-book-isbn").focus();
					$(".rent_wrapper").html("");	
				},
				error: function(error) {
					console.log(error);
				}
			});
		}
	});
	
	$("#scan-book-isbn").keypress(function(event){
		if (event.keyCode == 13) {
			event.preventDefault();
	
			var $this = $(this);
			nonce = $this.data('nonce');
					object_id = $("#scan-userid").val();
	
					$.ajax({
					type: 'post',
					url: user_ajax_url,
					dataType: 'JSON',
					data: {
						'object_id': object_id,
						'action': 'user_info'
					},
					success: function(data) {
						if (data[0] == -1){
							$("#scan-userid").val(data[1]);
							get_user_info(data[1]);
						} else {
							console.log(data);
							$(".user-info").html(data);
							$("#scan-book-isbn").focus();
						}
					},
					error: function(error) {
						console.log(error);
					}
				});
			}
	});
    
}

function get_user_info(userid) {
	
	var $this = jQuery(this);
	nonce = $this.data('nonce');
			object_id = userid;

			jQuery.ajax({
			type: 'post',
			url: user_ajax_url,
			dataType: 'JSON',
			data: {
				'object_id': object_id,
				'action': 'user_info'
			},
			success: function(data) {
				console.log(data);
				jQuery(".user-info").html(data);
				jQuery("#scan-book-isbn").focus();
				jQuery(".rent_wrapper").html("");	
			},
			error: function(error) {
				console.log(error);
			}
		});
}

jQuery(document).ready(function($) {
    rs_save_for_user($);
});