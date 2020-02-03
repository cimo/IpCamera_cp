"use strict";

/* global helper, ajax */

const pageComment = new PageComment();

function PageComment() {
    // Vars
    let self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
    };
    
    self.action = function() {
        const tableAndPagination = new TableAndPagination();
        tableAndPagination.init();
        tableAndPagination.create(window.url.pageCommentRender, "#pageComment_result", false);
        tableAndPagination.search();
        tableAndPagination.pagination();
        
        $("#form_pageComment").find(".button_reset").hide();
        
        $("#form_pageComment_type").val("new");
        
        $("#form_pageComment").on("submit", "", function(event) {
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
        
        $("#form_pageComment .button_reset").on("click", "", function(event) {
            $(this).hide();
            
            $("#form_pageComment_type").val("new");
            
            $("#form_pageComment").find(".mdc-text-field__input").next().removeClass("mdc-floating-label--float-above mdc-text-field--invalid");
            $("#form_pageComment").find(".mdc-text-field__input").val("");
        });
        
        $(document).on("click", "#pageComment_result .button_reply", function(event) {
            $("#form_pageComment").find(".button_reset").show();
            
            let id = $(event.target).parent().attr("data-comment");
            
            $("#form_pageComment_type").val("reply_" + id);
            
            $("#form_pageComment").find(".mdc-text-field__input").next().addClass("mdc-floating-label--float-above");
        });
        
        $(document).on("click", "#pageComment_result .button_edit", function(event) {
            $("#form_pageComment").find(".button_reset").show();
            
            let id = $(event.target).parent().attr("data-comment");
            let argument = $(event.target).parent().find(".argument").text().trim();
            
            $("#form_pageComment_type").val("edit_" + id);
            
            $("#form_pageComment").find(".mdc-text-field__input").next().addClass("mdc-floating-label--float-above");
            $("#form_pageComment").find(".mdc-text-field__input").val(argument);
        });
    };
    
    // Function private
}