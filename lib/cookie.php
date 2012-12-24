<?php

// static class to help manage cookie key/val pairs, so only one cookie is ever set
class cookie {

    // all key value pairs stored in the cookie
    private static $data = array();

    // have the cookies been loaded?
    private static $is_loaded = false;

    public static function set($key, $val) {
        self::load_cookies();
        self::$data[$key] = $val;
        self::save();
    }

    public static function delete($key) {
        self::load_cookies();
        unset(self::$data[$key]);
        self::save();
    }

    public static function get($key) {
        self::load_cookies();
        return isset(self::$data[$key]) ? self::$data[$key] : null;
    }

    private static function load_cookies() {
        if (!self::$is_loaded) {
            if (!empty($_COOKIE[COOKIE_NAME])) {
                self::$data = @unserialize(str_decrypt(COOKIE_SECRET, $_COOKIE[COOKIE_NAME]));
                // unserialization failed
                if (false === self::$data) {
                    self::$data = array();
                    self::save();
                }
                self::$is_loaded = true;
            }
        }
    }

    // save cookie data for 7 days
    private static function save() {
        // if we actually have data to store
        if (count(self::$data)) {
            $data = serialize(self::$data);
            $data = str_encrypt(COOKIE_SECRET, $data);
            setcookie(COOKIE_NAME, $data, time() + 7*86400, '/');
        } else {
            // delete the cookie
            setcookie(COOKIE_NAME, '', time() - 7*86400, '/');
        }
    }
}

?>
