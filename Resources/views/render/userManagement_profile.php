<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

if (isset($_SESSION['user_logged']) == false)
    return;

$root = new Root();

$id = isset($_SESSION['user_management']['id']) == true ? $_SESSION['user_management']['id'] : 0;

$userRow = $root->getUtility()->getQuery()->selectUserDatabase($id);
?>
<form id="form_user_management" class="margin_top" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=userManagementProfileAction" method="post" novalidate="novalidate">
    <table class="table table-bordered table-striped margin_bottom">
        <tbody class="table_tbody">
            <tr>
                <td>
                    Role
                </td>
                <td>
                    <input id="form_user_management_roleUserId" class="form-control" type="hidden" name="form_user_management[roleUserId]" value="<?php echo $userRow['role_user_id']; ?>" required="required">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-tags"></i></span>
                        <?php echo $root->getIpCameraUtility()->createRoleUserHtml("form_user_management_roleUserId_field", true); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    Username
                </td>
                <td>
                    <input id="form_user_management_username" class="form-control" type="text" name="form_user_management[username]" value="<?php echo $userRow['username']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Email
                </td>
                <td>
                    <input id="form_user_management_email" class="form-control" type="text" name="form_user_management[email]" value="<?php echo $userRow['email']; ?>" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Password
                </td>
                <td>
                    <input id="form_user_management_password" class="form-control" type="password" name="form_user_management[password]" value="" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Password confirm
                </td>
                <td>
                    <input id="form_user_management_passwordConfirm" class="form-control" type="password" name="form_user_management[passwordConfirm]" value="" required="required"/>
                </td>
            </tr>
            <tr>
                <td>
                    Not locked
                </td>
                <td>
                    <select id="form_user_management_notLocked" class="form-control" name="form_user_management[notLocked]">
                        <?php
                        $elements = Array('No' => 0, 'Yes' => 1);
                        $options = "";
                        
                        foreach($elements as $key => $value) {
                            $selected = $userRow['not_locked'] == $value ? "selected=\"selected\"" : "";
                            
                            $options .= "<option $selected value=\"$value\">$key</option>";
                        }
                        
                        echo $options;
                        ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>

    <input id="form_camera_apparatusProfile_token" class="form-control" type="hidden" name="form_camera_apparatusProfile[token]" value="<?php echo $_SESSION['token']; ?>"/>
    <input class="button_custom" type="submit" value="Send"/>
</form>