/* global loader */

var download = new Download();

function Download() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.send = function(path, name) {
        window.location.href = window.url.root + "/Requests/DownloadRequest.php?token=" + window.session.token + "&path=" + path + "&name=" + $.trim(name);

        loader.remove();
    };
    
    // Functions private
}