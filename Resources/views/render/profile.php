<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");

$utility = new Utility();

$token = isset($_SESSION['token']) == true ? $_SESSION['token'] : "";
$cameraNumber = isset($_SESSION['camera_number']) == true ? $_SESSION['camera_number'] : -1;

$deviceRows = $utility->getQuery()->selectAllDevicesFromDatabase();

$cameraRows = $utility->getQuery()->selectCameraFromDatabase($cameraNumber);
?>
<form id="form_camera_profile" class="margin_bottom" action="<?php echo $utility->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=profileAction" method="post" novalidate="novalidate">
    <table class="table table-bordered table-striped">
        <thead class="table_thead">
            <tr>
                <th>
                    Name
                </th>
                <th>
                    Value
                </th>
            </tr>
        </thead>
        <tbody class="table_tbody">
            <tr>
                <td>
                    Devices
                </td>
                <td>
                    <select id="form_camera_profile_device_id" class="form-control" name="form_camera_profile[deviceId]" required="required">
                        <option value="0">Select</option>
                        <?php
                        foreach($deviceRows as $key => $value) {
                            $selected = $value['id'] == $cameraRows['device_id'] ? "selected" : "";
                            
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
                    <input id="form_camera_profile_video_url" class="form-control" type="text" name="form_camera_profile[videoUrl]" value="<?php echo $cameraRows['video_url']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Username
                </td>
                <td>
                    <input id="form_camera_profile_username" class="form-control" type="text" name="form_camera_profile[username]" value="<?php echo $cameraRows['username']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Password
                </td>
                <td>
                    <input id="form_camera_profile_password" class="form-control" type="password" name="form_camera_profile[password]" value="<?php echo $cameraRows['password']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Motion url
                </td>
                <td>
                    <input id="form_camera_profile_motion_url" class="form-control" type="text" name="form_camera_profile[motionUrl]" value="<?php echo $cameraRows['motion_url']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Motion detection
                </td>
                <td>
                    <?php
                    $checked = $cameraRows['motion_detection_active'] == "start" ? "checked" : "";
                    ?>
                    <input id="form_camera_profile_detection_active" class="form-control" type="checkbox" name="form_camera_profile[motionDetectionActive]" value="<?php echo $cameraRows['motion_detection_active']; ?>" required="required" <?php echo $checked; ?> data-on-color="success" data-off-color="danger"/>
                    <input type="hidden" name="form_camera_profile[motionDetectionActive]" value="<?php echo $cameraRows['motion_detection_active']; ?>" required="required"/>
                </td>
            </tr>
        </tbody>
    </table>
    
    <input id="form_camera_profile_token" class="form-control" type="hidden" name="form_camera_profile[token]" value="<?php echo $token; ?>"/>
    <input class="btn btn-primary" type="submit" value="Update"/>
</form>

<div class="margin_bottom">
    <h3>Camera deletion</h3>

    <p>Warning! If you delete the camera is impossible return back.</p>

    <button id="camera_deletion" class="btn btn-primary" type="button">Delete</button>
</div>
<script>
    var textProfile = {
        'ipCameraDelete': "Really delete this camera?"
    };
</script>