	
function rs_save_for($) {
    
    $.fn.ready();
    'use strict';
    
    $('.return_book').click(function(event) {
		event.preventDefault();

		var $this = $(this);
		nonce = $this.data('nonce');
                object_id = { rent_id:$(this).attr('data'), book_id:$(this).attr('data-bookid'), user_id:$(this).attr('data-userid')}
                

        $.ajax({
            type: 'post',
            url: return_ajax_url,
            dataType: 'JSON',
            data: {
                'object_id': object_id,
                'action': 'return_book'
            },
            success: function(data) {
                console.log(data);
                $(".rent_wrapper").html("");
                $(".rent_wrapper").html(data);
                get_rent($);
                rs_save_for($);
            },
            error: function(error) {
                console.log(error);
            }
        });
	});
}

jQuery(document).ready(function($) {
    rs_save_for($);
});