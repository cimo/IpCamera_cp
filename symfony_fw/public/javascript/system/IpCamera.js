/* global ajax */

var ipCamera = new IpCamera();

function IpCamera() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        var srcs = new Array();
        
        $.each($(".video_container").find("img"), function(key, value) {
            srcs.push($(value).attr("src"));
        });
        
        setInterval(function() {
            $.each($(".video_container").find("img"), function(key, value) {
                $(value).attr("src", srcs[key] + "&time=" + new Date().getTime());
            });
        }, 1000);
    };
    
    // Functions private
}