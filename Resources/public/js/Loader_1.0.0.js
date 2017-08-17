var loader = new Loader();

function Loader() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.create = function(type) {
        if (type === "image")
            $("<div id=\"loader_request\" class=\"display_none shadow\"><img src=\"" + window.url.webBundle + "/images/templates/" + window.settings.template + "/loader_request.gif\" alt=\"loader_request.gif\"/></div>").appendTo("body");
        
        $(window).on("beforeunload", "", function() {
            if ($("#loader_request").length === 0 || $("#loader_request").css("display") === "none") {
                if (type === "font")
                    $("<div id=\"loader_request\" class=\"shadow\"><i class=\"fa fa-refresh fa-spin fa-3x fa-fw\"></i></div>").appendTo("body");
                else if (type === "image")
                    $("#loader_request").show();
            }
        });
    };
    
    self.show = function() {
        if ($("#backdrop").length === 0) {
            $("body").addClass("overflow_hidden");
            $("<div id=\"backdrop\" class=\"fade in\"></div>").appendTo("body");
            
            $("#loader").show();
        }
    };
    
    self.hide = function() {
        $("#loader").hide();
        
        $("#backdrop").remove();
        $("body").removeClass("overflow_hidden");
    };
    
    self.remove = function() {
        var requestLoader = setInterval(function() {
            if ($("#loader_request").length > 0 || $("#loader_request").css("display") !== "none") {
                $("#loader_request").remove();

                clearInterval(requestLoader);
            }
        }, 1000);
    };
    
    // Functions private
}