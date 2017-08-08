<?php
include("functions.php");
include("admin.php");

// This will accumulate all the errors during function calls.
// It will be converted to JSON and sent back to the caller.
$error_log = array();


function update_skill($skill_info)
{
    global $link, $error_log;
    $query = <<<EOQ
    UPDATE `skill` SET
    `name`   = '{$link->escape_string($skill_info->name)  }',
    `value`  = {$link->escape_string($skill_info->value)  },
    `ticks`  = {$link->escape_string($skill_info->ticks)  }
    WHERE id = {$link->escape_string($skill_info->skillid)}
    LIMIT 1
EOQ;
    if (!$link->query($query)) {
        $error_log[]  = "Query ".$query." failed.". $link->error;
        return FALSE; // Failed.
    }
    
    // Now insert or update the specialties.
    foreach ($skill_info->specialties as $spec) {
        if (array_key_exists('id', $spec)) {
            $query_spec = <<<EOQ2
            UPDATE `specialty` SET
            `name`   = '{$link->escape_string($spec->name)}',
            `value`  =  {$link->escape_string($spec->value)}
            WHERE id =  {$link->escape_string($spec->id)}
            LIMIT 1
EOQ2;
        } else {
            $query_spec = <<<EOQ3
            INSERT INTO `specialty` (`name`, `value`, `parent`)
            VALUES (
                '{$link->escape_string($spec->name)}',
                 {$link->escape_string($spec->value)},
                 {$link->escape_string($skill_info->skillid)}
            )
EOQ3;
        }
        if (!$link->query($query_spec)) {
            $error_log[] = "Query ".$query_spec." failed: ".$link->error;
            return FALSE;
        }
    }
    return TRUE; // Success!
}

function insert_skill($parent_id, $skill_data)
{
    global $link, $error_log;
    $query = <<<EOQ
        INSERT INTO `skill` (`name`, `value`, `ticks`, `parent`)
        VALUES (
            '{$link->escape_string($skill_data->name)}',
             {$link->escape_string($skill_data->value)},
             {$link->escape_string($skill_data->ticks)},
             {$link->escape_string($parent_id)}
        )
EOQ;
    if (!$link->query($query)) {
        $error_log[] = "Query ".$query." failed.". $link->error;
        return FALSE;
    }

    // Insert all the specialties attached to that skill
    // (It's a new skill, so there won't be any to update.)
    $skill_id = $link->insert_id;
    foreach ($skill_data->specialties as $specialty) {
        $query = <<<EOQ2
        INSERT INTO `specialty` (`name`, `value`, `parent`)
        VALUES (
            '{$link->escape_string($specialty->name)}',
            {$link->escape_string($specialty->value)},
            {$link->escape_string($skill_id)}
        )
EOQ2;
        if (!$link->query($query)) {
            $error_log[] = "Query ".$query." failed: ".$link->error;
            return FALSE;
        }
    }
    return TRUE; // Success.
}

function delete_skill($skill_id)
{
    global $link, $error_log;
    
    // Delete all the specialties associated with the skill
    $query_spec = <<<EOQ
        DELETE FROM `specialty`
        WHERE `parent` = {$link->escape_string($skill_id)}
EOQ;
    if (!$link->query($query_spec)) {
        $error_log[] = "Query ".$query_spec." failed.".$link->error;
        return FALSE;
    }
    
    // and then delete the skill itself.
    $query_skill = <<<EOQ2
        DELETE FROM `skill` 
        WHERE `id` = {$link->escape_string($skill_id)}
        LIMIT 1
EOQ2;
    if (!$link->query($query_skill)) {
        $error_log[] = "Query ".$query_skill." failed.".$link->error;
        return FALSE;
    }
    return TRUE; // Success.
}

function delete_specialties($spec_ids)
{
    global $link, $error_log;
    $safe_ids = array_map(function ($id) use ($link) {
        return $link->escape_string($id);
    }, $spec_ids);
    $id_clause = implode(", ", $spec_ids);
    $query = "DELETE FROM `specialty` WHERE id in ($id_clause)";
    if (!$link->query($query)) {
        $error_log[] = "Query ".$query." failed: ".$link->error;
        return FALSE;
    }
    return TRUE;
}

function insert_character($char_data) 
{
    global $link, $error_log;
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
    $result = $link->query($query);
    if (!$result) {
        $error_log[] = "Query ".$query." failed:".$link->error;
        return NULL; // Failure
    }

    $char_id = $link->insert_id;
    if ($char_id <= 0) {
        $error_log[] = "\$link->insert_id failed.";
        return NULL; // Failure
    }

    // New character, so there won't be any skills to delete or update.
    foreach ($char_data->skillsToInsert as $skill) {
        // This also inserts specialties.
        if (!insert_skill($char_id, $skill)) {
            return NULL; // Failure
        }
    }
    return $char_id; // Success.
}


function update_character($char_data) 
{
    global $link, $error_log;
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
    if (!$link->query($query)) {
        $error_log[] = "Query ".$query." failed: ".$link->error;
        return FALSE;
    }

    // The skillsToUpdate/Insert/Delete are maps with skill ID as the key and name/value/ticks/specialties as the attributes.
    
    // Now update any skills that need it.
    foreach ($char_data->skillsToUpdate as $skill) {
        if (!update_skill($skill)) {
            return FALSE;
        }
    }
    foreach ($char_data->skillsToInsert as $skill) {
        if (!insert_skill($char_data->charid, $skill)) {
            return FALSE;
        }
    }
    // I included the specialties for completeness, but really all we need are the skill IDs which are the keys.
    foreach (array_keys($char_data->skillsToRemove) as $skill_id) {
        if (!delete_skill($skill_id)) {
            return FALSE;
        }
    }
    if (count($char_data->specialtiesToRemove) > 0) {
        if (!delete_specialties($char_data->specialtiesToRemove)) {
            return FALSE;
        }
    }
    return TRUE; // Success!
}

function handle_actions()
{
    global $error_log, $link;

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

            $link->autocommit(FALSE);
            if (!$link->begin_transaction()) {
                $error_log[] = "Transaction error:".$link->error;
                return NULL;
            }

            // If the insert/update was successful, return an array which may include the character ID (for new inserts). If it failed, return NULL.
            if ($char_data->charid == 0) {
                $char_id = insert_character($char_data);
                $result = ($char_id === NULL) ? NULL: array('charid' => $char_id);
            } else {
                $success = update_character($char_data);
                $result = $success ? array() : NULL;
            }
            if ($result === NULL) {
                $error_log[] = "All changes rolled back.";
                if (!$link->rollback()) {
                    $error_log[] = "Rollback failed: ".$link->error;
                }
            } else {
                if (!$link->commit()) {
                    $error_log[] = "Commit failed: ".$link->error;
                }
            }

            $link->autocommit(TRUE);
            return $result;

        default:
            $error_log[] = "Unknown action: " . $_POST['action'];
            return NULL;
    }
}

$json_result = handle_actions();
if ($json_result === NULL) {
    $json_str = json_encode(array('success' => FALSE, 'errors' => $error_log));
} else {
    $json_str = json_encode(array_merge($json_result, array('success' => TRUE)));
}
echo $json_str;
?>
