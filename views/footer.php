<footer class="footer">
    <div class="container">
        <span class="text-muted">&copy; Pat Wallace 2017, all ripoffs reserved.</span>
    </div>
</footer>
<!-- Bootstrap depends on jQuery -->
<script src="dependencies/jquery.min.js"></script>
<script src="dependencies/tether.min.js"></script>
<script src="dependencies/bootstrap-4a6.min.js"></script>


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
                     class='alert alert-danger hidden' 
                     role='alert'>
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

                    <div class="form-group hidden" 
                         id="login-name-group">
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



<!-- a modal dialog initially hidden and used to show successful operations -->
<div class="modal fade" id="success-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Success</h5>
            </div>

            <div class="modal-body">
                <div id="success-modal-results" 
                     class='alert alert-success' 
                     role='alert'>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" 
                        class="btn btn-primary" 
                        data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>


<?php
$link->close();
?>