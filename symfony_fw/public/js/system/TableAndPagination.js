"use strict";

/* global helper, ajax */

function TableAndPagination() {
    // Vars
    let self = this;
    
    let urlRequest;
    let idResult;
    let selectOnlyOne;
    
    let current;
    let total;
    
    let clickedEvent;
    let sortOrderBy;
    
    let buttonsStatus;
    
    // Properties
    self.setButtonsStatus = function(value) {
        buttonsStatus = value;
    };
    
    // Functions public
    self.init = function() {
        urlRequest = "";
        idResult = "";
        selectOnlyOne = "";

        current = 0;
        total = 0;

        clickedEvent = false;
        sortOrderBy = false;

        buttonsStatus = "";
    };
    
    self.create = function(url, id, singleSelect) {
        urlRequest = url;
        idResult = id;
        selectOnlyOne = singleSelect;
        
        status();
        
        helper.linkPreventDefault();
        
        if (selectOnlyOne === true)
            helper.selectOnlyOneElement(idResult + " table tbody");
        
        resizeColumn();
    };
    
    self.search = function() {
        let parentField = idResult + " .tableAndPagination .mdc-text-field__input";
        let parentButton = idResult + " .tableAndPagination .mdc-text-field .material-icons";
        
        $(parentField).on("keyup", "", function(event) {
            if (clickedEvent === true)
                return;
            
            if (event.which === 13) {
                current = 0;
                
                send();
                
                clickedEvent = true;
            }
        });
        
        $(parentButton).on("click", "", function() {
            if (clickedEvent === true)
                return;
            
            current = 0;
            
            send();
            
            clickedEvent = true;
        });
    };
    
    self.pagination = function() {
        let parentPrevious = idResult + " .tableAndPagination .previous";
        let parentNext = idResult + " .tableAndPagination .next";
        
        $(parentPrevious).on("click", "", function() {
            if (clickedEvent === true)
                return;
            
            if (total > 1 && current > 0) {
                current --;
                
                send();
                
                clickedEvent = true;
            }
        });
        
        $(parentNext).on("click", "", function() {
            if (clickedEvent === true)
                return;
            
            if (total > 1 && current < (total - 1)) {
                current ++;
                
                send();
                
                clickedEvent = true;
            }
        });
    };
    
    self.sort = function() {
        $(idResult).on("click", "table thead tr th", function(event) {
            let bodyRows = $(idResult).find("table tbody tr");
            
            let currentIndex = $(event.target).is("i") === false ? $(event.target).index() : $(event.target).parent().index();
            
            $(this).parent().find("i").hide();
            
            if (sortOrderBy === false) {
                $(this).find("i").eq(0).show();
                $(this).find("i").eq(1).hide();
                
                sortOrderBy = true;
            }
            else {
                $(this).find("i").eq(0).hide();
                $(this).find("i").eq(1).show();
                
                sortOrderBy = false;
            }
            
            bodyRows.sort(function(a, b) {
                let result = 0;
                let first = $.trim($(a).children("td").eq(currentIndex).text().toLowerCase());
                let second = $.trim($(b).children("td").eq(currentIndex).text().toLowerCase());
                
                if (first !== "" && second !== "") {
                    if (sortOrderBy === true) {
                        if ($.isNumeric(first) === false)
                            result = first.localeCompare(second);
                        else
                            result = first - second;
                    }
                    else {
                        if ($.isNumeric(first) === false)
                            result = second.localeCompare(first);
                        else
                            result = second - first;
                    }
                }
                
                return result;
            });
            
            if (bodyRows.length > 1) {
                $.each(bodyRows, function(key, value) {
                    $(idResult).find("table tbody").append(value);
                });
            }
        });
    };
    
    self.populate = function(xhr) {
        $(idResult).find(".tableAndPagination .mdc-text-field__input").val("");
        $(idResult).find("table tbody").css("visibility", "visible");
        
        if (xhr.response.values !== undefined) {
            $(idResult).find(".tableAndPagination .mdc-text-field__input").val(xhr.response.values.search.value);
            $(idResult).find(".tableAndPagination .text").html(xhr.response.values.pagination.text);
            
            if (xhr.response.values.count !== undefined) {
                $(idResult).find(".tableAndPagination .count").show();
                $(idResult).find(".tableAndPagination .count span").html(xhr.response.values.count);
            }
            
            if ($(idResult).find("table tbody").length > 0)
                $(idResult).find("table tbody").html(xhr.response.values.listHtml);
            else
                $(idResult).find(".list_result").html(xhr.response.values.listHtml);

            status();
            
            resizeColumn();
        }
    };
    
    // Functions private
    function status() {
        let textHtml = $.trim($(idResult).find(".tableAndPagination .text").text());
        
        if (textHtml !== undefined || textHtml !== "") {
            let textSplit = textHtml.split("/");
            let valueA = parseInt($.trim(textSplit[0]));
            let valueB = parseInt($.trim(textSplit[1]));
            
            if (valueA > valueB && $(idResult).find("table tbody tr").length === 0)
                $(idResult).find(".tableAndPagination .previous").click();
            
            if (current < 0)
                current = 0;
            
            total = valueB;
        }
        
        $(".tableAndPagination .previous .mdc-button").prop("disabled", true);
        $(".tableAndPagination .next .mdc-button").prop("disabled", true);
        
        let buttonPrevious = $(idResult).find(".tableAndPagination .previous .mdc-button");
        let buttonNext = $(idResult).find(".tableAndPagination .next .mdc-button");
        
        if (buttonPrevious.length > 0 && buttonNext.length > 0) {
            if (total > 1 && current > 0)
                buttonPrevious.removeAttr("disabled");

            if (total > 1 && current < (total - 1))
                buttonNext.removeAttr("disabled");

            $.each($(idResult).find("table thead tr th"), function(key, value) {
                $(value).find("i").hide();
            });

            if (buttonsStatus === "show")
                $(idResult).find(".tableAndPagination .button_container").show();
        }
    }
    
    function send() {
        let data = {
            'event': "tableAndPagination",
            'searchWritten': $(idResult).find(".tableAndPagination .mdc-text-field__input").val(),
            'paginationCurrent': current,
            'token': window.session.token
        };
        
        ajax.send(
            false,
            urlRequest,
            "post",
            data,
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            function() {
                $(idResult).find(".update_loading").css("display", "inline-block");
                
                $(idResult).find("table tbody").css("visibility", "hidden");
            },
            function(xhr) {
                ajax.reply(xhr, "");
                
                if (xhr.response.values !== undefined)
                    self.populate(xhr);
                
                helper.linkPreventDefault();
                
                if (selectOnlyOne === true)
                    helper.selectOnlyOneElement(idResult + " table tbody");
                
                resizeColumn();
                
                clickedEvent = false;
                
                $(idResult).find(".update_loading").css("display", "none");
            },
            null,
            null
        );
    }
    
    function resizeColumn() {
        $(function() {
            $(idResult).find("table tbody tr td").resizable({
                handles: "e"
            });
        });
    }
}