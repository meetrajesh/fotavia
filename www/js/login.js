$(document).ready(function() {
    var cur = new Date();
    var tz_offset = -1 * cur.getTimezoneOffset() / 60;
    $('#tz_offset').val(tz_offset);
    $('#client_width').val(screen.width);
    $('#client_height').val(screen.height);
});
