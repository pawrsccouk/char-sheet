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

// This loads the skills for the current character and returns them as an array keyed by the skill ID with the value being an array containing the values and specialties.
function load_skill_info()
{
    global $link;
    $the_skills = [];
    $char_id = get_char_id();
    if ($char_id > 0) {
        $query = <<<EOQ
        SELECT `skill`.`id` as 'skill_id',
        `skill`.`name` as 'skill_name',
        `skill`.`value` as 'skill_value',
        `skill`.`ticks` as 'skill_ticks',
        `specialty`.`id` as 'specialty_id',
        `specialty`.`name` as 'specialty_name',
        `specialty`.`value` as 'specialty_value'
        FROM `skill`
        LEFT OUTER JOIN `specialty` ON (`specialty`.`parent` = `skill`.`id`)
        WHERE `skill`.`parent` = '$char_id'
        ORDER BY `skill`.`id` ASC
EOQ;
        $result = $link->query($query) or die ("Query ".$query." failed!". $link->error);
        while ($row = $result->fetch_assoc()) {
            $skill_id = $row['skill_id'];
            if (! array_key_exists($skill_id, $the_skills)) {
                $skill_data = [
                    'id'          => $skill_id,
                    'name'        => $row['skill_name'],
                    'value'       => $row['skill_value'],
                    'ticks'       => $row['skill_ticks'],
                    'specialties' => []
                ];
                $the_skills[$skill_id] = $skill_data;
            }
            if ($row['specialty_name'] !== NULL) {
                $the_skills[$skill_id]['specialties'][] = [
                    'id'    => $row['specialty_id'],
                    'name'  => $row['specialty_name'],
                    'value' => $row['specialty_value']
                ];
            }
        }
        $result->free();
    }
    return $the_skills;
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

// Retrieve the character ID from the 'GET' variables, or 0 if it is not found.
function get_char_id()
{
    if ($_GET and array_key_exists('charid', $_GET)) {
        return intval($_GET['charid']);
    }
    return 0;
}

function get_character_attributes_edit() 
{
    global $link;
    $char_id = get_char_id();
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



function get_character_stats_edit() 
{
    global $link;
    $char_id = get_char_id();
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





function get_character_skills_edit()
{
    $the_skills = load_skill_info();

    foreach ($the_skills as $skill_id => $skill_data) {
        $safe_id    = htmlspecialchars($skill_data['id']   ); // The skill ID
        $safe_value = htmlspecialchars($skill_data['value']);
        $safe_ticks = htmlspecialchars($skill_data['ticks']);
        $safe_name  = htmlspecialchars($skill_data['name'] );
        $specs_json = json_encode((object)['array' => $skill_data['specialties']]);
        $specs_string = implode(", ", array_map(function ($spec) {
            return $spec['name']." +".$spec['value'];
        }, $skill_data['specialties']));
        $safe_specialties = htmlspecialchars($specs_string);
        echo "\n";
        echo <<<EOH
            <tr data-skill-id='$safe_id' data-specialties='$specs_json' id='edit-char-skill-row-$safe_id'>
            <td>
                <label for='edit-char-skill-name-$safe_id'>Name</label>
                <input type='text'
                       class='form-control'
                       id='edit-char-skill-name-$safe_id'
                       value='$safe_name'
                       data-original-value='$safe_name'>
            </td>\n
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
            </td>\n
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
            </td>\n
EOH3;
        // Add a delete button at the end of each row.
        echo <<<EOH4
            <td>
                <button type='button' 
                        class='btn btn-secondary edit-char-delete-button'
                        id='edit-char-delete-$safe_id'
                        data-skill-id='$safe_id'>
                            &mdash;
                </button>
            </td>
        </tr>\n
EOH4;

        // This is where the specialties will appear.
        echo <<<EOH5
            <tr>
                <td colspan='4' class='edit-char-skill-specialties' id='edit-char-skill-specialties-$safe_id'>
                    <button type='button' 
                            class='btn btn-secondary edit-spec-button'
                            data-skill-id='$safe_id'>
                        Edit
                    </button>
                    <span class="specialty-summary">$safe_specialties</span>
                </td>
            </tr>\n
EOH5;
    }
}


function show_attribute_row($name1, $value1, $name2, $value2)
{
    echo <<<EOQ
    <div class="row use-attribute-row">
        <div class='col-sm-2 use-char-label'>$name1</div>    
        <div class='col-sm-3 use-char-value'>$value1</div>
EOQ;
    if ($name2 !== NULL) {
    echo <<<EOQ2
        <div class='col-sm-2'>&nbsp;</div>
        <div class='col-sm-2 use-char-label'>$name2</div>    
        <div class='col-sm-3 use-char-value'>$value2</div>
EOQ2;
    }
    echo "</div>";
}

function get_character_attributes_use() 
{
    global $link;
    $char_id = get_char_id();
    if ($char_id > 0) {
        $query = <<<ESQL
            SELECT `character`.`name` AS 'char_name', 
            `player`.`name` AS 'player_name',
            `game`, `age`, `gender` 
            FROM `character`
            INNER JOIN `player` ON ( `player`.`id` = `character`.`player` )
            WHERE `character`.`id` = $char_id 
            LIMIT 1
ESQL;
        $result = $link->query($query) or die ("Query ".$query." failed!". $link->error);
        if ($result->num_rows != 1) {
            die ("Query $query returned no rows.");
        }
        $row = $result->fetch_assoc();
        $name = htmlspecialchars($row['char_name']);
        $game = htmlspecialchars($row['game']);
        $age = htmlspecialchars($row['age']);
        $gender = htmlspecialchars($row['gender']);
        $player = htmlspecialchars($row['player_name']);
        $result->free();
    } else {
        $name = "" ; $game   = ""; $player = "";
        $age  = "0"; $gender = "Other";
    }
    show_attribute_row('Player', $player, 'Game', $game);
    show_attribute_row('Age', $age, 'Gender', $gender);
    show_attribute_row('Height', '', 'Weight', '');
    show_attribute_row('Hair', '', 'Eyes', '');
    show_attribute_row('Handed', '', NULL, NULL);
}

function show_stat_row($name1, $value1, $name2, $value2)
{
    echo <<<EOQ
    <!-- $name1 and $name2 -->
    <div class='row use-attribute-row'>
        <div class='col-sm-2 use-char-label'>$name1</div>
        <div class='col-sm-1 use-char-value'>$value1</div>
        <div class='col-sm-2 use-char-value'>&nbsp;</div>
        <div class='col-sm-2'>&nbsp;</div>
        <div class='col-sm-2 use-char-label'>$name2</div>
        <div class='col-sm-1 use-char-value'>$value2</div>
        <div class='col-sm-2 use-char-value'>&nbsp;</div>
     </div>
EOQ;
}

function get_character_stats_use() 
{
    global $link;
    $char_id = get_char_id();
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
EOQ;
    show_stat_row("Strength", $strength, "Constitution", $constitution);
    show_stat_row("Intelligence", $intelligence, "Charisma", $charisma);
    show_stat_row("Luck", $luck, "Speed", $speed);
    show_stat_row("Dexterity", $dexterity, "Perception", $perception);
}


function get_character_skills_use()
{
    $the_skills = load_skill_info();

    foreach ($the_skills as $skill_id => $skill_data) {
        $safe_id    = htmlspecialchars($skill_data['id']   ); // The skill ID
        $safe_value = htmlspecialchars($skill_data['value']);
        $safe_ticks = htmlspecialchars($skill_data['ticks']);
        $safe_name  = htmlspecialchars($skill_data['name'] );
        $specs_string = implode(", ", array_map(function ($spec) {
            return $spec['name']." +".$spec['value'];
        }, $skill_data['specialties']));
        $safe_specialties = htmlspecialchars($specs_string);
        if (!$safe_specialties) {
            $safe_specialties = "&nbsp;";
        }

        echo "\n";
        echo <<<EOH
            <div class='row use-attribute-row'>
                <div class='col-md-3 use-skill-name'>$safe_name</div>
                <div class='col-md-1 use-skill-value'>$safe_value</div>
                <div class='col-md-1 use-skill-ticks'>
                    <center>
                        <canvas class='ticks-canvas' 
                                width='50px' height='50px'
                                data-ticks='$safe_ticks'></canvas>
                    </center>
                </div>
                <div class='col-md-7 use-skill-specialties'>$safe_specialties</div>
            </div>
EOH;
    }
}



/*
// Returns the current specialties as a JSON-string, so we can work with them in the JavaScript.
function get_all_specialties($for)
{
    global $link;
    $spec_data = [];

    $char_id = get_char_id();
    if ($char_id > 0) {
        $query = <<<ESQL
            SELECT `skill`.`id` as 'skillid', 
            `specialty`.`id` as 'specid',
            `specialty`.`name`, 
            `specialty`.`value` 
            FROM `skill` 
            INNER JOIN `specialty` ON (`skill`.id = `specialty`.`parent`)
            WHERE `skill`.`parent` = $char_id
ESQL;
        $result = $link->query($query) or die ("Query ".$query." failed!". $link->error);
        while ($row = $result->fetch_assoc()) {
            $new_spec = array('id'    => $row['specid'], 
                              'name'  => $row['name'], 
                              'value' => $row['value']);
            if (array_key_exists($row['skillid'], $spec_data)) {
                $spec_data[$row['skillid']][] = $new_spec;
            } else {
                $spec_data[$row['skillid']] = array($new_spec);
            }
        }
        $result->free();
    }
    echo json_encode((object)$spec_data);
}
*/

?>
