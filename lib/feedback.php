<?php

class feedback {

    public static function add_to_db($name, $email, $msg) {
        $userid = user::has_active() ? user::active()->get_id() : 0;
        $sql = 'INSERT INTO feedback_form (logged_in_userid, name, email, msg) VALUES (%d, "%s", "%s", "%s")';
        db::query($sql, $userid, $name, $email, $msg);
    }

    public static function get_top_n($n, $include_reviewed=false) {
        $where = $include_reviewed ? '1' : 'is_reviewed=0';
        return db::query('SELECT id, logged_in_userid, name, email, msg, admin_comment, is_reviewed, UNIX_TIMESTAMP(time) AS stamp 
                          FROM feedback_form WHERE %s ORDER BY stamp DESC LIMIT %d', $where, $n);
    }

}

?>

