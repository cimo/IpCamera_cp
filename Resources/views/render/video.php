<?php
require_once(dirname(dirname(dirname(__DIR__))) . "/Classes/IpCamera.php");

$ipCamera = new IpCamera();
?>
<img id="camera_video" class="img-responsive margin_auto image_preload" src="<?php echo $ipCamera->getVideoUrl(); ?>" alt="Video"/>
<div id="camera_video_area"></div>