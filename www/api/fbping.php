<?php

require '../config.php';

// verify the authenticity of this request
ksort($_POST);
$sig = '';
foreach ($_POST as $key => $val) {
    if (substr($key, 0, 7) == 'fb_sig_') {
        $sig .= substr($key, 7) . '=' . $val;
    }
}
$valid_request = (ms5($sig . FACEBOOK_API_SECRET) == $_POST['fb_sig']);

// @todo: log the request in the db along with $valid_request

if (isset($_POST['fb_sig_uninstall']) && $_POST['fb_sig_uninstall'] == 1) {
    // @todo: uninstall the app


} elseif (isset($_POST['fb_sig_authorize']) && $_POST['fb_sig_authorize'] == 1 && 
          isset($_POST['fb_sig_ext_perms']) && $_POST['fb_sig_ext_perms'] == 'auto_publish_recent_activity') {

    // @todo: install the app here

}

?>
