fotavia = {};
fotavia.settings = {

    // called on page ready
    init: function() {
        // checking/unchecking the checkall box should be reflected in each of the optout checkboxes
        $('#checkall').click(function() {
            fotavia.settings.set_check_state(true);
            return false;
        });
        $('#checknone').click(function() {
            fotavia.settings.set_check_state(false);
            return false;
        });
        // event handler for fbpriv checkbox
        $('#fbconnect_priv').change(function() {
            if ($('#fbconnect_priv').attr('checked')) {
                fotavia.facebook.request_fb_publish_stream_priv();
            }
        });
        // event handler for twpriv checkbox
        $('#twconnect_priv').change(function() {
            if ($('#twconnect_priv').attr('checked')) {
                jQuery.get('/api/tw_auth_url.php', null, function(auth_url) {
                    window.location = auth_url;
                });
            }
        });
    },

    set_check_state: function(new_state) {
        $('form#optout :checkbox').each(function(i, box) {
            $(box).attr('checked', new_state);
        });
    }

}

$(document).ready(fotavia.settings.init);
