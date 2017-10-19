<?php

/*

To-Do:

*/
require_once("boot.php");

if(@is_null($a)) {
	$a = new App;
}

@include(".htconfig.php");
require_once("dba.php");
dba::connect($db_host, $db_user, $db_pass, $db_data);
unset($db_host, $db_user, $db_pass, $db_data);

$a->set_baseurl(get_config('system','url'));

require_once("addon/appnet/appnet.php");
require_once("include/plaintext.php");

$b['uid'] = 1;

$token = get_pconfig($b['uid'],'appnet','token');

require_once 'addon/appnet/AppDotNet.php';

$clientId     = get_pconfig($b["uid"],'appnet','clientid');
$clientSecret = get_pconfig($b["uid"],'appnet','clientsecret');

$app = new AppDotNet($clientId, $clientSecret);
$app->setAccessToken($token);

//$param = array("include_annotations" => true);
//$param = array("include_muted" => true, "include_directed_posts" => true);
$param = array("include_muted" => true, "include_deleted" => false, "include_directed_posts" => true,
		"include_html" => false, "include_post_annotations" => true);


//$param = array("include_post_annotations" => true, "include_muted" => true, "include_directed_posts" => true);
//$post = $app->getPost(37154801, $param);
$post = $app->getPost(37189594, $param);
//$post = $app->getPost(36892980, $param);
//$post = $app->getPost(36837961, $param);
//$post = $app->getPost(36843534, $param);

print_r($post);
die();

$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
	intval($b['uid']));

if(count($r))
	$me = $r[0];

$ownid =  get_pconfig($b['uid'],'appnet','ownid');

$user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
                intval($b['uid'])
        );

        if(count($user))
                $user = $user[0];

$test = appnet_createpost($a, $b['uid'], $post, $me, $user, $ownid, true, false, true);

print_r($test);

die();



/*
$recycle = html_entity_decode("&#x2672; ", ENT_QUOTES, 'UTF-8');

$post = "♲ AdoraBelle (_Adora_Belle_@twitter.com): They are a little tied up... *rofl* @Shysarah2009 @AldiCustCare";

$post = str_replace($recycle, ">> ", $post);
//$post = preg_replace("=".$recycle." (.*) \((.*)@(.*)\)=ism", ">> $1 ($2@$3)", $ppost);
//$post = preg_replace("=".$recycle."(.*)=ism", ">> $1", $ppost);

die($post);
*/
$b["uid"] = 1;
$b["plink"] = "https://pirati.ca/display/heluecht/2834617";
//$b["title"] = "Wenn sich Microsoft per Telefon meldet, sollte man stutzig werden.";

// Image
$b["body"] = "Nur ein kleiner Test, bitte ignorieren. (wird sowieso sofort wieder gelöscht)
[url=https://lh3.googleusercontent.com/-5J1tGHGvELQ/U2EL_6RuAHI/AAAAAAAAX5U/71dlHNFUjXw/30.04.14%2B-%2B1][img=479x640]https://lh3.googleusercontent.com/-5J1tGHGvELQ/U2EL_6RuAHI/AAAAAAAAX5U/71dlHNFUjXw/w506-h750/30.04.14%2B-%2B1[/img]
[/url]";

/*
$b["body"] = "Übrigens: Früher #[url=http://www.dabo.de]war[/url] alles besser.

[bookmark=https://www.youtube.com/watch?v=-8yF9zqlpR4]SALTATIO MORTIS - Früher war alles besser | Napalm Records[/bookmark]";
*/
$b["body"] = "Umfrageergebnisse aus der [url=http://www.heise.de]Hölle.[/url] In [url=http://www.heise.de]Deutschland[/url] wäre das Ergebnis sicherlich ähnlich.
[class=type-link][bookmark=http://www.zeit.de/gesellschaft/zeitgeschehen/2014-05/oesterreich-studie-fuehrer]Studie: Ein Drittel der Österreicher will einen starken Führer[/bookmark]
[img]http://images.zeit.de/politik/ausland/2014-05/oesterreich-umfrage/oesterreich-umfrage-540x304.jpg[/img]
[quote]Wahlen? Parlament? Nicht so wichtig, sagen viele Österreicher laut einer Umfrage. Sie wollen einen Führer, der sich um Demokratie nicht kümmern muss.[/quote]
[/class]";

$b['postopts'] = "appnet";
/*
$b["body"] = "Dies ist ein Testposting, dass wieder gelöscht werden wird.";
*/
$b["body"] = "\"This is the end ...\"

[url=https://pirati.ca/photos/heluecht/image/4ccfc897bf2ab350e0fcce93078365f5][img]https://pirati.ca/photo/4ccfc897bf2ab350e0fcce93078365f5-2.jpg[/img][/url]";

$b["body"] = "[share author='Lukas' profile='https://alpha.app.net/phasenkasper' avatar='https://d2rfichhc2fb9n.cloudfront.net/image/5/1kT9xKMb9JyBVTCBnDHEaHLRUnd7InMiOiJzMyIsImIiOiJhZG4tdXNlci1hc3NldHMiLCJrIjoiYXNzZXRzL3VzZXIvZjkvM2EvNjAvZjkzYTYwMDAwMDAwMDAwMC5wbmciLCJvIjoiIn0' link='https://alpha.app.net/phasenkasper/post/32422435' posted='2014-06-12 11:42:18']
Ich bin immer wieder begeistern wie toll mein Windows läuft. [url=https://photos.app.net/32422435/1]photos.app.net/32422435/1[/url] 
[img]https://files.app.net/1/1304673/aHwho5GfB2iXEXGGET4V3lOZVUZ5gyfFNI_CChgQ_iHYTs9sooUCIIMa3MPjLx4DHeFm3qCqEyIlo3ucFM2GDgr5SAHhJcXplNPqYGCzBxx4WP0rKxQAY65YE_tgBTaaxR5f6yMM3RzMBV6ooSH0y6zEmF0yRc6EEgn1WFaddqrSRb5XzT8ThiIspzQOy9b6m[/img][/share]";

$b["body"] = "Ein A380 ist jetzt nicht unbedingt das unwendigste Flugzeug, habe ich das Gefühl.
[share author='Javier Salgado' profile='https://plus.google.com/101295635357824725690' avatar='https://lh6.googleusercontent.com/-uE1alnITTco/AAAAAAAAAAI/AAAAAAAAAcc/jXjCG51oQfg/photo.jpg?sz=50' link='https://plus.google.com/101295635357824725690/posts/CjLEs9pSWFV']Unbelievable Airbus A380 vertical Take-off + Amaz…: http://youtu.be/RJxnwF-MPi0
[class=type-video][bookmark=http://youtu.be/RJxnwF-MPi0]Unbelievable Airbus A380 vertical Take-off + Amazing Air Show ( HD ) Paris Air show 2013[/bookmark]
[/class][/share]";

/*
	require_once("include/plaintext.php");

$post = plaintext($a, $b, 256, false, 6);

print_r($post);

die();
*/

/*
$url = "https://pirati.ca/photos/heluecht/image/54d898d7e1a8e9ba032a5fa352f51862";

require_once("mod/parse_url.php");
$data = parseurl_getsiteinfo($url, true);

print_r($data);

die();
*/

//$id = 3949352;
$id = 3949512;
$r = q("SELECT * FROM `item` WHERE `id` = %d", intval($id));
$b = $r[0];

$b['postopts'] = "appnet";

//$data = get_attached_data($b["body"]);
//print_r($data);

$post = plaintext($a, $b, 256, false, 6);

print_r($post);

$data = appnet_create_entities($a, $b, $post);

print_r($data);

die();

appnet_send($a, $b);
die();


$token = get_pconfig($b['uid'],'appnet','token');

require_once 'addon/appnet/AppDotNet.php';

$clientId     = get_pconfig($b["uid"],'appnet','clientid');
$clientSecret = get_pconfig($b["uid"],'appnet','clientsecret');

$app = new AppDotNet($clientId, $clientSecret);
$app->setAccessToken($token);

//$param = array("include_annotations" => true);
$param = array("include_muted" => true, "include_directed_posts" => true);
//$param = array("include_post_annotations" => true, "include_muted" => true, "include_directed_posts" => true);
//$post = $app->getPost(32236571, $param);
//$post = $app->getPost(32237235, $param);
//$post = $app->getPost(32217767, $param);
//$post = $app->getPost(32203349, $param);
//$post = $app->getPost(32239275, $param);
//$post = $app->getPost(32261367, $param);
//$post = $app->getPost(32306954, $param);
$post = $app->getPost(32926285, $param);

print_r($post);

die();

$lastid = @file_get_contents("addon/appnet/lastid.txt");
$clients = @file_get_contents("addon/appnet/clients.txt");
$users = @file_get_contents("addon/appnet/users.txt");

if ($lastid != "")
	$param["since_id"] = $lastid;

$posts = $app->getPublicPosts($param);

foreach ($posts AS $post) {
	$lastid = $post["id"];

	if ((count($post["entities"]["mentions"]) == 0) AND !strstr($clients, $post["source"]["client_id"]))
		continue;

	if ((count($post["entities"]["mentions"]) > 0) AND !strstr($clients, $post["source"]["client_id"]))
		$clients .= $post["source"]["client_id"]." - ".$post["source"]["link"]." - ".$post["source"]["name"]."\n";

	if (!strstr($userss, $post["user"]["canonical_url"]))
		$users .= $post["user"]["canonical_url"]." - ".$post["user"]["username"]."\n";

	echo $post["source"]["link"]." ".$post["source"]["name"]."\n";
	echo $post["user"]["username"]."\n";
	echo $post["text"]."\n";
	//print_r($post["entities"]["mentions"]);
	echo $post["id"]."\n";
	echo "---------------------------------\n";
}

file_put_contents("addon/appnet/lastid.txt", $lastid);
file_put_contents("addon/appnet/clients.txt", $clients);
file_put_contents("addon/appnet/users.txt", $users);

/*
       try {
$post = $app->getPost(323069541111, $param);
        }
        catch (AppDotNetException $e) {
		print_r(appnet_error($e->getMessage()));
        }
*/

//print_r($post);
die();

$data = array();
$data["annotations"][] = array(
                               "type" => "net.app.core.crosspost",
                               "value" => array(
					"canonical_url" => $b["plink"]
                                                )
                              );

$data["annotations"][] = array(
                               "type" => "com.friendica.post",
                               "value" => array(
					"raw" => $b["body2"]
                                                )
                              );

$ret = $app->createPost($b["body"], $data);

print_r($ret);
