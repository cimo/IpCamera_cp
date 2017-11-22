// Version 1.0.0

/* global ajax */

var authentication = new Authentication();

function Authentication() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_user_authentication").on("submit", "", function(event) {
            event.preventDefault();

            ajax.send(
                true,
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                JSON.stringify($(this).serializeArray()),
                "json",
                false,
                null,
                function(xhr) {
                    /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }*/
                    
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values !== undefined && xhr.response.values === "logged")
                        window.location.href = window.url.root + "/web/index.php";
                },
                null,
                null
            );
        });
        
        $(document).on("click", "#button_user_logout", function() {
            ajax.send(
                true,
                false,
                window.url.root + "/Requests/AuthenticationRequest.php?controller=authenticationExitCheckAction",
                "post",
                JSON.stringify({
                    'event': "logout",
                    'token': window.session.token
                }),
                "json",
                false,
                null,
                function(xhr) {
                    /*if (xhr.response.session !== undefined && xhr.response.session.userActivity !== "") {
                        ajax.reply(xhr, "");

                        return;
                    }*/
                    
                    ajax.reply(xhr, "");
                    
                    if (xhr.response.values !== undefined && xhr.response.values === "unlogged")
                        window.location.href = window.url.root + "/web/index.php";
                },
                null,
                null
            );
        });
    };
    
    // Functions private
    
}