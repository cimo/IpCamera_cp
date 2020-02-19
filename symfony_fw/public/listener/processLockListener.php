<?php
$remoteAddressExplode = explode(",", $_SERVER['REMOTE_ADDR']);
$serverAddressExplode = explode(",", $_SERVER['SERVER_ADDR']);

if ($remoteAddressExplode[0] != $serverAddressExplode[0] && $remoteAddressExplode[1] != $serverAddressExplode[1] && $remoteAddressExplode[2] != $serverAddressExplode[2])
    exit;

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) == true && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != "xmlhttprequest")
    exit;

$response = Array();
$name = isset($_REQUEST['name']) == true ? $_REQUEST['name'] : "";

if (empty($name) == true)
    exit;

sleep(5);

$path = "../../src/files/lock/$name";

if (file_exists($path) == true) {
    $fileContentExplode = explode("|", file_get_contents($path));
    
    if ($fileContentExplode[0] == $fileContentExplode[1]) {
        unlink($path);
        
        $response['status'] = "finish";
    }
    else {
        $response['status'] = "loop";
        $response['values']['name'] = $name;
        $response['values']['total'] = $fileContentExplode[0];
        $response['values']['count'] = $fileContentExplode[1];
    }
}
else
    $response['status'] = "error";

echo json_encode(Array(
    'response' => $response
));

exit;