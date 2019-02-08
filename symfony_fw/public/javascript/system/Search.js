/* global ajax */

var search = new Search();

function Search() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        var tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.create(window.url.searchRender, "#search_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $(".widget_search").on("submit", "", function(event) {
            event.preventDefault();
            
            if ($(".widget_search").find(".button_open").hasClass("animate") === true && $(".widget_search").find("input").val() !== "") {
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
                        
                        if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                            window.location.href = xhr.response.values.url;
                    },
                    null,
                    null
                );
            }
        });
        
        $(".widget_search").find(".button_open").on("click", "", function(event) {
            if ($(event.target).hasClass("animate") === true && $(".widget_search").find("input").val() !== "")
                $(this).parents(".widget_search").submit();
        });
    };
    
    // Functions private
}