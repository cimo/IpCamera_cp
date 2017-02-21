<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/IpCamera.php");

$utility = new Utility();

$ipCamera = new IpCamera();

$files = $ipCamera->filesList();
?>
<div id="camera_files_table" class="margin_bottom">
    <?php require_once("{$utility->getPathRootFull()}/Resources/views/include/table.php"); ?>
    
    <div class="overflow_y_hidden">
        <table class="table table-bordered table-striped">
            <thead class="table_thead">
                <tr>
                    <th class="cursor_pointer">
                        #
                        <i class="fa fa-caret-down"></i>
                        <i class="fa fa-caret-up"></i>
                    </th>
                    <th class="cursor_pointer">
                        Name
                        <i class="fa fa-caret-down"></i>
                        <i class="fa fa-caret-up"></i>
                    </th>
                    <th class="cursor_pointer">
                        Size
                        <i class="fa fa-caret-down"></i>
                        <i class="fa fa-caret-up"></i>
                    </th>
                    <th class="cursor_pointer">
                        Download
                        <i class="fa fa-caret-down"></i>
                        <i class="fa fa-caret-up"></i>
                    </th>
                    <th class="cursor_pointer">
                        Remove
                        <i class="fa fa-caret-down"></i>
                        <i class="fa fa-caret-up"></i>
                    </th>
                </tr>
            </thead>
            <tbody class="table_tbody">
                <?php echo $files['list']; ?>
            </tbody>
        </table>
    </div>
</div>