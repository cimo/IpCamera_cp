<?php
$remoteAddressExplode = explode(",", $_SERVER['REMOTE_ADDR']);
$serverAddressExplode = explode(",", $_SERVER['SERVER_ADDR']);

if ($remoteAddressExplode[0] != $serverAddressExplode[0] && $remoteAddressExplode[1] != $serverAddressExplode[1] && $remoteAddressExplode[2] != $serverAddressExplode[2])
    exit;

if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) == true && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != "xmlhttprequest")
    exit;

$response = Array();
$lockName = isset($_REQUEST['lockName']) == true ? $_REQUEST['lockName'] : "";

if (empty($lockName) == true)
    exit;

sleep(5);

$path = "../../src/files/lock/$lockName";

if (file_exists($path) == true) {
    $fileContent = file_get_contents($path);
    $fileContentExplode = explode("|", $fileContent);
    
    if (($fileContentExplode[0] - 1) == $fileContentExplode[1]) {
        unlink($path);
        
        $response['status'] = "finish";
    }
    else {
        $response['status'] = "loop";
        $response['values']['lockName'] = $lockName;
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