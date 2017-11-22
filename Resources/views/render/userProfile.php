<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();
?>
<p class="margin_clear"><b>Welcome:</b></p>
<p class="margin_clear"><?php echo $_SESSION['userLogged']['username']; ?></p>
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
<p class="margin_clear"><?php echo $_SESSION['userLogged']['date_last_login']; ?></p>
<div class="margin_top horizontal_center">
    <a id="button_user_logout" class="button_custom btn-block logout_button" href="#">Logout</a>
</div>
<?php require_once("{$root->getUtility()->getPathRoot()}/Resources/views/render/userProfile_password.php"); ?>