<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

if (isset($_SESSION['userLogged']) == false)
    return;

$root = new Root();

$id = isset($_SESSION['userLogged']['id']) == true ? $_SESSION['userLogged']['id'] : 0;

$userRow = $root->getUtility()->getQuery()->selectUserDatabase($id);
?>
<p class="margin_clear"><b>Welcome:</b></p>
<p class="margin_clear"><?php echo $userRow['username']; ?></p>
<p class="margin_clear"><b>Role:</b></p>
<p class="margin_clear">
    <?php
    $role = "";

    foreach ($_SESSION['userLogged']['userRoleRow'] as $key => $value)
        $role .= " - $value";

    echo $role;
    ?>
</p>
<p class="margin_clear"><b>Last login:</b></p>
<p class="margin_clear"><?php echo $userRow['date_last_login']; ?></p>
<div class="margin_top">
    <a id="button_user_logout" class="button_custom logout_button" href="#">Logout</a>
</div>

<form id="form_userProfile_data" class="margin_top" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=userProfileDataAction" method="post" novalidate="novalidate">
    <fieldset class="accordion_container">
        <legend class="title"><i class="fa fa-chevron-circle-down icon"></i> Data</legend>
        <div class="content">
            <div class="form-group">
                <label class="control-label required" for="form_userProfile_data_email">Email</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input id="form_userProfile_data_email" class="form-control" type="text" name="form_userProfile_data[email]" value="<?php echo $userRow['email']; ?>" required="required">
                </div>
            </div>

            <input id="form_userProfile_data__token" type="hidden" name="form_userProfile_data[_token]" value="<?php echo $_SESSION['token']; ?>">

            <input class="button_custom margin_bottom" type="submit" value="Update"/>
        </div>
    </fieldset>
</form>

<form id="form_userProfile_password" class="margin_top" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=userProfilePasswordAction" method="post" novalidate="novalidate">
    <fieldset class="accordion_container">
        <legend class="title"><i class="fa fa-chevron-circle-down icon"></i> Password</legend>
        <div class="content">
            <div class="form-group">
                <label class="control-label required" for="form_userProfile_password_old">Old</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input id="form_userProfile_password_old" class="form-control" type="password" name="form_userProfile_password[old]" value="" required="required">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label required" for="form_userProfile_password_new">New</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input id="form_userProfile_password_new" class="form-control" type="password" name="form_userProfile_password[new]" value="" required="required">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label required" for="form_userProfile_password_newConfirm">New confirm</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input id="form_userProfile_password_newConfirm" class="form-control" type="password" name="form_userProfile_password[newConfirm]" value="" required="required">
                </div>
            </div>

            <input id="form_userProfile_password__token" type="hidden" name="form_userProfile_password[_token]" value="<?php echo $_SESSION['token']; ?>">

            <input class="button_custom margin_bottom" type="submit" value="Update"/>
        </div>
    </fieldset>
</form>