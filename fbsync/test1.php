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

$feed = file_get_contents("/home/ike/friendica-data/fb.1");
$json = json_decode($feed);
//print_r($json);

$uid = 1;
$access_token = get_pconfig($uid,'facebook','access_token');

foreach ($json->data[0]->fql_result_set AS $post) {
	//print_r($post);
$type = "";
$content = "";

	if (isset($post->attachment->media) AND (($type == "") OR ($type == "link"))) {
                foreach ($post->attachment->media AS $media) {

			$image = "";

                        if (isset($media->type))
                                $type = $media->type;

			if (isset($media->src))
				$image = $media->src;

			if (isset($media->photo)) {
				if (isset($media->photo->images) AND (count($media->photo->images) > 1))
					$image = $media->photo->images[1]->src;

echo "\n-------------------------------------------------\n";
				//print_r($media->photo);
				$url = "https://graph.facebook.com/v2.0/".$media->photo->fbid."/?access_token=".$access_token;

        			$feed = fetch_url($url);
        			$data = json_decode($feed);
				if (isset($data->images))
					$image = $data->images[0]->source;
echo "\n-------------------------------------------------\n";

			}

			if(isset($media->href) AND ($image != "") AND ($media->href != ""))
				$content .= "\n".'[url='.$media->href.'][img]'.$image.'[/img][/url]';
			else {
				if ($image != "")
					$content .= "\n".'[img]'.$image.'[/img]';

				// if just a link, it may be a wall photo - check
				if (isset($post->link))
					$content .= fbpost_get_photo($media->href);
			}
			die($content."\n");
		}
	}
}

?>
