<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Query.php");

$utility = new Utility();
$query = new Query($utility->getDatabase());

$settingRow = $query->selectSettingDatabase();
?>
<div id="loader">
    <p class="text">Loading...</p>
    <img class="image" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/images/templates/<?php echo $settingRow['template']; ?>/loading.gif" alt="loading.gif"/>
</div>