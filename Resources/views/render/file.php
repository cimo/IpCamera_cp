<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

$root = new Root();

$htmlTable = $root->getIpCamera()->createListHtml();
?>
<div id="camera_file_table" class="margin_bottom">
    <?php require_once("{$root->getUtility()->getPathRoot()}/Resources/views/include/table_and_pagination.php"); ?>
    <div class="overflow_y_hidden table_min_height">
        <table class="table table-bordered table-striped margin_bottom">
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
                <?php echo isset($htmlTable['listHtml']) == true ? $htmlTable['listHtml'] : ""; ?>
            </tbody>
        </table>
    </div>
</div>