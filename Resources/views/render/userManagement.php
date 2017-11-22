<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();
?>
<div id="cp_user_selection">
    <div class="row">
        <div class="col-md-6">
            <form id="form_cp_user_selection" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=userManagementSelectionAction" method="post" novalidate="novalidate">
                <div class="form-group">
                    <label class="control-label required" for="form_user_selection_id">Users</label>
                    <select id="form_user_selection_id" name="form_user_selection[id]" required="required" class="form-control">
                        <option value="" selected="selected">Select</option>
                    </select>
                </div>

                <input type="hidden" id="form_user_selection__token" name="form_user_selection[_token]" value="<?php echo $_SESSION['token']; ?>">

                <input class="button_custom" type="submit" value="Send">
            </form>
        </div>
    </div>
</div>

<div id="cp_user_selection_result"></div>