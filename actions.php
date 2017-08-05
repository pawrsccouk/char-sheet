<?php
include("functions.php");
include("admin.php");

function update_skill($skill_info)
{
    global $link;
    $query = <<<EOQ
    UPDATE `skill` set
    `name`   = '{$link->escape_string($skill_info->name)   }',
    `value`  = '{$link->escape_string($skill_info->value)  }',
    `ticks`  = '{$link->escape_string($skill_info->ticks)  }'
    WHERE id = '{$link->escape_string($skill_info->skillid)}'
    LIMIT 1
EOQ;
    $link->query($query) or die ("Query ".$query." failed.". $link->error);
    return TRUE;
}

function insert_skill($parent_id, $skill_data)
{
    global $link;
    $query = <<<EOQ
        INSERT INTO `skill` (`name`, `value`, `ticks`, `parent`)
        VALUES (
            '{$link->escape_string($skill_data->name)}',
             {$link->escape_string($skill_data->value)},
             {$link->escape_string($skill_data->ticks)},
             {$link->escape_string($parent_id)}
        )
EOQ;
    $link->query($query) or die ("Query ".$query." failed.". $link->error);
    return TRUE;
}

function delete_skill($skill_id)
{
    global $link;
    $query = <<<EOQ
        DELETE FROM `skill` 
        WHERE `id` = {$link->escape_string($skill_id)}
        LIMIT 1
EOQ;
    $link->query($query) or die ("Query ".$query." failed.". $link->error);
    return TRUE;
}

function insert_character($char_data) 
{
    global $link;
    $query = <<<EOQ
        INSERT INTO `character` (
            `name`, `game`, `age`, `gender`, `player`,
            `strength`, `constitution`, `dexterity`, `speed`,
            `charisma`, `intelligence`, `perception`, `luck`)
        VALUES (
            '{$link->escape_string($char_data->name)}', 
            '{$link->escape_string($char_data->game)}', 
            '{$link->escape_string($char_data->age)}', 
            '{$link->escape_string($char_data->gender)}', 
            '{$link->escape_string($_SESSION['id'])}',
            '{$link->escape_string($char_data->str)}', 
            '{$link->escape_string($char_data->con)}', 
            '{$link->escape_string($char_data->dex)}',
            '{$link->escape_string($char_data->spd)}',
            '{$link->escape_string($char_data->cha)}', 
            '{$link->escape_string($char_data->int)}', 
            '{$link->escape_string($char_data->per)}', 
            '{$link->escape_string($char_data->lck)}' 
        )
EOQ;
    $result = $link->query($query) or die ("Query ".$query." failed.");
    $char_id = $link->insert_id;
    if ($char_id <= 0) { die ("link->insert_id failed."); }

    // New character, so there won't be any skills to delete or update.
    foreach ($char_data->skillsToInsert as $skill) {
        insert_skill($char_id, $skill);
    }

    return $char_id;
}


function update_character($char_data) 
{
    global $link;
    $query = <<<EOQ
        UPDATE `character` set
          `name`         = '{$link->escape_string($char_data->name)}',
          `age`          = '{$link->escape_string($char_data->age)}',
          `gender`       = '{$link->escape_string($char_data->gender)}',
          `game`         = '{$link->escape_string($char_data->game)}',
          `strength`     = '{$link->escape_string($char_data->str)}',
          `dexterity`    = '{$link->escape_string($char_data->dex)}',
          `constitution` = '{$link->escape_string($char_data->con)}',
          `speed`        = '{$link->escape_string($char_data->spd)}',
          `charisma`     = '{$link->escape_string($char_data->cha)}',
          `intelligence` = '{$link->escape_string($char_data->int)}',
          `perception`   = '{$link->escape_string($char_data->per)}',
          `luck`         = '{$link->escape_string($char_data->lck)}'
        WHERE id = '{$link->escape_string($char_data->charid)}' 
        LIMIT 1
EOQ;
    $link->query($query) or die ("Query ".$query." failed: ".$link->error);
    
    // Now update any skills that need it.
    foreach ($char_data->skillsToUpdate as $skill) {
        update_skill($skill);
    }
    foreach ($char_data->skillsToInsert as $skill) {
        insert_skill($char_data->charid, $skill);
        // TODO return JSON with the new skill IDs or reload the page.
    }
    foreach ($char_data->skillsToRemove as $skill_id) {
        delete_skill($skill_id);
    }
}

function handle_actions(&$error_log)
{
    // This is where we handle all the actions sent from the main pages
    // e.g. log in/out, send tweet etc.
    if (!$_POST) {
        $error_log[] = "Invalid request: no POST section.";
        return NULL;
    }

    switch ($_POST['action']) {
        case 'logIn':
            if (validate_user_password($error_log) and
                login_user($_POST['user'], $_POST['password'], $error_log)) {
                return array();
            }
            return NULL;

        case "signUp":
            if (validate_user_password($error_log) and
                signup_user($_POST['user'], $_POST['password'], $_POST["name"], $error_log)) {
                return array();
            }
            return NULL;

        case "updateCharacter":
            // Character data is passed as a JSON object.
            $char_data = json_decode($_POST['charData']);
            if ($char_data->charid == 0) {
                return array('charid' => insert_character($char_data));
            } else {
                update_character($char_data);
                return array();
            }
            
        default:
            $error_log[] = "Unknown action: " . $_POST['action'];
            return NULL;
    }
}

$error_log = array();
$json_result = handle_actions($error_log);
if ($json_result === NULL) {
    $json_str = json_encode(array('success' => FALSE, 'errors' => $error_log));
} else {
    $json_str = json_encode(array_merge($json_result, array('success' => TRUE)));
}
echo $json_str;
?>
