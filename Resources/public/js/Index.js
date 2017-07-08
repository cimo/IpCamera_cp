/* global utility, loader, flashBag, ipCamera */

$(document).ready(function() {
    utility.linkPreventDefault();
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    utility.checkMobile(true);
    
    loader.create("font");
    
    ipCamera.init();
});