(function ($) {

    var $action_button = $('#rop_conection_check');
    var $server_response = $('#server_responded');
    var $wordpress_response = $('#website_responded');

    if ($action_button.length) {
        $('#rop-debug-table').on(
            'click tap',
            '#rop_conection_check',
            function () {
                $server_response.html('N/A').css('color', 'black');
                $wordpress_response.html('N/A').css('color', 'black');
                var initial_label = $action_button.val();
                $action_button.attr('disabled', 'disabled').val('Checking connection...');
                $.ajax(
                    {
                        type: "GET",
                        url: rop_debug.remote_url,
                        data: {'secret_temp_key': rop_debug.nonce, 'respond_to': rop_debug.local_url},
                        dataType: "json", // xml, html, script, json, jsonp, text
                        success: function (data) {
                            if (typeof data !== 'undefined') {
                                if (true === data.success) {
                                    $server_response.html('&#10004; ' + data.message).css('color', 'darkgreen');
                                }else{
                                    $server_response.html('&#10006; ' + 'Could not reach ROP Cron System').css('color', 'darkgreen');
                                }

                                if (true === data.remote_success) {
                                    $wordpress_response.html('&#10004; ' + data.remote_message).css('color', 'darkgreen');
                                } else {
                                    $wordpress_response.html('&#10006; ' + data.remote_message).css('color', 'darkred');
                                }
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            $server_response.html('&#10006; ' + 'Could not reach ROP Cron System').css('color', 'darkred');
                            $wordpress_response.html('&#10006; ' + 'Could not reach ROP Cron System').css('color', 'darkred');
                        },
                        // called when the request finishes (after success and error callbacks are executed)
                        complete: function (jqXHR, textStatus) {
                            $action_button.removeAttr('disabled').val(initial_label);
                        }
                    }
                );

            }
        );
    }

})(jQuery);