"use strict";

/* global helper, ajax, loader, flashBag, materialDesign */

class UploadChunk {    
    // Properties
    set setUrlRequest(value) {
        this.urlRequest = value;
    }
    
    set setTagContainer(value) {
        this.tagContainer = value;
    }
    
    set setTagProgressBar(value) {
        this.tagProgressBar = value;
    }
    
    set setTagImageRefresh(value) {
        this.tagImageRefresh = value;
    }
    
    set setLockUrl(value) {
        this.lockUrl = value;
    }
    
    // Functions public
    constructor() {
        this.urlRequest = "";
        this.tagContainer = "";
        this.tagProgressBar = "";
        this.tagImageRefresh = "";
        this.lockUrl = "";
        
        this.inputLabel = "";
        
        this.byteChunkInit = 1048576;
        
        this.file = null;
        this.fileName = "";
        this.byteChunk = this.byteChunkInit;
        this.sizeStart = 0;
        this.sizeEnd = this.byteChunk;
        this.isStop = false;
        
        this.callbackComplete = null;
    }
    
    processFile = (callback) => {
        if (callback !== undefined)
            this.callbackComplete = callback;
        
        this.inputLabel = $(this.tagContainer).find(".material_upload label").text();
        
        $(this.tagContainer).find(".upload_chunk .file").on("change", "", (event) => {
            this.file = $(event.target)[0].files[0];
            this.fileName = this.file.name;
            
            this.ready();
        });
    }
    
    // Functions private
    ready = () => {
        if (this.file === null)
            return;
        
        $(this.tagContainer).find(".controls").show();
        
        $(this.tagContainer).find(".controls .button_start").off("click").on("click", "", (event) => {
            this.start();
        });
        
        $(this.tagContainer).find(".controls .button_stop").off("click").on("click", "", (event) => {
            this.stop();
        });
    }
    
    start = () => {
        if (this.file === null)
            return;
        
        $(this.tagContainer).find(".controls .button_start").prop("disabled", false);
        $(this.tagContainer).find(".controls .button_stop").prop("disabled", true);
        
        let formData = new FormData();
        
        let xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                let jsonParse = JSON.parse(xhr.response);
                
                let response = jsonParse.response.uploadChunk !== undefined ? jsonParse.response.uploadChunk.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    this.resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "start") {
                    this.isStop = false;
                    
                    $(this.tagContainer).find(".upload_chunk .mdc-linear-progress").show();
                    
                    $(this.tagContainer).find(".controls .button_start").prop("disabled", true);
                    $(this.tagContainer).find(".controls .button_stop").prop("disabled", false);
                    
                    this.chunk();
                }
            }
        };
        
        xhr.open("post", this.urlRequest + "&action=start&fileName=" + fileName);
        xhr.send(formData);
    }
    
    send = (chunkSize) => {
        if (this.file === null)
            return;
        
        let formData = new FormData();
        
        formData.append("file", chunkSize);
        
        let xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                let jsonParse = JSON.parse(xhr.response);
                
                let response = jsonParse.response.uploadChunk !== undefined ? jsonParse.response.uploadChunk.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    this.resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "send") {
                    if (this.isStop === false) {
                        if (this.sizeStart < this.file.size)
                            this.chunk();
                        else
                            this.complete();
                    }
                }
            }
        };
        
        xhr.open("post", this.urlRequest + "&action=send&fileName=" + fileName);
        xhr.send(formData);
    }
    
    complete = () => {
        if (this.file === null)
            return;
        
        materialDesign.linearProgress(this.tagProgressBar, this.sizeStart, this.file.size);
        
        let formData = new FormData();
        
        let xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                let jsonParse = JSON.parse(xhr.response);
                
                let response = jsonParse.response.uploadChunk !== undefined ? jsonParse.response.uploadChunk.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    this.resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "complete") {
                    this.resetValue();
                    
                    if (this.tagImageRefresh !== "")
                        helper.imageRefresh(this.tagImageRefresh, 1);
                    
                    if (this.lockUrl !== "")
                        this.lock(jsonParse.response.values.lockName);
                    
                    if (response.messages.success !== undefined)
                        flashBag.show(response.messages.success);
                    
                    if (this.callbackComplete !== null)
                        this.callbackComplete();
                }
            }
        };
        
        xhr.open("post", this.urlRequest + "&action=complete&fileName=" + fileName);
        xhr.send(formData);
    }
    
    stop = () => {
        if (this.file === null)
            return;
        
        this.isStop = true;
        
        $(this.tagContainer).find(".controls .button_start").attr("disabled", false);
        $(this.tagContainer).find(".controls .button_stop").attr("disabled", true);
        
        let formData = new FormData();
        
        let xhr = new XMLHttpRequest();
        
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                let jsonParse = JSON.parse(xhr.response);
                
                let response = jsonParse.response.uploadChunk !== undefined ? jsonParse.response.uploadChunk.processFile : jsonParse.response;
                
                if (response.messages.error !== undefined) {
                    this.resetValue();
                    
                    flashBag.show(response.messages.error);
                }
                else if (response.status === "stop") {
                    this.resetValue();
                    
                    if (response.messages.success !== undefined)
                        flashBag.show(response.messages.success);
                }
            }
        };
        
        xhr.open("post", this.urlRequest + "&action=stop&fileName=" + fileName);
        xhr.send(formData);
    }
    
    chunk = () => {
        if (this.file === null)
            return;
        
        let chunkSize = this.file.slice(this.sizeStart, this.sizeEnd);
        
        this.send(chunkSize);
        
        materialDesign.linearProgress(this.tagProgressBar, this.sizeStart, this.file.size);
        
        this.sizeStart = this.sizeEnd;
        this.sizeEnd = this.sizeStart + this.byteChunk;
    }
    
    lock = (lockName) => {
        $(".loader_back").show();
        
        $(this.tagContainer).find(".popupWait").show();
        
        ajax.send(
            false,
            this.lockUrl,
            "post",
            {
                'lockName': lockName
            },
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            null,
            (xhr) => {
                ajax.reply(xhr, "");
                
                if (xhr.response.status !== undefined) {
                    $(this.tagContainer).find(".popupWait .close").off("click").on("click", "", (event) => {
                        if ($(this.tagContainer).find(".popupWait .close").prop("disabled") === false) {
                            $(".loader_back").hide();
                            
                            $(this.tagContainer).find(".popupWait .result").html("");
                            $(this.tagContainer).find(".popupWait .result").hide();
                            
                            $(this.tagContainer).find(".popupWait .content").show();
                            
                            $(this.tagContainer).find(".popupWait").hide();
                        }
                    });
                    
                    if (xhr.response.values !== undefined) {
                        if (xhr.response.values.count !== null) {
                            $(this.tagContainer).find(".popupWait .content .status").html(xhr.response.values.count + "/" + xhr.response.values.total);
                            
                            materialDesign.linearProgress(this.tagContainer + " .popupWait .content .mdc-linear-progress", xhr.response.values.count, xhr.response.values.total);
                        }
                    }
                    
                    if (xhr.response.status === "error") {
                        $(this.tagContainer).find(".popupWait .close").prop("disabled", false);
                        
                        $(this.tagContainer).find(".popupWait .content .status").html("");
                        materialDesign.linearProgress(this.tagContainer + " .popupWait .content .mdc-linear-progress", 0, 0);
                        $(this.tagContainer).find(".popupWait .content").hide();
                        
                        $(this.tagContainer).find(".popupWait .result").show();
                        $(this.tagContainer).find(".popupWait .result").html("<p>" + window.textUploadChunk.label_1 + "</p>");
                    }
                    else if (xhr.response.status === "finish") {
                        $(this.tagContainer).find(".popupWait .close").prop("disabled", false);
                        
                        $(this.tagContainer).find(".popupWait .content .status").html("");
                        materialDesign.linearProgress(this.tagContainer + " .popupWait .content .mdc-linear-progress", 0, 0);
                        $(this.tagContainer).find(".popupWait .content").hide();
                        
                        $(this.tagContainer).find(".popupWait .result").show();
                        $(this.tagContainer).find(".popupWait .result").html("<p>" + window.textUploadChunk.label_2 + "</p>");
                    }
                    else if (xhr.response.status === "loop")
                        this.lock(xhr.response.values.lockName);
                }
            },
            null,
            null
        );
    }
    
    resetValue = () => {
        this.file = null;
        this.fileName = "";
        this.byteChunk = this.byteChunkInit;
        this.sizeStart = 0;
        this.sizeEnd = this.byteChunk;
        
        $(this.tagContainer).find(".upload_chunk .file").val("");
        
        $(this.tagContainer).find(".upload_chunk .mdc-linear-progress").hide();
        
        $(this.tagContainer).find(".controls .button_start").attr("disabled", false);
        $(this.tagContainer).find(".controls .button_stop").attr("disabled", true);
        
        $(this.tagContainer).find(".controls").hide();
        
        $(this.tagContainer).find(".material_upload label").text(this.inputLabel);
    }
}