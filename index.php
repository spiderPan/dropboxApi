<?php
session_start();
$_secretKey = '0085z0msynwxmm7';
$_apiKey = '235l2nlo3n1rndj';
$_clientIdentifier = "my-app/1.0";
$_redirectUri = 'http://127.0.0.1/dropbox/index.php';


$file_name = 'test.pdf';
$file_new_name = str_replace('.pdf','-revision-' . date('Y-m-d-G-i-s').'.pdf',$file_name);
//print_r($_SESSION);exit;
#require_once("dropbox-sdk/init-function.php");
require_once('dropbox-sdk/Dropbox/autoload.php');
use \Dropbox as dbx;

$configData = file_get_contents('config.json');

if (false !== $configData && null !== ($config = json_decode($configData))) {
	//Create file
	$client = new dbx\Client($config->accessToken, $config->userId);
} else {
	try { //Get Access
		$appInfo        = new dbx\AppInfo($_apiKey, $_secretKey);
		$csrfTokenStore = new dbx\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token');
		$auth           = new dbx\WebAuth($appInfo, $_clientIdentifier, $_redirectUri, $csrfTokenStore);

		if (isset($_SESSION['dropbox-auth-csrf-token']) && isset($_GET)) {
			list($accessToken, $userId, $urlState) = $auth->finish($_GET);
			assert($urlState === null); // Since we didn't pass anything in start()
			file_put_contents('config.json', json_encode(array('accessToken' => $accessToken, 'userId' => $userId)));
			header('Location: ' . $_redirectUri);
			exit;
		}

		$authorizeUrl = $auth->start();
		header('Location: ' . $authorizeUrl);
		exit;
	} catch (Exception $e) {
		echo $e->getTraceAsString();
		exit;
	}
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Dropbox Test</title>
</head>
<body>
We are logged in! <?php
$fd = fopen($file_name, 'rb');
$md1 = $client->uploadFile('/'.$file_new_name, dbx\WriteMode::add(), $fd);
fclose($fd);
print_r($md1);

#print_r($client->getAccountInfo()); ?>
</body>
</html>