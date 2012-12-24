<?php

class photo implements newsfeed_item {

    private $id;
    private $owner_id;
    private $title;
    private $text;
    private $hash;
    private $ext;
    private $page_url;
    private $stamp;
    private $user_date; // in the form 2009-08-14
    private $status;

    private $email_sent = true;
    private $fb_published = false;
    private $tw_published = false;

    // status enum
    const PENDING  = 0;
    const APPROVED = 1;
    const REJECTED = 2;
    const IS_GOOD  = 3; // IS_GOOD implies APPROVED

    // cached vars
    private $prev_photo;
    private $next_photo;
    private $formatted_text;
    private $exif_data;

    // static array of singleton objs
    private static $instances = array();

    // array of available thumbnail sizes and their dimensions
    private static $thumb_sizes = array('large' => 1200,
                                        'small' => 790,
                                        'rss' => 400,
                                        'thumb' => 220,
                                        'square' => array(120,120));

    // thumb size overrides to be used for vertical photos
    private static $vertical_thumb_sizes = array('large' => 800,
                                                 'small' => 650);

    private function __construct($id) {
        $this->id = (int)$id;
        $row = db::fetch_query('SELECT owner_id, title, text, hash, ext, page_url, email_sent, fb_published, tw_published, UNIX_TIMESTAMP(stamp), user_date, status
                                FROM photos WHERE photo_id=%d', $this->id);
        if (is_null($row)) {
            error('Invalid photo_id ' . $this->id . ' specified.');
        }
        list($this->owner_id, $this->title, $this->text, $this->hash, $this->ext, $this->page_url,
             $this->email_sent, $this->fb_published, $this->tw_published, $this->stamp, $this->user_date, $this->status) = array_values($row);

        // cast to correct types here
        $this->email_sent = (bool)$this->email_sent;
        $this->fb_published = (bool)$this->fb_published;
        $this->tw_published = (bool)$this->tw_published;
        $this->status = (int)$this->status;
    }

    public static function get($id) {
        if (!is_id($id)) {
            error('invalid id specified in photo::get(): ' . $id);
        }
        if (!isset(self::$instances[$id])) {
            self::$instances[$id] = new photo($id);
        }
        return self::$instances[$id];
    }

    public static function get_from_user_date($username, $date) {
        $sql = 'SELECT photo_id FROM photos p INNER JOIN users u ON p.owner_id=u.user_id
                WHERE u.username="%s"
                AND p.user_date="%s"
                AND p.status != %d';
        $row = db::fetch_query($sql, $username, $date, self::REJECTED);
        return is_null($row) ? null : self::get($row['photo_id']);
    }

    public function has_prev() {
        return $this->prev() !== -1;
    }

    public function has_next() {
        return $this->next() !== -1;
    }

    public function prev() {
        if (!isset($this->prev_photo)) {
            if (!isset($_GET['newp'])) {
                $sql = 'SELECT photo_id FROM photos WHERE owner_id=%d AND user_date < "%s" AND status != %d ORDER BY user_date DESC LIMIT 1';
                $row = db::fetch_query($sql, $this->get_owner_id(), $this->get_user_date(), self::REJECTED);
            } else {
                $sql = 'SELECT photo_id FROM photos WHERE status=%d AND photo_id > %d ORDER BY photo_id ASC LIMIT 1';
                $row = db::fetch_query($sql, self::IS_GOOD, $this->id);
            }
            $this->prev_photo = !is_null($row) ? self::get($row['photo_id']) : -1;
        }
        return $this->prev_photo;
    }

    public function next() {
        if (!isset($this->next_photo)) {
            if (!isset($_GET['newp'])) {
                $sql = 'SELECT photo_id FROM photos WHERE owner_id=%d AND user_date > "%s" AND status != %d ORDER BY user_date ASC LIMIT 1';
                $row = db::fetch_query($sql, $this->get_owner_id(), $this->get_user_date(), self::REJECTED);
            } else {
                $sql = 'SELECT photo_id FROM photos WHERE status=%d AND photo_id < %d ORDER BY photo_id DESC LIMIT 1';
                $row = db::fetch_query($sql, self::IS_GOOD, $this->id);
            }
            $this->next_photo = !is_null($row) ? self::get($row['photo_id']) : -1;
        }
        return $this->next_photo;
    }

    public function url($size = null) {
        if (!$this->is_valid_size($size)) {
            error('invalid size "' . $size . '"');
        }
        if (empty($size)) {
            // orig
            return spf('/photos/%d/%s.%s', $this->get_owner_id(), $this->hash, $this->ext);
        } else {
            // thumb
            return spf('/photos/%d/%s/%s.%s', $this->get_owner_id(), $size, $this->hash, 'jpg');
        }
    }

    private function path($size = null) {
        if (!$this->is_valid_size($size)) {
            error('invalid size "' . $size . '"');
        }
        // get the filename without extension
        $filename = ($this->status === self::REJECTED) ? REJECTED_PHOTO_PREFIX . $this->hash : $this->hash;
        if (empty($size)) {
            // orig
            $path = spf('%s/%d/%s.%s', USER_PHOTO_DIR, $this->get_owner_id(), $filename, $this->ext);
        } else {
            // only support jpg thumbs
            $path = spf('%s/%d/%s/%s.%s', USER_PHOTO_DIR, $this->get_owner_id(), $size, $filename, 'jpg');
        }
        return gdir($path);
    }

    // get an array of all paths for this photo
    private function get_paths() {
        $paths['orig'] = $this->path();
        foreach (self::$thumb_sizes as $size => $dim) {
            $paths[$size] = $this->path($size);
        }
        return $paths;
    }

    private function is_valid_size($size) {
        return empty($size) || array_key_exists($size, self::$thumb_sizes);
    }

    public static function exists($pid) {
        return db::has_row('SELECT null FROM photos WHERE photo_id=%d AND status != %d', $pid, self::REJECTED);
    }

    public function get_id() {
        return $this->id;
    }

    public function get_owner_id() {
        return $this->owner_id;
    }

    public function owner() {
        return user::get($this->owner_id);
    }

    public function get_title() {
        return $this->title;
    }

    public function get_short_title() {
        return truncate_str($this->title, 25);
    }

    public function get_text() {
        return $this->text;
    }

    public function get_formatted_text() {
        if (!isset($this->formatted_text)) {
            $this->formatted_text = format_user_text($this->text);
        }
        // can't hsc output since simple tags like a, p, b, etc. are allowed in the input
        return $this->formatted_text;
    }

    public function get_text_summary() {
        return summarize_text($this->text, 14);
    }

    public function get_page_url($show_comments=false) {
        return spf('%s%s', BASE_URL, $this->page_url . (isset($_GET['newp']) ? '?newp' : '') . ($show_comments ? '#comments' : ''));
    }

    public function get_stamp() {
        return $this->stamp;
    }

    public function get_user_date() {
        return $this->user_date;
    }

    public function get_user_date_stamp() {
        list($y, $m, $d) = explode('-', $this->user_date);
        return mktime(0,0,0, $m, $d, $y);
    }

    public static function add_temp($uid, $img) {
        list($width, $height, $mimetype) = getimagesize($img['tmp_name']);

        // check file type
        if (!in_array($mimetype, array(IMAGETYPE_JPEG, IMAGETYPE_PNG))) {
            throw new Exception(_('Sorry, we only support JPG and PNG photo uploads at this time. Please try again with a JPG or PNG image.'));
        }

        // check minimum file dimensions
        if ($width * $height < MIN_UPLOAD_AREA) {
            throw new Exception(spf(_('Sorry, your upload is too small in resolution to be added. Please try again with at least a %s megapixel photo.'), round(MIN_UPLOAD_AREA/1e6,2)));
        }

        // get hash
        $hash = md5_file($img['tmp_name']);

        // check hash uniqueness
        if (db::has_row('SELECT null FROM photos WHERE hash="%s"', $hash)) {
            throw new Exception(_('Sorry, that photo was already uploaded previously. Please try a different one.'));
        }

        // move photo to temp folder
        $user = user::get($uid);
        $date_stamp = $user->get_local_date();

        // create user temp directory
        $user_temp_path = gdir(spf('%s/%s/%d', TEMP_PHOTO_DIR, $date_stamp, $uid));
        if(!is_dir($user_temp_path)) {
            mkdir($user_temp_path, 0755, true);
        }

        // add photo to temp directory
        $dest_path = gdir(spf('%s/%s/%d/photo.temp', TEMP_PHOTO_DIR, $date_stamp, $uid));

        if (!move_uploaded_file($img['tmp_name'], $dest_path)) {
            throw new Exception(_('Unable to copy photo to destination directory. Please try again.'));
        }

        chmod($dest_path, 0644);

        // generate preview for temp photo
        $thumb = new thumbnail(DEFAULT_PHOTO_QUALITY, false);
        $preview_path = gdir(spf('%s/%s/%d/preview.jpg', TEMP_PHOTO_DIR, $date_stamp, $uid));
        $thumb->gen($dest_path, $preview_path, self::$thumb_sizes['rss']);

        // return the date stamp that can be used to find the temp photo
        return $date_stamp;
    }

    public static function add($uid, $date_stamp, $title, $text) {
        // move the temp file associated with the date stamp for the user
        $temp_path = gdir(spf('%s/%s/%d/photo.temp', TEMP_PHOTO_DIR, $date_stamp, $uid));

        list($width, $height, $mimetype) = getimagesize($temp_path);

        // get hash
        $hash = md5_file($temp_path);

        $ext = image_type_to_extension($mimetype, false);
        $ext = $ext == 'jpeg' ? 'jpg' : $ext; // jpeg -> jpg

        $dest_path = gdir(spf('%s/%d/%s.%s', USER_PHOTO_DIR, $uid, $hash, $ext));
        rename($temp_path, $dest_path);

        // delete preview file and user's temp directory
        $temp_dir = gdir(spf('%s/%s/%d', TEMP_PHOTO_DIR, $date_stamp, $uid));
        $preview_file =  $temp_dir . DIRECTORY_SEPARATOR . 'preview.jpg';
        unlink($preview_file);
        rmdir($temp_dir);

        // create a photo entry in the db
        $user = user::get($uid);
        $sql = 'INSERT INTO photos (owner_id, user_date, title, text, hash, ext) VALUES (%d, "%s", "%s", "%s", "%s", "%s")';
        db::query($sql, $uid, $date_stamp, $title, $text, $hash, $ext);

        // do other misc tasks
        $photo_id = db::insert_id();
        $photo = self::get($photo_id);
        // update the page url from the page's title
        $photo->update_page_url();
        // generate thumbnails
        $photo->gen_thumbs();
        // send photo emails if applicable
        $photo->send_email_notifications();

        // third party publishing
        
        // twitter
        if ($user->can_tw_publish()) {
            $photo->tw_publish();
        }

        // facebook
        if ($user->can_fb_publish()) {
            $photo->fb_publish();
        }

        return $photo_id;
    }

    public function gen_thumbs($overwrite=true, $sharpen=false) {
        $thumb = new thumbnail(DEFAULT_PHOTO_QUALITY, $sharpen);
        $source = $this->path();
        if ($this->is_vertical()) {
            // override the dimensions for certain thumbs
            $thumb_sizes = array_merge(self::$thumb_sizes, self::$vertical_thumb_sizes);
        } else {
            $thumb_sizes = self::$thumb_sizes;
        }
        foreach ($thumb_sizes as $size => $dim) {
            $target = $this->path($size);
            if (!$overwrite && file_exists($target)) {
                continue;
            }
            if (!is_array($dim)) {
                $thumb->gen($source, $target, $dim);
            } else {
                if ($dim[0] == $dim[1]) {
                    $thumb->gen_equal($source, $target, $dim[0]);
                } else {
                    error('cannot generate fixed width AND fixed height thumbs.');
                }
            }
            chmod($target, 0644);
        }
    }

    // is this a vertical photo?
    public function is_vertical() {
        list($width, $height) = getimagesize($this->path());
        return $height > ($width * 1.3);
    }

    public function save_details($title, $text) {
        db::query('UPDATE photos SET title="%s", text="%s" WHERE photo_id=%d', $title, $text, $this->id);
        if (db::affected_rows()) {
            $this->title = $title;
            $this->text = $text;
            $this->update_page_url();
        }
        // send photo emails if applicable
        $this->send_email_notifications();
    }

    // update the page url from the page's title
    private function update_page_url() {
        $owner = user::get($this->get_owner_id());
        $this->page_url = spf('/%s/%s/%s/', hsc(strtolower($owner->get_username())), str_replace('-', '/', $this->get_user_date()), $this->urlized_title());
        db::query('UPDATE photos SET page_url="%s" WHERE photo_id=%d', $this->page_url, $this->id);
    }

    private function urlized_title() {
        $title = trim(strtolower($this->title));
        // get rid of funky chars
        $title = str_replace(array("\r", "\n", "\t"), '', $title);
        // replace sequence of white-space or underscores with hyphen
        $title = preg_replace('/[\s_]+/', '-', $title);
        // get rid of all non-word chars
        $title = preg_replace('/[^\w-]/', '', $title);
        // replace underscores with hyphens
        $title = str_replace('_', '-', $title);
        // replace multiple hyphens with single hyphen
        $title = preg_replace('/-+/', '-', $title);
        return hsc($title);
    }

    public static function create_folders($uid) {
        $uid = trim($uid);
        if (!is_id($uid)) {
            error('non-numeric uid specified');
        }
        foreach (self::$thumb_sizes as $size => $dimensions) {
            $dir = gdir(spf('%s/%d/%s/', USER_PHOTO_DIR, $uid, $size));
            if (!is_dir($dir)) {
                mkdir($dir, 0766, true);
            }
        }
    }

    // get the last $num photos for a user with uids IN $uids
    // if $uids is null, then return photos from anyone
    public static function latest_from($uids, $num=1, $offset=0) {
        if (is_null($uids)) {
            return db::col_query('SELECT photo_id FROM photos WHERE status=%d ORDER BY stamp DESC LIMIT %d, %d', self::IS_GOOD, $offset, $num);
        }
        if (!is_array($uids)) {
            $uids = array($uids);
        }
        $uids = array_map('db::escape', $uids);
        $where = spf('owner_id IN (%s)', count($uids) ? implode(',', $uids) : '0');
        $sql = spf('SELECT photo_id FROM photos WHERE status != %d AND %s', self::REJECTED, $where);
        return db::col_query($sql . ' ORDER BY stamp DESC LIMIT %d, %d', $offset, $num);
    }

    // get photos that don't belong to $uid or $uid's friends
    public static function latest_from_others($num=1, $offset=0) {
        $uid = user::active()->get_id();
        $num = (int)$num;
        if (empty($uid)) {
            // get all approved good-quality photos by everyone
            return db::col_query('SELECT photo_id FROM photos WHERE status=%d ORDER BY stamp DESC LIMIT %d, %d', self::IS_GOOD, $offset, $num);
        } else {
            // show photos that don't belong to this user or to this user's friends
            $uids = user::get($uid)->get_leaders();
            $uids[] = $uid;
            return db::col_query('SELECT photo_id FROM photos WHERE owner_id NOT IN (%s) AND status=%d ORDER BY stamp DESC LIMIT %d, %d',
                                 implode(',', $uids), self::IS_GOOD, $offset, $num);
        }
    }

    public function has_exif() {
        $this->get_exif_data();
        return !empty($this->exif_data['Model']);
    }

    public function get_exif_data() {
        if (!isset($this->exif_data)) {
            // can only read exif data from jpeg files
            if ($this->ext != 'jpg') {
                $this->exif_data = '';
                return;
            }
            $exif = exif_read_data($this->path());
            $sections = array('DateTime', 'MimeType', 'Model', 'Orientation', 'ExposureTime', 'ExposureTime', 'ExposureProgram',
                              'ISOSpeedRatings', 'ShutterSpeedValue', 'ApertureValue', 'ExposureBiasValue', 'MeteringMode', 'Flash', 'FocalLength',
                              'ExposureMode', 'WhiteBalance', 'SceneCaptureType');
            foreach ($sections as $section) {
                if (isset($exif[$section])) {
                    $this->exif_data[$section] = $exif[$section];
                }
            }
            if (isset($exif['COMPUTED']['ApertureFNumber'])) {
                $this->exif_data['ApertureFNumber'] = $exif['COMPUTED']['ApertureFNumber'];
            }
            unset($exif, $sections);
        }
        return $this->exif_data;
    }

    // generate some body to throw in the rss feeds
    public function get_rss_body() {

        $o  = '<p><a target="_blank" href="' . $this->get_page_url() . '">';
        $o .= '<img src="' . BASE_URL . $this->url('rss') . '" style="padding:2px; border:1px #ddd solid;" />';
        $o .= '</a></p>';
        $o .= $this->get_formatted_text();
        // num comments img
        $api_url = spf('%s/api/numcomments.php?pid=%d', BASE_URL, $this->id);
        $num_comments = $this->num_comments();
        $alt = spf(ngettext('%d comment', '%d comments', $num_comments), $num_comments);
        $title = _('View photo comments');
        $o .= '<p><a target="_blank" href="' . $this->get_page_url() . '#comments">';
        $o .= spf('<img src="%s" alt="%s", title="%s" />', $api_url, $alt, $title) . '</a></p>';
        return $o;

    }

    public function approve() {
        $this->set_status(self::APPROVED);
    }

    public function mark_as_good() {
        $this->set_status(self::IS_GOOD);
    }

    public function reject() {
        // take a backup of the old paths
        $old_paths = $this->get_paths();
        // update the db status
        $this->set_status(self::REJECTED);
        // get the new paths
        $new_paths = $this->get_paths();
        // move the photo and thumbs to a new location
        foreach ($old_paths as $size => $old_path) {
            rename($old_path, $new_paths[$size]);
        }
    }

    public function is_rejected() {
        return $this->status === self::REJECTED;
    }

    // set the status to approved, but move the files back from their deleted locations
    public function restore() {
        $old_paths = $this->get_paths();
        $this->set_status(self::APPROVED);
        $new_paths = $this->get_paths();
        foreach ($old_paths as $size => $old_path) {
            rename($old_path, $new_paths[$size]);
        }
    }

    private function set_status($new_status) {
        db::query('UPDATE photos SET status=%d WHERE photo_id=%d', $new_status, $this->id);
        $this->status = $new_status;
    }

    public static function get_next_photo_for_review() {
        return db::result_query('SELECT photo_id FROM photos WHERE status=%d ORDER BY stamp ASC LIMIT 1', self::PENDING);
    }

    public function img_tag($size=null) {
        return spf('<img class="silver_frame" title="%s" alt="%s" src="%s" />', $this->get_title(), $this->get_title(), BASE_URL . $this->url($size));
    }

    // send out email notifications about a new post if not sent already
    private function send_email_notifications() {
        if (!$this->email_sent) {
            // check if the title has something
            if (strlen($this->title) < 4) {
                return;
            }
            // update db
            db::query('UPDATE photos SET email_sent=1 WHERE photo_id=%d', $this->id);
            $this->email_sent = true;
            // send email
            $email = new email('newphoto');
            $email->assign('photo_page_url', $this->get_page_url());
            $email->assign('owner_username', $this->owner()->get_username());
            $email->assign('owner_name', $this->owner()->get_name());
            $email->assign('photo_title', $this->title);
            foreach ($this->owner()->get_followers() as $follower_user_id) {
                $follower = user::get($follower_user_id);
                $email->assign('name', $follower->get_name());
                $email->send($follower);
            }
        }
    }

    public function delete() {
        // delete db entry
        db::query('DELETE FROM photos WHERE photo_id=%d', $this->id);
        // delete the photo and thumbs as well
        $old_paths = $this->get_paths();
        foreach ($old_paths as $path) {
            unlink($path);
        }
    }

    public static function get_photos_per_month($uid) {
        return db::col_query('SELECT DATE_FORMAT(user_date, "%%Y%%m") AS date, COUNT(photo_id) FROM photos
            WHERE owner_id=%d AND status != %d GROUP BY date DESC ORDER BY stamp DESC', (int)$uid, self::REJECTED);
    }

    public static function search($uid, $term) {

        $search_terms = array();
        preg_match_all('/"(.*?)"/', $term, $matches);

        foreach ($matches[0] as $i => $match) {
            if (!empty($matches[1][$i])) {
                $search_terms[] = $matches[1][$i];
            }
            $term = str_replace($matches[0][$i], '', $term);
        }

        $term = trim($term);
        if (!empty($term)) {
            $search_terms = array_merge($search_terms, preg_split('/\s+/', $term, PREG_SPLIT_NO_EMPTY));
        }
        $search_terms = array_unique($search_terms);

        if (count($search_terms) == 0) {
            $where_clause = '0';
        } else {
            $like_clauses = array();
            foreach ($search_terms as $search_term) {
                $like_clauses[] = spf('((title LIKE "%%%s%%") OR (text LIKE "%%%1$s%%"))', db::escape($search_term));
            }
            $where_clause = implode(' AND ', $like_clauses);
        }

        $sql = spf('SELECT photo_id FROM photos WHERE owner_id=%d AND (%s) AND status != %d ORDER BY stamp DESC LIMIT 10', (int)$uid, $where_clause, self::REJECTED);
        return db::col_query($sql);
    }

    public static function print_photo_entries($photo_ids, $empty_msg) {

        if (count($photo_ids) == 0) { ?>
          <p><em><?=$empty_msg?></em></p>
        <?
        } else {
            foreach($photo_ids as $photo_id) {
                $p = photo::get($photo_id); ?>
                <div class="photo_entry">
                  <a href="<?=$p->get_page_url()?>"><img class="silver_frame" src="<?=$p->url('square')?>" title="<?=hsc($p->get_title())?>" /></a>
                  <div class="photo_title"><?=hsc($p->get_title())?></div>
                  <div class="photo_stamp">Posted <?=fuzzydate($p->get_stamp())?> on <?=std_date($p->get_user_date_stamp())?></div>
                  <? if (user::has_active() && user::active()->get_id() == $p->get_owner_id()) { ?>
                    <div class="edit_delete">
                      <a href="/photo/edit/<?=$p->get_id()?>">Edit</a> |
                      <a href="/photo/delete/<?=$p->get_id()?>">Delete</a>
                    </div>
                  <? } ?>
                  <div class="photo_text"><p><?=$p->get_text_summary()?></p></div>
                </div>
            <?
            }
        }

    }

    public function get_tooltip($escape=true) {
        $ret = spf('"%s" by %s', $this->get_title(), $this->owner()->get_username());
        return $escape ? hsc($ret) : $ret;
    }

    public function get_comments() {
        $res = db::query('SELECT comment_id, photo_id, owner_id, UNIX_TIMESTAMP(stamp) AS stamp, body FROM comments WHERE photo_id=%d ORDER BY comment_id ASC', $this->id);
        return photo_comment::build($res);
    }

    public function add_comment($uid, $body) {
        $sql = 'INSERT INTO comments (photo_id, owner_id, body) VALUES (%d, %d, "%s")';
        db::query($sql, $this->id, (int)$uid, $body);
        $cid = db::insert_id();
        if (is_id($cid)) {
            $this->send_comment_emails($cid, $uid, $body);
        }
        return $cid;
    }

    private function send_comment_emails($comment_id, $commenter_id, $comment_body) {
        $commenter = user::get($commenter_id);

        $recipient_ids = array();
//         // STEP a) send email to people who commented above this comment
//         $recipient_ids = db::col_query('SELECT DISTINCT owner_id FROM comments WHERE photo_id=%d AND comment_id < %d', $this->id, $comment_id);
//         // delete the commenter from this list. he doesn't need to be notified about his own comment
//         $recipient_ids = array_delete($recipient_ids, $commenter_id);
//         // unique the list to ensure 2 emails aren't sent to the same person (sanity check)
//         $recipient_ids = array_unique($recipient_ids);
//         // set the properties that are common to all emails
//         $email = new email('commentreply');
//         $email->assign('commenter_name', $commenter->get_name());
//         $email->assign('commenter_username', $commenter->get_username());
//         $email->assign('photo_title', $this->get_title());
//         $email->assign('comment_url', spf('%s#comment_%d', $this->get_page_url(), $comment_id));
//         $email->assign('comment_body', wordwrap(trim($comment_body)));
//         // fire off emails to everyone else
//         foreach ($recipient_ids as $recipient_id) {
//             $recipient = user::get($recipient_id);
//             $email->assign('name', $recipient->get_name());
//             // so we're not telling the photo owner his photo was uploaded by him
//             $email->assign('photo_owner_username', ($recipient_id == $this->owner_id ? 'you' : $this->owner()->get_username()));
//             $email->assign('photo_owner_name', ($recipient_id == $this->owner_id ? 'your' : ($this->owner()->get_name() . "'s")));
//             $email->send($recipient);
//         }

        // STEP b) now send email to the photo owner if not already sent above
        if (!in_array($this->owner_id, $recipient_ids) && $commenter_id != $this->owner_id) {
            $email = new email('newcomment');
            $email->assign('name', $this->owner()->get_name());
            $email->assign('commenter_name', $commenter->get_name());
            $email->assign('commenter_username', $commenter->get_username());
            $email->assign('photo_title', $this->get_title());
            $email->assign('comment_body', wordwrap(trim($comment_body)));
            $email->assign('comment_url', spf('%s#comment_%d', $this->get_page_url(), $comment_id));
            $owner_email_sent = $email->send($this->owner());
        }
    }

    public function can_delete_comment($active_user_id, $comment_owner_id) {
        return in_array($active_user_id, array($comment_owner_id, $this->owner_id));
    }

    public function num_comments() {
        return db::result_query('SELECT COUNT(*) FROM comments WHERE photo_id=%d', $this->id);
    }

    // newsfeed_item member
    public function get_target_id() {
        return $this->id;
    }

    // newsfeed_item member
    public function get_item_type() {
        return newsfeed_item_types::PHOTO_TYPE;
    }

    // return an <a href=""> tag containing the photo's title that clicks to go to the photo's view page
    public function get_link($show_comments=false) {
        $title = $this->title;
        $truncated = false;
        if (strlen($title) > 25) {
            $title = substr($title, 0, 25);
            $truncated = true;
        }
        return spf('<a href="%s">%s</a>%s', $this->get_page_url($show_comments), hsc($title), ($truncated ? '..' : ''));
    }

    // to be used by admin tool /admin/sharpen_thumbs.php only
    public static function get_max_photo_id() {
        return db::result_query('SELECT MAX(photo_id) FROM photos');
    }

    public function is_fb_published() {
        return $this->fb_published;
    }

    public function is_tw_published() {
        return $this->tw_published;
    }

    public function fb_publish() {
        if ($this->fb_published || !$this->owner()->can_fb_publish()) {
            return;
        }

        // if the photo is more than 24 hours old, don't publish
        if ($this->missed_publish_window()) {
            return;
        }

        require 'facebook/facebook.php';
        $fbid = $this->owner()->get_fb_id();
        $facebook = new Facebook(FACEBOOK_API_KEY, FACEBOOK_API_SECRET);

        // publish feed story
        $msg = _('added a new photo on Fotavia.');
        $attach = array('name' => spf('%s', $this->get_title()),
                        'href' => $this->get_page_url(),
                        'caption' => spf(_('Source: %s'), preg_replace('~^http://~', '', BASE_URL)),
                        'description' => _('Fotavia. Place for beautiful photography.'),
                        'media' => array(array('type' => 'image',
                                               'src' => BASE_URL . $this->url('thumb'),
                                               'href' => $this->get_page_url())));
        $action_links = array(array('text' => _('View Photo'),
                                    'href' => $this->get_page_url()));

        try {
            // $facebook->api_client->auth_getSession($facebook->api_client->auth_createToken());
            // stream_publish(string message, object attachment, array action_links, string target_id, string uid)
            $ret = $facebook->api_client->stream_publish($msg, $attach, $action_links, $fbid);
            if ($ret === "") {
                return;
            }
            if ($ret == FacebookAPIErrorCodes::API_EC_PERMISSION) {
                return $this->owner()->unset_pref('fbid');
            }
            // mark the photo as published
            db::query('UPDATE photos SET fb_published=1 WHERE photo_id=%d', $this->id);
            $this->fb_published = true;
        } catch (FacebookRestClientException $e) {
            if ($e->getCode() == FacebookAPIErrorCodes::API_EC_PERMISSION) {
                $this->owner()->unset_pref('fbid');
            }
        } catch (Exception $e) {
            // do nothing
        }

    }

    private function missed_publish_window() {
        return ($this->stamp + 86400) < time();
    }

    public function tw_publish() {
        if ($this->tw_published || !$this->owner()->can_tw_publish()) {
            return;
        }

        // if the photo is more than 24 hours old, don't publish
        if ($this->missed_publish_window()) {
            return;
        }

        // shorten the url using bit.ly
        $url = spf('http://api.bit.ly/shorten?version=2.0.1&longUrl=%s&login=%s&apiKey=%s', urlencode($this->get_page_url()), BITLY_USERNAME, BITLY_API_KEY);
        $ret = json_decode(curl::get($url), true);
        $url = array_shift($ret['results']);

        // if bit.ly is down
        if (empty($url['shortUrl'])) {
            return false;
        }

        // clean up title
        $title = trim(str_replace(array("\r\n", "\n"), ' ', trim(strip_tags($this->title))));
        $title = preg_replace('/\s+/', ' ', $title);
        $title = substr($title, 0, 87);

        $status_msg = spf('%s: "%s" %s', _('added a new photo on Fotavia'), $title, $url['shortUrl']);

        require 'Epi/EpiTwitter.php';

        // update twitter status
        $twitter_obj = new EpiTwitter(TWITTER_KEY, TWITTER_SECRET);
        list($auth_token, $auth_token_secret) = $this->owner()->get_tw_auth();
        $twitter_obj->setToken($auth_token, $auth_token_secret);
        $result = $twitter_obj->post_statusesUpdate(array('status' => $status_msg));
        $response = $result->response;

        // check return status
        if (isset($response['error']) && strlen($response['error']) > 0) {
            // twitter privs was revoked
            return $this->owner()->unset_tw_auth();
        }

        // mark the photo as published
        db::query('UPDATE photos SET tw_published=1 WHERE photo_id=%d', $this->id);
        $this->tw_published = true;
    }

    public function increment_num_views() {
        db::query('UPDATE photos SET num_views=num_views+1 WHERE photo_id=%d', $this->id);
    }

    public static function total_good_photos() {
        return db::result_query('SELECT COUNT(*) FROM photos WHERE status=%d', self::IS_GOOD);
    }

}

?>
