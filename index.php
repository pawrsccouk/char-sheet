<?php
// Support functions
include("functions.php");

echo "<!DOCTYPE html>\n<html>\n";

// Headers, content and footers from the views.
// Stylesheets and page header information
include("views/header.php");

$page = "";
if ($_GET and array_key_exists('page', $_GET)) {
    $page = $_GET['page'];
}

// Protect all the pages via a check to ensure we're logged in.
if ($_SESSION and 
    array_key_exists('id', $_SESSION) and 
    $_SESSION['id'] != 0) {

    switch ($page) {
        case 'editCharacter':
            include('views/edit-character.php');
            break;

        case 'useCharacter':
            include('views/use-character.php');
            break;

            // Default is to show the list of characters.
        default:
            include('views/home.php');
            break;
    }
} else {
    echo <<<END
    <div class="container">
        <p>
            Please log in to see your characters.
        </p>
    </div>
END;
}
// Scripts and close tags.
include("views/footer.php");
echo "<script type='text/javascript' src='scripts/login.js'></script>\n";
switch ($page) {
    case 'editCharacter':
        echo "<script type='text/javascript' src='scripts/edit-character.js'></script>\n";
        break;
    case 'useCharacter':
        echo "<script type='text/javascript' src='scripts/use-character.js'></script>\n";
        break;
    default:
        break;
}

// HTML end.
echo "</body>\n</html>\n";
?>
