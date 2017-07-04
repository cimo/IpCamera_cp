/* global utility, loader, flashBag, ipCamera */

$(document).ready(function() {
    utility.linkPreventDefault();
    
    utility.mobileCheck(true);
    
    loader.create("font");
    
    ipCamera.init();
});