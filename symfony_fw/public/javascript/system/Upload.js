/* global utility, loader, flashBag */

var upload = new Upload();

function Upload() {
    // Vars
    var self = this;
    
    var urlRequest;
    var tagContainer;
    var tagProgressBar;
    var tagImageRefresh;
    
    var file;
    var fileName;
    var byteChunk;
    
    // Properties
    self.setUrlRequest = function(value) {
        urlRequest = value;
    };
    
    self.setTagContainer = function(value) {
        tagContainer = value;
    };
    
    self.setTagProgressBar = function(value) {
        tagProgressBar = value;
    };
    
    self.setTagImageRefresh = function(value) {
        tagImageRefresh = value;
    };
    
    // Functions public
    self.init = function() {
        urlRequest = "";
        tagContainer = "";
        tagImageRefresh = "";
        tagProgressBar = "";
        
        file = null;
        fileName = "";
        byteChunk = 1048576;
        sizeStart = 0;
        sizeEnd = byteChunk;
    };
    
    self.processFile = function() {
        $(tagContainer).find(".upload_chunk .file").on("change", "", function() {
            file = $(this)[0].files[0];
            fileName = file.name;
            
            start();
        });
    };
    
    // Functions private
    function start() {
        if (file === null)
            return;
        
        var formData = new FormData();
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                if (jsonParse.response === undefined || xhr.status !== 200)
                    return;
                
                if (jsonParse.response.upload.processFile.status === "start") {
                    $(tagContainer).find(".upload_chunk .mdc-linear-progress").show();
                    
                    chunk();
                }
            }
        };
        
        xhr.open("post", urlRequest + "?action=start&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function upload(chunkSize) {
        if (file === null)
            return;
        
        var formData = new FormData();
        
        formData.append("file", chunkSize);
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                if (jsonParse.response === undefined || xhr.status !== 200)
                    return;
                
                if (jsonParse.response.upload.processFile.status === "upload") {
                    if (sizeStart < file.size)
                        chunk();
                    else
                        complete();
                }
            }
        };
        
        xhr.open("post", urlRequest + "?action=upload&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function complete() {
        if (file === null)
            return;
        
        var formData = new FormData();
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            resetValue();
            
            if (xhr.readyState === 4) {
                if (xhr.response !== "") {
                    var jsonParse = JSON.parse(xhr.response);

                    if (jsonParse.response === undefined || xhr.status !== 200)
                        return;

                    if (jsonParse.response.upload.processFile.status === "complete") {
                        if (tagImageRefresh !== "")
                            utility.imageRefresh(tagImageRefresh, 1);

                        if (jsonParse.response.upload.processFile !== undefined)
                            flashBag.show(jsonParse.response.upload.processFile.text);
                    }
                }
                else {
                    if (tagImageRefresh !== "")
                        utility.imageRefresh(tagImageRefresh, 1);
                    
                    flashBag.show(window.textUpload.label_1);
                }
            }
        };
        
        xhr.open("post", urlRequest + "?action=complete&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function chunk() {
        if (file === null)
            return;
        
        var chunkSize = file.slice(sizeStart, sizeEnd);
        
        upload(chunkSize);
        
        materialDesign.linearProgress(tagProgressBar, sizeStart, file.size);
        
        sizeStart = sizeEnd;
        sizeEnd = sizeStart + byteChunk;
    }
    
    function resetValue() {
        file = null;
        fileName = "";
        byteChunk = 1048576;
        sizeStart = 0;
        sizeEnd = byteChunk;
        
        $(tagContainer).find(".upload_chunk .file").val("");
        
        $(tagContainer).find(".upload_chunk .mdc-linear-progress").hide();
    }
}