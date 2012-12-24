<?php

class newsfeed {

    private $uid;
    private $entries;

    public function __construct($uid) {
        $this->uid = $uid;
    }

    public function get_entries() {
        if (!isset($this->entries)) {
            $this->build_entries();
        }
        return $this->entries;
    }

    private function build_entries() {
        
        // 1) get all photos posted by this user and his leaders
        $team_uids = user::get($this->uid)->get_team();
        $pids = photo::latest_from($team_uids, NEWSFEED_NUM_ENTRIES);

        // 2) add to entries array
        $this->entries = array();
        foreach ($pids as $pid) {
            $this->entries[] = photo::get($pid);
        }

        // 3) get all comments posted on this user's photos
        // 4) get all comments posted by this user and this user's leaders
        // 5) find the union of the 2 sets (eliminate duplication)
        $res = db::query('SELECT c.comment_id, c.photo_id, c.owner_id, UNIX_TIMESTAMP(c.stamp) AS stamp, c.body FROM comments c
                          INNER JOIN photos p ON c.photo_id=p.photo_id
                          WHERE p.status != %d AND (c.owner_id IN (%s) OR p.owner_id = %d)
                          ORDER BY c.stamp DESC', photo::REJECTED, implode(',', $team_uids), $this->uid);

        // 6) add $comments to $entries
        $comments = photo_comment::build($res);
        $this->entries = array_merge($this->entries, $comments);

        // 7) get the follow history of this user and this user's leaders
        $follow_history = followhistory::latest_from($this->uid, NEWSFEED_NUM_ENTRIES);
        while ($row = $follow_history->fetch_assoc()) {
            $this->entries[] = new followhistory($row['user_id'], $row['leader_user_id'], $row['is_follow'], $row['stamp']);
        }

        // 8) now sort the entries by stamp desc
        usort($this->entries, array(__CLASS__, 'entry_comparer'));
        $this->entries = array_reverse($this->entries);

        // 9) now get just the top 10 entries
        $this->entries = array_slice($this->entries, 0, NEWSFEED_NUM_ENTRIES);

    }

    private static function entry_comparer($a, $b) {
        return strcmp($a->get_stamp(), $b->get_stamp());
    }

}

?>