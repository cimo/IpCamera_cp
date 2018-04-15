<div class="panel shadow authentication">
    <div class="panel-heading">
        <i class="fa fa-user fa-lg">&nbsp;</i>
        <h3 class="panel-title display_inline">Authentication</h3>
    </div>
    <div class="panel-body">
        <div class="authentication_container">
            <?php if (isset($_SESSION['userLogged']) == false) { ?>
                <form id="form_user_authentication" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/AuthenticationRequest.php?controller=authenticationEnterCheckAction" method="post" novalidate="novalidate">
                    <div class="form-group">
                        <label class="control-label required" for="_username">Username</label>
                        <input id="_username" class="form-control" type="text" name="_username" value="" required="required">
                    </div>
                    <div class="form-group">
                        <label class="control-label required" for="_password">Password</label>
                        <input id="_password" class="form-control" type="password" name="_password" value="" required="required">
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input class="button_custom" type="submit" value="Login">
                            <div class="checkbox remember_me_fix">
                                <label><input id="_remember_me" type="checkbox" name="_remember_me" value="1"> Remember me</label>
                            </div>
                        </div>
                    </div>
                    
                    <input id="_token" type="hidden" name="_token" value="<?php echo $_SESSION['token']; ?>">
                </form>
                <div class="horizontal_center">
                    <a class="button_custom btn-block" href="?routeRecoverPassword=true">Forgot password?</a>
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