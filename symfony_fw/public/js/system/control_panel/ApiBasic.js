"use strict";

/* global helper, ajax, uploadChunk, materialDesign, popupEasy, chaato, widgetDatePicker */

class ControlPanelApiBasic {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        $(document).on("submit", "#form_cp_apiBasic_select", (event) => {
            event.preventDefault();

            ajax.send(
                true,
                $(event.currentTarget).prop("action"),
                $(event.currentTarget).prop("method"),
                helper.serializeJson($(event.currentTarget)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_api_select_result").html("");
                },
                (xhr) => {
                    this._profile(xhr, `#${event.currentTarget.id}`);
                },
                null,
                null
            );
        });
        
        $("#form_cp_apiBasic_create").on("submit", "", (event) => {
            event.preventDefault();
            
            let name = $(event.target).find("input[name='form_apiBasic[name]']").val();
            
            ajax.send(
                true,
                $(event.target).prop("action"),
                $(event.target).prop("method"),
                $(event.target).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, `#${event.target.id}`);
                    
                    if (xhr.response.messages.success !== undefined)
                        $("#form_apiBasic_select_id").append(`<option value="${xhr.response.values.id}">${name}</option>`);
                },
                null,
                null
            );
        });
    }
    
    // Function private
    _profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            $("#cp_api_select_result").html(xhr.response.render);
            
            uploadChunk.setUrlRequest = `${window.url.cpApiBasicCsv}?token=${window.session.token}&event=csv`;
            uploadChunk.setTagContainer = "#upload_chunk_apiBasic_csv_container";
            uploadChunk.setTagProgressBar = "#upload_chunk_apiBasic_csv_container .upload_chunk .mdc-linear-progress";
            uploadChunk.setProcessLock = true;
            uploadChunk.processFile();
            
            widgetDatePicker.setInputFill = ".widget_datePicker_input";
            widgetDatePicker.action();
            
            materialDesign.refresh();
            
            $("#button_apiBasic_show_log").on("click", "", (event) => {
                ajax.send(
                    true,
                    window.url.cpApiBasicLog,
                    "post",
                    {
                        'event': "log",
                        'token': window.session.token
                    },
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    (xhr) => {
                        ajax.reply(xhr, "");

                        if (xhr.response.values.log !== undefined) {
                            popupEasy.create(
                                "File log",
                                xhr.response.values.log
                            );
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#button_apiBasic_show_graph").on("click", "", (event) => {
                ajax.send(
                    true,
                    window.url.cpApiBasicGraph,
                    "post",
                    {
                        'event': "graph",
                        'year': $(".graph_period_year").val(),
                        'month': $(".graph_period_month").val(),
                        'token': window.session.token
                    },
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    (xhr) => {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.render !== undefined) {
                            popupEasy.create(
                                `<p>Show graph</p>${xhr.response.values.selectPeriodYearHtml} ${xhr.response.values.selectPeriodMonthHtml}`,
                                xhr.response.render
                            );
                    
                            $(".graph_period_year, .graph_period_month").on("change", "", (event) => {
                                $("#button_apiBasic_show_graph").click();
                            });
                            
                            chaato.setBackgroundType = "grid"; // grid - lineX - lineY
                            chaato.setAnimationSpeed = 0.50;
                            chaato.setPadding = 30;
                            chaato.setTranslate = [95, 20];
                            chaato.setScale = [0.91, 0.88];
                            chaato.create = xhr.response.values.json;
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#download_detail_button").on("click", "", (event) => {
                $(".download_detail_command").toggle("slow");
                
                $("#button_apiBasic_download_detail").off("click").on("click", "", (event) => {
                    let dataEvent = $(event.target).attr("data-event") !== undefined ? $(event.target).attr("data-event") : $(event.target).parent().attr("data-event");
                    let dateStart = $("input[name='download_date_start']").val();
                    let dateEnd = $("input[name='download_date_end']").val();
                    
                    ajax.send(
                        true,
                        window.url.cpApiBasicDownloadDetail,
                        "post",
                        {
                            'event': dataEvent,
                            'dateStart': dateStart,
                            'dateEnd': dateEnd,
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        (xhr) => {
                            ajax.reply(xhr, "");
                            
                            if (xhr.response.values !== undefined && xhr.response.values.url !== undefined) {
                                window.location = xhr.response.values.url;
                                
                                let timeoutEvent = setTimeout(() => {
                                    clearTimeout(timeoutEvent);
                                    
                                    ajax.send(
                                        false,
                                        window.url.cpApiBasicDownloadDetail,
                                        "post",
                                        {
                                            'event': "download_delete",
                                            'token': window.session.token
                                        },
                                        "json",
                                        false,
                                        true,
                                        "application/x-www-form-urlencoded; charset=UTF-8",
                                        null,
                                        (xhr) => {
                                            ajax.reply(xhr, "");
                                        },
                                        null,
                                        null
                                    );
                                }, 100);
                            }
                        },
                        null,
                        null
                    );
                });
            });
            
            $("#form_cp_apiBasic_profile").on("submit", "", (event) => {
                event.preventDefault();
                
                let selectValue = $("#form_apiBasic_select_id").val();
                let name = $(event.target).find("input[name='form_apiBasic[name]']").val();

                ajax.send(
                    true,
                    $(event.target).prop("action"),
                    $(event.target).prop("method"),
                    $(event.target).serialize(),
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    (xhr) => {
                        ajax.reply(xhr, `#${event.target.id}`);
                        
                        if (xhr.response.messages.success !== undefined) {
                            $("#form_apiBasic_select_id").find(`option[value="${selectValue}"]`).text(name);
                            
                            $("#cp_api_select_result").html("");
                        }
                    },
                    null,
                    null
                );
            });

            $("#cp_apiBasic_delete").on("click", "", (event) => {
               popupEasy.create(
                    window.text.index_5,
                    window.textApiBasic.label_1,
                    () => {
                        ajax.send(
                            true,
                            window.url.cpApiBasicDelete,
                            "post",
                            {
                                'event': "delete",
                                'id': null,
                                'token': window.session.token
                            },
                            "json",
                            false,
                            true,
                            "application/x-www-form-urlencoded; charset=UTF-8",
                            null,
                            (xhr) => {
                                ajax.reply(xhr, "");

                                if (xhr.response.messages.success !== undefined) {
                                    $("#form_apiBasic_select_id").find(`option[value="${xhr.response.values.id}"]`).remove();

                                    $("#cp_api_select_result").html("");
                                }
                            },
                            null,
                            null
                        );
                    }
                );
            });
        }
    }
}