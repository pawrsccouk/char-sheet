<?php
// This is loaded at the start of each page.

/*******************************************************************************
 * This stuff is included on every page load and globally available.
 *******************************************************************************/

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


/*******************************************************************************
 * Support functions not called directly but used by the functions on the page.
 *
 * Functions starting show_ echo their data directly into the page.
 * Functions starting load_ retrieve data from the DB, make it HTML-safe and return it.
 *******************************************************************************/


// Retrieve the character ID from the 'GET' variables, or 0 if it is not found.
function char_id()
{
    if ($_GET and array_key_exists('charid', $_GET)) {
        return intval($_GET['charid']);
    }
    return 0;
}

// This loads the skills for the current character and returns them as an array keyed by the skill ID with the value being an array containing the values and specialties.
// The values in the array are HTML-escaped.
function load_skill_info()
{
    global $link;
    $the_skills = [];
    $char_id = char_id();
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
                    'id'          => htmlspecialchars($skill_id),
                    'name'        => htmlspecialchars($row['skill_name'] ),
                    'value'       => htmlspecialchars($row['skill_value']),
                    'ticks'       => htmlspecialchars($row['skill_ticks']),
                    'specialties' => []
                ];
                $the_skills[$skill_id] = $skill_data;
            }
            if ($row['specialty_name'] !== NULL) {
                $the_skills[$skill_id]['specialties'][] = [
                    'id'    => htmlspecialchars($row['specialty_id']   ),
                    'name'  => htmlspecialchars($row['specialty_name'] ),
                    'value' => htmlspecialchars($row['specialty_value'])
                ];
            }
        }
        $result->free();
    }
    return $the_skills;
}

// This loads the stats for the current character and returns them in an array keyed by the stat name. The values are HTML-escaped.
function load_stats()
{
    global $link;
    $output = [];
    $char_id = char_id();
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
        $output['strength']     = intval($row['strength']    );
        $output['constitution'] = intval($row['constitution']);
        $output['dexterity']    = intval($row['dexterity']   );
        $output['speed']        = intval($row['speed']       );
        $output['charisma']     = intval($row['charisma']    );
        $output['intelligence'] = intval($row['intelligence']);
        $output['perception']   = intval($row['perception']  );
        $output['luck']         = intval($row['luck']        );
        $result->free();
    } else {
        $output['strength']   = "0";  $output['constitution'] = "0";
        $output['dexterity']  = "0";  $output['speed']        = "0";
        $output['charisma']   = "0";  $output['intelligence'] = "0";
        $output['perception'] = "0";  $output['luck']         = "0";
    }
    return $output;
}

// This shows one row of stats, with each row having (name: value) x2
function show_stat_row($name1, $value1, $name2, $value2)
{
    echo <<<EOQ
    <!-- $name1 and $name2 -->
    <div class='row use-attribute-row'>
        <div class='col-sm-2 use-stat-label'>$name1</div>
        <div class='col-sm-1 use-stat-value'>$value1</div>
        <div class='col-sm-2 use-char-input'><input type="text"></div>
        <div class='col-sm-2'>&nbsp;</div>
        <div class='col-sm-2 use-stat-label'>$name2</div>
        <div class='col-sm-1 use-stat-value'>$value2</div>
        <div class='col-sm-2 use-char-input'><input type="text"></div>
     </div>
EOQ;
}

// This shows one stat edit box + the label identifying it.
function show_stat_edit($name, $value)
{
    $label = htmlspecialchars("char-".strtolower($name));
    $safe_name = htmlspecialchars($name);
    $value_text = "value='$value'";
    if ($value === "") {
        $value_text = ""; 
    }
    echo <<<END
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

// This shows one row of attributes, with each row having (name: value) x2
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



/*******************************************************************************
 * Functions used by the main page (the one that shows all the characters).
 *******************************************************************************/

// This returns the Log In/Log Out buttons for the given session, as well as a label indicating who is logged in.
function get_session_buttons()
{
    if ($_SESSION and
        array_key_exists('id', $_SESSION) and
        ($_SESSION['id'] > 0)) {
        echo "<span class='player-name'>{$_SESSION['name']}</span>";
        // The href here is a link to the page we were on with a parameter of 'logOut'.
        // functions.php is the first thing run and that will do the logging out.
        echo "<a href='?function=logOut'>Log Out</a>";
    } else {
        echo <<<EOH
<button class='btn btn-outline-success my-2 my-sm-0'
        type='button'
        data-toggle='modal'
        data-target='#login-modal'>
        Login / Sign Up
</button>
EOH;
    }
}



// Displays all the characters owned by the currently logged-in player.
function get_characters()
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



/*******************************************************************************
 * Functions used by the edit page.
 *******************************************************************************/



// This displays all the miscellaneous attributes for the 'edit' page.
// E.g. name, game, age etc.
function get_character_attributes_edit() 
{
    global $link;
    $char_id = char_id();
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
    $selected = [
        'male'   => ($gender === "Male"   ? "selected" : ""),
        'female' => ($gender === "Female" ? "selected" : ""),
        'other'  => ($gender === "Other"  ? "selected" : ""),
    ];

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
                   required='true'>
                    <option {$selected['male']}  >Male</option>
                    <option {$selected['female']}>Female</option>
                    <option {$selected['other']} >Other</option>
                </select>
        </div>
EOS;
}

// This displays the stats for the current character for the 'edit' page.
function get_character_stats_edit() 
{
    $row = load_stats();

    echo <<<EOQ
    <div class='form-group row'>
EOQ;
    show_stat_edit("Strength"    , $row['strength']);
    show_stat_edit("Constitution", $row['constitution']);
    show_stat_edit("Dexterity"   , $row['dexterity']);
    show_stat_edit("Speed"       , $row['speed']);
    echo <<<EOQ2
    </div>        
    <div class='form-group row'>
EOQ2;
    show_stat_edit("Charisma"    , $row['charisma']);
    show_stat_edit("Intelligence", $row['intelligence']);
    show_stat_edit("Perception"  , $row['perception']);
    show_stat_edit("Luck"        , $row['luck']);
    echo "</div>\n";
}

// This displays the skills for the current character so the user can edit them.
function get_character_skills_edit()
{
    $the_skills = load_skill_info();

    $concat_spec = function ($spec) {
        return $spec['name']." +".$spec['value'];
    };
    
    foreach ($the_skills as $skill_id => $skill) {
        $specs_json = json_encode((object)['array' => $skill['specialties']]);
        $specialties = implode(", ", array_map($concat_spec, $skill['specialties']));
        echo "\n";
        echo <<<EOH
            <tr data-skill-id='{$skill['id']}' 
                data-specialties='$specs_json' 
                id='edit-char-skill-row-{$skill['id']}'>
            <td>
                <label for='edit-char-skill-name-{$skill['id']}'>Name</label>
                <input type='text'
                       class='form-control'
                       id='edit-char-skill-name-{$skill['id']}'
                       value='{$skill['name']}'
                       data-original-value='{$skill['name']}'>
            </td>\n
EOH;
        echo <<< EOH2
            <td>
                <label for='edit-char-skill-value-{$skill['id']}'>Value</label>
                <input type='number' 
                       class='form-control'
                       id='edit-char-skill-value-{$skill['id']}'
                       placeholder='0'
                       value='{$skill['value']}'
                       data-original-value='{$skill['value']}'>
            </td>\n
EOH2;
        echo <<< EOH3
            <td>
                <label for='edit-char-skill-ticks-{$skill['id']}'>Ticks</label>
                <input type='number' 
                       class='form-control'
                       id='edit-char-skill-ticks-{$skill['id']}'
                       placeholder='0'
                       value='{$skill['ticks']}'
                       data-original-value='{$skill['ticks']}'>
            </td>\n
EOH3;
        // Add a delete button at the end of each row.
        echo <<<EOH4
            <td>
                <button type='button' 
                        class='btn btn-secondary edit-char-delete-button'
                        id='edit-char-delete-{$skill['id']}'
                        data-skill-id='{$skill['id']}'>
                            &mdash;
                </button>
            </td>
        </tr>\n
EOH4;

        // This is where the specialties will appear.
        echo <<<EOH5
            <tr>
                <td colspan='4' 
                    class='edit-char-skill-specialties' 
                    id='edit-char-skill-specialties-{$skill['id']}'>
                    <button type='button' 
                            class='btn btn-secondary edit-spec-button'
                            data-skill-id='{$skill['id']}'>
                        Edit
                    </button>
                    <span class="specialty-summary">$specialties</span>
                </td>
            </tr>\n
EOH5;
    }
}




/*******************************************************************************
 * Functions used by the use page
 *******************************************************************************/



// This displays all the miscellaneous attributes for the 'use' page.
// E.g. name, game, age etc.
function get_character_attributes_use() 
{
    global $link;
    $char_id = char_id();
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

// This displays the stats for the current character for the 'use' page.
function get_character_stats_use() 
{
    $row = load_stats();
    
    echo <<<EOQ
EOQ;
    show_stat_row("Strength", $row['strength'], "Constitution", $row['constitution']);
    show_stat_row("Intelligence", $row['intelligence'], "Charisma", $row['charisma']);
    show_stat_row("Luck", $row['luck'], "Speed", $row['speed']);
    show_stat_row("Dexterity", $row['dexterity'], "Perception", $row['perception']);
}

// This displays the skills for the current character for the 'use' page.
function get_character_skills_use()
{
    $the_skills = load_skill_info();

    $concat_spec = function ($spec) {
        return $spec['name']." +".$spec['value'];
    };
    
    foreach ($the_skills as $skill_id => $skill) {
        $specs_json = json_encode((object)['skillId' => $skill['id'],
                                           'array'   => $skill['specialties']]);

        $specialties = implode(", ", array_map($concat_spec, $skill['specialties']));
        if (!$specialties) {
            $specialties = "&nbsp;";
        }

        echo "\n";
        echo <<<EOH
            <tr class='use-attribute-row' data-specialties='{$specs_json}'>
                <td class='use-skill-name'>{$skill['name']}</td>
                <td class='use-skill-value'>
                    <center>{$skill['value']}</center>
                </td>
                <td class='use-skill-ticks'>
                    <center>
                        <canvas class='ticks-canvas' 
                                width='50px' height='50px'
                                data-ticks='{$skill['ticks']}'></canvas>
                    </center>
                </td>
                <td class='use-skill-specialties'>$specialties</td>
            </tr>
EOH;
    }
}

?>
