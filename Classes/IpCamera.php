<?php
require_once("Utility.php");
require_once("UtilityPrivate.php");
require_once("Query.php");
require_once("Ajax.php");
require_once("Table.php");

class IpCamera {
    // Vars
    private $response;
    
    private $utility;
    private $utilityPrivate;
    private $query;
    private $ajax;
    private $table;
    
    private $videoUrl;
    private $controlUrl;
    
    private $resolution;
    private $rate;
    
    private $settingRow;
    
    // Properties
    public function getVideoUrl() {
        return $this->videoUrl;
    }
    
    public function getControlUrl() {
        return $this->controlUrl;
    }
    
    // Functions public
    public function __construct() {
        $this->response = Array();
        
        $this->utility = new Utility();
        $this->utilityPrivate = new UtilityPrivate();
        $this->query = new Query($this->utility->getDatabase());
        $this->ajax = new Ajax();
        $this->table = new Table();
        
        $this->videoUrl = "";
        $this->controlUrl = "";
        
        $this->resolution = 0;
        $this->rate = 0;
        
        $this->settingRow = $this->query->selectSettingDatabase();
        
        $_SESSION['camera_number'] = isset($_SESSION['camera_number']) == true ? $_SESSION['camera_number'] : 1;
        
        $this->parameters();
    }
    
    public function phpInput() {
        $this->utilityPrivate->checkSessionOverTime();
        
        $content = file_get_contents("php://input");
        $json = json_decode($content);

        if ($json != null) {
            if (isset($_GET['controller']) == true) {
                $token = is_array($json) == true ? end($json)->value : $json->token;

                if (isset($_SESSION['token']) == true && $token == $_SESSION['token']) {
                    if ($_GET['controller'] == "selectionAction")
                        $this->selectionAction($json);
                    else if ($_GET['controller'] == "profileAction")
                        $this->profileAction($json);
                    else if ($_GET['controller'] == "controlsAction")
                        $this->controlsAction($json);
                    else if ($_GET['controller'] == "filesAction")
                        $this->filesAction($json);
                    else if ($_GET['controller'] == "deleteAction")
                        $this->deleteAction();
                }
            }
        }
        else if (isset($_POST['searchWritten']) == true && isset($_POST['paginationCurrent']) == true)
            $this->filesList();
        else
            $this->response['messages']['error'] = "Json error!";
        
        echo $this->ajax->response(Array(
            'response' => $this->response
        ));
        
        $this->utility->getDatabase()->close();
    }
    
    public function generateSelectOptionFromMotionFolders() {
        $motionFolderPath = "{$_SERVER['DOCUMENT_ROOT']}/motion";
        
        $scanDirElements = @scandir($motionFolderPath, 1);
        
        if ($scanDirElements != false) {
            asort($scanDirElements);
            
            $count = 0;
            
            foreach ($scanDirElements as $key => $value) {
                if ($value != "." && $value != ".." && $value != ".htaccess" && is_dir("$motionFolderPath/$value") == true) {
                    $count ++;
                    
                    echo "<option value=\"$count\">Camera $count</option>";
                }
            }
        }
    }
    
    public function filesList() {
        $motionFolderPath = "{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['camera_number']}";
        
        $scanDirElements = @scandir($motionFolderPath);
        
        if ($scanDirElements == true) {
            if ($scanDirElements[0] == ".") {
                unset($scanDirElements[0]);
                unset($scanDirElements[1]);
                
                $index = array_search("lastsnap.jpg", $scanDirElements);
                unset($scanDirElements[$index]);
            }
            
            $tableResult = $this->table->request($scanDirElements, 5, "file", true, false);
            
            $count = 0;
            $list = "";
            
            foreach ($tableResult['list'] as $key => $value) {
                $count ++;
                
                $list .= "<tr>
                    <td>
                        $count
                    </td>
                    <td class=\"name_column\">
                        $value
                    </td>
                    <td>
                        {$this->utility->sizeUnits(filesize("$motionFolderPath/$value"))}
                    </td>
                    <td class=\"horizontal_center\">
                        <button class=\"camera_files_download btn btn-primary\"><i class=\"fa fa-download\"></i></button>
                    </td>
                    <td class=\"horizontal_center\">
                        <button class=\"camera_files_delete btn btn-danger\"><i class=\"fa fa-remove\"></i></button>
                    </td>
                </tr>";
            }
            
            $this->response['values']['search'] = $tableResult['search'];
            $this->response['values']['pagination'] = $tableResult['pagination'];
            $this->response['values']['list'] = $list;
            
            return Array(
                'search' => $this->response['values']['search'],
                'pagination' => $this->response['values']['pagination'],
                'list' => $this->response['values']['list']
            );
        }
        
        return Array(
            Array(
                'search' => ""
            ),
            Array(
                'pagination' => ""
            ),
            Array(
                'list' => ""
            )
        );
    }
    
    // Functions private
    private function parameters() {
        $cameraRow = $this->query->selectCameraDatabase($_SESSION['camera_number']);
        
        $deviceRow = $this->query->selectDeviceDatabase($cameraRow['device_id']);
        
        $this->resolution = 32;
        $this->rate = 0;
        
        $this->videoUrl = "{$cameraRow['video_url']}/{$deviceRow['video']}user={$cameraRow['username']}&pwd={$cameraRow['password']}&resolution=$this->resolution&rate=$this->rate";
        $this->controlUrl = "{$cameraRow['video_url']}/decoder_control.cgi?user={$cameraRow['username']}&pwd={$cameraRow['password']}";
    }
    
    private function profileConfig($deviceId, $videoUrl, $username, $password, $motionUrl, $motionDetectionActive, $cameraNumber) {
        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_$cameraNumber");
        @chmod("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_$cameraNumber", 0777);
        
        $netcamUrl = $motionUrl == "" ? $videoUrl : $motionUrl;
        $threshold = $motionDetectionActive == "start" ? "1500" : "0";
        
        $deviceRow = $this->query->selectDeviceDatabase($deviceId);
        
        $content = "framerate 30\n";
        $content .= "netcam_url $netcamUrl/{$deviceRow['video']}user=$username&pwd=$password&resolution=$this->resolution\n";
        $content .= "netcam_http 1.0\n";
        $content .= "netcam_userpass $username:$password\n";
        $content .= "threshold $threshold\n";
        $content .= "ffmpeg_cap_new on\n";
        $content .= "output_normal off\n";
        $content .= "target_dir /home/user_1/www/motion/camera_$cameraNumber";
        
        file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_$cameraNumber.conf", $content.PHP_EOL);
        
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?framerate=30");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?netcam_url=$netcamUrl/{$deviceRow['video']}user=$username&pwd=$password&resolution=$this->resolution");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?netcam_http=1.0");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?netcam_userpass=$username:$password");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?threshold=$threshold");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?ffmpeg_cap_new=on");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?output_normal=off");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/$cameraNumber/config/set?target_dir=/home/user_1/www/motion/camera_$cameraNumber");
    }
    
    private function curlCommandsUrls($url) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        curl_exec($curl);
        
        curl_close($curl);
    }
    
    // Controllers
    private function selectionAction($json) {
        $elements = Array();
        
        foreach($json as $key => $value)
            $elements[] = $value->value;
        
        if ($elements[0] == 0) {
            $cameraRows = $this->query->selectAllCamerasDatabase();
            
            $lastCamera = end($cameraRows);
            $_SESSION['camera_number'] = $lastCamera['camera_number'] + 1;
            
            $deviceId = "";
            $videoUrl = "";
            $username = "";
            $password = "";
            $motionUrl = "";
            $motionDetectionActive = "pause";
            
            $query = $this->utility->getDatabase()->getPdo()->prepare("INSERT INTO cameras (
                                                                            camera_number,
                                                                            device_id,
                                                                            video_url,
                                                                            username,
                                                                            password,
                                                                            motion_url,
                                                                            motion_detection_active
                                                                        )
                                                                        VALUES (
                                                                            :cameraNumber,
                                                                            :deviceId,
                                                                            :videoUrl,
                                                                            :username,
                                                                            :password,
                                                                            :motionUrl,
                                                                            :motionDetectionActive
                                                                        );");
            
            $query->bindValue(":cameraNumber", $_SESSION['camera_number']);
            $query->bindValue(":deviceId", $deviceId);
            $query->bindValue(":videoUrl", $videoUrl);
            $query->bindValue(":username", $username);
            $query->bindValue(":password", $password);
            $query->bindValue(":motionUrl", $motionUrl);
            $query->bindValue(":motionDetectionActive", $motionDetectionActive);
            
            $query->execute();
            
            $this->profileConfig("", "", "", "", "", "pause", $_SESSION['camera_number']);
            
            $this->utility->searchInFile("/etc/motion/motion.conf", "thread /home/user_1/www/motion/camera_{$_SESSION['camera_number']}.conf", null);
            
            // Pause
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['camera_number']}/detection/pause");
            
            $this->response['messages']['success'] = "New camera created with success.";
        }
        else if ($elements[0] > 0) {
            $_SESSION['camera_number'] = $elements[0];
            
            $this->response = "ok";
        }
    }
    
    private function profileAction($json) {
        $motionDetectionActive = "";
                
        $elements = Array();
        
        foreach($json as $key => $value) {
            $elements[] = $value->value;
            
            // Detection
            $curl = curl_init();
            
            if ($key == 5) {
                $motionDetectionActive = $elements[$key];
                curl_setopt($curl, CURLOPT_URL, "{$this->settingRow['server_url']}/{$_SESSION['camera_number']}/detection/$motionDetectionActive");
            }
            
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_exec($curl);
            curl_close($curl);
        }
        
        $this->profileConfig($elements[0], $elements[1], $elements[2], $elements[3], $elements[4], $motionDetectionActive, $_SESSION['camera_number']);
        
        $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE cameras
                                                                    SET device_id = :deviceId,
                                                                        video_url = :videoUrl,
                                                                        username = :username,
                                                                        password = :password,
                                                                        motion_url = :motionUrl,
                                                                        motion_detection_active = :motionDetectionActive
                                                                    WHERE camera_number = :cameraNumber");
        
        $query->bindValue(":deviceId", $elements[0]);
        $query->bindValue(":videoUrl", $elements[1]);
        $query->bindValue(":username", $elements[2]);
        $query->bindValue(":password", $elements[3]);
        $query->bindValue(":motionUrl", $elements[4]);
        $query->bindValue(":motionDetectionActive", $motionDetectionActive);
        $query->bindValue(":cameraNumber", $_SESSION['camera_number']);
        
        $query->execute();
        
        // Restart
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['camera_number']}/action/restart");
        
        $this->response['messages']['success'] = "Settings updated with success.";
    }
    
    private function controlsAction($json) {
        $curl = curl_init();
        
        if ($json->event == "picture") {
            curl_setopt($curl, CURLOPT_URL, "{$this->settingRow['server_url']}/{$_SESSION['camera_number']}/action/snapshot");
            
            $this->response['messages']['success'] = "Picture taked.";
        }
        
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        curl_close($curl);
    }
    
    private function filesAction($json) {
        $path = "{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['camera_number']}/" . trim($json->name);
        
        if ($json->event == "delete") {
            if (file_exists($path) == true)
                unlink($path);
            
            $this->response['messages']['success'] = "File deleted with success!";
        }
        else if ($json->event == "deleteAll") {
            $this->utility->removeDirRecursive($path, false);
            
            $this->response['messages']['success'] = "All files deleted with success!";
        }
        
        $this->filesList();
    }
    
    private function deleteAction() {
        $this->utility->removeDirRecursive("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['camera_number']}", true);
        
        unlink("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['camera_number']}.conf");
        
        $this->utility->searchInFile("/etc/motion/motion.conf", "thread /home/user_1/www/motion/camera_{$_SESSION['camera_number']}.conf", " ");
        
        $query = $this->utility->getDatabase()->getPdo()->prepare("DELETE FROM cameras
                                                                    WHERE camera_number = :cameraNumber");
        
        $query->bindValue(":cameraNumber", $_SESSION['camera_number']);
        
        $query->execute();
        
        // Quit
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['camera_number']}/action/quit");
        
        $_SESSION['camera_number'] = -1;
        
        $this->response['messages']['success'] = "Camera deleted with success!";
    }
}