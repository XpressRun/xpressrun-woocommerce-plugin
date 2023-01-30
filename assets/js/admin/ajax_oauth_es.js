jQuery(document).ready(function ($) {
    $('#woocommerce_xpressrun_es_oauth_ajax').on('click', function (e) {
        e.preventDefault();
        var data = {
            action: "xpr_oauth_es"
        };

        $.ajax({
            url: ajaxurl,
            type: 'post',
            data: data,
            success: function (response) {
                if (response.error) {
                    console.log('error');
                } else {
                    //window.open(response.redirect_url, "_self");
                    //window.location.href = url; //"https://app.developer.xpressrun.com/";
                    location.reload(true);
                }
            }
        });
    });  
});
