/* global utility, ajax */

function Table() {
    // Vars
    var self = this;
    
    var urlRequest = "";
    var idResult = "";
    
    var current = 0;
    var total = 0;
    
    var sortOrderBy = false;
    
    // Properties
    
    // Functions public
    self.init = function(url, id) {
        urlRequest = url;
        idResult = id;
        
        status();
        
        utility.linkPreventDefault();
        
        utility.selectOnlyOneElement(idResult + " .table_tbody");
    };
    
    self.search = function(delegate) {
        var parent = delegate === true ? document : idResult + " .search_input input";
        var child = delegate === true ? idResult + " .search_input input" : "";
        
        $(parent).on("keyup", child, function(event) {
            if (event.which === 13) {
                current = 0;
                
                send();
            }
        });
        
        var parent = delegate === true ? document : idResult + " .search_input button";
        var child = delegate === true ? idResult + " .search_input button" : "";
        
        $(parent).on("click", child, function() {
            current = 0;
            
            send();
        });
    };
    
    self.pagination = function(delegate) {
        var parent = delegate === true ? document : idResult + " .pagination .previous";
        var child = delegate === true ? idResult + " .pagination .previous" : "";
        
        $(parent).on("click", child, function() {
            if (current > 0 && total > 1) {
                current --;
                
                send();
            }
        });
        
        var parent = delegate === true ? document : idResult + " .pagination .next";
        var child = delegate === true ? idResult + " .pagination .next" : "";
        
        $(parent).on("click", child, function() {
            if (current < (total - 1) && total > 1) {
                current ++;
                
                send();
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
        $(idResult).find(".table_spinner i").addClass("display_none");
        $(idResult).find("table tbody").removeClass("visibility_hidden");
        
        $(idResult).find(".search_input input").val(xhr.response.values.search.value);
        $(idResult).find(".pagination .text").html(xhr.response.values.pagination.text);
        $(idResult).find("table tbody").html(xhr.response.values.list);
        
        status();
    };
    
    // Functions private
    function status() {
        var textHtml = $(idResult).find(".pagination .text").html();
        
        if (textHtml !== undefined) {
            var textSplit = textHtml.split("/");
            
            if (current === 0)
                current = parseInt($.trim(textSplit[0])) - 1;
            
            if (current < 0)
                current = 0;
            
            total = parseInt($.trim(textSplit[1]));
        }
        
        $(idResult).find(".pagination .previous").addClass("disabled");
        $(idResult).find(".pagination .next").addClass("disabled");
        
        if (current > 0 && total > 1)
            $(idResult).find(".pagination .previous").removeClass("disabled");
        
        if (current < (total - 1) && total > 1)
            $(idResult).find(".pagination .next").removeClass("disabled");
        
        $.each($(idResult).find("table thead tr"), function(key, value) {
            $(value).find("th i").addClass("display_none");
        });
    }
    
    function send() {
        var data = {
            'searchWritten': $(idResult).find(".search_input input").val(),
            'paginationCurrent': current
        };
        
        ajax.send(
            urlRequest,
            "post",
            data,
            false,
            function() {
                $(idResult).find("table tbody").addClass("visibility_hidden");
                $(idResult).find(".table_spinner i").removeClass("display_none");
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
                
                utility.linkPreventDefault();
                
                utility.selectOnlyOneElement(idResult + " .table_tbody");
            },
            null,
            null
        );
    }
}