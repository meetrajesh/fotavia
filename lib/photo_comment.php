<?php

class photo_comment implements newsfeed_item {

    public $id;
    public $photo_id;
    public $owner_id;
    public $stamp;
    public $body;

    private $owner;

    // takes a mysql result set and cranks out an array of photo_comment object out of them
    public static function build(mysqli_result $res) {
        $comments = array();
        while($row = $res->fetch_assoc()) {
            $comment = new photo_comment();
            $comment->id = $row['comment_id'];
            $comment->photo_id = $row['photo_id'];
            $comment->owner_id = $row['owner_id'];
            $comment->stamp = $row['stamp'];
            $comment->body = $row['body'];
            $comments[] = $comment;
        }
        return $comments;
    }

    // newsfeed_item member
    public function get_owner_id() {
        return $this->owner_id;
    }

    public function owner() {
        if (!isset($this->owner)) {
            $this->owner = user::get($this->owner_id);
        }
        return $this->owner;
    }

    public function get_id() {
        return $this->id;
    }

    // newsfeed_item member
    public function get_stamp() {
        return $this->stamp;
    }

    public function get_formatted_body() {
        return format_user_text($this->body);
    }

    public function get_permalink() {
        return spf('%s#comment_%d', photo::get($this->photo_id)->get_page_url(), $this->id);
    }

    public static function exists($cid) {
        return db::has_row('SELECT null FROM comments WHERE comment_id=%d', (int)$cid);
    }

    public static function get_photo_and_owner_ids($cid) {
        $row = db::fetch_query('SELECT photo_id, owner_id FROM comments WHERE comment_id=%d', (int)$cid);
        return array($row['photo_id'], $row['owner_id']);
    }

    public static function delete($cid) {
        db::query('DELETE FROM comments WHERE comment_id=%d', (int)$cid);
    }

    // newsfeed_item member
    public function get_target_id() {
        return $this->photo_id;
    }

    // newsfeed_item member
    public function get_item_type() {
        return newsfeed_item_types::COMMENT_TYPE;
    }

}

?>
