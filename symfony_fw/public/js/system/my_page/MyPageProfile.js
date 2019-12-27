/* global helper, ajax */

var myPageProfile = new MyPageProfile();

function MyPageProfile() {
    // Vars
    var self = this;
    
    // Properties
    
    // Functions public
    self.init = function() {
        $("#form_myPage_profile").on("submit", "", function(event) {
            event.preventDefault();
            
            ajax.send(
                true,
                $(this).prop("action"),
                $(this).prop("method"),
                new FormData(this),
                "json",
                false,
                false,
                false,
                null,
                function(xhr) {
                    ajax.reply(xhr, "#" + event.currentTarget.id);
                },
                null,
                null
            );
        });

        $("#form_myPage_profile_password").on("submit", "", function(event) {
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
        
        $("#form_myPage_profile_credit").on("submit", "", function(event) {
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
                    
                    if (xhr.response.errors === undefined) {
                        var credit = $("#form_myPage_profile_credit").find("input[name='credit']").val();
                        $("#form_myPage_profile_credit_paypal").find("input[name='quantity']").val(credit);
                        
                        $("#form_myPage_profile_credit_paypal").submit();
                    }
                },
                null,
                null
            );
        });
    };
    
    // Function private
}