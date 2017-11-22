<div class="panel shadow authentication">
    <div class="panel-heading">
        <i class="fa fa-user fa-lg">&nbsp;</i>
        <h3 class="panel-title display_inline">Authentication</h3>
    </div>
    <div class="panel-body">
        <div class="authentication_container">
            <?php if (isset($_SESSION['userLogged']) == false && empty($_SESSION['userLogged']) == true) { ?>
                <form id="form_user_authentication" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/AuthenticationRequest.php?controller=authenticationEnterCheckAction" method="post" novalidate="novalidate">
                    <div class="form-group">
                        <label class="control-label required" for="_username">Username</label>
                        <input type="text" id="_username" name="_username" required="required" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="control-label required" for="_password">Password</label>
                        <input type="password" id="_password" name="_password" required="required" class="form-control">
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input class="button_custom" type="submit" value="Login">
                            <div class="checkbox remember_me_fix">
                                <label><input type="checkbox" id="_remember_me" name="_remember_me" value="1"> Remember me</label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="_token" name="_token" value="<?php echo $_SESSION['token']; ?>">
                </form>
                <div class="horizontal_center">
                    <a class="button_custom btn-block" href="#">Forgot password?</a>
                </div>
            <?php
            }
            else {
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
            <?php
            }
            ?>
        </div>
    </div>
</div>