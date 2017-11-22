<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();

$settingRow = $root->getUtility()->getQuery()->selectSettingDatabase();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $root->getUtility()->getWebsiteName(); ?></title>
        <!-- Meta -->
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">-->
        <!-- Favicon -->
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/images/templates/<?php echo $settingRow['template']; ?>/favicon.ico" rel="icon" type="image/x-icon">
        <!-- Css -->
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/library/jquery-ui_1.12.1.min.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/library/jquery-ui_1.12.1_structure.min.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/library/bootstrap_3.3.7.min.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/library/bootstrap-switch_3.3.2.min.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/library/font-awesome_4.7.0_custom.min.css" rel="stylesheet">
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/system/<?php echo $settingRow['template']; ?>.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/loader.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/flashBag.css" rel="stylesheet"/>
        <link href="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/css/table_and_pagination.css" rel="stylesheet"/>
    </head>
    <body class="user_select_none">
        <div>
            <div class="logo_big display_desktop">
                <img class="logo_svg" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/images/templates/<?php echo $settingRow['template']; ?>/logo.svg"/>
                <p class="logo_text"><?php echo $root->getUtility()->getWebsiteName(); ?></p>
            </div>
            <div class="logo_small display_mobile">
                <img class="logo_svg" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/images/templates/<?php echo $settingRow['template']; ?>/logo.svg"/>
                <p class="logo_text"><?php echo $root->getUtility()->getWebsiteName(); ?></p>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <?php require_once("{$root->getUtility()->getPathRoot()}/Resources/views/include/flashBag.php"); ?>
                </div>
            </div>
            <?php
            if (isset($_SESSION['userLogged']) == false && empty($_SESSION['userLogged']) == true)
                require_once("{$root->getUtility()->getPathRoot()}/Resources/views/render/module/authentication.php");
            else {
            ?>
                <div class="row">
                    <div class="col-md-8">
                        <div class="panel panel-primary">
                            <div class="panel-heading clearfix">
                                <div class="pull-left">
                                    <h3 class="panel-title">Video</h3>
                                </div>
                                <div class="pull-right display_mobile">
                                    <input id="camera_control_swipe_switch" type="checkbox" data-on-text="Drag on" data-on-color="success" data-off-text="Drag off" data-off-color="danger"/>
                                    <i class="fa fa-camera fa-3x camera_control camera_control_picture"></i>
                                </div>
                            </div>
                            <div class="panel-body overflow_hidden padding_clear">
                                <div id="camera_video_result"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div id="control_container" class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">Controls</h3>
                            </div>
                            <div class="panel-body overflow_hidden">
                                <div id="camera_control_result"></div>
                            </div>
                        </div>
                        <div class="panel panel-primary">
                            <div class="panel-heading">
                                <h3 class="panel-title">Actions</h3>
                            </div>
                            <div class="panel-body overflow_hidden">
                                <ul class="nav nav-tabs">
                                    <li class="active">
                                        <a id="actions_tab_1" data-toggle="tab" href="#actions_tab_content_1">Status</a>
                                    </li>
                                    <li>
                                        <a id="actions_tab_2" data-toggle="tab" href="#actions_tab_content_2">Apparatus profile</a>
                                    </li>
                                    <li>
                                        <a id="actions_tab_3" data-toggle="tab" href="#actions_tab_content_3">Files</a>
                                    </li>
                                    <li>
                                        <a id="actions_tab_4" data-toggle="tab" href="#actions_tab_content_4">User profile</a>
                                    </li>
                                    <li>
                                        <a id="actions_tab_5" data-toggle="tab" href="#actions_tab_content_5">Users management</a>
                                    </li>
                                    <li>
                                        <a id="actions_tab_6" data-toggle="tab" href="#actions_tab_content_6">Settings</a>
                                    </li>
                                </ul>
                                <div class="tab-content clearfix camera_tab_container">
                                    <div id="actions_tab_content_1" class="tab-pane active">
                                        <?php require_once("{$root->getUtility()->getPathRoot()}/Resources/views/render/status.php"); ?>
                                    </div>
                                    <div id="actions_tab_content_2" class="tab-pane">
                                        <div class="margin_top overflow_y_hidden">
                                            <div id="camera_apparatusProfile_result"></div>
                                        </div>
                                    </div>
                                    <div id="actions_tab_content_3" class="tab-pane">
                                        <div class="margin_top">
                                            <div id="camera_file_result"></div>
                                        </div>
                                    </div>
                                    <div id="actions_tab_content_4" class="tab-pane">
                                        <div class="margin_top margin_bottom">
                                            <div id="camera_userProfile_result"></div>
                                        </div>
                                    </div>
                                    <div id="actions_tab_content_5" class="tab-pane">
                                        <div class="margin_top margin_bottom">
                                            <div id="camera_userManagement_result"></div>
                                        </div>
                                    </div>
                                    <div id="actions_tab_content_6" class="tab-pane">
                                        <div class="margin_top">
                                            <div id="camera_setting_result"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
        <?php
        require_once("{$root->getUtility()->getPathRoot()}/Resources/views/include/loader.php");
        require_once("{$root->getUtility()->getPathRoot()}/Resources/views/include/popup_easy.php");
        ?>
        <!-- Javascript -->
        <script type="text/javascript">
            var session = {
                'token': "<?php echo $_SESSION['token']; ?>",
                'userActivity': "<?php echo $_SESSION['user_activity']; ?>",
                'cameraNumber': "<?php echo $_SESSION['camera_number']; ?>"
            };
            
            var path = {
                'documentRoot': "<?php echo $_SERVER['DOCUMENT_ROOT']; ?>",
                'root': "<?php echo $root->getUtility()->getPathRoot(); ?>"
            };
            
            var url = {
                'root': "<?php echo $root->getUtility()->getUrlRoot(); ?>",
                'cameraControl': "<?php echo $root->getIpCamera()->getControlUrl(); ?>"
            };
            
            var text = {
                'warning': "Warning!",
                'ok': "Ok",
                'abort': "Abort",
                'ajaxConnectionError': "Connection error, please reload the page."
            };
            
            var setting = {
                'widthMobile': 991,
                'widthDesktop': 992,
                'template': "<?php echo $settingRow['template']; ?>",
                'serverUrl': "<?php echo $settingRow['server_url']; ?>"
            };
        </script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/library/jquery_3.1.1.min.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/library/jquery-ui_1.12.1.min.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/library/jquery-mobile_1.5.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/library/bootstrap_3.3.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/library/bootstrap-switch_3.3.2.min.js"></script>
        <!--[if lte IE 9]>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/library/media-match_2.0.2.min.js"></script>
        <![endif]-->
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/system/Utility.js"></script>
        
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/Ajax.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/Loader.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/FlashBag.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/PopupEasy.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/TableAndPagination.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/Download.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/Authentication.js"></script>
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/IpCamera.js"></script>
        
        <script type="text/javascript" src="<?php echo $root->getUtility()->getUrlRoot(); ?>/Resources/public/javascript/system/Index.js"></script>
    </body>
</html>