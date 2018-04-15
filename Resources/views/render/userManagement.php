<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

if (isset($_SESSION['userLogged']) == false)
    return;

$root = new Root();  
?>
<div id="user_management_selection">
    <div class="row">
        <div class="col-md-6">
            <form id="form_user_management_selection" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=userManagementSelectionAction" method="post" novalidate="novalidate">
                <div class="form-group">
                    <label class="control-label required" for="form_user_management_selection_id">Users</label>
                    <select id="form_user_management_selection_id" name="form_user_management_selection[id]" required="required" class="form-control">
                        <option value="-1">Select</option>
                        <option value="0">New</option>
                        <?php $root->getIpCamera()->generateSelectOptionUser(); ?>
                    </select>
                </div>

                <input id="form_user_management_selection__token" type="hidden" name="form_user_management_selection[_token]" value="<?php echo $_SESSION['token']; ?>">

                <input class="button_custom" type="submit" value="Send">
            </form>
        </div>
    </div>
</div>

<div id="user_management_selection_result"></div>