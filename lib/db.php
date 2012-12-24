<?php

class db {

    private static $db;
    public static $num_queries = 0;
    
    public static function query($sql, $args=array()) {
        $db = self::get();
        $args = func_get_args();
        $sql = array_shift($args);
        if (isset($args[0])) {
            if (is_array($args[0])) {
                $args = $args[0];
            } else {
                foreach ($args as $key => $val) {
                    $args[$key] = $db->escape_string($val);
                }
            }
        }
        if (count($args) > 0) {
            $sql = vsprintf($sql, $args);
        }
        // increment the db query count
        self::$num_queries++;
        #v($sql);
        $result = $db->query($sql);
        if (!$result) {
            error('SQL error: ' . $db->error);
        }
        return $result;
    }
    
    public static function fetch_query($sql, $args=array()) {
        $args = func_get_args();
        $sql = array_shift($args);
        return self::query($sql, $args)->fetch_assoc();
    }

    // assumes either one or two columns
    // if one column, then returns an array of all the rows in that column
    // if two columns, then retuns a hash where the 1st row is the key and 2nd row is value (beware of duplicate keys!)
    public static function col_query($sql, $args=array()) {
        $args = func_get_args();
        $sql = array_shift($args);
        $res = self::query($sql, $args);
        $out = array();
        while ($row = $res->fetch_row()) {
            if ($res->field_count == 1) {
                $out[] = $row[0];
            } else {
                $out[$row[0]] = $row[1];
            }
        }
        return $out;
    }

    // whether the query returns at least one row
    public static function has_row($sql, $args=array()) {
        $args = func_get_args();
        $sql = array_shift($args);
        $res = self::query($sql, $args);
        return $res->num_rows > 0;
    }

    public static function result_query($sql, $args=array()) {
        $args = func_get_args();
        $sql = array_shift($args);
        $row = self::query($sql, $args)->fetch_row();
        if (!is_null($row)) {
            return $row[0];
        }
        return $row;
    }

    public static function escape($val) {
        return self::get()->escape_string($val);
    }

    public static function insert_id() {
        return self::get()->insert_id;
    }

    public static function affected_rows() {
        return self::get()->affected_rows;
    }

    private static function get() {
        if (!self::$db) {
            self::$db = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
            if (!self::$db) {
                error('failed to obtain db connection');
            }
        }
        return self::$db;
    }

}

?>
