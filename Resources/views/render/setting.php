<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();

$settingRow = $root->getUtility()->getQuery()->selectSettingDatabase();
?>
<form id="form_camera_setting" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=settingAction" method="post" novalidate="novalidate">
    <table class="table table-bordered table-striped margin_bottom">
        <tbody class="table_tbody">
            <tr>
                <td>
                    Template
                </td>
                <td>
                    <select id="form_camera_setting_template" class="form-control" name="form_camera_setting[template]" required="required">
                        <?php
                        foreach($root->getIpCameraUtility()->createTemplatesList() as $key => $value) {
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
                    <input id="form_camera_setting_serverUrl" class="form-control" type="text" name="form_camera_setting[serverUrl]" value="<?php echo $settingRow['server_url']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Motion version
                </td>
                <td>
                    <select id="form_camera_setting_motionVersion" class="form-control" name="form_camera_setting[motionVersion]" required="required">
                        <?php
                        foreach($root->getIpCameraUtility()->createMotionVersionList() as $key => $value) {
                            $selected = $value == $settingRow['motion_version'] ? "selected" : "";
                            
                            echo "<option $selected value=\"{$value}\">{$value}</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    
    <input id="form_camera_setting_token" class="form-control" type="hidden" name="form_camera_setting[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom margin_bottom" type="submit" value="Update"/>
</form>