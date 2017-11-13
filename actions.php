<?php
include("functions.php");

// This will accumulate all the errors during function calls.
// It will be converted to JSON and sent back to the caller.
$error_log = array();

include("admin.php");

function update_skill_values($id, $name, $value, $ticks)
{
    global $link, $error_log;
    $query = <<<EOQ
    UPDATE `skill` SET
      `name`   = '{$link->escape_string($name)  }',
      `value`  = $value,
      `ticks`  = $ticks
    WHERE id = $id
    LIMIT 1
EOQ;
    if (!$link->query($query)) {
        $error_log[]  = "Query ".$query." failed.". $link->error;
        return FALSE; // Failed.
    }
    return TRUE;
}

function update_skill($skill_info)
{
    global $link, $error_log;
    if (!update_skill_values(intval($skill_info->skillid),
                             $link->escape_string($skill_info->name),
                             intval($skill_info->value),
                             intval($skill_info->ticks))) {
        return FALSE; // Failed
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

function update_notes($char_id, $new_notes)
{
    global $link, $error_log;
    $safe_id = intval($char_id);
    if ($safe_id < 1) {
        $error_log[] = "Character ID ($safe_id) was invalid.";
        return FALSE;
    }
    $query = <<<EOQ
        UPDATE `character` set
          `notes` = '{$link->escape_string($new_notes)}'
        WHERE `id`  = $safe_id 
        LIMIT 1
EOQ;
    if (!$link->query($query)) {
        $error_log[] = "Query ".$query." failed: ".$link->error;
        return FALSE;
    }
    return TRUE;
}


// Returns the data for one character as a text stream in the requested format.
// Currently only supports $format="json".
function export($char_id, $format)
{
    global $link, $error_log;
    if ($format !== "json") {
        $error_log[] = "Unknown format: " . $format;
        return NULL;
    }
    if ($char_id <= 0) {
        $error_log[] = "Invalid char_id: " . $char_id;
        return NULL;
    }

    $attrs = load_attributes($char_id);
    $stats = load_stats($char_id);

    // Switch from a map keyed by ID, to an array of objects with the ID as primary attribute.
    $skills = [];
    foreach (load_skills($char_id) as $value) {
        $skills[] = $value;
    }
    return ["jsonData" => array_merge($attrs, ["stats" => $stats], ["skills" => $skills])];
}

// This wraps the function $change_fn in a begin/commit/rollback block.
// If $change_fn() returns NULL, the change will be rolled back, otherwise it'll be committed.
function in_transaction($change_fn)
{
    global $link, $error_log;
    $link->autocommit(FALSE);
    if (!$link->begin_transaction()) {
        $error_log[] = "Transaction error:".$link->error;
        return NULL;
    }
    $result = $change_fn();
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
            return in_transaction(function () use ($char_data) {
                // If the insert/update was successful, return an array which may include the character ID (for new inserts). If it failed, return NULL.
                if ($char_data->charid == 0) {
                    $char_id = insert_character($char_data);
                    $result = ($char_id === NULL) ? NULL: array('charid' => $char_id);
                } else {
                    $success = update_character($char_data);
                    $result = $success ? array() : NULL;
                }
                return $result;
            });

        case "updateSkill":
            return update_skill_values(intval($_POST['skillId']), 
                                $link->escape_string($_POST['name']),
                                intval($_POST['value']), 
                                intval($_POST['ticks'])) 
                ? array() : NULL;

        case 'updateNotes':
            return update_notes(intval($_POST['charId']), $_POST['notes'])
                ? array() : NULL;

        case 'export':
            return export(intval($_POST['charId']), $_POST['format']);

        default:
            $error_log[] = "Unknown action: " . $_POST['action'];
            return NULL;
    }
}

$json_result = handle_actions();
if ($json_result === NULL) {
    $json_str = json_encode(array('success' => FALSE, 'errors' => $error_log));
} else {
    $json_str = json_encode(array_merge($json_result, array('success' => TRUE)), JSON_NUMERIC_CHECK);
}
echo $json_str;
?>
