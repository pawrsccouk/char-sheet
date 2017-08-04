<html lang="en">
    <head>
        <title>CharSheet</title>

        <!-- Tell future versions of ie to use Edge compatibility in case it changes -->
        <meta http-equiv="x-au-compatible" content="ie=edge">
        <meta charset="utf-8">

        <!-- integrity gives a tag to ensure the file has not been tampered with
crossorigin="anonymous" means we are not sending data back to the site.
-->

        <link rel="stylesheet"
              href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css"
              integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ"
              crossorigin="anonymous">
        <link rel="stylesheet"
              href="styles.css">
    </head>
    <body>


        <!-- Navigation bar across the top of each page -->

        <nav class="navbar navbar-toggleable-md navbar-light bg-faded">

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
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Characters</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=tweets">Your tweets</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="?page=public">Public Profiles</a>
                    </li>
                </ul>

                <!-- Login info &amp; button -->
                <div class="form-inline my-2 my-lg-0">

                    <?php 
                    if ($_SESSION and 
                        array_key_exists('id', $_SESSION) 
                        and $_SESSION['id'] > 0) {
                        echo "<span class='player-name'>{$_SESSION['name']}</span>";
                        // This is just a link to the page we were on with a GET parameter. The functions.php is the first thing run on each page and that can remove the session ID before anything else sees it.
                        echo "<a href='?function=logOut'>Log Out</a>";
                    } else {
                        echo <<<EOS
                        <button class="btn btn-outline-success my-2 my-sm-0" 
                            type="button"
                            data-toggle="modal"
                            data-target="#login-modal">
                            Login / Sign Up
                        </button>
EOS;
                    }
                    ?>
                </div>
            </div>
        </nav>