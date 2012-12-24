<?php

require '../config.php';

$page = new page();
$page->title(_('Welcome'));
$page->add_script('jquery-1.3.2.min.js');
$page->add_script('login.js');
$page->add_script('autofocus.js');

// attempt to autologin if we have the user's auth saved in a cookie
user::attempt_auto_login();

if (user::has_active()) {
    $page->smart_redirect();
}

if ($page->is_post()) {
    if (user::is_valid_login($_POST['username'], $_POST['password'])) {
        $user = user::login($_POST['username']);
        $user->set_tz_offset($_POST['tz_offset']);
        $user->set_client_dimensions($_POST['client_width'], $_POST['client_height']);
        if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
            cookie::set('login', user::active()->get_id());
        }
        $page->smart_redirect();
    } else {
        $page->err = 'Invalid credentials. Please try again.';
    }
}

$page->header();
$page->feedback();

?>

<h2>Welcome to Fotavia!</h2>

<div id="right_side_div">
  <div id="login_form_div">
    <p>Login here, or <a href="/signup">signup</a> if you don&#39;t have an account yet!</p>
    <div class="blackbox">
      <form action="<?=$page->get_form_action()?>" method="post">
        <input type="hidden" id="tz_offset" name="tz_offset" value="0" />
        <input type="hidden" id="client_width" name="client_width" value="0" />
        <input type="hidden" id="client_height" name="client_height" value="0" />
        <p>
          <label for="username">Username/Email:</label>
          <input id="username" name="username" type="text" size="60" value="<?=pts('username')?>" />
        </p>
        <p>
          <label for="password">Password:</label>
          <input id="password" name="password" type="password" size="60" />
        </p>
        <p><input id="remember" name="remember" type="checkbox" /> <label id="label_remember" for="remember">Remember me?</label></p>
        <p><a href="/forgotpass">Forgot your password?</a></p>
        <input type="submit" name="btn_submit" value="Login!" />
      </form>
    </div>
  </div>
</div>

<div id="welcome">
  <h3>Fotavia is a place for <strong><em>beautiful photography</em></strong>.</h3>

  <p>At Fotavia, you upload only your <strong><em>best</em></strong> photos!</p>
  
  <p> We limit you to <strong><em>one photo a day</strong></em>, which means
  you can only submit your best shots here! The flip-side is that you will
  only see your friends&#39; best works of creativity.</p>

  <p>Fotavia encourages you to go out everyday and take the best possible photo you
  can. Your best portrait, your best wildlife pic, your best sunrise/sunset,
  or your best sports pic. We want you to catch the world <strong><em>in
  action</em></strong>.</p>

  <p>If you could upload <strong><em>one</em></strong> photo that describes
  the <strong><em>highlight</em></strong> of your day, what would it be?</p>
 
  <a href="http://twitter.com/fotavia/">Follow us on twitter</a> to keep
  abreast of latest development.</a>

  <div id="recent_upload">
    <h3>Photos Uploaded Recently</h3>

    <? $num_photos = 10;
       $recent_photos = photo::latest_from(null, $num_photos);

    if (count($recent_photos) == 0) { ?>
      <em>No photos!</em>

    <?
    }

    foreach ($recent_photos as $i => $photo_id) {
        $p = photo::get($photo_id);
        if ($i != 0 && $i % 5 == 0) {
            echo '<br/>';
        } ?>
        <a href="<?=$p->get_page_url()?>?newp"><img src="<?=$p->url('square')?>" title="<?=$p->get_tooltip()?>" width="65" height="65" /></a>
    <?
    }
    if (count($recent_photos) == $num_photos) { ?>
      <br/><a href="/newphotos">more photos</a>
    <? } ?>
  </div>

</div>
<br class="clear" />
<?php $page->footer(); ?>
