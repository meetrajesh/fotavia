fotavia = {};
fotavia.month = {
    init: function() {
        // shortcut keys to move to prev and next photos
        $(document).keydown(
            function(e) {
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
    }
}

$(document).ready(fotavia.month.init);
