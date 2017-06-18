<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Root.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/IpCamera.php");

$utility = new Utility();
$settings = $utility->getSettings();

$root = new Root();

$ipCamera = new IpCamera();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo $utility->getWebsiteName(); ?></title>
        <!-- Meta -->
        <meta charset="utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">-->
        <!-- Favicon -->
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/images/templates/<?php echo $settings['template']; ?>/favicon.ico" rel="icon" type="image/x-icon">
        <!-- Css -->
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/css/lib/jquery-ui_1.12.1.min.css" rel="stylesheet"/>
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/css/lib/jquery-ui_1.12.1_structure.min.css" rel="stylesheet"/>
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/css/lib/bootstrap_3.3.7.min.css" rel="stylesheet"/>
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/css/lib/bootstrap-switch_3.3.2.min.css" rel="stylesheet"/>
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/css/lib/font-awesome_4.7.0_custom.min.css" rel="stylesheet">
        <link href="<?php echo $utility->getUrlRoot(); ?>/Resources/public/css/basic.css" rel="stylesheet"/>
    </head>
    <body class="user_select_none">
        <div class="container-fluid">
            <p class="logo_desktop display_desktop"><?php echo $utility->getWebsiteName(); ?></p>
            <p class="logo_mobile display_mobile"><?php echo $utility->getWebsiteName(); ?></p>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    require_once("{$utility->getPathRoot()}/Resources/views/include/loader.php");
                    require_once("{$utility->getPathRoot()}/Resources/views/include/flashBag.php");
                    require_once("{$utility->getPathRoot()}/Resources/views/include/popup_easy.php");
                    ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-primary">
                        <div class="panel-heading clearfix">
                            <div class="pull-left">
                                <h3 class="panel-title">Video</h3>
                            </div>
                            <div class="pull-right display_mobile">
                                <input id="camera_control_swipe_switch" type="checkbox" data-on-color="success" data-off-color="danger"/>
                                <i class="fa fa-camera fa-3x camera_controls camera_control_picture"></i>
                            </div>
                        </div>
                        <div class="panel-body overflow_hidden padding_clear">
                            <div id="camera_video_result"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div id="controls_container" class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Controls</h3>
                        </div>
                        <div class="panel-body overflow_hidden">
                            <div id="camera_controls_result"></div>
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
                                    <a id="actions_tab_2" data-toggle="tab" href="#actions_tab_content_3">Profile</a>
                                </li>
                                <li>
                                    <a id="actions_tab_2" data-toggle="tab" href="#actions_tab_content_2">Files</a>
                                </li>
                            </ul>
                            <div class="tab-content clearfix camera_tab_container">
                                <div id="actions_tab_content_1" class="tab-pane active">
                                    <?php require_once("{$utility->getPathRoot()}/Resources/views/render/status.php"); ?>
                                </div>
                                <div id="actions_tab_content_3" class="tab-pane">
                                    <div class="margin_top overflow_y_hidden">
                                        <div id="camera_profile_result"></div>
                                    </div>
                                </div>
                                <div id="actions_tab_content_2" class="tab-pane">
                                    <div class="margin_top">
                                        <div id="camera_files_result"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Javascript -->
        <script type="text/javascript">
            var session = {
                'token': "<?php echo $_SESSION['token']; ?>",
                'userActivity': "<?php echo $_SESSION['user_activity']; ?>",
                'cameraNumber': "<?php echo $_SESSION['camera_number']; ?>"
            };
            
            var path = {
                'documentRoot': "<?php echo $_SERVER['DOCUMENT_ROOT']; ?>",
                'root': "<?php echo $utility->getPathRoot(); ?>"
            };
            
            var url = {
                'root': "<?php echo $utility->getUrlRoot(); ?>",
                'cameraControl': "<?php echo $ipCamera->getControlUrl(); ?>"
            };
            
            var text = {
                'warning': "Warning!",
                'ok': "Ok",
                'abort': "Abort",
                'ajaxConnectionError': "Connection error, please reload the page."
            };
            
            var settings = {
                'maxWidth': "992px",
                'minWidth': "992px",
                'template': "<?php echo $settings['template']; ?>",
                'serverUrl': "<?php echo $settings['server_url']; ?>"
            };
        </script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/lib/jquery_3.1.1.min.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/lib/jquery-ui_1.12.1.min.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/lib/jquery-mobile_1.5.0.min.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/lib/bootstrap_3.3.7.min.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/lib/bootstrap-switch_3.3.2.min.js"></script>
        <!--[if lte IE 9]>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/lib/media-match_2.0.2.min.js"></script>
        <![endif]-->
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/Utility.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/Ajax.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/Loader.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/FlashBag.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/PopupEasy.js"></script>
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/Table.js"></script>
                <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/Download.js"></script>
        
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/IpCamera.js"></script>
        
        <script type="text/javascript" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/js/Index.js"></script>
    </body>
</html>