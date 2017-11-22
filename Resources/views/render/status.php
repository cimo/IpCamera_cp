<div class="margin_top overflow_y_hidden">
    <form id="form_camera_selection" action="<?php echo $root->getUtility()->getUrlRoot() ?>/Requests/IpCameraRequest.php?controller=selectionAction" method="post" novalidate="novalidate">
        <div class="form-group">
            <label class="control-label required" for="form_camera_selection_cameraNumber">Cameras</label>
            <select id="form_camera_selection_cameraNumber" class="form-control" name="form_camera_selection[cameraNumber]" required="required">
                <option value="-1">Select</option>
                <option value="0">New</option>
                <?php $root->getIpCamera()->generateSelectOptionFromMotionConfig(); ?>
            </select>
        </div>
        
        <input id="form_camera_selection_token" class="form-control" type="hidden" name="form_camera_selection[token]" value="<?php echo $_SESSION['token']; ?>"/>
        <input class="button_custom" type="submit" value="Send"/>
    </form>
    
    <ul class="margin_top margin_bottom">
        <li>
            <p class="display_inline text_bold">Motion detection:</p> <p id="camera_detection_status" class="display_inline"></p>
        </li>
    </ul>
</div>