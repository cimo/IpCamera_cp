<?php
sleep(5);

$response = Array();
$lockName = isset($_REQUEST['lockName']) == true ? $_REQUEST['lockName'] : "";

$response['testA'] = $_SERVER['SERVER_ADDR'];
$response['testB'] = $_SERVER['REMOTE_ADDR'];

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