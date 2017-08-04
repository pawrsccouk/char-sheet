<?php
// This file deals with user administration, e.g. validating user ids, computing password hashes and operating on the `player` table (which holds the user IDs).





// Is $user a valid user ID, in the sense that it has only digits, letters, hyphens and underscore characters.
function is_valid_userid($user) 
{
    $result = preg_match("/^[a-z0-9_-]+$/i", $user);
    if ($result === FALSE) { die ("Bad regular expression."); }
    return $result;
}


// If $_POST has a valid email and password, return TRUE; otherwise return FALSE and add the problems encountered to the error log.
function validate_user_password(&$error_log) 
{
    $user_valid = (array_key_exists('user', $_POST) and is_valid_userid($_POST['user']));
    if (!$user_valid) {
        $error_log[] = "User name ".$_POST['user']." is not valid.\n";
    }
    $password_valid = (array_key_exists('password', $_POST) and $_POST['password']);
    if (!$password_valid) {
        $error_log[] = "Password does not exist.";
    }
    return $user_valid and $password_valid;
}

// Returns true if successful, false if not.
// Updates err_log if there was an error.
function signup_user($user, $password, $nice_name, &$error_log) 
{
    global $link;
    
    // If they don't set a name to be known by, just use their login ID.
    if (!trim($nice_name)) {
        $nice_name = $user;
    }
    
    $error = "";
    $query = "SELECT `id` FROM `player` WHERE `login_name` = '{$link->escape_string($user)}' LIMIT 1";
    $result = $link->query($query) or die ("Query [".$query."] failed.");

    if ($result->num_rows > 0) {
        $error_log[] = "The login name ". $user ." is already taken.";
        return FALSE;
    }
    
    $ins_query = <<<EOQ
    INSERT INTO `player` (`login_name`, `password`, `name`) 
    VALUES ( 
        '{$link->escape_string($user)}', 
        '{$link->escape_string(password_hash($password, PASSWORD_DEFAULT))}', 
        '{$link->escape_string($nice_name)}' 
        )
EOQ;

    $link->query($ins_query) or die ("Insert query ".$ins_query."failed.");

    // set a session variable (no need to make the user log in again).
    if ($link->insert_id <= 0) { die ("link->insert_id failed."); }
    $_SESSION['id'] = $link->insert_id;
    $_SESSION['name'] = $nice_name;
    return TRUE;
}

// Returns true if successful, false if not.
// Updates err_log if there was an error.
function login_user($user, $password, &$error_log) 
{
    global $link;
    $query = <<<EOQ
    SELECT * FROM `player` 
    WHERE `login_name` = '{$link->escape_string($user)}'
    LIMIT 1
EOQ;
    
    $result = $link->query($query) or die ("Query [".$query."] failed.");

    if ($result->num_rows <= 0) {
        $error_log[] = "The user name " . $user . " was not recognised.";
        return FALSE;
    }

    $array = $result->fetch_assoc() or die ("fetch_assoc failed.");

    if (!password_verify($password, $array['password'])) {
        $error_log[] = "The password provided was invalid.";
        return FALSE;
    } 
    // set a session variable.
    $_SESSION['id'] = $array['id'];
    $_SESSION['name'] = $array['name'];
    return TRUE;
}
?>