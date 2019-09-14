	
function rs_save_for_btn($) {
    
    $.fn.ready();
	'use strict';

	/**
	 * Remove All from Saved for Later
	 */
	$("#rent_btn").click(function(event) {
		event.preventDefault();

		var $this = $(this);
		nonce = $this.data('nonce');
				object_id = {userid:$(this).attr('data-userid'), bookid:$(this).attr('data-bookid')};

        $.ajax({
            type: 'post',
            url: rent_ajax_url,
            dataType: 'JSON',
            data: {
                'object_id': object_id,
                'action': 'rent_book'
            },
            success: function(data) {
                    console.log(data);
                    $("#rent_btn").val('');
                    get_rent($);

            },
            error: function(error) {
                console.log(error);
            }
        });
	});
}

	jQuery(document).ready(function($) {
		rs_save_for_btn($);
	});