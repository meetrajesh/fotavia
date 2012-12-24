<?php

// like file_get_contents(), only this processes the php inside
function file_include_contents($file) {
    ob_start();
    include($file);
    return ob_get_clean();
}

// alias to htmlspecialchars()
function hsc($str) {
    return htmlspecialchars($str);
}

// meant to be used in the "value" field on input textboxes and inside textareas
function pts($key, $default='') {
    echo isset($_POST[$key]) ? hsc(trim($_POST[$key])) : hsc($default);
}

// short for var_dump()
function v($key) {
    foreach (func_get_args() as $key) {
        var_dump($key);
    }
}

function error($err) {
    if (IS_DEV) {
        trigger_error($err, E_USER_ERROR);
    } else {
        trigger_error($err, E_USER_WARNING);
        user::has_active() ? redirect('/dash') : redirect('/error');
    }
    exit;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function redirect_ref($default) {
    $ref = !empty($_SERVER['HTTP_REFERER']) && !in_str($_SERVER['HTTP_REFERER'], '/search/users') && $_SERVER['HTTP_REFERER'] != $_SERVER['REQUEST_URI'] ? $_SERVER['HTTP_REFERER'] : $default;
    redirect($ref);
}

// short for gettext()
if (!function_exists('_')) {
    function _($str) {
        return $str;
    }
}

// plural version of _()
if (!function_exists('ngettext')) {
    function ngettext($str1, $str2, $n) {
        return $n == 1 ? $str1 : $str2;
    }
}

function dropdown($id, $options, $default=null, $include_header=false) {
    $o = '';    
    $o .= '<select id="' . $id . '" name="' . $id . '">';
    if (empty($default) && $include_header) {
        $o .= '<option value=""> -- SELECT -- </option>';
    }
    foreach($options as $key => $val) {
        $o .= '<option value="' . hsc($key) . '"' . ($default == $key ? ' selected="selected"' : '') . '>' . hsc($val) . '</option>';
    }
    $o .= '</select>';
    return $o;
}

// does $needle exist in $haystack? (case-sensitive)
function in_str($haystack, $needle) {
    return false !== strpos($haystack, $needle);
}

// shorthand for sprintf/vsprintf
function spf($format, $args=array()) {
    $args = func_get_args();
    $format = array_shift($args);
    if (isset($args[0]) && is_array($args[0])) {
        $args = $args[0];
    }
    return vsprintf($format, $args);
}

// is this a valid system id?
function is_id($id) {
    $id = (string)$id;
    return ctype_digit($id) && $id > 0;
}

// takes everything after the domain name in the URL and returns each
// slash-separated param as an array
function get_params() {
    $gets = explode('/', str_replace(BASE_URL, '', $_SERVER['REQUEST_URI']));
    array_shift($gets);
    $gets = array_map('trim', $gets);
    return $gets;
}

// replace all instances of / with / or \ depending on the OS
function gdir($dir) {
    return str_replace('/', DIR_SEP, $dir);
}

// prints the string odd/even depending on the parity of $i
function oddeven($i) {
    return $i % 2 == 0 ? 'even' : 'odd';
}

function str_encrypt($key, $text) {
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    $out = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB, $iv);
    $out = base64_encode($out);
    $out = rtrim($out, '=');
    return str_replace(array('+', '/'), array('-', '_'), $out);
}

function str_decrypt($key, $text) {
    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
    // use trim to remove trailing spaces
    $text = str_replace(array('-', '_'), array('+', '/'), $text);
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($text), MCRYPT_MODE_ECB, $iv));
}

function std_date($stamp=null) {
    return is_null($stamp) ? date('D M j Y') : date('D M j Y', $stamp);
}

function std_datetime($stamp=null) {
    return (is_null($stamp) ? date('D M j Y g:i A') : date('D M j Y g:i A', $stamp)) . ' UTC';
}

function fuzzydate($date) {
    $delta = time() - $date;
    if ($delta < 60) {
        return $delta == 1 ? _('a second ago') : spf(_('%d seconds ago'), $delta);
    }
    if ($delta < 120) {
        return _('a minute ago');
    }
    if ($delta < 2700) { // 45 * 60
        return spf(_('%d minutes ago'), ceil($delta / 60));
    }
    if ($delta < 5400) { // 90 * 60
        return _('an hour ago');
    }
    if ($delta < 86400) { // 24 * 60 * 60
        return spf(_('%d hours ago'), ceil($delta / 3600));
    }
    if ($delta < 172800) { // 48 * 60 * 60
        return _('yesterday');
    }
    if ($delta < 2592000) { // 30 * 24 * 60 * 60
        return spf(_('%d days ago'), ceil($delta / 86400));
    }
    if ($delta < 31104000) { // 12 * 30 * 24 * 60 * 60
        $months = $delta / 86400 / 30;
        return $months <= 1 ? _('one month ago') : spf(_('%d months ago'), ceil($months));
    }
    $years = $delta / 86400 / 365;
    return $years <= 1 ? _('one year ago') : spf(_('%d years ago'), ceil($years));
}

function is_sitedown_page() {
    return in_str($_SERVER['REQUEST_URI'], 'sitedown');
}

// converts a string like "1-4,5,9-13,4-6" into array(1,2,3,4,5,6,9,10,11,12,13)
function get_range($str) {
    $pids = explode(',', $str);
    $all_pids = array();
    foreach ($pids as $pid) {
        if (in_str($pid, '-')) {
            list($a, $b) = explode('-', $pid);
            $all_pids = array_merge($all_pids, range(trim($a), trim($b)));
        } else {
            $all_pids[] = (int)trim($pid);
        }
    }
    $pids = array_unique($all_pids);
    rsort($pids);
    return $pids;
}

function summarize_text($str, $num_words = 50) {

    // no need for hsc since we call strip_tags()
    $str = strip_tags($str);
    $str = str_replace(array("\r\n", "\n"), ' ', $str);

    $positions = array_keys(str_word_count($str, 2));
    $actual_num_words = count($positions);

    if ($actual_num_words > $num_words) {
        $str = substr($str, 0, $positions[$num_words] - 1);
        // chop off trailing spaces and periods
        $str = preg_replace('/[ \.]+$/', '', $str);
        $str .= '...';
    }

    // no need for hsc since we call strip_tags() on the input
    return $str;

}

function truncate_str($str, $len) {
    $truncated = false;
    if (strlen($str) > $len) {
        $str = substr($str, 0, $len);
        $truncated = true;
    }
    return $str . ($truncated ? '..' : '');
}

function is_page($page) {
    return isset($_SERVER['REQUEST_URI']) && rtrim($_SERVER['REQUEST_URI'], '/') == rtrim($page, '/');
}

function exif_popup_header($msg) {
    echo '<h6>' . hsc($msg) . '</h6>';
    echo '<a href="#" id="exif_hide">hide</a> (Esc) &nbsp;<br/><br/>';
}

function format_user_text($text) {
    $from = array("\r\n", "\n\n");
    $to   = array("\n", "</p><p>");

    if (!empty($text)) {
        // insert new lines
        $text = '<p>' . str_replace($from, $to, $text) . '</p>';
        // strip html tags
        $text = strip_tags($text, '<a><p><b><i><u><strong><br/><br><hr><hr/><em><center><abbr><pre><strike><blockquote>');
        // sanitize text
        $text = preg_replace('/<\s*script\s*>.*script\s*>/is', '', $text);
        // automatically hyperlinkify/anchorize links in photo text 
        $text = preg_replace('/((ht|f)tps?:\/\/[,;:%#{}~\[\]\!&\/?=\$\|\w\.+-]+)/i', '<a target="_blank" href="$1">$1</a>', $text);
        // email links
        $text = preg_replace('/(\W{1}|\s{1}|\A)((\w)+\:)?([\w\.\_\-]+)(@)([\w\.]+)(\W{1}|\s{1}|\Z)/i', '$1<a href="mailto:$4$5$6">$4$5$6</a>$7', $text);
        // replace @william with <a href="http://www.fotavia.com/william">William Chen</a>
        $text = preg_replace_callback('/@(\w+)/', array('user', 'profile_link_callback'), $text);
    }
    
    return $text;
}

function array_delete($array, $entry_to_delete) {
    $output_array = array();
    foreach((array)$array as $val) {
        if ($val != $entry_to_delete) {
            $output_array[] = $val;
        }
    }
    return $output_array;
}

function starts_with($haystack, $needle) {
    return strpos($haystack, $needle) === 0;
}

function ends_with($haystack, $needle) {
    return substr($haystack, -strlen($needle)) == $needle;
}

function erpt() {
    ini_set('display_errors', true);
}

function array_last($array) {
    $tmp = array_slice($array, -1);
    return count($tmp) == 0 ? null : $tmp[0];
}

function is_admin() {
    return user::has_active() && user::active()->get_id() == ADMIN_UID;
}

?>
