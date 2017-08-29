<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();

$deviceRows = $root->getUtility()->getQuery()->selectAllDevicesDatabase();
$cameraRow = $root->getUtility()->getQuery()->selectCameraDatabase($_SESSION['camera_number']);
?>
<form id="form_camera_profile" class="margin_bottom" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=profileAction" method="post" novalidate="novalidate">
    <table class="table table-bordered table-striped">
        <tbody class="table_tbody">
            <tr>
                <td>
                    Devices
                </td>
                <td>
                    <select id="form_camera_profile_deviceId" class="form-control" name="form_camera_profile[deviceId]" required="required">
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
                    <input id="form_camera_profile_videoUrl" class="form-control" type="text" name="form_camera_profile[videoUrl]" value="<?php echo $cameraRow['video_url']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Username
                </td>
                <td>
                    <input id="form_camera_profile_username" class="form-control" type="text" name="form_camera_profile[username]" value="<?php echo $cameraRow['username']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Password
                </td>
                <td>
                    <input id="form_camera_profile_password" class="form-control" type="password" name="form_camera_profile[password]" value="<?php echo $cameraRow['password']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Threshold
                </td>
                <td>
                    <input id="form_camera_profile_threshold" class="form-control" type="text" name="form_camera_profile[threshold]" value="<?php echo $cameraRow['threshold']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Motion detection
                </td>
                <td>
                    <?php $checked = $cameraRow['motion_detection_status'] == "start" ? "checked" : ""; ?>
                    <input id="form_camera_profile_motionDetectionStatus" class="form-control" type="checkbox" name="form_camera_profile[motionDetectionStatus]" value="<?php echo $cameraRow['motion_detection_status']; ?>" required="required" <?php echo $checked; ?> data-on-color="success" data-off-color="danger"/>
                    <input type="hidden" name="form_camera_profile[motionDetectionStatus]" value="<?php echo $cameraRow['motion_detection_status']; ?>" required="required"/>
                </td>
            </tr>
        </tbody>
    </table>
    
    <input id="form_camera_profile_token" class="form-control" type="hidden" name="form_camera_profile[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom" type="submit" value="Update"/>
</form>

<div class="margin_bottom">
    <h3>Camera deletion</h3>

    <p>Warning! If you delete the camera is impossible return back.</p>

    <button id="camera_deletion" class="button_custom_danger" type="button">Delete</button>
</div>
<script>
    var textProfile = {
        'delete': "Really delete this camera?"
    };
</script>