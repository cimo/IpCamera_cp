<?php
// Version 1.0.0

require_once("System/Utility.php");
require_once("Ajax.php");
require_once("TableAndPagination.php");
require_once("IpCameraUtility.php");

class IpCamera {
    // Vars
    private $response;
    
    private $utility;
    private $query;
    private $ajax;
    private $tableAndPagination;
    private $ipCameraUtility;
    
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
        $this->query = $this->utility->getQuery();
        $this->ajax = new Ajax();
        $this->tableAndPagination = new TableAndPagination();
        $this->ipCameraUtility = new IpCameraUtility();
        
        $this->settingRow = $this->query->selectSettingDatabase();
        
        $_SESSION['apparatus_number'] = isset($_SESSION['apparatus_number']) == true ? $_SESSION['apparatus_number'] : 1;
        
        $apparatusRow = $this->query->selectApparatusDatabase($_SESSION['apparatus_number']);
        
        $deviceRow = $this->query->selectDeviceDatabase($apparatusRow['device_id']);
        
        $this->videoUrl = "{$apparatusRow['video_url']}/{$deviceRow['video']}user={$apparatusRow['username']}&pwd={$apparatusRow['password']}&resolution=$this->resolution&rate=$this->rate";
        $this->controlUrl = "{$apparatusRow['video_url']}/decoder_control.cgi?user={$apparatusRow['username']}&pwd={$apparatusRow['password']}";
        
        $this->resolution = 32;
        $this->rate = 0;
    }
    
    public function phpInput() {
        $this->utility->checkSessionOverTime();
        
        $content = file_get_contents("php://input");
        $json = json_decode($content);

        if ($json != null) {
            if (isset($_GET['controller']) == true) {
                $token = is_array($json) == true ? end($json)->value : $json->token;

                if ($this->utility->checkToken($token) == true) {
                    $parameters = $this->utility->requestParametersParse($json);
                    
                    if ($_GET['controller'] == "selectionAction")
                        $this->selectionAction($parameters);
                    else if ($_GET['controller'] == "controlAction")
                        $this->controlAction($parameters);
                    else if ($_GET['controller'] == "apparatusProfileAction")
                        $this->apparatusProfileAction($parameters);
                    else if ($_GET['controller'] == "apparatusProfileDeleteAction")
                        $this->apparatusProfileDeleteAction();
                    else if ($_GET['controller'] == "fileAction")
                        $this->fileAction($parameters);
                    else if ($_GET['controller'] == "userProfileDataAction")
                        $this->userProfileDataAction($parameters);
                    else if ($_GET['controller'] == "userProfilePasswordAction")
                        $this->userProfilePasswordAction($parameters);
                    else if ($_GET['controller'] == "userManagementSelectionAction")
                        $this->userManagementSelectionAction($parameters);
                    else if ($_GET['controller'] == "userManagementProfileAction")
                        $this->userManagementProfileAction($parameters);
                    else if ($_GET['controller'] == "settingAction")
                        $this->settingAction($parameters);
                }
            }
        }
        else if (isset($_POST['searchWritten']) == true && isset($_POST['paginationCurrent']) == true)
            $this->createListHtml();
        else
            $this->response['messages']['error'] = "Json error!";
        
        echo $this->ajax->response(Array(
            'response' => $this->response
        ));
        
        $this->utility->getDatabase()->close();
    }
    
    public function generateSelectOptionFromMotionConfig() {
        $motionFolderPath = "{$_SERVER['DOCUMENT_ROOT']}/motion";
        
        $scanDirElements = @scandir($motionFolderPath, 1);
        
        if ($scanDirElements != false) {
            asort($scanDirElements);
            
            $count = 0;
            
            foreach ($scanDirElements as $key => $value) {
                if ($value != "." && $value != ".." && $value != ".htaccess" && is_file("$motionFolderPath/$value") == true) {
                    if (pathinfo("$motionFolderPath/$value", PATHINFO_EXTENSION) == "conf") {
                        $count ++;
                        
                        if ($this->ipCameraUtility->checkApparatusUserId() == true)
                            echo "<option value=\"$count\">Camera $count</option>";
                    }
                }
            }
        }
    }
    
    public function generateSelectOptionUser() {
        $userRows = $this->query->selectAllUserDatabase(1);
        
        if ($userRows != false) {
            foreach ($userRows as $key => $value)
                echo "<option value=\"{$value['id']}\">{$value['username']}</option>";
        }
    }
    
    public function createListHtml() {
        $motionFolderPath = "{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}";
        
        $scanDirElements = @scandir($motionFolderPath);
        
        if ($scanDirElements == true) {
            if ($scanDirElements[0] == ".") {
                unset($scanDirElements[0]);
                unset($scanDirElements[1]);
                
                $index = array_search("lastsnap.jpg", $scanDirElements);
                unset($scanDirElements[$index]);
            }
            
            $tableAndPagination = $this->tableAndPagination->request($scanDirElements, 5, "file", true, false);
            
            $count = 0;
            $listHtml = "";
            
            foreach ($tableAndPagination['listHtml'] as $key => $value) {
                $count ++;
                
                $listHtml .= "<tr>
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
                        <button class=\"apparatus_file_download button_custom\"><i class=\"fa fa-download\"></i></button>
                    </td>
                    <td class=\"horizontal_center\">
                        <button class=\"apparatus_file_delete button_custom_danger\"><i class=\"fa fa-remove\"></i></button>
                    </td>
                </tr>";
            }
            
            $this->response['values']['search'] = $tableAndPagination['search'];
            $this->response['values']['pagination'] = $tableAndPagination['pagination'];
            $this->response['values']['listHtml'] = $listHtml;
            
            return Array(
                'search' => $this->response['values']['search'],
                'pagination' => $this->response['values']['pagination'],
                'listHtml' => $this->response['values']['listHtml']
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
                'listHtml' => ""
            )
        );
    }
    
    // Functions private
    private function curlCommandsUrls($url) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        
        return $response;
    }
    
    private function apparatusProfileConfig($deviceId, $videoUrl, $username, $password, $threshold, $motionDetectionStatus) {
        @mkdir("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}");
        @chmod("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}", 0777);
        
        $deviceRow = $this->query->selectDeviceDatabase($deviceId);
        
        $content = "framerate 10\n";
        $content .= "netcam_url $videoUrl/{$deviceRow['video']}user=$username&pwd=$password&resolution=$this->resolution\n";
        $content .= "netcam_userpass $username:$password\n";
        $content .= "threshold $threshold\n";
        
        if ($this->settingRow['motion_version'] == "3.1.12") {
            $content .= "netcam_http 1.0\n";
            $content .= "ffmpeg_cap_new on\n";
            $content .= "output_normal off\n";
        }
        else if ($this->settingRow['motion_version'] == "4.0.1") {
            $content .= "ffmpeg_output_movies on\n";
            $content .= "output_debug_pictures off\n";
        }
        
        $content .= "target_dir {$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}";
        
        file_put_contents("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}.conf", $content.PHP_EOL);
        @chmod("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}.conf", 0664);
        
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?target_dir={$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?framerate=10");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?netcam_url=$videoUrl/{$deviceRow['video']}user=$username&pwd=$password&resolution=$this->resolution");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?netcam_userpass=$username:$password");
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?threshold=$threshold");
        
        if ($this->settingRow['motion_version'] == "3.1.12") {
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?netcam_http=1.0");
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?ffmpeg_cap_new=on");
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?output_normal=off");
        }
        else if ($this->settingRow['motion_version'] == "4.0.1") {
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?ffmpeg_output_movies=on");
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/config/set?output_debug_pictures=off");
        }
        
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/detection/$motionDetectionStatus");
    }
    
    // Controllers
    private function selectionAction($parameters) {
        $error = false;
        
        if (isset($parameters['number']) == false) {
            $this->response['errors']['number'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($error == true)
            return;
        
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_ADMIN"), $_SESSION['user_logged']['role_user_id']);
        
        if ($parameters['number'] == 0 && $checkRoleUser == true) {
            $apparatusRows = $this->query->selectAllApparatusDatabase();
            
            $lastCamera = end($apparatusRows);
            $_SESSION['apparatus_number'] = $lastCamera['number'] + 1;
            
            $query = $this->utility->getDatabase()->getPdo()->prepare("INSERT INTO apparatus (
                                                                            number,
                                                                            device_id,
                                                                            video_url,
                                                                            username,
                                                                            password
                                                                        )
                                                                        VALUES (
                                                                            :number,
                                                                            :deviceId,
                                                                            :videoUrl,
                                                                            :username,
                                                                            :password
                                                                        );");
            
            $query->bindValue(":number", $_SESSION['apparatus_number']);
            $query->bindValue(":deviceId", "");
            $query->bindValue(":videoUrl", "");
            $query->bindValue(":username", "");
            $query->bindValue(":password", "");
            
            $query->execute();
            
            $this->utility->searchInFile("/etc/motion/motion.conf", "thread {$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}.conf", null);
            
            $this->apparatusProfileConfig("", "", "", "", "", "pause");
            
            $this->response['messages']['success'] = "New camera created with success.";
        }
        else if ($parameters['number'] > 0) {
            $_SESSION['apparatus_number'] = $parameters['number'];
            
            $motionDetectionStatus = "pause";
            
            $status = $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/detection/status");
            
            if (strpos($status, "Detection status PAUSE") === false)
                $motionDetectionStatus = "start";
            
            $this->response['values']['motionDetectionStatus'] = $motionDetectionStatus;
        }
        else
            $this->response['messages']['error'] = "No camera selected!";
    }
    
    private function controlAction($parameters) {
        if ($parameters['event'] == "picture") {
            $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/action/snapshot");
            
            $this->response['messages']['success'] = "Picture taked.";
        }
    }
    
    private function apparatusProfileAction($parameters) {
        $error = false;
        
        if ($parameters['videoUrl'] == "") {
            $this->response['errors']['videoUrl'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['username'] == "") {
            $this->response['errors']['username'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['password'] == "") {
            $this->response['errors']['password'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($error == true) {
            $this->response['messages']['error'] = "Profile not updated!";
            
            return;
        }
        
        $this->apparatusProfileConfig($parameters['deviceId'],
                                        $parameters['videoUrl'],
                                        $parameters['username'],
                                        $parameters['password'],
                                        $parameters['threshold'],
                                        $parameters['motionDetectionStatus']);
        
        $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE apparatus
                                                                    SET label = :label,
                                                                        device_id = :deviceId,
                                                                        video_url = :videoUrl,
                                                                        username = :username,
                                                                        password = :password,
                                                                        threshold = :threshold,
                                                                        motion_detection_status = :motionDetectionStatus,
                                                                        user_id = :userId
                                                                    WHERE number = :number");
        
        $query->bindValue(":label", $parameters['label']);
        $query->bindValue(":deviceId", $parameters['deviceId']);
        $query->bindValue(":videoUrl", $parameters['videoUrl']);
        $query->bindValue(":username", $parameters['username']);
        $query->bindValue(":password", $parameters['password']);
        $query->bindValue(":threshold", $parameters['threshold']);
        $query->bindValue(":motionDetectionStatus", $parameters['motionDetectionStatus']);
        $query->bindValue(":userId", $parameters['userId']);
        $query->bindValue(":number", $_SESSION['apparatus_number']);
        
        $query->execute();
        
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/action/restart");
        
        $this->response['messages']['success'] = "Profile updated with success.";
    }
    
    private function apparatusProfileDeleteAction() {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_ADMIN"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        $this->utility->removeDirRecursive("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}", true);
        
        unlink("{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}.conf");
        
        $this->utility->searchInFile("/etc/motion/motion.conf", "thread {$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}.conf", " ");
        
        $query = $this->utility->getDatabase()->getPdo()->prepare("DELETE FROM apparatus
                                                                    WHERE number = :number");
        
        $query->bindValue(":number", $_SESSION['apparatus_number']);
        
        $query->execute();
        
        $this->curlCommandsUrls("{$this->settingRow['server_url']}/{$_SESSION['apparatus_number']}/action/quit");
        
        $_SESSION['apparatus_number'] = -1;
        
        $this->response['messages']['success'] = "Camera deleted with success!";
    }
    
    private function fileAction($parameters) {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_ADMIN"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        if ($parameters['event'] == "delete") {
            $path = "{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}/" . trim($parameters['name']);
            
            if (file_exists($path) == true)
                unlink($path);
            
            $this->response['messages']['success'] = "File deleted with success!";
        }
        else if ($parameters['event'] == "deleteAll") {
            $path = "{$_SERVER['DOCUMENT_ROOT']}/motion/camera_{$_SESSION['apparatus_number']}/";
            
            $this->utility->removeDirRecursive($path, false);
            
            $this->response['messages']['success'] = "All files deleted with success!";
        }
        
        $this->createListHtml();
    }
    
    private function userProfileDataAction($parameters) {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_USER"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        $error = false;
        
        if ($parameters['email'] == "") {
            $this->response['errors']['email'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($error == true) {
            $this->response['messages']['error'] = "Profile not modified!";
            
            return;
        }
        
        $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE users
                                                                    SET email = :email
                                                                    WHERE id = :id");

        $query->bindValue(":email", $parameters['email']);
        $query->bindValue(":id", $_SESSION['user_logged']['id']);

        $query->execute();

        $this->response['messages']['success'] = "Profile modified.";
    }
    
    private function userProfilePasswordAction($parameters) {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_USER"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        $error = false;
        
        if ($parameters['old'] == "") {
            $this->response['errors']['old'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['new'] == "") {
            $this->response['errors']['new'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['newConfirm'] == "") {
            $this->response['errors']['newConfirm'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($error == true) {
            $this->response['messages']['error'] = "Password not modified!";
            
            return;
        }
        
        $messagePassword = $this->ipCameraUtility->assigUserPassword("withOld", $_SESSION['user_logged'], $parameters);

        if (isset($messagePassword['password']) == true) {
            $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE users
                                                                        SET password = :password
                                                                        WHERE id = :id");

            $query->bindValue(":password", $messagePassword['password']);
            $query->bindValue(":id", $_SESSION['user_logged']['id']);

            $query->execute();

            $this->response['messages']['success'] = "Password modified.";
        }
        else
            $this->response['messages']['error'] = $messagePassword['error'];
    }
    
    private function userManagementSelectionAction($parameters) {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_ADMIN"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        $_SESSION['user_management'] = null;
        
        if ($parameters['id'] == 0) {
            $_SESSION['user_management'] = "new";
            
            $this->response['render'] = "userManagement_profile.php";
        }
        else if ($parameters['id'] > 0) {
            $userRow = $this->query->selectUserDatabase($parameters['id']);
            
            $_SESSION['user_management'] = $userRow;
            
            $this->response['render'] = "userManagement_profile.php";
        }
        else
            $this->response['messages']['error'] = "No user selected!";
    }
    
    private function userManagementProfileAction($parameters) {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_ADMIN"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        $error = false;
        
        if ($parameters['roleUserId'] == "") {
            $this->response['errors']['roleUserId'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['username'] == "") {
            $this->response['errors']['username'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['email'] == "") {
            $this->response['errors']['email'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($error == true) {
            if ($_SESSION['user_management'] == "new")
                $this->response['messages']['error'] = "User not created!";
            else
                $this->response['messages']['error'] = "User not updated!";
            
            return;
        }
        
        $messagePassword = $this->ipCameraUtility->assigUserPassword("withoutOld", null, $parameters);

        if (isset($messagePassword['password']) == true) {
            if ($_SESSION['user_management'] == "new") {
                $query = $this->utility->getDatabase()->getPdo()->prepare("INSERT INTO users (
                                                                                role_user_id,
                                                                                username,
                                                                                email,
                                                                                password,
                                                                                not_locked
                                                                            )
                                                                            VALUES (
                                                                                :roleUserId,
                                                                                :username,
                                                                                :email,
                                                                                :password,
                                                                                :notLocked
                                                                            );");

                $query->bindValue(":roleUserId", $parameters['roleUserId']);
                $query->bindValue(":username", $parameters['username']);
                $query->bindValue(":email", $parameters['email']);
                $query->bindValue(":password", $messagePassword['password']);
                $query->bindValue(":notLocked", "1");

                $query->execute();

                $this->response['messages']['success'] = "User created with success.";
            }
            else {
                $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE users
                                                                            SET role_user_id = :roleUserId,
                                                                                username = :username,
                                                                                email = :email,
                                                                                password = :password,
                                                                                not_locked = :notLocked
                                                                            WHERE id = :id");

                $query->bindValue(":roleUserId", $parameters['roleUserId']);
                $query->bindValue(":username", $parameters['username']);
                $query->bindValue(":email", $parameters['email']);

                if ($messagePassword['password'] == "")
                    $query->bindValue(":password", $_SESSION['user_management']['password']);
                else
                    $query->bindValue(":password", $messagePassword['password']);

                $query->bindValue(":notLocked", $parameters['notLocked']);
                $query->bindValue(":id", $_SESSION['user_management']['id']);

                $query->execute();

                $this->response['messages']['success'] = "User updated with success.";
            }
        }
        else
            $this->response['messages']['error'] = $messagePassword['error'];
    }
    
    private function settingAction($parameters) {
        $checkRoleUser = $this->ipCameraUtility->checkRoleUser(Array("ROLE_ADMIN"), $_SESSION['user_logged']['role_user_id']);
        
        if ($checkRoleUser == false)
            return;
        
        $error = false;
        
        if ($parameters['template'] == "") {
            $this->response['errors']['template'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['serverUrl'] == "") {
            $this->response['errors']['serverUrl'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($parameters['motionVersion'] == "") {
            $this->response['errors']['motionVersion'] = "This value should not be blank.";
            
            $error = true;
        }
        
        if ($error == true) {
            $this->response['messages']['error'] = "Settings not updated!";
            
            return;
        }
        
        $query = $this->utility->getDatabase()->getPdo()->prepare("UPDATE settings
                                                                    SET template = :template,
                                                                        server_url = :serverUrl,
                                                                        motion_version = :motionVersion
                                                                    WHERE id = :id");
        
        $query->bindValue(":template", $parameters['template']);
        $query->bindValue(":serverUrl", $parameters['serverUrl']);
        $query->bindValue(":motionVersion", $parameters['motionVersion']);
        $query->bindValue(":id", 1);
        
        $query->execute();
        
        $this->response['messages']['success'] = "Settings updated with success.";
    }
}