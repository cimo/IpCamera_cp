<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

if (isset($_SESSION['userLogged']) == false)
    return;

$root = new Root();

$settingRow = $root->getUtility()->getQuery()->selectSettingDatabase();
?>
<form id="form_apparatus_setting" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=settingAction" method="post" novalidate="novalidate">
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_setting_template">Template</label>
        <select id="form_apparatus_setting_template" class="form-control" name="form_apparatus_setting[template]" required="required">
            <?php
            foreach($root->getUtility()->createTemplateList() as $key => $value) {
                $selected = $value == $settingRow['template'] ? "selected" : "";

                echo "<option $selected value=\"{$key}\">{$value}</option>";
            }
            ?>
        </select>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_setting_serverUrl">Server url</label>
        <input id="form_apparatus_setting_serverUrl" class="form-control" type="text" name="form_apparatus_setting[serverUrl]" value="<?php echo $settingRow['server_url']; ?>" required="required"/>
    </div>
    <div class="form-group">
        <label class="control-label required" for="form_apparatus_setting_serverUrl">Motion version</label>
        <select id="form_apparatus_setting_motionVersion" class="form-control" name="form_apparatus_setting[motionVersion]" required="required">
            <?php
            foreach($root->getUtility()->createMotionVersionList() as $key => $value) {
                $selected = $key == $settingRow['motion_version'] ? "selected" : "";

                echo "<option $selected value=\"{$key}\">{$value}</option>";
            }
            ?>
        </select>
    </div>
    
    <input id="form_apparatus_setting_token" class="form-control" type="hidden" name="form_apparatus_setting[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom margin_bottom" type="submit" value="Update"/>
</form>