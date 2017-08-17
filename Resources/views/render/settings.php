<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/UtilityPrivate.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Query.php");

$utility = new Utility();
$utilityPrivate = new UtilityPrivate();
$query = new Query($utility->getDatabase());

$settingRow = $query->selectSettingDatabase();
?>
<form id="form_camera_settings" class="margin_bottom" action="<?php echo $utility->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=settingsAction" method="post" novalidate="novalidate">
    <table class="table table-bordered table-striped">
        <tbody class="table_tbody">
            <tr>
                <td>
                    Template
                </td>
                <td>
                    <select id="form_camera_settings_template" class="form-control" name="form_camera_settings[template]" required="required">
                        <option value="0">Select</option>
                        <?php
                        foreach($utilityPrivate->createTemplatesList() as $key => $value) {
                            $selected = $value == $settingRow['template'] ? "selected" : "";
                            
                            echo "<option $selected value=\"{$key}\">{$value}</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    Server url
                </td>
                <td>
                    <input id="form_camera_settings_serverUrl" class="form-control" type="text" name="form_camera_settings[serverUrl]" value="<?php echo $settingRow['server_url']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Motion version
                </td>
                <td>
                    <select id="form_camera_settings_motionVersion" class="form-control" name="form_camera_settings[motionVersion]" required="required">
                        <option value="0">Select</option>
                        <?php
                        foreach($utilityPrivate->createMotionVersionList() as $key => $value) {
                            $selected = $value == $settingRow['motion_version'] ? "selected" : "";
                            
                            echo "<option $selected value=\"{$value}\">{$value}</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    
    <input id="form_camera_settings_token" class="form-control" type="hidden" name="form_camera_settings[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom" type="submit" value="Update"/>
</form>