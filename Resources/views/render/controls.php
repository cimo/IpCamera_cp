<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/IpCamera.php");

$utility = new Utility();

$settings = $utility->getSettings();

$ipCamera = new IpCamera();
?>
<div class="row">
    <div class="col-md-12">
        <table class="table table_borderless margin_clear">
            <tbody class="table_tbody">
                <tr>
                    <td class="horizontal_center">
                        <i id="camera_control_move_left_up" class="fa fa-arrow-circle-o-left fa-3x rotate_45 camera_controls"></i>
                    </td>
                    <td class="horizontal_center">
                        <i id="camera_control_move_up" class="fa fa-arrow-circle-o-up fa-3x camera_controls"></i>
                    </td>
                    <td class="horizontal_center">
                        <i id="camera_control_move_right_up" class="fa fa-arrow-circle-o-right fa-3x rotate_315 camera_controls"></i>
                    </td>
                </tr>
                <tr>
                    <td class="horizontal_center">
                        <i id="camera_control_move_left" class="fa fa-arrow-circle-o-left fa-3x camera_controls"></i>
                    </td>
                    <td class="horizontal_center">
                        <i class="fa fa-camera fa-3x camera_controls camera_control_picture"></i>
                    </td>
                    <td class="horizontal_center">
                        <i id="camera_control_move_right" class="fa fa-arrow-circle-o-right fa-3x camera_controls"></i>
                    </td>
                </tr>
                <tr>
                    <td class="horizontal_center">
                        <i id="camera_control_move_left_down" class="fa fa-arrow-circle-o-left fa-3x rotate_315 camera_controls"></i>
                    </td>
                    <td class="horizontal_center">
                        <i id="camera_control_move_down" class="fa fa-arrow-circle-o-down fa-3x camera_controls"></i>
                    </td>
                    <td class="horizontal_center">
                        <i id="camera_control_move_right_down" class="fa fa-arrow-circle-o-right fa-3x rotate_45 camera_controls"></i>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>