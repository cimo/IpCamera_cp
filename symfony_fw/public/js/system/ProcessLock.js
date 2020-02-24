"use strict";

/* global ajax, materialDesign */

class ProcessLock {    
    // Properties
    
    // Functions public
    constructor() {
    }
    
    execute = (tag, name) => {
        $(tag).find(".process_lock .close").prop("disabled", true);
        
        $(".loader_back").show();
        
        $(tag).find(".process_lock").show();
        
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
                    $(tag).find(".process_lock .close").off("click").on("click", "", (event) => {
                        if ($(tag).find(".process_lock .close").prop("disabled") === false) {
                            $(".loader_back").hide();
                            
                            $(tag).find(".process_lock .result").html("");
                            $(tag).find(".process_lock .result").hide();
                            
                            $(tag).find(".process_lock .content").show();
                            
                            $(tag).find(".process_lock").hide();
                        }
                    });
                    
                    if (xhr.response.values !== undefined) {
                        if (xhr.response.values.count !== null) {
                            $(tag).find(".process_lock .content .status").html(`${xhr.response.values.count}/${xhr.response.values.total}`);
                            
                            materialDesign.linearProgress(`${tag} .process_lock .content .mdc-linear-progress`, xhr.response.values.count, xhr.response.values.total);
                        }
                    }
                    
                    if (xhr.response.status === "error") {
                        $(tag).find(".process_lock .close").prop("disabled", false);
                        
                        $(tag).find(".process_lock .content .status").html("");
                        
                        materialDesign.linearProgress(`${tag} .process_lock .content .mdc-linear-progress`, 0, 0);
                        
                        $(tag).find(".process_lock .content").hide();
                        
                        $(tag).find(".process_lock .result").show();
                        $(tag).find(".process_lock .result").html(`<p>${window.textProcessLock.label_1}</p>`);
                    }
                    else if (xhr.response.status === "finish") {
                        $(tag).find(".process_lock .close").prop("disabled", false);
                        
                        $(tag).find(".process_lock .content .status").html("");
                        
                        materialDesign.linearProgress(`${tag} .process_lock .content .mdc-linear-progress`, 0, 0);
                        
                        $(tag).find(".process_lock .content").hide();
                        
                        $(tag).find(".process_lock .result").show();
                        $(tag).find(".process_lock .result").html(`<p>${window.textProcessLock.label_2}</p>`);
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