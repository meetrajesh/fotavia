<?php

class user {

    private $id;
    private $name;
    private $username;
    private $email;
    private $bio;
    private $website;
    private $location;
    private $secret;
    private $client_width;
    private $client_height;
    private $headshot_ext;
    private $tz_offset;
    private $signup_stamp;
    private $status;
    private $prefs;

    // status enum
    const PENDING  = 0;
    const APPROVED = 1;
    const REJECTED = 2;

    // cached vars
    private $can_upload_now;

    // mapping between usernames and user_ids
    private static $username_id_map = array();

    // static array of singleton objs
    private static $instances = array();

    private function __construct($id) {
        $this->id = (int)$id;
        $row = db::fetch_query('SELECT name, username, email, bio, website, location, secret, tz_offset, client_width, client_height, headshot_ext,
                                    UNIX_TIMESTAMP(signup_stamp), status
                                FROM users WHERE user_id=%d', $this->id);
        if (is_null($row)) {
            error('Invalid user_id ' . $this->id . ' specified.');
        }
        list($this->name, $this->username, $this->email, $this->bio, $this->website, $this->location, $this->secret, $this->tz_offset, $this->client_width, 
             $this->client_height, $this->headshot_ext, $this->signup_stamp, $status) = array_values($row);

        $this->status = (int)$this->status;
    }

    public static function get_from_username($username) {
        if (!array_key_exists($username, self::$username_id_map)) {
            self::$username_id_map[$username] = db::result_query('SELECT user_id FROM users WHERE LOWER(username) = LOWER("%s")', $username);
        }
        $uid = self::$username_id_map[$username];
        return !is_null($uid) ? self::get($uid) : null;
    }

    public static function get_from_email($email) {
        $uid = db::result_query('SELECT user_id FROM users WHERE LOWER(email) = LOWER("%s")', $email);
        return !is_null($uid) ? self::get($uid) : null;
    }

    public static function get($id) {
        if (!is_id($id)) {
            error('invalid id specified in photo::get(): ' . $id);
        }
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new user($id);
        }
        return self::$instances[$id];
    }

    public static function active() {
        return self::has_active() ? self::get($_SESSION['uid']) : null;
    }

    public function can_upload_photo() {
        if (!isset($this->can_upload_now)) {
            $row = db::fetch_query('SELECT stamp, user_date FROM photos WHERE owner_id=%d ORDER BY stamp DESC LIMIT 1', $this->get_id());
            // a minimum of 4 hours between photo uploads
            $this->can_upload_now = is_null($row) || (($row['user_date'] != $this->get_local_date()) && (time() > $row['stamp'] + MIN_SECS_BETWEEN_UPLOADS));
        }
        return $this->can_upload_now;
    }

    public function get_local_date() {
        return date('Y-m-d', time() + $this->tz_offset * 60 * 60);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_username() {
        return $this->username;
    }

    public function get_email() {
        return $this->email;
    }

    public function set_email($newemail) {
        if (self::valid_email($newemail)) {
            db::query('UPDATE users SET email="%s" WHERE user_id=%d', $newemail, $this->id);
            $this->email = $newemail;
        }
    }

    public function get_bio() {
        return $this->bio;
    }

    public function get_website() {
        return $this->website;
    }

    public function get_location() {
        return $this->location;
    }

    public function save_profile_details($new_name, $new_bio, $new_location, $new_website) {
        trim($new_website);
        if (!empty($new_website) && !preg_match('~^http://~', $new_website)) {
            $new_website = 'http://' . $new_website;
        }
        db::query('UPDATE users SET name="%s", bio="%s", location="%s", website="%s" WHERE user_id=%d', $new_name, $new_bio, $new_location, $new_website, $this->id);
        list($this->name, $this->bio, $this->location, $this->website) = array($new_name, $new_bio, $new_location, $new_website);
    }

    public function get_client_width() {
        return $this->client_width;
    }

    public function get_client_height() {
        return $this->client_height;
    }

    public function get_secret() {
        return $this->secret;
    }


    public function get_signup_stamp() {
        return $this->signup_stamp;
    }

    public function set_tz_offset($tz_offset) {
        if ($this->tz_offset != $tz_offset) {
            db::query('UPDATE users SET tz_offset=%d WHERE user_id=%d', $tz_offset, $this->id);
            $this->tz_offset = $tz_offset;
        }
    }

    public function set_client_dimensions($width, $height) {
        if($this->client_width != $width || $this->client_height != $height) {
            db::query('UPDATE users SET client_width=%d, client_height=%d WHERE user_id=%d', $width, $height, $this->id);
            $this->client_width = $width;
            $this->client_height = $height;
        }
    }

    public function has_big_screen() {
        // 1440 is the resolution width on macbook pros
        return $this->client_width > 1440;
    }

    public static function has_active() {
        return session_id() && !empty($_SESSION['uid']);
    }

    public static function email_exists($email) {
        return db::has_row('SELECT null FROM users WHERE LOWER(email) = LOWER("%s")', trim($email));
    }

    public static function username_exists($username) {
        $username = strtolower($username);
        if (!array_key_exists($username, self::$username_id_map)) {
            self::$username_id_map[$username] = db::result_query('SELECT user_id FROM users WHERE LOWER(username) = LOWER("%s")', trim($username));
        }
        return !is_null(self::$username_id_map[$username]);
    }

    public function is_right_password($password) {
        return db::has_row('SELECT null FROM users WHERE user_id=%d AND password="%s"', $this->id, sha1(PWD_SALT . $password));
    }

    public function update_password($new_password) {
        db::query('UPDATE users SET password="%s" WHERE user_id=%d', sha1(PWD_SALT . $new_password), $this->id);
    }

    public static function login($username, $update_login_stamp=true) {
        if (!session_id()) {
            session_start();
        }
        if (self::valid_email($username)) {
            // email
            $sql = 'SELECT user_id, status FROM users WHERE email="%s"';
        } elseif (is_id($username)) {
            // integer userid
            $sql = 'SELECT user_id, status FROM users WHERE user_id="%d"';
        } else {
            // username
            $sql = 'SELECT user_id, status FROM users WHERE username="%s"';
        }
        $row = db::fetch_query($sql, $username);
        if (empty($row) || $row['status'] == self::REJECTED) {
            return null;
        }
        $_SESSION['uid'] = $row['user_id'];
        if ($update_login_stamp) {
            db::query('UPDATE users SET last_login_stamp=NOW() WHERE user_id=%d', $row['user_id']);
        }
        return self::get($_SESSION['uid']);
    }

    public static function create_temp($email, $name) {
        $confirm_code = self::get_unique_confirm_code();
        $sql = 'INSERT INTO temp_users (confirm_id, email, name, ip) VALUES ("%s", "%s", "%s", "%s")';
        db::query($sql, $confirm_code, strtolower(trim($email)), $name, $_SERVER['REMOTE_ADDR']);
        return $confirm_code;
    }

    // get a confirm code but ensure it doesn't already exist in the db
    private static function get_unique_confirm_code() {
        $ok = false;
        $i = 0;
        while (!$ok) {
            $confirm_code = substr(md5(round(microtime(true)) * rand(1,10000) . 'x2:9k2##ko-'), 0, 10);
            $ok = !db::has_row('SELECT null FROM temp_users WHERE confirm_id="%s"', $confirm_code);
            if ($i++ >= 10) {
                error('could not obtain unique confirm code after 10 tries');
            }
        }
        return $confirm_code;
    }

    public static function create($confirm_code, $username, $password) {
        db::query('UPDATE temp_users SET is_confirmed=1 WHERE confirm_id="%s" AND is_confirmed=0', $confirm_code); 
        if (db::affected_rows() > 0) {
            $sql = 'INSERT INTO users (username, password, name, email, secret, signup_stamp)
                SELECT "%s", "%s", name, email, "%s", signup_stamp FROM temp_users WHERE confirm_id="%s"';
            db::query($sql, trim($username), sha1(PWD_SALT . $password), md5(microtime() . '0(Mi201:-f,{' . rand(1,10000)), $confirm_code);
            $uid = db::insert_id();
            photo::create_folders($uid);
            return true;
        } else {
            return false;
        }
    }

    public static function logout() {
        // start the session if not already started
        if (!session_id()) {
            session_start();
        }
        // unset the login and destroy the session
        unset($_SESSION['uid']);
        $_SESSION = array();
        session_destroy();
        // delete any login cookies
        cookie::delete('login');
    }

    public static function valid_email($email) {
        return preg_match('/^[A-Za-z0-9\.\_+-]+@[A-Za-z0-9\_-]+.[A-Za-z0-9\_-]+.*/', $email);
    }

    public static function is_valid_login($username, $pwd) {
        if (self::valid_email($username)) {
            $sql = 'SELECT user_id, username, password, UNIX_TIMESTAMP(signup_stamp) AS signup_stamp FROM users WHERE status != %d AND email="%s"';
        } else {
            $sql = 'SELECT user_id, username, password, UNIX_TIMESTAMP(signup_stamp) AS signup_stamp FROM users WHERE status != %d AND username="%s"';
        }
        $row = db::fetch_query($sql, self::REJECTED, $username, $pwd);
        if (!$row) {
            return false;
        }
        if ($row['signup_stamp'] > 1251260736) {
            // new system
            return $row['password'] == sha1(PWD_SALT . $pwd);
        } else {
            // old system
            // check if the old user has been migrated already
            $migrated_users_file = BASE_DIR . 'migration' . DIR_SEP . 'migrated-users.txt';
            if (file_exists($migrated_users_file) && in_array($row['username'], explode("\n", file_get_contents($migrated_users_file)))) {
                return $row['password'] == sha1(PWD_SALT . $pwd);
            } else {
                if ($row['password'] == sha1($pwd)) {
                    // password is correct
                    // upgrade the user to the new system
                    file_put_contents($migrated_users_file, $row['username'] . "\n", FILE_APPEND);
                    db::query('UPDATE users SET password="%s" WHERE user_id=%d', sha1(PWD_SALT . $pwd), $row['user_id']);
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    public static function get_name_from_confirm_code($confirm_code) {
        $name = db::result_query('SELECT name FROM temp_users WHERE confirm_id="%s" AND is_confirmed=0', $confirm_code);
        return is_null($name) ? false : $name;
    }

    public static function suggest_username($name) {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $name));
    }

    public static function valid_username($username) {
        return 0 !== preg_match('/^[a-z0-9]+$/i', $username);
    }

    public static function is_reserved_username($username) {
        return in_array(strtolower(trim($username)), array('photo', 'add', 'edit', 'signup', 'confirm', 'dash', 'dashboard', 'logout', 'login', 'about', 'raj', 'meetrajesh', 'william', 'rajesh', 'orangenorth', 'feedback', 'profile', 'feed', 'follow', 'unfollow', 'account', 'settings', 'setting', 'notices', 'notifications', 'site', 'down', 'up', 'sitedown', 'rkumar', 'rajeshkumar', 'forgot', 'pass', 'forgotpass', 'error', 'fotavia', 'photo', 'foto', 'tavia', 'fotav', 'new', 'email', 'prefs', 'preferences', 'emailprefs', 'search'));
    }

    // used to check if username is composed only of numbers in confirm.php
    public static function is_numbers_only($username) {
        return ctype_digit((string)$username);
    }

    public static function valid_username_length($username) {
        return strlen($username) >= USERNAME_MIN_LENGTH;
    }

    public function follow($leader_user_id) {
        // if following yourself or following someone you're already following, do nothing
        if ($leader_user_id == $this->id || in_array($leader_user_id, $this->get_leaders())) {
            return;
        }
        db::query('INSERT INTO follows (follower_user_id, leader_user_id) VALUES (%d, %d)', $this->id, $leader_user_id);
        db::query('INSERT INTO follow_history (user_id, leader_user_id, is_follow) VALUES (%d, %d, 1)', $this->id, $leader_user_id);
//         // send email
//         $leader = user::get($leader_user_id);
//         // check if the leader i just followed was already following me
//         $tmpl = in_array($this->id, $leader->get_leaders()) ? 'alsofollowing' : 'newfollower';
//         $email = new email($tmpl);
//         $email->assign('name', $leader->get_name());
//         $email->assign('follower_username', $this->username);
//         $email->assign('follower_name', $this->name);
//         $email->assign('follower_profile_page_url', BASE_URL . $this->get_profile_url());
//         $email->send($leader);
    }

    public function unfollow($leader_user_id) {
        if ($leader_user_id != $this->id && in_array($leader_user_id, $this->get_leaders())) {
            db::query('DELETE FROM follows WHERE follower_user_id=%d AND leader_user_id=%d', $this->id, $leader_user_id);
            db::query('INSERT INTO follow_history (user_id, leader_user_id, is_follow) VALUES (%d, %d, 0)', $this->id, $leader_user_id);
        }
    }

    // a list of users that follow this user
    // a regular user should NEVER be able to see this list
    public function get_followers() {
        return db::col_query('SELECT follower_user_id FROM follows WHERE leader_user_id=%d', $this->id);
    }

    public function num_followers() {
        return db::result_query('SELECT COUNT(*) FROM follows WHERE leader_user_id=%d', $this->id);
    }

    // a list of people this user follows
    // this LIST should only be visible to this user, no one else
    public function get_leaders() {
        return db::col_query('SELECT leader_user_id FROM follows WHERE follower_user_id=%d', $this->id);
    }

    public function num_leaders() {
        return db::result_query('SELECT COUNT(*) FROM follows WHERE follower_user_id=%d', $this->id);
    }

    public function total_photos() {
        return db::result_query('SELECT COUNT(*) FROM photos WHERE owner_id=%d AND status != %d', $this->id, photo::REJECTED);
    }

    // is this user following $leader_uid?
    public function is_following($leader_uid) {
        if ($this->id == $leader_uid) {
            return true;
        }
        return db::has_row('SELECT null FROM follows WHERE follower_user_id=%d AND leader_user_id=%d', $this->id, $leader_uid);
    }

    public function get_private_feed($limit, $include_myself=true) {
        $followers = array($include_myself ? $this->id : 0);
        $followers = array_merge($followers, $this->get_leaders());
        $followers = implode(',', $followers);
        return db::col_query('SELECT photo_id FROM photos WHERE owner_id IN (%s) AND status != %d ORDER BY stamp DESC LIMIT %d', $followers, photo::REJECTED, $limit);
    }

    public function get_public_feed($limit=10) {
        return photo::latest_from($this->id, $limit);
    }

    public function feed_secret_key() {
        return str_encrypt(FEED_SECRET, $this->id . '-' . substr($this->secret, 0, 6));
    }

    public static function attempt_auto_login() {
        if (!self::has_active()) {
            $uid = cookie::get('login');
            if (is_id($uid)) {
                self::login($uid);
            }
        }
    }

    public function reject() {
        db::query('UPDATE users SET status=%d WHERE user_id=%d', self::REJECTED, $this->id);
        $this->status = self::REJECTED;
    }

    public function is_rejected() {
        return $this->status == self::REJECTED;
    }

    public function send_first_reminder() {
        if (db::result_query('SELECT sent_first_reminder FROM users WHERE user_id=%d', $this->id)) {
            return;
        }
        if ($this->is_rejected() || !$this->can_upload_photo()) {
            return;
        }
        // send email
        $email = new email('firstreminder');
        $email->assign('name', $this->name);
        $email->assign('photo_add_url', BASE_URL . '/photo/add');
        $email->send($this);
        // flip the setting
        db::query('UPDATE users SET sent_first_reminder=1 WHERE user_id=%d', $this->id);
    }

    public function get_email_optouts() {
        return db::col_query('SELECT email_type FROM email_optouts WHERE user_id=%d', $this->id);
    }

    public function set_email_optouts($optouts) {
        db::query('DELETE FROM email_optouts WHERE user_id=%d', $this->id);
        $optouts = array_unique($optouts);
        $vals = array();
        foreach ($optouts as $type) {
            if (preg_match('/^HEADER-/', $type)) {
                continue;
            }
            $vals[] = spf('(%d, "%s")', $this->id, db::escape($type));
        }
        if (count($vals)) {
            $sql = 'INSERT INTO email_optouts VALUES ' . implode(', ', $vals);
            db::query($sql);
        }
    }

    public function send_forgot_pass_email() {
        $email = new email('forgotpass');
        $email->assign('name', $this->name);
        $link = spf('%s/resetpass/%s', BASE_URL, str_encrypt(FORGOT_PASS_SECRET, $this->id . '-' . time()));
        $email->assign('forgot_pass_link', $link);
        $email->send($this);
    }

    public function get_profile_link() {
        return spf('<a href="%s">%s</a>', $this->get_profile_url(), hsc($this->username));
    }

    // same as get_profile_link() except use the user's name instead of just username
    public function get_full_profile_link() {
        return spf('<a href="%s">%s</a>', $this->get_profile_url(), hsc($this->name));
    }

    public function get_profile_url() {
        return '/' . hsc($this->username);
    }

    public function get_prefs() {
        if (!isset($this->prefs)) {
            // set defaults
            $this->prefs['clicknext'] = false;
            $this->prefs['twpub'] = false;
            $res = db::query('SELECT pref_name, pref_val FROM user_prefs WHERE user_id=%d', $this->id);
            while ($row = $res->fetch_assoc()) {
                $this->prefs[$row['pref_name']] = $row['pref_val'];
            }
            // do necessary casting
            $this->prefs['clicknext'] = (bool)$this->prefs['clicknext'];
            $this->prefs['twpub'] = (bool)$this->prefs['twpub'];
        }
        return $this->prefs;
    }

    public function get_pref($pref_name) {
        $this->get_prefs();
        return isset($this->prefs[$pref_name]) ? $this->prefs[$pref_name] : null;
    }

    public function set_pref($pref_name, $pref_val) {
        $this->get_prefs();
        $this->prefs[$pref_name] = $pref_val;
        $this->save_prefs($this->prefs);
    }        

    public function unset_pref($pref_name) {
        $this->get_prefs();
        unset($this->prefs[$pref_name]);
        $this->save_prefs($this->prefs);
    }

    public function save_prefs($prefs) {
        db::query('DELETE FROM user_prefs WHERE user_id=%d', $this->id);
        $rows = array();
        foreach ($prefs as $pref_name => $pref_val) {
            // cast to int if bool
            if (is_bool($pref_val)) {
                $pref_val = $pref_val ? 1 : 0;
            }
            $rows[] = spf('(%d, "%s", "%s")', $this->id, db::escape($pref_name), db::escape($pref_val));
        }
        if (count($rows) == 0) {
            return;
        }
        db::query(spf('INSERT INTO user_prefs VALUES %s', implode(',', $rows)));
        $this->prefs = $prefs;
    }

    // get the pids of all the non-rejected photos uploaded by this user
    public function get_all_photos() {
        return db::col_query('SELECT photo_id FROM photos WHERE owner_id=%d AND status != %d ORDER BY stamp DESC', $this->id, photo::REJECTED);
    }

    // get all the users who haven't logged in over 2 weeks
    public static function get_inactive_users() {
        return db::col_query('SELECT user_id FROM users 
                              WHERE UNIX_TIMESTAMP(IF(ISNULL(last_login_stamp), signup_stamp, last_login_stamp)) + 14*86400 <= UNIX_TIMESTAMP() 
                              AND sent_inactive_reminder=0');
    }

    public function send_inactive_reminder() {
        $email = new email('inactivereminder');
        $email->assign('name', $this->name);
        $email->assign('photo_upload_url', BASE_URL . '/photo/add/');
        $email->assign('forgot_pass_url', BASE_URL . '/forgotpass/');
        $email->send($this);
        db::query('UPDATE users SET sent_inactive_reminder=1 WHERE user_id=%d', $this->id);
    }

    public function save_headshot($img) {
        if ($img['error'] != UPLOAD_ERR_OK) {
            return _('There was an error uploading your headshot. Please try a smaller file.');
        }

        list($width, $height, $mimetype) = getimagesize($img['tmp_name']);

        // check file type
        if (!in_array($mimetype, array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
            return _('Sorry, we only support JPG, PNG and GIF headshots at this time. Please try again with one of these imgage types.');
        }

        $ext = image_type_to_extension($mimetype, false);

        if ($ext == 'jpeg') {
            $ext = 'jpg';
        }

        $dest_path = spf('%s/%d/%s.%s', USER_PHOTO_DIR, $this->id, 'headshot', $ext);
        $dest_path = gdir($dest_path);

        if (!move_uploaded_file($img['tmp_name'], $dest_path)) {
            return _('Unable to copy headshot to destination directory. Please try again.');
        }

        chmod($dest_path, 0644);

        // save the details in the db
        db::query('UPDATE users SET headshot_ext="%s" WHERE user_id=%d', $ext, $this->id);
        $old_ext = $this->headshot_ext;
        $this->headshot_ext = $ext;

        // generate thumb
        $thumb = new thumbnail(DEFAULT_PHOTO_QUALITY, true);
        $thumb->gen_equal($dest_path, $dest_path, 55);

        // delete old headshot if it exists and its ext is different from what we have now
        if (!empty($old_ext) && $this->headshot_ext != $old_ext) {
            unlink(gdir(spf('%s/%d/%s.%s', USER_PHOTO_DIR, $this->id, 'headshot', $old_ext)));
        }

    }

    public function has_headshot() {
        return !empty($this->headshot_ext);
    }

    public function headshot_url() {
        if ($this->has_headshot()) {
            return spf('/photos/%d/%s.%s', $this->id, 'headshot', $this->headshot_ext);
        } else {
            return spf('http://www.gravatar.com/avatar/%s?s=55&rating=g&default=%s', md5($this->email), BASE_URL . '/img/noheadshot.png');
        }
    }

    public function headshot_pic_link($img_id='') {
        return spf('<a href="%s"><img id="%s" class="silver_frame" src="%s" alt="headshot" /></a>', $this->get_profile_url(), hsc($img_id), $this->headshot_url());
    }

    public static function search($term) {
        $searchable_fields = array('username', 'name', 'bio', 'email', 'location', 'website');
        $like_clauses = array();
        $escaped_term = db::escape($term);
        foreach ($searchable_fields as $field) {
            $like_clauses[] = spf('%s LIKE "%%%s%%"', $field, $escaped_term);
        }
        $where_clause = implode(' OR ', $like_clauses);
        $sql = spf('SELECT user_id FROM users WHERE status != %d AND (%s)', self::REJECTED, $where_clause);
        return db::col_query($sql);
    }

    // get an array of this user and all the people he's following
    public function get_team() {
        return array_merge(array($this->id), $this->get_leaders());
    }

    public function can_fb_publish() {
        return is_id($this->get_pref('fbid'));
    }

    public function get_fb_id() {
        return $this->get_pref('fbid');
    }

    public function can_tw_publish() {
        return (bool)$this->get_pref('twpub');
    }

    public function save_tw_auth($auth_token, $auth_token_secret) {
        $prefs = $this->get_prefs();
        $prefs['twpub'] = true;
        $prefs['tw_token'] = $auth_token;
        $prefs['tw_token_secret'] = $auth_token_secret;
        $this->save_prefs($prefs);
    }

    public function get_tw_auth() {
        $prefs = $this->get_prefs();
        return array($prefs['tw_token'], $prefs['tw_token_secret']);
    }

    public function unset_tw_auth() {
        $prefs = user::active()->get_prefs();
        unset($prefs['twpub'], $prefs['tw_token'], $prefs['tw_token_secret']);
        user::active()->save_prefs($prefs);
    }

    // replace @william with <a href="http://www.fotavia.com/william">William Chen</a>
    // this is the callback function for the preg_replace_callback() in format_user_text():functions.php
    public static function profile_link_callback($match) {
        $username = trim($match[1]);
        if ($user = self::get_from_username($username)) {
            return $user->get_full_profile_link();
        } else {
            return $match[0];
        }
    }

    public static function default_thumb_size() {
        return user::has_active() && user::active()->has_big_screen() ? 'large' : 'small';
    }

}

?>