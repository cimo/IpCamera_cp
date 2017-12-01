<a class="button_custom" href="<?php echo $root->getUtility()->getUrlRoot() . "/web/index.php" ?>">Back</a>
<div id="recover_password">
    <?php if (isset($_GET['helpCode']) == false) { ?>
        <h3 class="margin_clear">Recover password form</h3>
        <form id="form_recover_password" class="margin_top" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/RecoverPasswordRequest.php?controller=dataCheckAction" method="post" novalidate="novalidate">
            <div class="form-group">
                <label class="control-label required" for="form_recover_password_email">Email</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                    <input id="form_recover_password_email" class="form-control" type="email" name="form_recover_password[email]" required="required">
                </div>
            </div>

            <input id="_token" type="hidden" name="_token" value="<?php echo $_SESSION['token']; ?>">

            <input class="button_custom" type="submit" value="Send"/>
        </form>
    <?php
    }
    else {
    ?>
        <h3 class="margin_clear">Change password form</h3>
        <form id="form_change_password" class="margin_top" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/RecoverPasswordRequest.php?controller=dataChangeAction" method="post" novalidate="novalidate">
            <div class="form-group">
                <label class="control-label required" for="form_change_password_password">Password</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input id="form_change_password_password" class="form-control" type="password" name="form_change_password[password]" required="required">
                </div>
            </div>
            <div class="form-group">
                <label class="control-label required" for="form_change_password_passwordConfirm">Password confirm</label>
                <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-key"></i></span>
                    <input id="form_change_password_passwordConfirm" class="form-control" type="password" name="form_change_password[passwordConfirm]" required="required">
                </div>
            </div>
            
            <input id="_token" type="hidden" name="form_change_password[helpCode]" value="<?php echo $_GET['helpCode']; ?>">
            <input id="_token" type="hidden" name="_token" value="<?php echo $_SESSION['token']; ?>">

            <input class="button_custom" type="submit" value="Send"/>
        </form>
    <?php } ?>
</div>