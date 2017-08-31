<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();

$htmlFiles = $root->getIpCamera()->createHtmlFiles();
?>
<div id="camera_files_table" class="margin_bottom">
    <?php require_once("{$root->getUtility()->getPathRoot()}/Resources/views/include/table_and_pagination.php"); ?>
    <div class="overflow_y_hidden table_min_height">
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
                <?php echo isset($htmlFiles['list']) == true ? $htmlFiles['list'] : ""; ?>
            </tbody>
        </table>
    </div>
</div>