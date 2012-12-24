// Tab objects, contains information to render tabs and pagination
var tabs = new Object();

tabs['friend'] = {
    'page': 0,
    'display': "Your Friends' Recent Photos",
    'none_msg': 'No photos have been uploaded by your friends!'
};

tabs['other'] = {
    'page': 0,
    'display': "Other Recent Photos",
    'none_msg': 'No photos have been uploaded by people other than you and your friends'
};

// constants
var NUM_PER_ROW = 5;
var NUM_ROWS = 2;

// Set up the links
function render_links(selected, has_prev, has_next) {
    var heading = $('#recent_photos_heading').attr('class', 'tab_head');

    heading.html('');
    
    for(tab_type in tabs) {
        if(tab_type != selected) {
            var tab_link = $('<a href="#" />').html(tabs[tab_type]['display']);
            tab_link.click(change_tab(tab_type, tabs[tab_type]['page']));
            tab_link.appendTo(heading);
        } else {
            var selected_heading = $('<span />').html(tabs[selected]['display']);
            selected_heading.appendTo(heading);
        }
    }

    var pagination = $('<span class="tab_pagination" />');

    // set up pagination links
    if(has_prev) {
        var prev_link = $('<a href="#" />').html('view more recent');
        prev_link.click(change_tab(selected, tabs[selected]['page'] - 1));
        prev_link.appendTo(pagination);
    }

    if(has_next) {
        var prev_link = $('<a href="#" />').html('view older');
        prev_link.click(change_tab(selected, tabs[selected]['page'] + 1));
        prev_link.appendTo(pagination);
    }

    if(pagination.html() != '') {
        pagination.prependTo(heading);
    }

}

// Returns a listener to change type of photos being displayed
function change_tab(type, page) {
    return function(event) {
        event.preventDefault();
        var num = NUM_PER_ROW * NUM_ROWS;
        var offset = num * page;
        $.getJSON('/api/recent_photos.php', {'type':type,'num':num,'offset':offset}, update_photos(type, page));
    };
}

// Returns a callback function to update photos of specified type and page
function update_photos(type, page) {
    return function(data) {
        var photo_container = $('#recent_photos');
        photo_container.empty();

        tabs[type]['page'] = page;
        render_links(type, data.has_prev, data.has_next);

        $.each(data.photos,
            function(i, item) {
                if(i % NUM_PER_ROW == 0 && i != 0) {
                    $('<br/><br/>').appendTo(photo_container);
                }
                var img_link = $('<a/>').attr('href', item.page_url).appendTo(photo_container);
                $('<img class="silver_frame" />').attr('src', item.img_url).attr('alt', item.alt).attr('title', item.title).appendTo(img_link);
            }
        );

        if(photo_container.html() == '') {
            var none_message = $('<em/>').html(tabs[type]['none_msg']);
            photo_container.html(none_message);
        }
    };
}

// Polling interval in seconds
var polling_interval = 2;

var progress_poller = null;

// Attach upload listener to init progress bar
function attach_upload_listener() {
    $('#photo_add').submit(init_upload());
}

// Returns a listener to initialize progress bar
function init_upload() {
    return function(event) {
        // Hide form fields button
        $('#photo_add_submit').attr('disabled', true).hide();

        // Create upload bar
        var upload_bar = $('<div id="upload_bar"/>');
        var upload_progress = $('<div id="upload_progress"/>');
        upload_bar.html(upload_progress);

        // Put upload bar in DOM
        var add_form = $('#photo_add');
        upload_bar.insertAfter(add_form);

        // Put upload text next to bar
        var upload_text = $('<span/>').attr('class', 'upload_text');
        upload_text.append('Upload is <span id="upload_percentage">0</span>% complete.');
        upload_text.insertAfter(upload_bar);

        var key = $('#progress_key').val();

        // Set intervals to poll for upload progress
        progress_poller = setInterval(
            function() {
                $.getJSON('/api/upload_status.php', {'key':key}, update_progress());
            }, polling_interval * 1000
        );
    };
}

function update_progress() {
    return function(data) {
        $('#upload_progress').css('width', data + '%');
        $('#upload_percentage').html(Math.floor(data));

        if(data >= 100) {
            clearInterval(progress_poller);
        }
    };
}

$('#recent_photos').ready(
    function() {
        render_links('friend', false, $('#photo_has_next').val() == 'true');
        attach_upload_listener();
    }
);
