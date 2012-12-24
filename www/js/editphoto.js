fotavia = {};
fotavia.editphoto = {
    // called on page ready
    init: function() {
        // event handler for fbpriv link
        $('#fbconnect_priv').click(function() {
            fotavia.facebook.request_fb_publish_stream_priv(fotavia.editphoto.hide_fb_publish_link);
            return false;
        });
    },
    hide_fb_publish_link: function() {
        $('#fbconnect_priv').addClass('hide');
    }
}

$(document).ready(fotavia.editphoto.init);
