"use strict";

/* global helper, ajax, loader, flashBag, materialDesign, processLock */

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
    
    set setProcessLock(value) {
        this.processLock = value;
    }
    
    // Functions public
    constructor() {
        this.urlRequest = "";
        this.tagContainer = "";
        this.tagProgressBar = "";
        this.tagImageRefresh = "";
        this.processLock = false;
        
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
        
        xhr.open("post", `${this.urlRequest}&action=start&fileName=${this.fileName}`);
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
        
        xhr.open("post", `${this.urlRequest}&action=send&fileName=${this.fileName}`);
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
                    
                    if (this.processLock === true)
                        processLock.execute(this.tagContainer, jsonParse.response.processLock.name);
                    
                    if (response.messages.success !== undefined)
                        flashBag.show(response.messages.success);
                    
                    if (this.callbackComplete !== null)
                        this.callbackComplete();
                }
            }
        };
        
        xhr.open("post", `${this.urlRequest}&action=complete&fileName=${this.fileName}`);
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
        
        xhr.open("post", `${this.urlRequest}&action=stop&fileName=${this.fileName}`);
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