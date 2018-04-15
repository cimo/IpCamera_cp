<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/System/Root.php");

if (isset($_SESSION['userLogged']) == false)
    return;

$root = new Root();

if ($root->getUtility()->checkApparatusUserId() == false)
    return;
?>
<img id="apparatus_video" class="img-responsive margin_auto image_preload" src="<?php echo $root->getIpCamera()->getVideoUrl(); ?>" alt="Video"/>
<div id="apparatus_video_area"></div>