/* global utility, loader, flashBag, ipCamera */

$(document).ready(function() {
    utility.linkPreventDefault();
    
    utility.mobileCheck(true);
    
    utility.watch("#flashBag", flashBag.sessionActivity);
    
    loader.create("font");
    
    ipCamera.status();
});