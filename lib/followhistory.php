<?php

class followhistory implements newsfeed_item {

    public $user_id; // follower user id
    public $leader_user_id;
    public $is_follow;
    public $stamp;

    public static function latest_from($current_uid, $num) {
        $uids = user::get($current_uid)->get_team();
        // exclude unfollow history of leaders, just not mine
        $sql = 'SELECT user_id, leader_user_id, is_follow, UNIX_TIMESTAMP(stamp) AS stamp
                FROM follow_history
                WHERE user_id IN (%s) 
                AND (is_follow=1 OR user_id=%d) 
                ORDER BY stamp DESC LIMIT %d';
        return db::query($sql, implode(',', $uids), $current_uid, $num);
    }

    public function __construct($user_id, $leader_user_id, $is_follow, $stamp) {
        $this->user_id = $user_id;
        $this->leader_user_id = $leader_user_id;
        $this->is_follow = (bool)$is_follow;
        $this->stamp = $stamp;
    }

    // interface newsfeed_item member
    public function get_stamp() {
        return $this->stamp;
    }

    // interface newsfeed_item member
    public function get_owner_id() {
        return $this->user_id;
    }

    // interface newsfeed_item member
    public function get_target_id() {
        return $this->leader_user_id;
    }

    // interface newsfeed_item member
    public function get_item_type() {
        return newsfeed_item_types::FOLLOW_TYPE;
    }

}

?>