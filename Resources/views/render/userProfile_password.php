<div class="row">
    <div class="col-md-12">
        <h3 class="form_title">Profile password form</h3>
        <form id="form_userProfile_password" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=userProfilePasswordAction" method="post" novalidate="novalidate">
            <fieldset class="accordion_container">
                <legend class="title"><i class="fa fa-chevron-circle-down icon"></i> Password</legend>
                <div class="content">
                    <div class="form-group">
                        <label class="control-label required" for="form_userProfile_password_old">Old</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>

                            <input type="password" id="form_userProfile_password_old" name="form_userProfile_password[old]" required="required" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required" for="form_userProfile_password_new">New</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>

                            <input type="password" id="form_userProfile_password_new" name="form_userProfile_password[new]" required="required" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required" for="form_userProfile_password_newConfirm">New confirm</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-key"></i></span>

                            <input type="password" id="form_userProfile_password_newConfirm" name="form_userProfile_password[newConfirm]" required="required" class="form-control">
                        </div>
                    </div>

                    <input type="hidden" id="form_userProfile_password__token" name="form_userProfile_password[_token]" value="<?php echo $_SESSION['token']; ?>">

                    <input class="button_custom margin_bottom" type="submit" value="Update"/>
                </div>
            </fieldset>
        </form>
    </div>
</div>