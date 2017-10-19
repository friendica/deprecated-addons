<?php

/*
To-Do:
 - like empfangen
 - Links besser auflösen

Testen:

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

//require_once("addon/appnet/appnet.php");

$uid = 1;
appnet_fetchstream($a, $uid);

function appnet_fetchstream($a, $uid) {
	require_once("addon/appnet/AppDotNet.php");
	require_once('include/items.php');

	$token = get_pconfig($uid,'appnet','token');
	$clientId     = get_pconfig($uid,'appnet','clientid');
	$clientSecret = get_pconfig($uid,'appnet','clientsecret');

	$app = new AppDotNet($clientId, $clientSecret);
	$app->setAccessToken($token);

	$r = q("SELECT * FROM `contact` WHERE `self` = 1 AND `uid` = %d LIMIT 1",
		intval($uid));

	if(count($r))
		$me = $r[0];
	else {
		logger("appnet_fetchstream: Own contact not found for user ".$uid, LOGGER_DEBUG);
		return;
	}

        $user = q("SELECT * FROM `user` WHERE `uid` = %d AND `account_expired` = 0 LIMIT 1",
                intval($uid)
        );

	if(count($user))
		$user = $user[0];
	else {
		logger("appnet_fetchstream: Own user not found for user ".$uid, LOGGER_DEBUG);
                return;
	}

	$ownid = get_pconfig($uid,'appnet','ownid');

	$param = array("include_annotations" => true);
	$post = $app->getPost(32189565, $param);
	//$post = $app->getPost(32166492, $param);
	//$post = $app->getPost(32166065, $param);
	//$post = $app->getPost(32161780, $param);
	$postarray = appnet2_createpost($a, $uid, $post, $me, $user, $ownid, false);
print_r($postarray);
//	$item = item_store($postarray);
	die();


	// Fetch stream
	$param = array("count" => 200, "include_deleted" => false, "include_directed_posts" => true, "include_html" => false, "include_annotations" => true);

	$lastid  = get_pconfig($uid, 'appnet', 'laststreamid');

	if ($lastid <> "")
		$param["since_id"] = $lastid;

	try {
		$stream = $app->getUserStream($param);
	}
	catch (AppDotNetException $e) {
		logger("appnet_fetchstream: Error fetching stream for user ".$uid);
	}

	$stream = array_reverse($stream);
	foreach ($stream AS $post) {
		$postarray = appnet_createpost($a, $uid, $post, $me, $user, $ownid, true);

		$item = item_store($postarray);
		logger('appnet_fetchstream: User '.$uid.' posted stream item '.$item);

		$lastid = $post["id"];

		if (($item != 0) AND ($postarray['contact-id'] != $me["id"])) {
			$r = q("SELECT `thread`.`iid` AS `parent` FROM `thread`
				INNER JOIN `item` ON `thread`.`iid` = `item`.`parent` AND `thread`.`uid` = `item`.`uid`
				WHERE `item`.`id` = %d AND `thread`.`mention` LIMIT 1", dbesc($item));

			if (count($r)) {
				require_once('include/enotify.php');
				notification(array(
					'type'         => NOTIFY_COMMENT,
                        	        'notify_flags' => $user['notify-flags'],
                                	'language'     => $user['language'],
	                                'to_name'      => $user['username'],
        	                        'to_email'     => $user['email'],
                	                'uid'          => $user['uid'],
                        	        'item'         => $postarray,
                                	'link'         => $a->get_baseurl() . '/display/' . $user['nickname'] . '/' . $item,
	                                'source_name'  => $postarray['author-name'],
        	                        'source_link'  => $postarray['author-link'],
                	                'source_photo' => $postarray['author-avatar'],
                        	        'verb'         => ACTIVITY_POST,
                                	'otype'        => 'item',
	                                'parent'       => $r[0]["parent"],
        	                ));
			}
		}
	}

	set_pconfig($uid, 'appnet', 'laststreamid', $lastid);

	// Fetch mentions
	$param = array("count" => 200, "include_deleted" => false, "include_directed_posts" => true, "include_html" => false, "include_annotations" => true);

	$lastid  = get_pconfig($uid, 'appnet', 'lastmentionid');

	if ($lastid <> "")
		$param["since_id"] = $lastid;

	try {
		$mentions = $app->getUserMentions("me", $param);
	}
	catch (AppDotNetException $e) {
		logger("appnet_fetchstream: Error fetching mentions for user ".$uid);
	}

	$mentions = array_reverse($mentions);
	foreach ($mentions AS $post) {
		$postarray = appnet_createpost($a, $uid, $post, $me, $user, $ownid, false);

		if (isset($postarray["id"]))
			$item = $postarray["id"];
		elseif (isset($postarray["body"])) {
			$item = item_store($postarray);
			logger('appnet_fetchstream: User '.$uid.' posted mention item '.$item);
		} else
			$item = 0;

		$lastid = $post["id"];

		if ($item != 0) {
			require_once('include/enotify.php');
			notification(array(
				'type'         => NOTIFY_TAGSELF,
                                'notify_flags' => $user['notify-flags'],
                                'language'     => $user['language'],
                                'to_name'      => $user['username'],
                                'to_email'     => $user['email'],
                                'uid'          => $user['uid'],
                                'item'         => $postarray,
                                'link'         => $a->get_baseurl() . '/display/' . $user['nickname'] . '/' . $item,
                                'source_name'  => $postarray['author-name'],
                                'source_link'  => $postarray['author-link'],
                                'source_photo' => $postarray['author-avatar'],
                                'verb'         => ACTIVITY_TAG,
                                'otype'        => 'item'
                        ));
		}
	}

	set_pconfig($uid, 'appnet', 'lastmentionid', $lastid);


/* To-Do
	$param = array("interaction_actions" => "star");
	$interactions = $app->getMyInteractions($param);
	foreach ($interactions AS $interaction)
		appnet_dolike($a, $uid, $interaction);
*/
}

function appnet2_createpost($a, $uid, $post, $me, $user, $ownid, $createuser, $threadcompletion = true) {
	require_once('include/items.php');

	if ($post["machine_only"])
		return;

	if ($post["is_deleted"])
		return;

	$postarray = array();
	$postarray['gravity'] = 0;
	$postarray['uid'] = $uid;
	$postarray['wall'] = 0;
	$postarray['verb'] = ACTIVITY_POST;
	$postarray['network'] =  dbesc(NETWORK_APPNET);
	$postarray['uri'] = "adn::".$post["id"];

	$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
		dbesc($postarray['uri']),
		intval($uid)
		);

//	if (count($r))
//		return($r[0]);

	$r = q("SELECT * FROM `item` WHERE `extid` = '%s' AND `uid` = %d LIMIT 1",
		dbesc($postarray['uri']),
		intval($uid)
		);

//	if (count($r))
//		return($r[0]);

	$postarray['parent-uri'] = "adn::".$post["thread_id"];
	if (isset($post["reply_to"]) AND ($post["reply_to"] != "")) {
		$postarray['thr-parent'] = "adn::".$post["reply_to"];

		// Complete the thread if the parent doesn't exists
		if ($threadcompletion) {
			$r = q("SELECT * FROM `item` WHERE `uri` = '%s' AND `uid` = %d LIMIT 1",
				dbesc($postarray['thr-parent']),
				intval($uid)
				);
			if (!count($r)) {
				require_once("addon/appnet/AppDotNet.php");

				$token = get_pconfig($uid,'appnet','token');
				$clientId     = get_pconfig($uid,'appnet','clientid');
				$clientSecret = get_pconfig($uid,'appnet','clientsecret');

				$app = new AppDotNet($clientId, $clientSecret);
				$app->setAccessToken($token);

				$param = array("count" => 200, "include_deleted" => false, "include_directed_posts" => true, "include_html" => false, "include_annotations" => true);
				try {
					$thread = $app->getPostReplies($post["thread_id"], $param);
				}
				catch (AppDotNetException $e) {
					logger("appnet_createpost: Error fetching thread for user ".$uid);
				}
				$thread = array_reverse($thread);
				foreach ($thread AS $tpost) {
					$threadpost = appnet2_createpost($a, $uid, $tpost, $me, $user, $ownid, $createuser, false);
					$item = item_store($threadpost);
				}
			}
		}
	} else
		$postarray['thr-parent'] = $postarray['uri'];

	$postarray['plink'] = $post["canonical_url"];

	if ($post["user"]["id"] != $ownid) {
		$postarray['owner-name'] = $post["user"]["name"];
		$postarray['owner-link'] = $post["user"]["canonical_url"];
		$postarray['owner-avatar'] = $post["user"]["avatar_image"]["url"];
		$postarray['contact-id'] = appnet_fetchcontact($a, $uid, $post["user"], $me, $createuser);
	} else {
		$postarray['owner-name'] = $me["name"];
		$postarray['owner-link'] = $me["url"];
		$postarray['owner-avatar'] = $me["thumb"];
		$postarray['contact-id'] = $me["id"];
	}

	$links = array();

	if (is_array($post["repost_of"])) {
		$postarray['author-name'] = $post["repost_of"]["user"]["name"];
		$postarray['author-link'] = $post["repost_of"]["user"]["canonical_url"];
		$postarray['author-avatar'] = $post["repost_of"]["user"]["avatar_image"]["url"];

		$content = $post["repost_of"];
	} else {
		$postarray['author-name'] = $postarray['owner-name'];
		$postarray['author-link'] = $postarray['owner-link'];
		$postarray['author-avatar'] = $postarray['owner-avatar'];

		$content = $post;
	}

	if (is_array($content["entities"])) {
		$converted = appnet_expand_entities($a, $content["text"], $content["entities"]);
		$postarray['body'] = $converted["body"];
		$postarray['tag'] = $converted["tags"];
	} else
		$postarray['body'] = $content["text"];

	if (is_array($content["annotations"]))
		$postarray['body'] = appnet_expand_annotations($a, $postarray['body'], $content["annotations"]);

	if (sizeof($content["entities"]["links"]))
		foreach($content["entities"]["links"] AS $link) {
			$url = normalise_link($link["url"]);
			$links[$url] = $url;
		}

	if (sizeof($content["annotations"]))
		foreach($content["annotations"] AS $annotation) {
			if (isset($annotation["value"]["embeddable_url"])) {
				$url = normalise_link($annotation["value"]["embeddable_url"]);
				if (isset($links[$url]))
					unset($links[$url]);
			}
		}

	if (sizeof($links)) {
		$link = array_pop($links);
		$url = "[url=".$link."]".$link."[/url]";

		$removedlink = trim(str_replace($url, "", $postarray['body']));

		if (($removedlink == "") OR strstr($postarray['body'], $removedlink))
			$postarray['body'] = $removedlink;

		$postarray['body'] .= add_page_info($link);
	}

	$postarray['created'] = datetime_convert('UTC','UTC',$post["created_at"]);
	$postarray['edited'] = datetime_convert('UTC','UTC',$post["created_at"]);

	$postarray['app'] = $post["source"]["name"];

	return($postarray);
	//print_r($postarray);
	//print_r($post);
}

function appnet_expand_entities($a, $body, $entities) {

	if (!function_exists('substr_unicode')) {
		function substr_unicode($str, $s, $l = null) {
			return join("", array_slice(
				preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $s, $l));
		}
	}

	$tags_arr = array();
	$replace = array();

	foreach ($entities["mentions"] AS $mention) {
		$url = "@[url=https://alpha.app.net/".rawurlencode($mention["name"])."]".$mention["name"]."[/url]";
		$tags_arr["@".$mention["name"]] = $url;
		$replace[$mention["pos"]] = array("pos"=> $mention["pos"], "len"=> $mention["len"], "replace"=> $url);
	}

	foreach ($entities["hashtags"] AS $hashtag) {
		$url = "#[url=".$a->get_baseurl()."/search?tag=".rawurlencode($hashtag["name"])."]".$hashtag["name"]."[/url]";
		$tags_arr["#".$hashtag["name"]] = $url;
		$replace[$hashtag["pos"]] = array("pos"=> $hashtag["pos"], "len"=> $hashtag["len"], "replace"=> $url);
	}

	foreach ($entities["links"] AS $links) {
		$url = "[url=".$links["url"]."]".$links["text"]."[/url]";
		$replace[$links["pos"]] = array("pos"=> $links["pos"], "len"=> $links["len"], "replace"=> $url);
	}


	if (sizeof($replace)) {
		krsort($replace);
		foreach ($replace AS $entity) {
			$pre = substr_unicode($body, 0, $entity["pos"]);
			$post = substr_unicode($body, $entity["pos"] + $entity["len"]);

			$body = $pre.$entity["replace"].$post;
		}
	}

	return(array("body" => $body, "tags" => implode($tags_arr, ",")));
}

function appnet_expand_annotations($a, $body, $annotations) {
	foreach ($annotations AS $annotation) {
		if ($annotation["value"]["type"] == "photo") {
			if (($annotation["value"]["thumbnail_large_url"] != "") AND ($annotation["value"]["url"] != ""))
				$body .= "\n[url=".$annotation["value"]["url"]."][img]".$annotation["value"]["thumbnail_large_url"]."[/img][/url]";
			elseif ($annotation["value"]["url"] != "")
				$body .= "\n[img]".$annotation["value"]["url"]."[/img]";
		}
	}
	return $body;
}

function appnet_fetchcontact($a, $uid, $contact, $me, $create_user) {
	$r = q("SELECT * FROM `contact` WHERE `uid` = %d AND `alias` = '%s' LIMIT 1",
		intval($uid), dbesc("adn::".$contact["id"]));

	if(!count($r) AND !$create_user)
		return($me);


	if (count($r) AND ($r[0]["readonly"] OR $r[0]["blocked"])) {
		logger("appnet_fetchcontact: Contact '".$r[0]["nick"]."' is blocked or readonly.", LOGGER_DEBUG);
		return(-1);
	}

	if(!count($r)) {
		// create contact record
		q("INSERT INTO `contact` (`uid`, `created`, `url`, `nurl`, `addr`, `alias`, `notify`, `poll`,
					`name`, `nick`, `photo`, `network`, `rel`, `priority`,
					`writable`, `blocked`, `readonly`, `pending` )
					VALUES ( %d, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %d, %d, %d, 0, 0, 0 ) ",
			intval($uid),
			dbesc(datetime_convert()),
			dbesc($contact["canonical_url"]),
			dbesc(normalise_link($contact["canonical_url"])),
			dbesc($contact["username"]."@app.net"),
			dbesc("adn::".$contact["id"]),
			dbesc(''),
			dbesc("adn::".$contact["id"]),
			dbesc($contact["name"]),
			dbesc($contact["username"]),
			dbesc($contact["avatar_image"]["url"]),
			dbesc(NETWORK_APPNET),
			intval(CONTACT_IS_FRIEND),
			intval(1),
			intval(1)
		);

		$r = q("SELECT * FROM `contact` WHERE `alias` = '%s' AND `uid` = %d LIMIT 1",
			dbesc("adn::".$contact["id"]),
			intval($uid)
			);

		if(! count($r))
			return(false);

		$contact_id  = $r[0]['id'];

		$g = q("SELECT def_gid FROM user WHERE uid = %d LIMIT 1",
			intval($uid)
		);

		if($g && intval($g[0]['def_gid'])) {
			require_once('include/group.php');
			group_add_member($uid,'',$contact_id,$g[0]['def_gid']);
		}

		require_once("Photo.php");

		$photos = import_profile_photo($contact["avatar_image"]["url"],$uid,$contact_id);

		q("UPDATE `contact` SET `photo` = '%s',
					`thumb` = '%s',
					`micro` = '%s',
					`name-date` = '%s',
					`uri-date` = '%s',
					`avatar-date` = '%s'
				WHERE `id` = %d",
			dbesc($photos[0]),
			dbesc($photos[1]),
			dbesc($photos[2]),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			dbesc(datetime_convert()),
			intval($contact_id)
		);

	} else {
		// update profile photos once every two weeks as we have no notification of when they change.

		//$update_photo = (($r[0]['avatar-date'] < datetime_convert('','','now -2 days')) ? true : false);
		$update_photo = ($r[0]['avatar-date'] < datetime_convert('','','now -12 hours'));

		// check that we have all the photos, this has been known to fail on occasion

		if((! $r[0]['photo']) || (! $r[0]['thumb']) || (! $r[0]['micro']) || ($update_photo)) {

			logger("appnet_fetchcontact: Updating contact ".$contact["username"], LOGGER_DEBUG);

			require_once("Photo.php");

			$photos = import_profile_photo($contact["avatar_image"]["url"], $uid, $r[0]['id']);

			q("UPDATE `contact` SET `photo` = '%s',
						`thumb` = '%s',
						`micro` = '%s',
						`name-date` = '%s',
						`uri-date` = '%s',
						`avatar-date` = '%s',
						`url` = '%s',
						`nurl` = '%s',
						`addr` = '%s',
						`name` = '%s',
						`nick` = '%s'
					WHERE `id` = %d",
				dbesc($photos[0]),
				dbesc($photos[1]),
				dbesc($photos[2]),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc(datetime_convert()),
				dbesc($contact["canonical_url"]),
				dbesc(normalise_link($contact["canonical_url"])),
				dbesc($contact["username"]."@app.net"),
				dbesc($contact["username"]),
				dbesc($contact["name"]),
				intval($r[0]['id'])
			);
		}
	}

	return($r[0]["id"]);
}

// starPost
// unstarPost
// repost
// deleteRepost
