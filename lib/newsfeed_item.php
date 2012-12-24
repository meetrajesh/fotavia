<?php

interface newsfeed_item {

    public function get_stamp();
    public function get_owner_id();
    public function get_target_id();
    public function get_item_type();

}

?>