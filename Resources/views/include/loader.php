<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/Utility.php");

$utility = new Utility();

$settings = $utility->getSettings();
?>
<div id="loader">
    <p class="text">Loading...</p>
    <img class="image" src="<?php echo $utility->getUrlRoot(); ?>/Resources/public/images/templates/<?php echo $settings['template']; ?>/loading.gif" alt="loading.gif"/>
</div>