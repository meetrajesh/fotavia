var preload = new Array();

fotavia = {};
fotavia.view = {

    // has the exif for this page been loaded yet?
    exif_loaded: false,

    // have the comments for this page been loaded yet?
    comments_loaded: false,

    preload_images: function(data) {
        // preload previous images
        $.each(data.prev,
            function(i, item) {
                var img = new Image();
                img.src = item;
                preload.push(img);
            }
        );
    
        // preload next images
        $.each(data.next,
            function(i, item) {
                var img = new Image();
                img.src = item;
                preload.push(img);
            }
        );
    },

    init: function() {

        // unhide the "view comments" link
        $('#toggle_comments').removeClass('hide');

        // hit Esc key to close exif popup
        $(document).keypress(
            function(e) {
                if (e.which == 0) {
                    $('#exif').slideUp(400);
                } 
            }
        );

        // open/close exif popup on click
        $('#exif_toggle').click(function() {
            $('#exif').slideToggle(400);
            if (!fotavia.view.exif_loaded) {
                $('#exif').load('/api/getexif.php', { pid : $('#photo_id').val() }, function(data) {
                    fotavia.view.exif_loaded = true;
                    $('#exif').removeClass('loading');
                    fotavia.view.set_exif_close_event_handler();
                });
            }
            return false;
        });

        // shortcuts keys for next/prev
        fotavia.view.register_nav_shortcut_keys();

        // open comments on click
        $('#show_comments').click(function() {
            fotavia.view.load_comments();
            return false;
        });

        // check if #comments or #comment_* hashtag is set, if yes, then auto load comments
        if (fotavia.view.show_comments()) {
            fotavia.view.load_comments();
        }

        var params = {'pid':$('#photo_id').val(), 'num':1};
        if (document.location.search.match(/newp/)) {
            params['newp'] = 1;
        }
        $.getJSON('/api/thumbs_around.php', params, fotavia.view.preload_images);
    },

    register_nav_shortcut_keys: function() {
        // shortcut keys to move to prev and next photos
        $(document).keydown(
            function(e) {
                if (e.which == 17 || e.which == 18) {
                    return;
                }
                if (e.which == 37) {
                    var link = $('#prevlink')[0];
                    if(link) {
                        document.location = link.href;
                    }
                } else if(e.which == 39) {
                    var link = $('#nextlink')[0];
                    if (link) {
                        document.location = link.href;
                    }
                }
            }
        );
    },

    unregister_nav_shortcut_keys: function() {
        $('#comment_body').keydown(function(e) {
            e.stopPropagation();
        });
    },

    load_comments: function() {
        $('#comments').slideToggle(1);
        $('#show_comments').attr('disabled', true);
        $('#comments').load('/api/getcomments.php', { pid : $('#photo_id').val() }, function(data) {
            fotavia.view.comments_loaded = true;
            $('#comments').toggleClass('loading');
            $('#toggle_comments').toggleClass('hide');
            if (fotavia.view.show_comments()) {
                document.location = document.location;
            }
            // del comment link event handlers
            $('span.comment_delete a').each(function(i, a) {
                $(a).click(function() {
                    return confirm('Are you sure you want to delete this comment? The deletion cannot be undone!');
                });
            });
            // disable right and left keyboard shortcuts when textarea is in focus
            $('#comment_body').focus(function() {
                fotavia.view.unregister_nav_shortcut_keys();
            });
        });
    },

    // should comments be loaded by default?
    show_comments: function() {
        return document.location.hash.match(/#comment/);
    },

    set_exif_close_event_handler: function() {
        // close exif popup on clicking 'hide' link
        $('#exif_hide').click(function() {
            fotavia.view.close_exif_popup();
            return false;
        });
        $('#content').click(fotavia.view.close_exif_popup);
        $('#footer').click(fotavia.view.close_exif_popup);
    },

    close_exif_popup: function() {
        $('#exif').slideUp(400);
    }

}

$(document).ready(fotavia.view.init);
