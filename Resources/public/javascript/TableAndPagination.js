// Version 1.0.0

/* global utility, ajax */

function TableAndPagination() {
    // Vars
    var self = this;
    
    var urlRequest = "";
    var idResult = "";
    var selectOnlyOne = "";
    
    var current = 0;
    var total = 0;
    
    var clickedEvent = false;
    var sortOrderBy = false;
    
    var buttonsStatus = "";
    
    // Properties
    self.setButtonsStatus = function(value) {
        buttonsStatus = value;
    };
    
    // Functions public
    self.init = function(url, id, singleSelection) {
        urlRequest = url;
        idResult = id;
        selectOnlyOne = singleSelection;
        
        status();
        
        utility.linkPreventDefault();
        
        if (selectOnlyOne === true)
            utility.selectOnlyOneElement(idResult + " .table_tbody");
        
        resizeColumn();
    };
    
    self.search = function(delegate) {
        var parentButton = delegate === true ? document : idResult + " .search_input .button_search";
        var childButton = delegate === true ? idResult + " .search_input .button_search" : "";
        
        var parentField = delegate === true ? document : idResult + " .search_input input";
        var childField = delegate === true ? idResult + " .search_input input" : "";
        
        $(parentField).on("keyup", childField, function(event) {
            if (clickedEvent === true)
                return;
            
            if (event.which === 13) {
                current = 0;
                
                send(childButton);
                
                clickedEvent = true;
            }
        });
        
        $(parentButton).on("click", childButton, function() {
            if (clickedEvent === true)
                return;
            
            current = 0;
            
            send(childButton);
            
            clickedEvent = true;
        });
    };
    
    self.pagination = function(delegate) {
        var parentPrevious = delegate === true ? document : idResult + " .pagination .previous";
        var childPrevious = delegate === true ? idResult + " .pagination .previous" : "";
        
        var parentNext = delegate === true ? document : idResult + " .pagination .next";
        var childNext = delegate === true ? idResult + " .pagination .next" : "";
        
        $(parentPrevious).on("click", childPrevious, function() {
            if (clickedEvent === true)
                return;
            
            if (total > 1 && current > 0) {
                current --;
                
                send(childPrevious);
                
                clickedEvent = true;
            }
        });
        
        
        
        $(parentNext).on("click", childNext, function() {
            if (clickedEvent === true)
                return;
            
            if (total > 1 && current < (total - 1)) {
                current ++;
                
                send(childNext);
                
                clickedEvent = true;
            }
        });
    };
    
    self.sort = function(delegate) {
        var parent = delegate === true ? document : idResult + " table thead tr";
        var child = delegate === true ? idResult + " table thead tr" : "";
        
        $(parent).on("click", child, function(event) {
            var bodyRows = $(idResult).find("table tbody tr");
            
            var currentIndex = $(event.target).is("i") === false ? $(event.target).index() : $(event.target).parent().index();
            
            $(this).find("th i").addClass("display_none");
            
            if (sortOrderBy === false) {
                $(this).find("th").eq(currentIndex).find("i").eq(0).removeClass("display_none");
                $(this).find("th").eq(currentIndex).find("i").eq(1).addClass("display_none");
                
                sortOrderBy = true;
            }
            else {
                $(this).find("th").eq(currentIndex).find("i").eq(0).addClass("display_none");
                $(this).find("th").eq(currentIndex).find("i").eq(1).removeClass("display_none");
                
                sortOrderBy = false;
            }
            
            bodyRows.sort(function(a, b) {
                var result = 0;
                var first = $.trim($(a).children("td").eq(currentIndex).text().toLowerCase());
                var second = $.trim($(b).children("td").eq(currentIndex).text().toLowerCase());
                
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
        $(idResult).find("table tbody").removeClass("visibility_hidden");
        
        if (xhr.response.values !== undefined) {
            $(idResult).find(".search_input input").val(xhr.response.values.search.value);
            $(idResult).find(".pagination .text").html(xhr.response.values.pagination.text);
            
            if ($(idResult).find("table tbody").length > 0)
                $(idResult).find("table tbody").html(xhr.response.values.listHtml);
            else
                $(idResult).find(".container_list").html(xhr.response.values.listHtml);

            status();
            
            resizeColumn();
        }
    };
    
    // Functions private
    function status() {
        var textHtml = $(idResult).find(".pagination .text").html();
        
        if (textHtml !== undefined) {
            var textSplit = textHtml.split("/");
            var valueA = parseInt($.trim(textSplit[0]));
            var valueB = parseInt($.trim(textSplit[1]));
            
            if (valueA > valueB && $(idResult).find("table tbody tr").length === 0)
                $(idResult).find(".pagination .previous").click();
            
            current = valueA - 1;
            
            if (current < 0)
                current = 0;
            
            total = valueB;
        }
        
        $(idResult).find(".pagination .previous").addClass("disabled");
        $(idResult).find(".pagination .next").addClass("disabled");
        
        if (total > 1 && current > 0)
            $(idResult).find(".pagination .previous").removeClass("disabled");
        
        if (total > 1 && current < (total - 1))
            $(idResult).find(".pagination .next").removeClass("disabled");
        
        $.each($(idResult).find("table thead tr"), function(key, value) {
            $(value).find("th i").addClass("display_none");
        });
        
        if (buttonsStatus === "show")
            $(idResult).find(".buttons").removeClass("display_none");
    }
    
    function send(child) {
        if ($(child).find("i").length > 1) {
            $(child).find("i").eq(0).hide();
            $(child).find("i").eq(1).show();
        }
        else if ($(child).find("i").length === 1)
            $(child).find("i").show();
        else
            $(child).parent().find("i").show();
        
        var data = {
            'searchWritten': $(idResult).find(".search_input input").val(),
            'paginationCurrent': current,
            'token': window.session.token
        };
        
        ajax.send(
            false,
            true,
            urlRequest,
            "post",
            data,
            "json",
            false,
            function() {
                $(idResult).find("table tbody").addClass("visibility_hidden");
            },
            function(xhr) {
                ajax.reply(xhr, "");
                
                if (xhr.response.render !== undefined) {
                    $(idResult).html(xhr.response.render);
                    
                    status();
                }
                else {
                    if (xhr.response.values !== undefined)
                        self.populate(xhr);
                }
                
                if ($(child).find("i").length > 1) {
                    $(child).find("i").eq(0).show();
                    $(child).find("i").eq(1).hide();
                }
                else if ($(child).find("i").length === 1)
                    $(child).find("i").hide();
                else
                    $(child).parent().find("i").hide();
                
                utility.linkPreventDefault();
                
                if (selectOnlyOne === true)
                    utility.selectOnlyOneElement(idResult + " .table_tbody");
                
                resizeColumn();
                
                clickedEvent = false;
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