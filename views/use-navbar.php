<!-- Navigation bar across the top of each 'edit' page.
This bar has only the 'Login' button -->

<nav class="navbar navbar-toggleable-sm navbar-light bg-faded">

    <!-- dropdown menu button for narrow screens  -->
    <button class="navbar-toggler navbar-toggler-right"
            type="button"
            data-toggle="collapse"
            data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent"
            aria-expanded="false"
            aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="index.php">CharSheet</a>

    <!-- Navbar controls -->
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <!-- Plain buttons and links -->
        <ul id='navbar-item-list' class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Characters</a>
            </li>
            <li class="nav-item btn btn-primary" id="make-die-roll">Roll</li>
            <li class="nav-item btn btn-secondary" id="reset-selections">Reset</li>
        </ul>


        <!-- Login info &amp; button -->
        <div class="form-inline my-2 my-lg-0">
            <?php get_session_buttons(); ?>
        </div>
    </div>
</nav>
