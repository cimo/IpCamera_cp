/* global utility, loader, flashBag, authentication, ipCamera */

$(document).ready(function() {
    utility.checkMobile(true);
    
    utility.linkPreventDefault();
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    loader.create("font");
    
    authentication.init();
    
    ipCamera.init();
    ipCamera.changeView();
    
    $(window).resize(function() {
        ipCamera.changeView();
    });
});