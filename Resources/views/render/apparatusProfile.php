<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();

$deviceRows = $root->getUtility()->getQuery()->selectAllDeviceDatabase();
$cameraRow = $root->getUtility()->getQuery()->selectCameraDatabase($_SESSION['camera_number']);
?>
<form id="form_camera_apparatusProfile" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=apparatusProfileAction" method="post" novalidate="novalidate">
    <table class="table table-bordered table-striped margin_bottom">
        <tbody class="table_tbody">
            <tr>
                <td>
                    Devices
                </td>
                <td>
                    <select id="form_camera_apparatusProfile_deviceId" class="form-control" name="form_camera_apparatusProfile[deviceId]" required="required">
                        <option value="0">Select</option>
                        <?php
                        foreach($deviceRows as $key => $value) {
                            $selected = $value['id'] == $cameraRow['device_id'] ? "selected" : "";
                            
                            echo "<option $selected value=\"{$value['id']}\">{$value['name']}</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    Video url
                </td>
                <td>
                    <input id="form_camera_apparatusProfile_videoUrl" class="form-control" type="text" name="form_camera_apparatusProfile[videoUrl]" value="<?php echo $cameraRow['video_url']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Username
                </td>
                <td>
                    <input id="form_camera_apparatusProfile_username" class="form-control" type="text" name="form_camera_apparatusProfile[username]" value="<?php echo $cameraRow['username']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Password
                </td>
                <td>
                    <input id="form_camera_apparatusProfile_password" class="form-control" type="password" name="form_camera_apparatusProfile[password]" value="<?php echo $cameraRow['password']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Threshold
                </td>
                <td>
                    <input id="form_camera_apparatusProfile_threshold" class="form-control" type="text" name="form_camera_apparatusProfile[threshold]" value="<?php echo $cameraRow['threshold']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Motion detection
                </td>
                <td>
                    <?php $checked = $cameraRow['motion_detection_status'] == "start" ? "checked" : ""; ?>
                    <input id="form_camera_apparatusProfile_motionDetectionStatus" class="form-control" type="checkbox" name="form_camera_apparatusProfile[motionDetectionStatus]" value="<?php echo $cameraRow['motion_detection_status']; ?>" required="required" <?php echo $checked; ?> data-on-color="success" data-off-color="danger"/>
                    <input type="hidden" name="form_camera_apparatusProfile[motionDetectionStatus]" value="<?php echo $cameraRow['motion_detection_status']; ?>" required="required"/>
                </td>
            </tr>
        </tbody>
    </table>
    
    <input id="form_camera_apparatusProfile_token" class="form-control" type="hidden" name="form_camera_apparatusProfile[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom" type="submit" value="Update"/>
</form>

<div class="margin_bottom">
    <h3>Camera deletion</h3>

    <p>Warning! If you delete the camera is impossible return back.</p>

    <button id="camera_deletion" class="button_custom_danger" type="button">Delete</button>
</div>