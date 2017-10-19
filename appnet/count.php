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

$token = get_pconfig($b['uid'],'appnet','token');

require_once 'addon/appnet/AppDotNet.php';

$clientId     = get_pconfig($b["uid"],'appnet','clientid');
$clientSecret = get_pconfig($b["uid"],'appnet','clientsecret');

$app = new AppDotNet($clientId, $clientSecret);
$app->setAccessToken($token);

$param = array("include_muted" => true, "include_directed_posts" => true, "count" => 3000);

$lastid = @file_get_contents("addon/appnet/lastid.txt");
$clients = @file_get_contents("addon/appnet/clients.txt");
$users = @file_get_contents("addon/appnet/users.txt");

if ($lastid != "")
	$param["since_id"] = $lastid;

$posts = $app->getPublicPosts($param);

foreach ($posts AS $post) {
	if ($lastid < $post["id"])
		$lastid = $post["id"];

	if (!isset($post["reply_to"]) AND !strstr($clients, $post["source"]["client_id"]))
		continue;

	if (isset($post["reply_to"]) AND !strstr($clients, $post["source"]["client_id"]))
		$clients .= $post["source"]["client_id"]." - ".$post["source"]["link"]." - ".$post["source"]["name"]."\n";

	if (!strstr($users, $post["user"]["canonical_url"]))
		$users .= $post["user"]["canonical_url"]." - ".$post["user"]["name"]."\n";

	//echo $post["source"]["link"]." ".$post["source"]["name"]."\n";
	//echo $post["user"]["name"]."\n";
	//echo $post["text"]."\n";
	//echo $post["canonical_url"]."\n";
	//print_r($post["user"]);
	//echo "---------------------------------\n";

}

file_put_contents("addon/appnet/lastid.txt", $lastid);
file_put_contents("addon/appnet/clients.txt", $clients);
file_put_contents("addon/appnet/users.txt", $users);
