<?php

require_once("boot.php");

if(@is_null($a)) {
        $a = new App;
}

@include(".htconfig.php");
require_once("dba.php");
dba::connect($db_host, $db_user, $db_pass, $db_data);
unset($db_host, $db_user, $db_pass, $db_data);

$a->set_baseurl(get_config('system','url'));

require_once("addon/fbsync/fbsync.php");

$uid = 90;

fbsync_get_self($uid);

fbsync_fetchfeed($a, $uid);

?>
