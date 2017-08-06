<?php
// This is loaded at the start of each page.

session_start();

// If we were called with ?function="logOut" then continue to load the page
// but without the session variables.
if ($_GET and 
    array_key_exists('function', $_GET) 
    and $_GET['function'] == 'logOut') {
    session_unset();
}


$link = new mysqli(/*"127.0.0.1"*/ NULL, "paw", "15t2chr2", "charsheet", 3306);
if ($link->connect_error) {
    // Note that connect_error can disclose public info, so don't display in production.
    die ('Connect error (' . $link->connect_errno . ') '. $link->connect_error);
}



function show_characters()
{
    global $link;
    $query = <<<EOQ
        SELECT `id`, `name`, `game` 
        FROM `character`
        WHERE `player` = '{$link->escape_string($_SESSION['id'])}'
EOQ;
    $result = $link->query($query) or die ("Query ".$query." failed.". $link->error);
    if ($result->num_rows < 1) {
        echo "You have no characters to display.";
    } else {
        while ($row = $result->fetch_assoc()) {
            $character_text = htmlspecialchars($row['name']." - ".$row['game']);
            $name = htmlspecialchars($row['name']);
            $id = htmlspecialchars($row['id']);
            echo <<<EOH
            <p class='character-box'>
                    <span class='character-name'>
                       $character_text
                    </span>
                    <br>
                    <a class='use-edit' 
                       href='?page=useCharacter&charid=$id&name=$name'> 
                          Use
                    </a>
                    <a class='use-edit' 
                       href='?page=editCharacter&charid=$id&name=$name'>
                          Edit
                    </a>
            </p>
EOH;
        }
    }
    $result->free();
}

function show_stat_edit($name, $value)
{
    $label = htmlspecialchars("char-".strtolower($name));
    $safe_name = htmlspecialchars($name);
    $value_text = "value='$value'";
    if ($value === "") {
        $value_text = ""; 
    }
    echo <<<END
        <!-- Game -->
        <div class="form-group col-md-2">
            <label for="$label">$safe_name</label>
            <input type="number" 
                   class="form-control stat-input"
                   id="$label"
                   $value_text
                   min="1">
        </div>
END;
}


function get_character_attributes($for) 
{
    global $link;
    $char_id = 0;
    if ($_GET and array_key_exists('charid', $_GET)) {
        $char_id = $_GET['charid'];
    }
    if ($char_id > 0) {
        $query = <<<ESQL
            SELECT `name`, `game`, `age`, `gender` FROM `character`
            WHERE id = $char_id LIMIT 1
ESQL;
        $result = $link->query($query) or die ("Query ".$query." failed!". $link->error);
        if ($result->num_rows != 1) {
            die ("Query $query returned no rows.");
        }
        $row = $result->fetch_assoc();
        $name = htmlspecialchars($row['name']);
        $game = htmlspecialchars($row['game']);
        $age = htmlspecialchars($row['age']);
        $gender = htmlspecialchars($row['gender']);
        $result->free();
    } else {
        $name = "" ; $game   = "";
        $age  = "0"; $gender = "Other";
    }


    echo <<<EOS
        <!-- Hidden input to pass the ID of the character to JavaScript -->
        <input type='hidden' id='char-id' value='$char_id'>
        <!-- Name -->
        <div class="form-group row">
            <label for="char-name"
                   class="col-md-1">
                Name
            </label>
            <input type="text" 
                   class="form-control col-md-8" 
                   id="char-name"
                   placeholder="John Smith"
                   value='$name'>
        </div>
        <!-- Game -->
        <div class="form-group row">
            <label for="char-game"
                   class="col-md-1">
                Game
            </label>
            <input type="text" 
                   class="form-control col-md-8"
                   id="char-game"
                   placeholder="Weird West"
                   value='$game'>
        </div>
        <div class="row">
            <!-- Age -->
                <label for="char-age"
                       class="col-md-1">
                    Age
                </label>
                <input type="number" 
                       class="form-control col-md-3"
                       id="char-age"
                       placeholder="0"
                       value='$age'>
            <!-- Spacer -->
            <div class="col-md-1">
                &nbsp;
            </div>
            <!-- Gender -->
                <label for="char-gender" 
                       class="col-md-1">
                    Gender
                </label>
                <select class="form-control col-md-3" 
                   id="char-gender"
                   value='$gender'>
                    <option>Male</option>
                    <option>Female</option>
                    <option>Other</option>
                </select>
        </div>
EOS;
}



function get_character_stats($for) 
{
    global $link;
    $char_id = 0;
    if ($_GET and array_key_exists('charid', $_GET)) {
        $char_id = $_GET['charid'];
    }
    if ($char_id > 0) {
        $query = <<<ESQL
            SELECT * FROM `character`
            WHERE id = $char_id LIMIT 1
ESQL;
        $result = $link->query($query) or die ("Query ".$query." failed!". $link->error);
        if ($result->num_rows != 1) {
            die ("Query $query returned no rows.");
        }
        $row = $result->fetch_assoc();
        $strength     = intval($row['strength']    );
        $constitution = intval($row['constitution']);
        $dexterity    = intval($row['dexterity']   );
        $speed        = intval($row['speed']       );
        $charisma     = intval($row['charisma']    );
        $intelligence = intval($row['intelligence']);
        $perception   = intval($row['perception']  );
        $luck         = intval($row['luck']        );
        $result->free();
    } else {
        $strength     = "0";  $constitution = "0";
        $dexterity    = "0";  $speed        = "0";
        $charisma     = "0";  $intelligence = "0";
        $perception   = "0";  $luck         = "0";
    }

    echo <<<EOQ
    <div class='form-group row'>
EOQ;
    show_stat_edit("Strength"    , $strength);
    show_stat_edit("Constitution", $constitution);
    show_stat_edit("Dexterity"   , $dexterity);
    show_stat_edit("Speed"       , $speed);
    echo <<<EOQ2
    </div>        
    <div class='form-group row'>
EOQ2;
    show_stat_edit("Charisma"    , $charisma);
    show_stat_edit("Intelligence", $intelligence);
    show_stat_edit("Perception"  , $perception);
    show_stat_edit("Luck"        , $luck);
    echo "</div>\n";
}





function get_character_skills($for)
{
    global $link;
    $char_id = 0;
    if ($_GET and array_key_exists('charid', $_GET)) {
        $char_id = $link->escape_string($_GET['charid']);
    }
    echo "<table id='edit-char-skills'>\n<tbody>\n";
    if ($char_id > 0) {
        $query = <<<EOQ
        SELECT `skill`.*, GROUP_CONCAT(CONCAT_WS(' +', `specialty`.`name`, `specialty`.`value`)) AS 'specstring'
        FROM `skill`
        LEFT OUTER JOIN `specialty` ON (`specialty`.`parent` = `skill`.`id`)
        WHERE `skill`.`parent` = '$char_id'
        GROUP BY `skill`.`id`
EOQ;
        $result = $link->query($query) or die ("Query ".$query." failed!". $link->error);
        while ($row = $result->fetch_assoc()) {
            $safe_id = htmlspecialchars($row['id']); // The skill ID
            $safe_value = htmlspecialchars($row['value']);
            $safe_ticks = htmlspecialchars($row['ticks']);
            $safe_name = htmlspecialchars($row['name']);
            $safe_specstring = "&nbsp;";
            if ($row['specstring']) {
                $safe_specstring = htmlspecialchars($row['specstring']);
            }
            echo <<<EOH
            <tr data-skill-id='$safe_id'>
            <td>
                <label for='edit-char-skill-name-$safe_id'>Name</label>
                <input type='text'
                       class='form-control'
                       id='edit-char-skill-name-$safe_id'
                       value='$safe_name'
                       data-original-value='$safe_name'>
            </td>
EOH;
            echo <<< EOH2
            <td>
                <label for='edit-char-skill-value-$safe_id'>Value</label>
                <input type='number' 
                       class='form-control'
                       id='edit-char-skill-value-$safe_id'
                       placeholder='0'
                       value='$safe_value'
                       data-original-value='$safe_value'>
            </td>
EOH2;
            echo <<< EOH3
            <td>
                <label for='edit-char-skill-ticks-$safe_id'>Ticks</label>
                <input type='number' 
                       class='form-control'
                       id='edit-char-skill-ticks-$safe_id'
                       placeholder='0'
                       value='$safe_ticks'
                       data-original-value='$safe_ticks'>
            </td>
EOH3;
            // This is where the update/remove buttons will go.
            echo <<<EOH4
            <td>
                <button type='button' 
                        class='btn btn-secondary edit-char-delete-button'
                        id='edit-char-delete-$safe_id'
                        data-skill-id='$safe_id'>
                            &mdash;
                </button>
            </td>
        </tr>
EOH4;
            

            // This is where the specialties will appear.
            echo <<<EOH5
            <tr>
                <td colspan='4' class='edit-char-skill-specialties'>
                    $safe_specstring
                </td>
            </tr>
EOH5;
        }
        $result->free();
    }
    echo <<<EOH6
    </tbody>
    </table>
    <!-- The button to add new table rows. -->
    <button id='char-skill-add' 
            class='btn btn-secondary'
            type='button'>
            +
    </button>
    
EOH6;
}

?>
