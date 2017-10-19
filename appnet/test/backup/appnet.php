<?php
/*
 * login_with_buffer.php
 *
 * @(#) $Id: login_with_buffer.php,v 1.1 2014/03/17 09:45:08 mlemos Exp $
 *
 */

	/*
	 *  Get the http.php file from http://www.phpclasses.org/httpclient
	 */
	require('http.php');
	require('oauth_client.php');

	$client = new oauth_client_class;
	$client->debug = true;
	$client->debug_http = true;
	$client->server = '';

	$client->oauth_version = '2.0';
	$client->dialog_url = 'https://account.app.net/oauth/authenticate?client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&scope={SCOPE}&response_type=code&state={STATE}';
	$client->access_token_url = 'https://account.app.net/oauth/access_token';

	$client->redirect_uri = 'https://'.$_SERVER['HTTP_HOST'].
		dirname(strtok($_SERVER['REQUEST_URI'],'?')).'/appnet.php';

	$client->client_id = 'js4qF6UN4fwXTK87Ax9Bjf3DhEQuK5hA'; $application_line = __LINE__;
	$client->client_secret = 'Z4hsLHh82d5cQAwNVD2uZtNg3WqFxLXF ';

	if(strlen($client->client_id) == 0
	|| strlen($client->client_secret) == 0)
		die('Please create an application in App.net Apps page '.
			'https://bufferapp.com/developers/apps/create '.
			' and in the line '.$application_line.
			' set the client_id to Client ID and client_secret with Client'.
			' Secret');

	//$client->access_token = 'AQAAAAAACzfmWzVa5o69CFJrV-fBt9PLkV9sd9_0BnnHTI02_NGvvsZDCgz-38eA5_yAgu9AwaFcUzFp0qdCj4y2svy6qUl42g';

	/* API permissions
	 */
	$client->scope = '';
	if(($success = $client->Initialize()))
	{
		if(($success = $client->Process()))
		{
			if(strlen($client->access_token))
			{;
				$success = $client->CallAPI(
					'https://api.app.net/users/me',
					'GET', array(), array('FailOnAccessError'=>true, 'RequestBody'=>true), $user);
/*
				$params["text"] = "Nur ein Test";
				$params["profile_ids"][] = "52b844df9db82271330000b8";
				//$params["profile_ids"][] = "5280e86b5b3c91d77b0000dd";
				//$params["profile_ids"][] = "52b844ed9db82271330000bc";
				//$params["profile_ids"][] = "52b8463d9db822db340000e1";
				$params["shorten"] = false;
				$params["now"] = false;
print_r($params);
				$success = $client->CallAPI(
					'https://api.bufferapp.com/1/updates/create.json',
					'POST', $params, array('FailOnAccessError'=>true, 'RequestContentType'=>'application/json'), $user);
*/
			}
		}
		$success = $client->Finalize($success);
	}
	if($client->exit)
		exit;

	if($success)
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>App.net OAuth client results</title>
</head>
<body>
<?php
		echo '<h1>', HtmlSpecialChars($user->name), 
			' you have logged in successfully with App.net!</h1>';
		echo '<pre>', HtmlSpecialChars(print_r($user, 1)), '</pre>';
?>
</body>
</html>
<?php
	}
	else
	{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>OAuth client error</title>
</head>
<body>
<h1>OAuth client error</h1>
<pre>Error: <?php echo HtmlSpecialChars($client->error); ?></pre>
</body>
</html>
<?php
	}

?>
