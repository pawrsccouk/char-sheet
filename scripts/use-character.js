/* globals DieRoll, saveAs */

let dieRollModal = $("#die-roll-modal");


// Get all the character data as a JSON object.  This allows me to keep all the DOM-scraping in one place.  This returns the data on the main screen (i.e. all the possible skills and specialties for the character.)
function getCharacterInfo()
{
    "use strict";

    let statsBlock = $("div.use-stats-block");
    let statsRows = statsBlock.find("div.use-stat-row");
    let stats = {};
    let selectedStat;

    statsRows.each((i, statRow) => { // jshint unused:true
        let name1 = statRow.children[0].innerText;
        let value1 = parseInt(statRow.children[1].innerText);
        if (statRow.children[0].dataset.selected === "1") {
            selectedStat = name1;
        }
        let name2 = statRow.children[4].innerText;
        let value2 = parseInt(statRow.children[5].innerText);
        if (statRow.children[4].dataset.selected === "1") {
            selectedStat = name2;
        }
        stats[name1] = value1;
        stats[name2] = value2;
    });

    let skillsTable = $("table#use-skill-table tbody");
    let skills = [];
    skillsTable.find("tr").each((i, tr) => { // jshint unused:true
        let specialties = $(tr).data("specialties");
        let id = specialties.skillId;
        let name = tr.children[0].innerText;
        let selected = tr.dataset.selected === "1" ? true : false;
        let value = parseInt(tr.children[1].innerText);
        // td -> center -> canvas
        let ticks = $(tr.children[2].firstElementChild.firstElementChild).data("ticks");
        skills.push({ id, name, value, ticks, specialties, selected });
    });

    let charid = $("#session-block").data("charid");

    return {
        stats, selectedStat, skills, charid: charid,
        findSkill(name)
        {
            let match = this.skills.filter(s => s.name === name);
            console.assert(match.length > 0, "No skill found called " + name);
            return match.length > 0 ? match[0] : {};
        },
        findSpecialty(skillName, name)
        {
            let skill = this.findSkill(skillName);
            let match = skill.specialties.array.filter(spec => spec.name === name);
            console.assert(match.length > 0, 
                           "No specialty found called " + name + " for skill " + skillName);
            return match.length > 0 ? match[0] : {};
        }
    };    
}

// Get all the data from the 'Die Roll' modal; i.e. the selected stat, skills and specialties. This allows me to keep all the DOM-scraping in one place.
function getDieRollModalInfo()
{
    "use strict";
    // The skills.
    let allSkills = [];
    let tbody = dieRollModal.find("#die-roll-skills tbody");
    tbody.find("tr").each((i, tr) => { // jshint unused:true
        let skillName = tr.children[1].innerText;
        let theSkill = { name: skillName };
        let selectedChecks = $(tr.children[2]).find("input").filter((i,e) => e.checked);
        let selectedSpecs = selectedChecks.map(
            (i,input) => input.nextElementSibling.innerText);
        theSkill.specialties = Array.from(selectedSpecs);
        allSkills.push(theSkill);
    });

    // Adds & D4s
    let adds = parseInt(dieRollModal.find("#die-roll-stats-misc #static-adds").val());
    let d4s = parseInt(dieRollModal.find("#die-roll-stats-misc #extra-d4s").val());
    let json = { skills: allSkills, adds, extraD4s: d4s };

    // Add the Stat (if present)
    let statOption = dieRollModal.find("#stat-select")[0].selectedOptions[0];
    let statName = statOption.innerHTML;
    if (statName !== "None") {
        json.stat = statName;
    }
    return json;
}

function insetBy(rect, x, y) {
    "use strict";
    rect.x += x;
    rect.y += y;
    rect.width -= 2 * x;
    rect.height -= 2 * y;
    return rect;
}

// Takes a text string and returns a JSON object with parsed text.
// On failure, displays the string in an alert (it is usually PHP error code intermingled with the response data if there is a compilation error in the PHP code) and returns undefined.
function parseResult(resultText)
{
    "use strict";
    try {
        return JSON.parse(resultText);
    } catch (e) {
        // Probably php mixing error data in with the JSON result.
        // View the text which contains the php warnings.
        alert(resultText);
    }
    return undefined;
}

// This draws the ticks into the canvas object represented by `gc`.
// `gc`     is the GC of the canvas to draw into.
// `bounds` is a rect object with {x, y, width, height}.
// `ticks` is the number of ticks (out of 20) which are filled-in.
function drawTicks(gc, bounds, ticks) 
{
    "use strict";
    gc.clearRect(bounds.x, bounds.y, bounds.width, bounds.height);

    // 4 rows of 5 boxes.
    // 1 find which is smaller, width / 5, or height / 4
    let boxSize = Math.min(bounds.width / 5, bounds.height / 4);

    gc.lineWidth = 1;
    gc.strokeStyle = "darkblue";

    let ticksDrawn = 0;

    for (let iRow = 0; iRow < 4; iRow += 1) {
        for (let iCol = 0; iCol <= 4; iCol += 1) {
            let x = bounds.x + iCol * boxSize,
                y = bounds.y + iRow * boxSize;
            let rectBox = {
                x: x,
                y: y,
                width: boxSize,
                height: boxSize
            };
            gc.strokeRect(rectBox.x, rectBox.y, rectBox.width, rectBox.height);

            if (ticksDrawn < ticks) {
                rectBox = insetBy(rectBox, 2, 2);
                gc.fillRect(rectBox.x, rectBox.y, rectBox.width, rectBox.height);
                ticksDrawn += 1;
            }
        }
    }
}

// Called when a value in the ticks has been changed. Redraw all the ticks boxes.
function updateTicks() 
{
    "use strict";
    $(".ticks-canvas").each(function (index, value) { // jshint unused:true
        drawTicks(
            value.getContext("2d"), 
            {
                x: 0,
                y: 0,
                width: 50,
                height: 50
            }, 
            $(value).data("ticks"));
    });
}

// Select the stat in the element given by `target` to be alternately selected and not selected. `otherElement` is the other element in the pair (each stat is identified as Label: Value) and we need to toggle the select on both.
function toggleStatSelect(target, otherElement) 
{
    // Note: we must use .attr("data-selected") instead of .data("selected") because jQuery caches the data entries elsewhere once they have been set, and we need to pick up the selected tag in the CSS.
    "use strict";
    let tgt = $(target);
    let selected = (tgt.attr("data-selected") === "1");

    // first deselect every stat, then select the one we want to show.
    $(".use-stat-label").add($(".use-stat-value")).attr("data-selected", "0");

    // If it wasn't selected, then select it now.
    if (!selected) {
        tgt.attr("data-selected", "1");
        $(otherElement).attr("data-selected", "1");
    }
}

// All the stat rows should toggle a data value to indicate if they are selected or not.
// This will be picked up in the CSS and used to highlight the rows.
$(".use-stat-label").click(function (evt) {
    "use strict";
    toggleStatSelect(evt.target, evt.target.nextElementSibling);
});

$(".use-stat-value").click(function (evt) {
    "use strict";
    toggleStatSelect(evt.target, evt.target.previousElementSibling);
});

// The skill rows use tables, so we can just select the 'tr' elements.
// Also we can select multiple rows so we just toggle the selection flag.
$("#use-skill-table tbody tr").click(function (evt) {
    "use strict";
    let toggle = b => b ? "0" : "1";
    let tgt = $(evt.currentTarget);
    let selected = tgt.attr("data-selected") === "1";
    tgt.attr("data-selected", toggle(selected));
});

updateTicks();

function removeSkillHandler(evt) 
{
    "use strict";
    // Remove the skill row from the skills dialog
    let skillRow = evt.target.parentElement.parentElement;
    skillRow.remove();

    // and add it back to the 'Add Skill' selector's options.
    let skillName = $(evt.target).data("skill");
    let options = $($("#roll-add-skill")[0].options);
    options.last().after("<option>" + skillName + "</option>");

    // and disable the tick checkbox unless there is exactly one skill.
    let tbody = dieRollModal.find("#die-roll-skills tbody");
    let cb = dieRollModal.find("#roll-add-tick")[0];
    cb.disabled = (tbody.find("tr").length !== 1);
    if (cb.disabled) {
        cb.checked = false;
    }
}

function rowForSkill(skillJSON)
{
    "use strict";
    function specialtyBoxes(specsJSON) 
    {
        return specsJSON.array.map(function (spec) {
            let cbId = `roll-skill-${specsJSON.skillId}-${spec.id}`;
            return `<input type='checkbox' id='${cbId}'>
<label for='${cbId}'>${spec.name}</label>`;
        }).join("<br>");
    }

    let skillRowId = 'roll-remove-skill-' + skillJSON.specialties.skillId;
    return `
<tr>
<td><button type='button' class='btn btn-secondary' 
id='${skillRowId}' data-skill='${skillJSON.name}'>-</button></td>
<td>${skillJSON.name}</td>
<td>${specialtyBoxes(skillJSON.specialties)}</td>
</tr>`;
}

// Sets the initial conditions of the die roll modal based on the pre-selected stats and skills.
function prepareDieRollModal(modal) 
{
    "use strict";

    dieRollModal.find("#collapse-choose").collapse('show');
    dieRollModal.find("#collapse-results").collapse('hide');

    // Remove any errors from previous sessions.
    dieRollModal.find("#roll-error-div").empty().hide();

    let charJSON = getCharacterInfo();

    // Add Stats
    let stat = charJSON.selectedStat;
    let statSelect = dieRollModal.find("#stat-select")[0];
    $(statSelect.options).each((i, option) => { // jshint unused:true
        option.selected = option.innerText === stat;
    });
    if (!stat) { // If no stat selected, then select 'None'.
        statSelect.options[0].selected = true;
    }

    // Add Skills
    let tbody = modal.find("#die-roll-skills tbody");
    tbody.empty();
    let includedSkills = new Set();
    let selectedSkills = charJSON.skills.filter(sk => sk.selected);

    selectedSkills.map(rowForSkill).forEach(tr => {
        if (tbody[0].lastElementChild) {
            $(tbody[0].lastElementChild).before(tr);
        } else {
            tbody[0].innerHTML = tr;
        }
    });

    selectedSkills.forEach(sk => {
        includedSkills.add(sk.name);
    });

    // Bind all the 'remove skill' buttons to the handler.
    tbody.find("button").click(removeSkillHandler);


    // If we have exactly one skill selected, default the ticks checkbox to checked and enable it.
    let initTick = selectedSkills.length === 1;
    let cb = dieRollModal.find("#roll-add-tick")[0];
    cb.checked = initTick;
    cb.disabled = !initTick;

    // Prepare the select with the remaining skills to add.
    $("#roll-add-skill option:not(:first-child)").remove();

    let otherSkills = charJSON.skills
    .filter(sk => !includedSkills.has(sk.name))
    .map(sk => "<option>" + sk.name + "</option>");
    $("#roll-add-skill option:first-child").after($.makeArray(otherSkills));
}

function addSkillChangeHandler(evt) 
{
    "use strict";
    let charJSON = getCharacterInfo();
    let selectedOption = evt.target.selectedOptions[0];
    if (!selectedOption.dataset.dummy) {

        // Find the skill data and add the row to the skills table.
        let skillName = selectedOption.innerText;
        let skillData = charJSON.findSkill(skillName);
        let skillRowHTML = rowForSkill(skillData);
        let tbody = $("#die-roll-skills tbody");
        if (tbody[0].lastElementChild) {
            $(tbody[0].lastElementChild).after(skillRowHTML);
        } else {
            tbody[0].innerHTML = skillRowHTML;
        }

        // Rebind all the buttons to the same handler, removing any bindings already in place or the handler will be called multiple times.
        tbody.find("button").off("click").click(removeSkillHandler);

        // Now remove the row from the select's options.
        $(selectedOption).remove();

        // If we now have exactly one skill, then enable the 'Ticks' button.
        let cb = dieRollModal.find("#roll-add-tick")[0];
        cb.disabled = tbody.find("tr").length !== 1;
        if (cb.disabled) {
            cb.checked = false;
        }
    }
}

// If the 'Add skill' combo in the die roll modal is changed, add the skill to the list and remove it from the combo.
$("#roll-add-skill").change(addSkillChangeHandler);

// Find the row in the skills table which represents this skill, and then update it to the new skill values.
function updateSkillRow(skill)
{
    "use strict";
    let tbody = $("#use-skill-table tbody");
    let rows = tbody.find("tr").filter( // jshint unused:true
        (i, tr) => $(tr).data("specialties").skillId === skill.id);
    console.assert(rows.length === 1, "Skill row not found!");
    let tr = rows[0];
    tr.cells[1].firstElementChild.innerText = skill.value;
    let ticksCanvas = tr.cells[2].firstElementChild.firstElementChild;
    $(ticksCanvas).data("ticks", skill.ticks);
    updateTicks();
}

// Takes an array of errors, and displays them on a warning panel ID `contentId`.
function showErrors(errors, contentId)
{
    "use strict";
    let errorText = errors.join("</li><li>");
    let html = "<ul class='error-list'><li>" +
        errorText +
        "</li></ul>";
    dieRollModal.find(contentId)
        .html(`<div class='alert alert-danger'>${html}</div>`)
        .show();
}

// This is called to add a tick to a skill.  It will be done when the 'Roll' button is clicked and will output errors into the results window.
// Returns true if successful.
function addTickToSkill(skill)
{
    "use strict";

    skill.ticks += 1;
    if (skill.ticks >= 20) {
        skill.ticks = 0;
        skill.value += 1;
    }

    $.post("actions.php", {
        action:  "updateSkill",
        skillId: skill.id,
        name:    skill.name,
        value:   skill.value,
        ticks:   skill.ticks
    }, function (resultText) {
        console.log("addTick: " + resultText);
        let result = parseResult(resultText);
        if (!result) { 
            return false; 
        }
        // On error, add the errors to a box on the page.
        if (result.success) {
            updateSkillRow(skill);
        } else {
            showErrors(result.errors, "#roll-error-div");
            return false;
        }
    }).fail(function (xhr, error, text) {
        //jshint unused:true
        showErrors("AJAX request failed: " + error + " " + text);
        return false;
    });
    return true;
}

// This is called when the 'Roll' button is clicked. Set up the DieRoll object and show the results.
function rollDiceModalHandler()
{
    "use strict";
    let roll = new DieRoll();
    let modalJSON = getDieRollModalInfo();
    let charJSON = getCharacterInfo();

    // Find the selected stat.
    if (modalJSON.stat) {
        roll.stat = { 
            name: modalJSON.stat, 
            value: charJSON.stats[modalJSON.stat] 
        };
    }

    // Find the selected skills
    modalJSON.skills.forEach(sk => {
        let skill = charJSON.findSkill(sk.name);
        roll.addSkill(skill.name, skill.value, skill.ticks);
        sk.specialties.forEach(specName => {
            let specJSON = charJSON.findSpecialty(sk.name, specName);
            roll.addSpecialty(sk.name, specName, specJSON.value);
        });
    });

    roll.extraD4s = modalJSON.extraD4s;
    roll.adds = modalJSON.adds;
    roll.addTick = dieRollModal.find("#roll-add-tick")[0].checked;

    // Now make the roll and record the results.
    roll.roll();
    dieRollModal.find("#roll-results").html(roll.resultAsHTML);

    if (roll.addTick) {
        let numSkills = modalJSON.skills.length;
        console.assert(numSkills === 1, "addTick but ", numSkills, " skills!");
        if (numSkills > 0) {
            let skill = modalJSON.skills[0];
            addTickToSkill(charJSON.findSkill(skill.name));
        }
    }

    dieRollModal.find("#collapse-choose").collapse('hide');
    dieRollModal.find("#collapse-results").collapse('show');
}

dieRollModal.find("#die-roll-roll-dice").click(rollDiceModalHandler);

// Present a JSON version to the user for backup purposes.
$("#download-json").click(function () {
    "use strict";
    $.post("actions.php", {
        action: "export",
        format: "json",
        charId: getCharacterInfo().charid
    }, function (resultText) {
        let result = parseResult(resultText);
        if (!result) {
            return false;
        }
        // On success, trigger a new download with the data provided, otherwise add the errors to a box on the page.
        if (result.success) {
            let blob = new Blob([JSON.stringify(result.jsonData)], {type: "application/json"});
            saveAs(blob, result.jsonData.name + ".json");
        } else {
            console.log(result.errors);
            showErrors(result.errors, "#error-div");
            return false;
        }
    }).fail(function (xhr, error, text) { //jshint unused:true
        showErrors("AJAX request failed: " + error + " " + text);
        return false;
    });
    return true;
});

// Clear all selections when the 'Reset' button is clicked.
$("#reset-selections").click(function () {
    "use strict";
    let toProcess = $("#use-skill-table tbody tr[data-selected='1']")
    .add($(".use-stat-label[data-selected='1']"))
    .add($(".use-stat-value[data-selected='1']"));
    toProcess.attr("data-selected", "0");
});

// Show the modal when the roll button is clicked.
$("#make-die-roll").click(function () {
    "use strict";
    prepareDieRollModal(dieRollModal);
    dieRollModal.modal();
});
