/* global utility, ajax, language, popupEasy, wysiwyg, materialDesign */

var controlPanelPage = new ControlPanelPage();

function ControlPanelPage() {
    // Vars
    var self = this;
    
    var selectSended = false;
    var selectId = -1;
    
    var profileFocus = false;
    
    // Properties
    self.getProfileFocus = function() {
        return profileFocus;
    };
    
    // ---
    
    self.setProfileFocus = function(value) {
        profileFocus = value;
    };
    
    // Functions public
    self.init = function() {
        selectDesktop();
        
        selectMobile();
        
        rankInMenu();
        
        wysiwyg.init();
        wysiwyg.create("#form_page_argument", $("#form_cp_page_create").find("input[type='submit']"));
        
        fieldsVisibility();
        
        utility.wordTag("#page_roleUserId", "#form_page_roleUserId");
        
        $("#form_cp_page_create").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                $(this).serialize(),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    };
    
    self.changeView = function() {
        profileFocus = false;

        if (utility.checkWidthType() === "mobile") {
            if (selectSended === true) {
                selectId = $("#form_cp_page_select_mobile").find("select option:selected").val();

                selectSended = false;
            }

            if (selectId >= 0) {
                $("#cp_page_select_result_desktop").find(".checkbox_column input[type='checkbox']").prop("checked", false);

                var id = $("#cp_page_select_result_desktop").find(".checkbox_column input[type='checkbox']").parents("tr").find(".id_column");

                $.each(id, function(key, value) {
                    if ($.trim($(value).text()) === String(selectId))
                        $(value).parents("tr").find(".checkbox_column input").prop("checked", true);
                });
            }
        }
        else {
            if (selectSended === true) {
                selectId = $.trim($("#cp_page_select_result_desktop").find(".checkbox_column input[type='checkbox']:checked").parents("tr").find(".id_column").text());

                selectSended = false;
            }

            if (selectId > 0)
                $("#form_cp_page_select_mobile").find("select option[value='" + selectId + "']").prop("selected", true);
        }
        
        rankInMenu();
    };
    
    // Function private
    function selectDesktop() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.setButtonsStatus("show");
        tableAndPagination.create(window.url.cpPageSelect, "#cp_page_select_result_desktop", true);
        tableAndPagination.search();
        tableAndPagination.pagination();
        tableAndPagination.sort();
        
        $(document).on("click", "#cp_page_select_result_desktop .refresh", function() {
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
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    tableAndPagination.populate(xhr);
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#cp_page_select_result_desktop .delete_all", function() {
            popupEasy.create(
                window.text.index_5,
                window.textPage.label_2,
                function() {
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
                        function(xhr) {
                            ajax.reply(xhr, "");

                            $.each($("#cp_page_select_result_desktop").find("table .id_column"), function(key, value) {
                                var id = $.trim($(value).parents("tr").find(".id_column").text());
                                
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
        
        $(document).on("click", "#cp_page_select_result_desktop .cp_page_delete", function() {
            var id = $.trim($(this).parents("tr").find(".id_column").text());
            
            deleteElement(id);
        });
        
        $(document).on("click", "#cp_page_select_button_desktop", function(event) {
            var id = $.trim($(this).parent().find(".checkbox_column input:checked").parents("tr").find(".id_column").text());

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
                function() {
                    $("#cp_page_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function selectMobile() {
        $(document).on("submit", "#form_cp_page_select_mobile", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                utility.serializeJson($(this)),
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                function() {
                    $("#cp_page_select_result").html("");
                },
                function(xhr) {
                    profile(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });
    }
    
    function profile(xhr, tag) {
        ajax.reply(xhr, tag);
        
        if ($.isEmptyObject(xhr.response) === false && xhr.response.render !== undefined) {
            selectSended = true;
            
            $("#cp_page_select_result").html(xhr.response.render);
            
            rankInMenu();

            language.page();
            
            wysiwyg.init();
            wysiwyg.create("#form_page_argument", $("#form_cp_page_profile").find("input[type='submit']"));
            
            fieldsVisibility();
            
            pageSelectFieldWithDisabledElement("#form_page_parent", xhr);

            utility.wordTag("#page_roleUserId", "#form_page_roleUserId");
            
            materialDesign.refresh();
            
            $("#form_cp_page_profile").find(".form_row input, .form_row textarea").on("focus", "", function() {
                profileFocus = true;
            });
            
            // Iframe focus
            var iframeMouseOver = false;
            
            $("#form_cp_page_profile").find(".wysiwyg").on("mouseover", "", function() {
                iframeMouseOver = true;
            });
            $("#form_cp_page_profile").find(".wysiwyg").on("mouseout", "", function() {
                iframeMouseOver = false;
            });
            
            $(window).on("blur", "", function() {
                if (iframeMouseOver === true)
                    profileFocus = true;
            });

            $("#form_cp_page_profile").on("submit", "", function(event) {
                wysiwyg.save();
                
                event.preventDefault();

                ajax.send(
                    true,
                    $(this).prop("action"),
                    $(this).prop("method"),
                    $(this).serialize(),
                    "json",
                    false,
                    true,
                    "application/x-www-form-urlencoded; charset=UTF-8",
                    null,
                    function(xhr) {
                        ajax.reply(xhr, "#" + event.currentTarget.id);

                        if ($.isEmptyObject(xhr.response.messages.success) === false) {
                            profileFocus = false;
                        }
                    },
                    null,
                    null
                );
            });
            
            $("#cp_page_delete").on("click", "", function() {
               deleteElement(null);
            });
        }
    }
    
    function rankInMenu() {
        utility.sortableElement("#page_rankMenuSort", "#form_page_rankMenuSort");
        
        $("#form_page_parent").off("change").on("change", "", function() {
            ajax.send(
                true,
                window.url.cpPageProfileSort,
                "post",
                {
                    'event': "refresh",
                    'id': $(this).val(),
                    'token': window.session.token
                },
                "json",
                false,
                true,
                "application/x-www-form-urlencoded; charset=UTF-8",
                null,
                function(xhr) {
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values.pageSortListHtml !== undefined) {
                        $("#page_rankMenuSort").find(".sort_result").html(xhr.response.values.pageSortListHtml);

                        utility.sortableElement("#page_rankMenuSort", "#form_page_rankMenuSort");
                    }
                },
                null,
                null
            );
        });
    }
    
    function deleteElement(id) {
        popupEasy.create(
            window.text.index_5,
            window.textPage.label_1,
            function() {
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
                    function(xhr) {
                        ajax.reply(xhr, "");
                        
                        if (xhr.response.values.text !== undefined && xhr.response.values.button !== undefined && xhr.response.values.pageSelectHtml !== undefined) {
                            popupEasy.create(
                                window.text.index_5,
                                xhr.response.values.text + xhr.response.values.button + xhr.response.values.pageSelectHtml
                            );

                            $("#cp_page_delete_parent_all").on("click", "", function() {
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
                                    function(xhr) {
                                        ajax.reply(xhr, "");
                                        
                                        deleteResponse(xhr);
                                    },
                                    null,
                                    null
                                );
                            });

                            $("#cp_page_delete_parent_new").on("change", "", function() {
                                ajax.send(
                                    true,
                                    window.url.cpPageDelete,
                                    "post",
                                    {
                                        'event': "parentNew",
                                        'id': id,
                                        'parentNew': $(this).find("select").val(),
                                        'token': window.session.token
                                    },
                                    "json",
                                    false,
                                    true,
                                    "application/x-www-form-urlencoded; charset=UTF-8",
                                    null,
                                    function(xhr) {
                                        ajax.reply(xhr, "");
                                        
                                        deleteResponse(xhr);
                                    },
                                    null,
                                    null
                                );
                            });

                            pageSelectFieldWithDisabledElement("#cp_page_delete_parent_new", xhr);
                        }
                        else
                            deleteResponse(xhr);
                    },
                    null,
                    null
                );
            }
        );
    }
    
    function pageSelectFieldWithDisabledElement(id, xhr) {
        var options = $(id).find("option");
        
        var disabled = false;
        var optionLength = 0;
        
        $.each(options, function(key, val) {
            var optionValue = parseInt(val.value);
            var optionText = val.text.substr(0, val.text.indexOf("-|") + 2);
            var pageIdElementSelected = parseInt(xhr.response.values.pageId);
            var parentIdElementSelected = parseInt(xhr.response.values.parentId);
            
            if (optionValue === pageIdElementSelected || optionValue === parentIdElementSelected) {
                disabled = true;
                optionLength = optionText.length;
            }
            else if (optionText.length <= optionLength)
                disabled = false;
            
            if (disabled === true)
                $(id).find("option").eq(key).prop("disabled", true);
        });
    };
    
    function deleteResponse(xhr) {
        if (xhr.response.messages.success !== undefined) {
            $.each($("#cp_page_select_result_desktop").find("table .id_column"), function(key, value) {
                if (xhr.response.values.id !== undefined && xhr.response.values.id === $.trim($(value).text()) ||
                        xhr.response.values.removedId !== undefined && $.inArray($.trim($(value).text()), xhr.response.values.removedId) !== -1)
                    $(value).parents("tr").remove();
            });

            $("#form_page_select_id").find("option[value='" + xhr.response.values.id + "']").remove();

            $("#cp_page_select_result").html("");
            
            $("#cp_page_select_result_desktop").find(".refresh").click();
        }
    }
    
    function fieldsVisibility() {
        fieldsVisibilityMenu();
        
        fieldsVisibilityLink();
        
        $("#form_page_showInMenu").on("change", "", function() {
            fieldsVisibilityMenu();
            
            materialDesign.refresh();
        });
        
        $("#form_page_onlyLink").on("change", "", function() {
            fieldsVisibilityLink();
            
            materialDesign.refresh();
        });
    }
    
    function fieldsVisibilityMenu() {
        if ($("#form_page_showInMenu").val() === "0") {
            $("#form_page_menuName").parents(".form_row ").hide();
            $("#page_rankMenuSort").hide();
        }
        else {
            $("#form_page_menuName").parents(".form_row ").show();
            $("#page_rankMenuSort").show();
        }
    }
    
    function fieldsVisibilityLink() {
        if ($("#form_page_onlyLink").val() === "0")
            $("#form_page_link").parents(".form_row ").hide();
        else
            $("#form_page_link").parents(".form_row ").show();
    }
}