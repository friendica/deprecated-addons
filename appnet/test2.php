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

$items = q("select * from item where id=3602849");

$b = $items[0];
$b["body"] = "[vimeo]http://vimeo.com/91121293[/vimeo]";

$b["body"] = "[share author='KueltuertageAugsburg' profile='https://twitter.com/KueltuertageA' avatar='https://pbs.twimg.com/profile_images/1575855154/logo_kueltuerverein_brief_normal.jpg' link='https://twitter.com/KueltuertageA/status/424977769080958976']Da will eine 'BÃ¼rgerinitiative AuslÃ¤nderstopp' in #Augsburg zur [url=http://www.heise.de]Kommunalwahl[/url] antreten.[/share]";


require_once("include/plaintext.php");
require_once("addon/appnet/appnet.php");
$post = plaintext($a, $b, 256, false, 6);
$text = appnet_create_entities($a, $b, $post);

//print_r($post);
echo $text;
