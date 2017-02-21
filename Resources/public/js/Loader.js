var loader = new Loader();

function Loader() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.create = function(type) {
        if (type === "image")
            $("<div id=\"request_loader\" class=\"display_none shadow\"><img src=\"" + window.url.public + "/images/templates/" + window.settings.template + "/request_loader.gif\" alt=\"request_loader.gif\"/></div>").appendTo("body");
        
        $(window).on("beforeunload", "", function() {
            if ($("#request_loader").length === 0 || $("#request_loader").css("display") === "none") {
                if (type === "font")
                    $("<div id=\"request_loader\" class=\"shadow\"><i class=\"fa fa-refresh fa-spin fa-3x fa-fw\"></i></div>").appendTo("body");
                else if (type === "image")
                    $("#request_loader").show();
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
            if ($("#request_loader").length > 0 || $("#request_loader").css("display") !== "none") {
                $("#request_loader").remove();

                clearInterval(requestLoader);
            }
        }, 1000);
    };
    
    // Functions private
}