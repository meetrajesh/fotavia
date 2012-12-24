fotavia.facebook = {
    request_fb_publish_stream_priv: function(callback) {
        FB.init('bdd8bbc52c4392edfb9d9ac75039d4ce', '/xd_receiver.html');
        FB.ensureInit(function() {
            FB.Connect.requireSession(function() {
                FB.Facebook.apiClient.users_hasAppPermission('publish_stream', function(has) {
                     if (!has) {
                         FB.Connect.showPermissionDialog('publish_stream', function(granted) {
                             fotavia.facebook.save_fb_id(callback);
                         });
                     } else {
                         fotavia.facebook.save_fb_id(callback);
                     }
                });
            });
        });
    },
    save_fb_id: function(callback) {
        FB.Facebook.apiClient.users_getLoggedInUser(function(fbid) {
            jQuery.post('/api/fbconnect.php', {'fbid' : fbid});
            if (callback) {
                callback();
            }
        });
    }
}
