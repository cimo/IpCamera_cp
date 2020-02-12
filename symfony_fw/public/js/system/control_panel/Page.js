"use strict";

/* global helper, ajax, language, popupEasy, wysiwyg, materialDesign */

class ControlPanelPage {
    // Properties
    get getProfileFocus() {
        return this.profileFocus;
    }
    
    // ---
    
    set setProfileFocus(value) {
        this.profileFocus = value;
    }
    
    // Functions public
    constructor() {
        this.selectSended = false;
        this.selectId = -1;
        
        this.profileFocus = false;
    }
    
    action = () => {
        this.selectDesktop();
        
        this.selectMobile();
        
        this.rankInMenu();
        
        wysiwyg.create("#form_page_argument", $("#form_cp_page_create").find("input[type='submit']"));
        
        this.fieldsVisibility();
        
        helper.wordTag("#page_roleUserId", "#form_page_roleUserId");
        
        $("#cp_page_saveDraft").on("click", "", (event) => {
            this.saveDraft("create");
        });
        
        $("#form_cp_page_create").on("submit", "", (event) => {
            event.preventDefault();
            
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
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                    
                    if (xhr.response.values !== undefined && xhr.response.values.id !== undefined) {
                        $("#cp_page_select_result").html("");

                        $("#cp_page_select_result_desktop").find(".refresh").click();
                    }
                },
                null,
                null
            );
        });
    }
    
    changeView = () => {
        this.profileFocus = false;

        if (helper.checkWidthType() === "mobile") {
            if (this.selectSended === true) {
                this.selectId = $("#form_cp_page_select_mobile").find("select option:selected").val();

                this.selectSended = false;
            }

            if (this.selectId >= 0) {
                $("#cp_page_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                let id = $("#cp_page_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, (key, value) => {
                    if ($.trim($(value).text()) === String(this.selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (this.selectSended === true) {
                this.selectId = $.trim($("#cp_page_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                this.selectSended = false;
            }

            if (this.selectId > 0)
                $("#form_cp_page_select_mobile").find("select option[value='" + this.selectId + "']").prop("selected", true);
        }
        
        this.rankInMenu();
    }
    
    // Function private
    selectDesktop = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.setButtonsStatus = "show";
        tableAndPagination.create(window.url.cpPageSelect, "#cp_page_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_page_select_result_desktop .refresh", (event) => {
            ajax.send(
                true,
                window.url.cpPageSelect,
                "post",
                {
                    'event': "refresh",
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_page_select_result_desktop .delete_all", (event) => {
            popupEasy.create(
                window.text.index_5,
                window.textPage.label_2,
                () => {
                    ajax.send(
                        true,
                        window.url.cpPageDelete,
                        "post",
                        {
                            'event': "deleteAll",
                            'token': window.session.token
                        },
                        "json",
                        false,
                        true,
                        "application/x-www-form-urlencoded; charset=UTF-8",
                        null,
                        (xhr) => {
                            ajax.reply(xhr, "");

                            $.each($("#cp_page_select_result_desktop").find("table .id_column"), (key, value) => {
                                let id = $.trim($(value).parents("tr").find(".id_column").text());
                                
                                if (id > 5)
                                    $(value).parents("tr").remove();
                            });
                            
                            $("#cp_page_select_result").html("");
                        },
                        null,
                        null
                    );
                }
            );
        });
        
        $(document).on("click", "#cp_page_select_result_desktop .cp_page_delete", (event) => {
            let id = $.trim($(event.currentTarget).parents("tr").find(".id_column").text());
            
            this.deleteElement(id);
        });
        
        $(document).on("click", "#cp_page_select_button_desktop", (event) => {
            let id = $.trim($(event.currentTarget).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

            ajax.send(
                true,
                window.url.cpPageProfile,
                "post",
                {
                    'event': "result",
                    'id': id,
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                () => {
                    $("#cp_page_select_result").html("");
                },
                (xhr) => {
                    this.profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("click", ".checkbox_column input[type='checkbox']", (event) => {
            $("#cp_page_select_result").html("");
        });
    }
    
    selectMobile = () => {
        $(document).on("submit", "#form_cp_page_select_mobile", (event) => {
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
                    $("#cp_page_select_result").html("");
                },
                (xhr) => {
                    this.profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
        
        $(document).on("change", "#form_page_select_id", (event) => {
            $("#cp_page_select_result").html("");
        });
    }
    
    profile = (xhr, tag) => {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            this.selectSended = true;
            
            $("#cp_page_select_result").html(xhr.response.render);
            
            this.rankInMenu();

            language.page();
            
            wysiwyg.create("#form_page_argument", $("#form_cp_page_profile").find("input[type='submit']"));
            
            this.fieldsVisibility();
            
            this.selectFieldWithDisabledElement("#form_page_parent", xhr);

            helper.wordTag("#page_roleUserId", "#form_page_roleUserId");
            
            materialDesign.refresh();
            
            $("#form_cp_page_profile").find(".form_row input, .form_row textarea").on("focus", "", (event) => {
                this.profileFocus = true;
            });
            
            // Iframe focus
            let iframeMouseOver = false;
            
            $("#form_cp_page_profile").find(".wysiwyg").on("mouseover", "", (event) => {
                iframeMouseOver = true;
            });
            $("#form_cp_page_profile").find(".wysiwyg").on("mouseout", "", (event) => {
                iframeMouseOver = false;
            });
            
            $(window).on("blur", "", (event) => {
                if (iframeMouseOver === true)
                    this.profileFocus = true;
            });
            
            $("#cp_page_saveDraft").on("click", "", (event) => {
                this.saveDraft("modify");
            });
            
            $("#cp_page_publishDraft").on("click", "", (event) => {
                this.publishDraft();
            });

            $("#form_cp_page_profile").on("submit", "", (event) => {
                wysiwyg.save();
                
                event.preventDefault();

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
                        ajax.reply(xhr, "#" + event.currentTarget.id);
                        
                        if (xhr.response.messages.success !== undefined) {
                            this.profileFocus = false;
                            
                            $("#form_page_event").val("");
                            
                            $("#cp_page_select_result").html("");
                            
                            $("#cp_page_select_result_desktop").find(".refresh").click();
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_page_delete").on("click", "", (event) => {
               this.deleteElement(null);
            });
        }
    }
    
    rankInMenu = () => {
        helper.sortableElement("#page_rankMenuSort", "#form_page_rankMenuSort");
        
        $("#form_page_parent").off("change").on("change", "", (event) => {
            ajax.send(
                true,
                window.url.cpPageProfileSort,
                "post",
                {
                    'event': "refresh",
                    'id': $(event.target).val(),
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                (xhr) => {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.pageSortListHtml !== undefined) {
                        $("#page_rankMenuSort").find(".sort_result").html(xhr.response.values.pageSortListHtml);

                        helper.sortableElement("#page_rankMenuSort", "#form_page_rankMenuSort");
                    }
                },
                null,
                null
            );
        });
    }
    
    deleteElement = (id) => {
        popupEasy.create(
            window.text.index_5,
            window.textPage.label_1,
            () => {
                ajax.send(
                    true,
                    window.url.cpPageDelete,
                    "post",
                    {
                        'event': "delete",
                        'id': id,
                        'token': window.session.token
                    },
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    (xhr) => {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.values.text !== undefined && xhr.response.values.button !== undefined && xhr.response.values.pageSelectHtml !== undefined) {
                            popupEasy.create(
                                window.text.index_5,
                                xhr.response.values.text + xhr.response.values.button + xhr.response.values.pageSelectHtml
                            );

                            $("#cp_page_delete_parent_all").on("click", "", (event) => {
                                ajax.send(
                                    true,
                                    window.url.cpPageDelete,
                                    "post",
                                    {
                                        'event': "parentAll",
                                        'id': id,
                                        'token': window.session.token
                                    },
                                    "json",
                                    false,
                                    true,
                                    "application/x-www-form-urlencoded; charset=UTF-8",
                                    null,
                                    (xhr) => {
                                        ajax.reply(xhr, "");
                                        
                                        this.deleteResponse(xhr);
                                    },
                                    null,
                                    null
                                );
                            });

                            $("#cp_page_delete_parent_new").on("change", "", (event) => {
                                ajax.send(
                                    true,
                                    window.url.cpPageDelete,
                                    "post",
                                    {
                                        'event': "parentNew",
                                        'id': id,
                                        'parentNew': $(event.target).find("select").val(),
                                        'token': window.session.token
                                    },
                                    "json",
                                    false,
                                    true,
                                    "application/x-www-form-urlencoded; charset=UTF-8",
                                    null,
                                    (xhr) => {
                                        ajax.reply(xhr, "");
                                        
                                        this.deleteResponse(xhr);
                                    },
                                    null,
                                    null
                                );
                            });

                            this.selectFieldWithDisabledElement("#cp_page_delete_parent_new", xhr);
                        }
                        else
                            this.deleteResponse(xhr);
                    },
                    null,
                    null
                );
            }
        );
    }
    
    saveDraft = (type) => {
        popupEasy.create(
            window.text.index_5,
            window.textPage.label_3,
            () => {
                $("#form_page_event").val("save_draft_" + type);
                
                if ($("#form_cp_page_create").length > 0)
                    $("#form_cp_page_create").submit();
                else
                    $("#form_cp_page_profile").submit();
                
                $("#form_page_event").val("");
            }
        );
    }
    
    publishDraft = () => {
        popupEasy.create(
            window.text.index_5,
            window.textPage.label_4,
            () => {
                $("#form_page_event").val("publish_draft");
                
                $("#form_cp_page_profile").submit();
                
                $("#form_page_event").val("");
            }
        );
    }
    
    selectFieldWithDisabledElement = (id, xhr) => {
        let options = $(id).find("option");
        
        let disabled = false;
        let optionLength = 0;
        
        $.each(options, (key, val) => {
            let optionValue = parseInt(val.value);
            let optionText = val.text.substr(0, val.text.indexOf("-|") + 2);
            let pageIdElementSelected = parseInt(xhr.response.values.pageId);
            let parentIdElementSelected = parseInt(xhr.response.values.parentId);
            
            if (optionValue === pageIdElementSelected || optionValue === parentIdElementSelected) {
                disabled = true;
                optionLength = optionText.length;
            }
            else if (optionText.length <= optionLength)
                disabled = false;
            
            if (disabled === true)
                $(id).find("option").eq(key).prop("disabled", true);
        });
    }
    
    deleteResponse = (xhr) => {
        if (xhr.response.messages.success !== undefined) {
            $.each($("#cp_page_select_result_desktop").find("table .id_column"), (key, value) => {
                if (xhr.response.values.id !== undefined && xhr.response.values.id === $.trim($(value).text()) ||
                        xhr.response.values.removedId !== undefined && $.inArray($.trim($(value).text()), xhr.response.values.removedId) !== -1)
                    $(value).parents("tr").remove();
            });

            $("#form_page_select_id").find("option[value='" + xhr.response.values.id + "']").remove();

            $("#cp_page_select_result").html("");
            
            $("#cp_page_select_result_desktop").find(".refresh").click();
        }
    }
    
    fieldsVisibility = () => {
        this.fieldsVisibilityMenu();
        
        this.fieldsVisibilityLink();
        
        $("#form_page_showInMenu").on("change", "", (event) => {
            this.fieldsVisibilityMenu();
            
            materialDesign.refresh();
        });
        
        $("#form_page_onlyLink").on("change", "", (event) => {
            this.fieldsVisibilityLink();
            
            materialDesign.refresh();
        });
    }
    
    fieldsVisibilityMenu = () => {
        if ($("#form_page_showInMenu").val() === "0") {
            $("#form_page_menuName").parents(".form_row ").hide();
            $("#page_rankMenuSort").hide();
        }
        else {
            $("#form_page_menuName").parents(".form_row ").show();
            $("#page_rankMenuSort").show();
        }
    }
    
    fieldsVisibilityLink = () => {
        if ($("#form_page_onlyLink").val() === "0")
            $("#form_page_link").parents(".form_row ").hide();
        else
            $("#form_page_link").parents(".form_row ").show();
    }
}