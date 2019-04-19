/* global utility, ajax, loader, flashBag, materialDesign */

var upload = new Upload();

function Upload() {
    // Vars
    var self = this;
    
    var urlRequest;
    var tagContainer;
    var tagProgressBar;
    var tagImageRefresh;
    var lockUrl;
    
    var inputLabel;
    
    var file;
    var fileName;
    var byteChunk;
    var isStop;
    
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
    
    self.setLockUrl = function(value) {
        lockUrl = value;
    };
    
    // Functions public
    self.init = function() {
        urlRequest = "";
        tagContainer = "";
        tagImageRefresh = "";
        tagProgressBar = "";
        lockUrl = "";
        
        inputLabel = "";
        
        file = null;
        fileName = "";
        byteChunk = 1048576;
        sizeStart = 0;
        sizeEnd = byteChunk;
        isStop = false;
        
        callbackComplete = null;
    };
    
    self.processFile = function(callback) {
        callbackComplete = callback;
        
        inputLabel = $(tagContainer).find(".material_upload label").text();
        
        $(tagContainer).find(".upload_chunk .file").on("change", "", function() {
            file = $(this)[0].files[0];
            fileName = file.name;
            
            ready();
        });
    };
    
    // Functions private
    function ready() {
        if (file === null)
            return;
        
        $(tagContainer).find(".controls").show();
        
        $(tagContainer).find(".controls .button_start").off("click").on("click", "", function() {
            start();
        });
        
        $(tagContainer).find(".controls .button_stop").off("click").on("click", "", function() {
            stop();
        });
    }
    
    function start() {
        if (file === null)
            return;
        
        $(tagContainer).find(".controls .button_start").prop("disabled", false);
        $(tagContainer).find(".controls .button_stop").prop("disabled", true);
        
        var formData = new FormData();
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                var response = jsonParse.response.upload !== undefined ? jsonParse.response.upload.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "start") {
                    isStop = false;
                    
                    $(tagContainer).find(".upload_chunk .mdc-linear-progress").show();
                    
                    $(tagContainer).find(".controls .button_start").prop("disabled", true);
                    $(tagContainer).find(".controls .button_stop").prop("disabled", false);
                    
                    chunk();
                }
            }
        };
        
        xhr.open("post", urlRequest + "&action=start&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function send(chunkSize) {
        if (file === null)
            return;
        
        var formData = new FormData();
        
        formData.append("file", chunkSize);
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                var response = jsonParse.response.upload !== undefined ? jsonParse.response.upload.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "send") {
                    if (isStop === false) {
                        if (sizeStart < file.size)
                            chunk();
                        else
                            complete();
                    }
                }
            }
        };
        
        xhr.open("post", urlRequest + "&action=send&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function complete() {
        if (file === null)
            return;
        
        materialDesign.linearProgress(tagProgressBar, sizeStart, file.size);
        
        var formData = new FormData();
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                var response = jsonParse.response.upload !== undefined ? jsonParse.response.upload.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "complete") {
                    resetValue();
                    
                    if (tagImageRefresh !== "")
                        utility.imageRefresh(tagImageRefresh, 1);
                    
                    if (lockUrl !== "")
                        lock(jsonParse.response.values.lockName);
                    
                    if (response.messages.success !== undefined)
                        flashBag.show(response.messages.success);
                    
                    if (callbackComplete !== null)
                        callbackComplete();
                }
            }
        };
        
        xhr.open("post", urlRequest + "&action=complete&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function stop() {
        if (file === null)
            return;
        
        isStop = true;
        
        $(tagContainer).find(".controls .button_start").attr("disabled", false);
        $(tagContainer).find(".controls .button_stop").attr("disabled", true);
        
        var formData = new FormData();
        
        var xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                var jsonParse = JSON.parse(xhr.response);
                
                var response = jsonParse.response.upload !== undefined ? jsonParse.response.upload.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "stop") {
                    resetValue();
                    
                    if (response.messages.success !== undefined)
                        flashBag.show(response.messages.success);
                }
            }
        };
        
        xhr.open("post", urlRequest + "&action=stop&fileName=" + fileName);
        xhr.send(formData);
    }
    
    function chunk() {
        if (file === null)
            return;
        
        var chunkSize = file.slice(sizeStart, sizeEnd);
        
        send(chunkSize);
        
        materialDesign.linearProgress(tagProgressBar, sizeStart, file.size);
        
        sizeStart = sizeEnd;
        sizeEnd = sizeStart + byteChunk;
    }
    
    function lock(lockName) {
        $(".loader_back").show();
        
        $(tagContainer).find(".popupWait").show();
        
        ajax.send(
            false,
            lockUrl,
            "post",
            {
                'lockName': lockName
            },
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            null,
            function(xhr) {
                ajax.reply(xhr, "");
                
                if (xhr.response.status !== undefined) {
                    $(tagContainer).find(".popupWait .close").off("click").on("click", "", function() {
                        if ($(tagContainer).find(".popupWait .close").prop("disabled") === false) {
                            $(".loader_back").hide();
                            
                            $(tagContainer).find(".popupWait .result").html("");
                            $(tagContainer).find(".popupWait .result").hide();
                            
                            $(tagContainer).find(".popupWait .content").show();
                            
                            $(tagContainer).find(".popupWait").hide();
                        }
                    });
                    
                    if (xhr.response.values !== undefined) {
                        if (xhr.response.values.count !== null) {
                            $(tagContainer).find(".popupWait .content .status").html(xhr.response.values.count + "/" + xhr.response.values.total);
                            
                            materialDesign.linearProgress(tagContainer + " .popupWait .content .mdc-linear-progress", xhr.response.values.count, xhr.response.values.total);
                        }
                    }
                    
                    if (xhr.response.status === "error") {
                        $(tagContainer).find(".popupWait .close").prop("disabled", false);
                        
                        $(tagContainer).find(".popupWait .content .status").html("");
                        materialDesign.linearProgress(tagContainer + " .popupWait .content .mdc-linear-progress", 0, 0);
                        $(tagContainer).find(".popupWait .content").hide();
                        
                        $(tagContainer).find(".popupWait .result").show();
                        $(tagContainer).find(".popupWait .result").html("<p>" + window.textUpload.label_1 + "</p>");
                    }
                    else if (xhr.response.status === "finish") {
                        $(tagContainer).find(".popupWait .close").prop("disabled", false);
                        
                        $(tagContainer).find(".popupWait .content .status").html("");
                        materialDesign.linearProgress(tagContainer + " .popupWait .content .mdc-linear-progress", 0, 0);
                        $(tagContainer).find(".popupWait .content").hide();
                        
                        $(tagContainer).find(".popupWait .result").show();
                        $(tagContainer).find(".popupWait .result").html("<p>" + window.textUpload.label_2 + "</p>");
                    }
                    else if (xhr.response.status === "loop")
                        lock(xhr.response.values.lockName);
                }
            },
            null,
            null
        );
    }
    
    function resetValue() {
        file = null;
        fileName = "";
        byteChunk = 1048576;
        sizeStart = 0;
        sizeEnd = byteChunk;
        
        $(tagContainer).find(".upload_chunk .file").val("");
        
        $(tagContainer).find(".upload_chunk .mdc-linear-progress").hide();
        
        $(tagContainer).find(".controls .button_start").attr("disabled", false);
        $(tagContainer).find(".controls .button_stop").attr("disabled", true);
        
        $(tagContainer).find(".controls").hide();
        
        $(tagContainer).find(".material_upload label").text(inputLabel);
    }
}