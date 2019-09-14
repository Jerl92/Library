function get_rent($) {
    
    var $this = $(this);
    nonce = $this.data('nonce');
            object_id = $("#rent_btn").attr('data-bookid');

    $.ajax({
        type: 'post',
        url: get_rent_ajax_url,
        dataType: 'JSON',
        data: {
            'object_id': object_id,
            'action': 'get_rent_book'
        },
        success: function(data) {
            console.log(object_id );
            $(".rent_wrapper").html("");
            $(".rent_wrapper").html(data);
            rs_save_for($);
        },
        error: function(error) {
            console.log(error);
        }
    });
}

jQuery(document).ready(function($) {
    get_rent($)
});