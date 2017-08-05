// JavaScript to handle the modal login dialog
"use strict";

// Change the title and button text to indicate if we are logging in or signing up.
// Also change the button's name, so the PHP can discover which action we are trying.
let loginModal = $("#login-modal");
loginModal.find("#toggle-login").click(function () {
    if (loginModal.find(".modal-title").html() == "Log In") {
        loginModal.find(".modal-title").html("Sign Up");
        loginModal.find(".btn-primary").html("Sign Up").attr("name", "signUp");
        loginModal.find("#login-name-group").show();
        loginModal.find("#toggle-login").html("Log In");
    } else {
        loginModal.find(".modal-title").html("Log In");
        loginModal.find(".btn-primary").html("Log In").attr("name", "logIn");
        loginModal.find("#login-name-group").hide();
        loginModal.find("#toggle-login").html("Sign Up");
    }
});

// AJAX request to log in or sign up the user.
loginModal.find(".btn-primary").click(function () {
    $.post("actions.php", {
        action:   loginModal.find(".btn-primary").attr("name"), // logIn or signUp
        user:     loginModal.find("#login-user").val(),
        password: loginModal.find("#login-password").val(),
        name:     loginModal.find("#login-name").val()
    }, function (result) { // success
        if (result.success) {
            window.location.assign("index.php");
        } else {
            let html = "<ul class='error-list'><li>" + 
                result.errors.join('</li><li>') + 
                "</li></ul>";
            loginModal.find("#errors").html(html).show();
        }
    },
           "json");
});
