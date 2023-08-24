function bettertools_toggle_classic_editor(event) {
    event.preventDefault();

    var data = {
        action: 'bettertools_toggle_classic_editor',
        classic_editor_action: bettertools_ajax.classic_editor_action,
        security: bettertools_ajax.classic_editor_nonce
    };

    jQuery.post(bettertools_ajax.ajax_url, data, function(response) {
        if (response.success) {
            location.reload();
        } else {
            console.log(response.data);
        }
    });
}
