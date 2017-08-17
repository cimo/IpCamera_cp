/* global utility, loader, flashBag, ipCamera */

$(document).ready(function() {
    utility.linkPreventDefault();
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    utility.checkMobile(true);
    
    utility.checkWidth(window.settings.widthMobile);
    
    loader.create("font");
    
    ipCamera.init();
    ipCamera.changeView();
    
    $(window).resize(function() {
        ipCamera.changeView();
    });
});