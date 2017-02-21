<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/IpCamera.php");

$utility = new Utility();

$settings = $utility->getSettings();

$ipCamera = new IpCamera();
?>
<div class="row">
    <div class="col-md-6">
        <table class="table table_borderless margin_clear">
            <tbody class="table_tbody">
                <tr>
                    <td class="horizontal_center">
                        <img id="camera_control_move_left_up" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_left_up.png" alt="control_move_left_up.png"/>
                    </td>
                    <td class="horizontal_center">
                        <img id="camera_control_move_up" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_up.png" alt="control_move_up.png"/>
                    </td>
                    <td class="horizontal_center">
                        <img id="camera_control_move_right_up" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_right_up.png" alt="control_move_right_up.png"/>
                    </td>
                </tr>
                <tr>
                    <td class="horizontal_center">
                        <img id="camera_control_move_left" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_left.png" alt="control_move_left.png"/>
                    </td>
                    <td class="horizontal_center"></td>
                    <td class="horizontal_center">
                        <img id="camera_control_move_right" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_right.png" alt="control_move_right.png"/>
                    </td>
                </tr>
                <tr>
                    <td class="horizontal_center">
                        <img id="camera_control_move_left_down" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_left_down.png" alt="control_move_left_down.png"/>
                    </td>
                    <td class="horizontal_center">
                        <img id="camera_control_move_down" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_down.png" alt="control_move_down.png"/>
                    </td>
                    <td class="horizontal_center">
                        <img id="camera_control_move_right_down" class="camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/control_move_right_down.png" alt="control_move_right_down.png"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-6 horizontal_center">
        <table class="table table_borderless margin_clear camera_controls_2">
            <tbody class="table_tbody">
                <tr>
                    <td class="horizontal_center">
                        <img class="camera_control_picture camera_controls" src="<?php echo $utility->getUrlPublic(); ?>/images/templates/<?php echo $settings['template']; ?>/picture.png" alt="picture.png"/>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    /* global url */
    
    url.ipCameraControl = "<?php echo $ipCamera->getControlUrl(); ?>";
</script>