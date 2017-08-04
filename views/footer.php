<footer class="footer">
    <div class="container">
        <span class="text-muted">&copy; Pat Wallace 2017, all ripoffs reserved.</span>
    </div>
</footer>
<!-- Bootstrap depends on jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js"
        integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn"
        crossorigin="anonymous">
</script>


<!-- a modal dialog initially hidden and used to handle login prompts -->
<div class="modal fade" id="login-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log In</h5>
                <button type="button" 
                        class="close" 
                        data-dismiss="modal" 
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>



            <div class="modal-body">
                <div id="errors" 
                     class='alert alert-danger' 
                     role='alert'
                     style="display:none">
                </div>
                <form>
                    <div class="form-group">
                        <label for="login-user">Login ID</label>
                        <input type="text" 
                               class="form-control" 
                               id="login-user"
                               name="user"
                               aria-describedby="userHelp"
                               placeholder="jdoe">
                        <small id="userHelp" class="form-text text-muted">
                            This must contain only letters, digits, hyphens and underscores.
                        </small>
                    </div>

                    <div class="form-group" 
                         id="login-name-group" 
                         style="display:none">
                        <label for="login-name">Display name</label>
                        <input type="text" 
                               class="form-control" 
                               id="login-name"
                               name="name"
                               aria-describedby="nameHelp"
                               placeholder="Jane Doe">
                        <small id="nameHelp" class="form-text text-muted">
                            This is the name you will be known by on the site.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" 
                               class="form-control"
                               id="login-password"
                               name="password">
                    </div>
                </form>
            </div>



            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-primary"
                        style="border: none"
                        id="toggle-login">
                    Sign up
                </button>
                <button type="button" 
                        class="btn btn-primary"
                        name="logIn">
                    Log In
                </button>
                <button type="button" 
                        class="btn btn-secondary" 
                        data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to handle the modal login dialog -->
<script type="text/javascript">
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

</script>

<!-- Javascript for actions on the Characters page. -->
<script type="text/javascript">

    // Search for existing skills that have been changed since the page was generated and return a JSON array of skill attributes to change.
    function updateSkills()
    {
        skillsToUpdate = [];
        // Each skill in the table rows has three important td elements:
        // edit-char-skill-name-<id>, edit-char-skill-value-<id> and edit-char-skill-ticks-<id>. If any of those have been updated, we need to amend the skill.
        $("#edit-char-skills tr[data-skill-id]").each(function (index) {
            // Each updates `this` to the current element each time the function is called.
            skillId = $(this).data("skillId");
            skillName  = $(this).find("#edit-char-skill-name-" + skillId);
            skillValue = $(this).find("#edit-char-skill-value-"+ skillId);
            skillTicks = $(this).find("#edit-char-skill-ticks-"+ skillId);
            if ((skillName.val()            !== skillName.data("originalValue") ) ||
                (parseInt(skillValue.val()) !== skillValue.data("originalValue")) ||
                (parseInt(skillTicks.val()) !== skillTicks.data("originalValue")) ) {

                let skillToUpdate = {
                    skillid: skillId,
                    name: skillName.val(),
                    value: skillValue.val(),
                    ticks: skillTicks.val()
                };
                skillsToUpdate.push(skillToUpdate);
            }
        });
        return skillsToUpdate;
    }



    $("#char-form").submit(function () {
        $("#char-error-div").hide();

        let charAttributes = {
            charid: $("#char-id").val(),
            name:   $("#char-name").val(),
            game:   $("#char-game").val(),
            age:    $("#char-age").val(),
            gender: $("#char-gender").val(),
            str:    $("#char-strength").val(),
            con:    $("#char-constitution").val(),
            dex:    $("#char-dexterity").val(),
            spd:    $("#char-speed").val(),
            cha:    $("#char-charisma").val(),
            int:    $("#char-intelligence").val(),
            per:    $("#char-perception").val(),
            lck:    $("#char-luck").val(),
            skills: updateSkills()
        }

        $.post("actions.php", {
            action       : "updateCharacter",
            charData     : JSON.stringify(charAttributes)
        }, function (resultText) {
            let result = null;
            try {
                result = JSON.parse(resultText);
            } catch (e) {
                // Probably php mixing error data in with the JSON result.
                alert(resultText);
                return false;
            }
            if (result.success) {
                let action = $("#char-id").val() == 0 ? "created" : "updated";
                let text = $("#char-name").val() + " was " + action + " successfully.";
                $("#char-error-div")
                    .html(`<div class='alert alert-success'>${text}</div>`)
                    .show();
            } else {
                let html = "<ul class='error-list'><li>" +
                    result.errors.join("</li><li>") +
                    "</li></ul>";
                $("#char-error-div")
                    .html(`<div class='alert alert-danger'>${html}</div>`)
                    .show();
            }
        }).fail(function (xhr, error, text) {
            alert(error + text);
        });
        return false; // Prevent the submit.
    });



</script>


</body>
</html>