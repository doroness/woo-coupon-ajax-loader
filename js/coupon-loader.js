/* -- */
jQuery( document ).ready( function( $ ){

    $('.coupon-loader-btn').click(function (e) { 
        e.preventDefault();
        var theBtn         =    $(this),
            coupon_code    =    $(theBtn).attr('data-coupon'),
            redirectToCart =    $(theBtn).attr('data-redirect');
        $.ajax({
            type: "POST",   
            url: coupon_loader_data.ajax_url, 
            data: {
                coupon_code : coupon_code,
                coupon_key  : coupon_loader_data.security,
                action      : 'load_coupon'
            },
            success:function(data) {                
                $(theBtn).siblings('.coupon-loader-message').html(data.data[0]).css({
                    visibility: 'visible',
                });
                if (redirectToCart == "yes")
                    window.location.href = (data.data[1]);
            },
            error: function(errorThrown){
                console.log(errorThrown); // error
            }
        });
    });
    $('.coupon-loader-message').click(function (e) { 
        e.preventDefault();
        $(this).css({
            visibility: 'hidden'
        });
    });
});