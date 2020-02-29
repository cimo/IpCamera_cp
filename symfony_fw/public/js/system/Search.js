"use strict";

/* global ajax */

class Search {
    // Properties
    
    // Functions public
    constructor() {
    }
    
    action = () => {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.create(window.url.searchRender, "#search_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $(".widget_search").on("submit", "", (event) => {
            event.preventDefault();
            
            if ($(".widget_search").find(".button_open").hasClass("animate") === true && $(".widget_search").find("input").val() !== "") {
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
                        
                        if ($.isEmptyObject(xhr.response) === false && xhr.response.values !== undefined)
                            window.location.href = xhr.response.values.url;
                    },
                    null,
                    null
                );
            }
        });
        
        $(".widget_search").find(".button_open").on("click", "", (event) => {
            if ($(event.target).hasClass("animate") === true && $(".widget_search").find("input").val() !== "")
                $(event.target).parents(".widget_search").submit();
        });
    }
    
    // Functions private
}