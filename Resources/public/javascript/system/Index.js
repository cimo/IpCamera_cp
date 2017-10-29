/* global utility, loader, flashBag, ipCamera */

$(document).ready(function() {
    utility.checkMobile(true);
    
    utility.linkPreventDefault();
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    loader.create("font");
    
    ipCamera.init();
    ipCamera.changeView();
    
    $(window).resize(function() {
        ipCamera.changeView();
    });
});