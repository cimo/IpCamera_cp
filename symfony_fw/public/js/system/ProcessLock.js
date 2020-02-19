"use strict";

/* global ajax, materialDesign */

class ProcessLock {    
    // Properties
    
    // Functions public
    constructor() {
    }
    
    execute = (tag, name) => {
        $(tag).find(".processLock .close").prop("disabled", true);
        
        $(".loader_back").show();
        
        $(tag).find(".processLock").show();
        
        ajax.send(
            false,
            window.url.processLockListener,
            "post",
            {
                'name': name
            },
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            null,
            (xhr) => {
                ajax.reply(xhr, "");
                
                if (xhr.response.status !== undefined) {
                    $(tag).find(".processLock .close").off("click").on("click", "", (event) => {
                        if ($(tag).find(".processLock .close").prop("disabled") === false) {
                            $(".loader_back").hide();
                            
                            $(tag).find(".processLock .result").html("");
                            $(tag).find(".processLock .result").hide();
                            
                            $(tag).find(".processLock .content").show();
                            
                            $(tag).find(".processLock").hide();
                        }
                    });
                    
                    if (xhr.response.values !== undefined) {
                        if (xhr.response.values.count !== null) {
                            $(tag).find(".processLock .content .status").html(`${xhr.response.values.count}/${xhr.response.values.total}`);
                            
                            materialDesign.linearProgress(`${tag} .processLock .content .mdc-linear-progress`, xhr.response.values.count, xhr.response.values.total);
                        }
                    }
                    
                    if (xhr.response.status === "error") {
                        $(tag).find(".processLock .close").prop("disabled", false);
                        
                        $(tag).find(".processLock .content .status").html("");
                        
                        materialDesign.linearProgress(`${tag} .processLock .content .mdc-linear-progress`, 0, 0);
                        
                        $(tag).find(".processLock .content").hide();
                        
                        $(tag).find(".processLock .result").show();
                        $(tag).find(".processLock .result").html(`<p>${window.textProcessLock.label_1}</p>`);
                    }
                    else if (xhr.response.status === "finish") {
                        $(tag).find(".processLock .close").prop("disabled", false);
                        
                        $(tag).find(".processLock .content .status").html("");
                        
                        materialDesign.linearProgress(`${tag} .processLock .content .mdc-linear-progress`, 0, 0);
                        
                        $(tag).find(".processLock .content").hide();
                        
                        $(tag).find(".processLock .result").show();
                        $(tag).find(".processLock .result").html(`<p>${window.textProcessLock.label_2}</p>`);
                    }
                    else if (xhr.response.status === "loop")
                        this.execute(tag, xhr.response.values.name);
                }
            },
            null,
            null
        );
    }
    
    // Functions private
}