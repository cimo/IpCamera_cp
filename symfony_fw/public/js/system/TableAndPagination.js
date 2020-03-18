"use strict";

/* global helper, ajax */

class TableAndPagination {
    // Properties
    set setButtonStatus(value) {
        this.buttonStatus = value;
    }
    
    // Functions public
    constructor() {
        this.urlRequest = "";
        this.idResult = 0;
        this.selectOnlyOne = false;

        this.current = 0;
        this.total = 0;

        this.clickedEvent = false;
        this.sortOrderBy = false;

        this.buttonStatus = "";
    }
    
    create = (url, id, singleSelect) => {
        this.urlRequest = url;
        this.idResult = id;
        this.selectOnlyOne = singleSelect;
        
        this._status();
        
        helper.linkPreventDefault();
        
        if (this.selectOnlyOne === true)
            helper.selectOnlyOneElement(this.idResult + " table tbody");
        
        this._resizeColumn();
    }
    
    search = () => {
        let parentField = this.idResult + " .tableAndPagination_container .mdc-text-field__input";
        let parentButton = this.idResult + " .tableAndPagination_container .mdc-text-field .material-icons";
        
        $(parentField).on("keyup", "", (event) => {
            if (this.clickedEvent === true)
                return;
            
            if (event.which === 13) {
                this.current = 0;
                
                this._send();
                
                this.clickedEvent = true;
            }
        });
        
        $(parentButton).on("click", "", (event) => {
            if (this.clickedEvent === true)
                return;
            
            this.current = 0;
            
            this._send();
            
            this.clickedEvent = true;
        });
    }
    
    pagination = () => {
        let parentPrevious = this.idResult + " .tableAndPagination_container .previous";
        let parentNext = this.idResult + " .tableAndPagination_container .next";
        
        $(parentPrevious).on("click", "", (event) => {
            if (this.clickedEvent === true)
                return;
            
            if (this.total > 1 && this.current > 0) {
                this.current --;
                
                this._send();
                
                this.clickedEvent = true;
            }
        });
        
        $(parentNext).on("click", "", (event) => {
            if (this.clickedEvent === true)
                return;
            
            if (this.total > 1 && this.current < (this.total - 1)) {
                this.current ++;
                
                this._send();
                
                this.clickedEvent = true;
            }
        });
    }
    
    sort = () => {
        $(this.idResult).on("click", "table thead tr th", (event) => {
            let bodyRows = $(this.idResult).find("table tbody tr");
            
            let currentIndex = $(event.currentTarget).is("i") === false ? $(event.currentTarget).index() : $(event.currentTarget).parent().index();
            
            $(event.currentTarget).parent().find("i").hide();
            
            if (this.sortOrderBy === false) {
                $(event.currentTarget).find("i").eq(0).show();
                $(event.currentTarget).find("i").eq(1).hide();
                
                this.sortOrderBy = true;
            }
            else {
                $(event.currentTarget).find("i").eq(0).hide();
                $(event.currentTarget).find("i").eq(1).show();
                
                this.sortOrderBy = false;
            }
            
            bodyRows.sort((a, b) => {
                let result = 0;
                let first = $.trim($(a).children("td").eq(currentIndex).text().toLowerCase());
                let second = $.trim($(b).children("td").eq(currentIndex).text().toLowerCase());
                
                if (first !== "" && second !== "") {
                    if (this.sortOrderBy === true) {
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
                $.each(bodyRows, (key, value) => {
                    $(this.idResult).find("table tbody").append(value);
                });
            }
        });
    }
    
    populate = (xhr) => {
        $(this.idResult).find(".tableAndPagination_container .mdc-text-field__input").val("");
        $(this.idResult).find("table tbody").css("visibility", "visible");
        
        if (xhr.response.values !== undefined) {
            $(this.idResult).find(".tableAndPagination_container .mdc-text-field__input").val(xhr.response.values.search.value);
            $(this.idResult).find(".tableAndPagination_container .text").html(xhr.response.values.pagination.text);
            
            if (xhr.response.values.count !== undefined) {
                $(this.idResult).find(".tableAndPagination_container .count").show();
                $(this.idResult).find(".tableAndPagination_container .count span").html(xhr.response.values.count);
            }
            
            if ($(this.idResult).find("table tbody").length > 0)
                $(this.idResult).find("table tbody").html(xhr.response.values.listHtml);
            else
                $(this.idResult).find(".list_result").html(xhr.response.values.listHtml);

            this._status();
            
            this._resizeColumn();
        }
    }
    
    // Functions private
    _status = () => {
        let textHtml = $.trim($(this.idResult).find(".tableAndPagination_container .text").text());
        
        if (textHtml !== undefined || textHtml !== "") {
            let textSplit = textHtml.split("/");
            let valueA = parseInt($.trim(textSplit[0]));
            let valueB = parseInt($.trim(textSplit[1]));
            
            if (valueA > valueB && $(this.idResult).find("table tbody tr").length === 0)
                $(this.idResult).find(".tableAndPagination_container .previous").click();
            
            if (this.current < 0)
                this.current = 0;
            
            this.total = valueB;
        }
        
        $(this.idResult).find(".tableAndPagination_container .previous .mdc-button").prop("disabled", true);
        $(this.idResult).find(".tableAndPagination_container .next .mdc-button").prop("disabled", true);
        
        let buttonPrevious = $(this.idResult).find(".tableAndPagination_container .previous .mdc-button");
        let buttonNext = $(this.idResult).find(".tableAndPagination_container .next .mdc-button");
        
        if (buttonPrevious.length > 0 && buttonNext.length > 0) {
            if (this.total > 1 && this.current > 0)
                buttonPrevious.removeAttr("disabled");

            if (this.total > 1 && this.current < (this.total - 1))
                buttonNext.removeAttr("disabled");

            $.each($(this.idResult).find("table thead tr th"), (key, value) => {
                $(value).find("i").hide();
            });

            if (this.buttonStatus === "show")
                $(this.idResult).find(".tableAndPagination_container .button_container").show();
        }
    }
    
    _send = () => {
        let data = {
            'event': "tableAndPagination",
            'searchWritten': $(this.idResult).find(".tableAndPagination_container .mdc-text-field__input").val(),
            'paginationCurrent': this.current,
            'token': window.session.token
        };
        
        ajax.send(
            false,
            this.urlRequest,
            "post",
            data,
            "json",
            false,
            true,
            "application/x-www-form-urlencoded; charset=UTF-8",
            () => {
                $(this.idResult).find(".update_loading").css("display", "inline-block");
                
                $(this.idResult).find("table tbody").css("visibility", "hidden");
            },
            (xhr) => {
                ajax.reply(xhr, "");
                
                if (xhr.response.values !== undefined)
                    this.populate(xhr);
                
                helper.linkPreventDefault();
                
                if (this.selectOnlyOne === true)
                    helper.selectOnlyOneElement(this.idResult + " table tbody");
                
                this._resizeColumn();
                
                this.clickedEvent = false;
                
                $(this.idResult).find(".update_loading").css("display", "none");
            },
            null,
            null
        );
    }
    
    _resizeColumn = () => {
        $(() => {
            $(this.idResult).find("table tbody tr td").resizable({
                handles: "e"
            });
        });
    }
}