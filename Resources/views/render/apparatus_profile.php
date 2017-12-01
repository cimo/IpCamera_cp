<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

if (isset($_SESSION['user_logged']) == false)
    return;

$root = new Root();

$userLoggedRoleUserId = isset($_SESSION['user_logged']) == true ? $_SESSION['user_logged']['role_user_id'] : 0;

$checkRoleUser = $root->getIpCameraUtility()->checkRoleUser(Array("ROLE_ADMIN"), $userLoggedRoleUserId);

$deviceRows = $root->getUtility()->getQuery()->selectAllDeviceDatabase();
$apparatusRow = $root->getUtility()->getQuery()->selectApparatusDatabase($_SESSION['apparatus_number']);
?>
<form id="form_apparatus_profile" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=apparatusProfileAction" method="post" novalidate="novalidate">
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_label">Label</label>
        <input id="form_apparatus_profile_label" class="form-control" type="text" name="form_apparatus_profile[label]" value="<?php echo $apparatusRow['label']; ?>" required="required"/>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_deviceId">Devices</label>
        <select id="form_apparatus_profile_deviceId" class="form-control" name="form_apparatus_profile[deviceId]" required="required">
            <option value="0">Select</option>
            <?php
            foreach($deviceRows as $key => $value) {
                $selected = $value['id'] == $apparatusRow['device_id'] ? "selected" : "";

                echo "<option $selected value=\"{$value['id']}\">{$value['name']}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_videoUrl">Video url</label>
        <input id="form_apparatus_profile_videoUrl" class="form-control" type="text" name="form_apparatus_profile[videoUrl]" value="<?php echo $apparatusRow['video_url']; ?>" required="required"/>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_username">Username</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-paw"></i></span>
            <input id="form_apparatus_profile_username" class="form-control" type="text" name="form_apparatus_profile[username]" value="<?php echo $apparatusRow['username']; ?>" required="required"/>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_password">Password</label>
        <div class="input-group">
            <span class="input-group-addon"><i class="fa fa-key"></i></span>
            <input id="form_apparatus_profile_password" class="form-control" type="password" name="form_apparatus_profile[password]" value="<?php echo $apparatusRow['password']; ?>" required="required"/>
        </div>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_threshold">Threshold</label>
        <input id="form_apparatus_profile_threshold" class="form-control" type="text" name="form_apparatus_profile[threshold]" value="<?php echo $apparatusRow['threshold']; ?>" required="required"/>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_profile_motionDetectionStatus">Motion detection</label>
        <?php $checked = $apparatusRow['motion_detection_status'] == "start" ? "checked" : ""; ?>
        <div>
            <input id="form_apparatus_profile_motionDetectionStatus" class="form-control" type="checkbox" name="form_apparatus_profile[motionDetectionStatus]" value="<?php echo $apparatusRow['motion_detection_status']; ?>" required="required" <?php echo $checked; ?> data-on-color="success" data-off-color="danger"/>
            <input type="hidden" name="form_apparatus_profile[motionDetectionStatus]" value="<?php echo $apparatusRow['motion_detection_status']; ?>" required="required"/>
        </div>
    </div>
    <?php
    if ($checkRoleUser == true) {
    ?>
        <div id="apparatus_profile_userId_wordTag_container" class="form-group">
            <label class="control-label required" for="form_apparatus_profile_userId">User</label>
            <input id="form_apparatus_profile_userId" class="form-control" type="hidden" name="form_apparatus_profile[userId]" value="<?php echo $apparatusRow['user_id']; ?>" required="required">
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-tags"></i></span>
                <?php echo $root->getIpCameraUtility()->createUserHtml("form_apparatus_profile_userId_field", true); ?>
            </div>
        </div>
    <?php
    }
    ?>
    
    <input id="form_apparatus_profile_token" class="form-control" type="hidden" name="form_apparatus_profile[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom" type="submit" value="Update"/>
</form>

<?php
if ($checkRoleUser == true) {
?>
    <div class="margin_bottom">
        <h3>Camera deletion</h3>

        <p>Warning! If you delete the camera is impossible return back.</p>

        <button id="apparatus_deletion" class="button_custom_danger" type="button">Delete</button>
    </div>
<?php
}
?>