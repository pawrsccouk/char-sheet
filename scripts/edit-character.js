// The IDs of skills which the user has deleted.
let skillsToRemove = [];

// The pseudo-id used to identify skills which haven't been added to the DB yet.
// Incremented whenever we add a skill.
let newSkillId = 0; 

// The same but for specialties.
let newSpecialtyId = 0;

// The modal dialog that we show when the edit was successful.
let successModal = $("#success-modal");

// The modal dialog we show to edit specialties.
let editSpecialtiesModal = $("#char-edit-specialties-modal");

// A set of specialty IDs the user has selected for deletion.
let specialtiesToDelete = new Set();

// Javascript for actions on the Characters page.
// Search for existing skills that have been changed since the page was generated and return a JSON array of skill attributes to change.
function skillsToUpdate()
{
    "use strict";
    let skillsToUpdate = [];
    // Each skill in the table rows has three important td elements:
    // edit-char-skill-name-<id>, edit-char-skill-value-<id> and edit-char-skill-ticks-<id>. If any of those have been updated, we need to amend the skill.
    $("#edit-char-skills tr[data-skill-id]").each(function (index, row) {
        // jshint unused:true
        let skillId    = $(row).data("skillId");
        let skillName  = $(row).find("#edit-char-skill-name-" + skillId);
        let skillValue = $(row).find("#edit-char-skill-value-"+ skillId);
        let skillTicks = $(row).find("#edit-char-skill-ticks-"+ skillId);
        if ((skillName.val()            !== skillName.data("originalValue") ) ||
            (parseInt(skillValue.val()) !== skillValue.data("originalValue")) ||
            (parseInt(skillTicks.val()) !== skillTicks.data("originalValue")) ||
            ($(row).data("modified") === true) ) {

            let skillToUpdate = {
                skillid: skillId,
                name: skillName.val(),
                value: skillValue.val(),
                ticks: skillTicks.val(),
                specialties: $(row).data("specialties").array
            };
            skillsToUpdate.push(skillToUpdate);
        }
    });
    return skillsToUpdate;
}

// Search for skills that have been added since the page was generated and return a JSON array of skill attributes to insert.
function skillsToInsert()
{
    "use strict";
    let skillsToInsert = [];
    // Each skill in the table rows has three important td elements:
    // edit-char-skill-name-<id>, edit-char-skill-value-<id> and edit-char-skill-ticks-<id>. If any of those have been updated, we need to amend the skill.
    $("#edit-char-skills tr[data-new-skill-id]").each(function (index, row) { 
        // jshint unused:true
        let skillId =    $(row).data("newSkillId");
        let skillName  = $(row).find("#edit-char-new-skill-name-" + skillId);
        let skillValue = $(row).find("#edit-char-new-skill-value-"+ skillId);
        let skillTicks = $(row).find("#edit-char-new-skill-ticks-"+ skillId);

        let skillToInsert = {
            name:  skillName.val(),
            value: skillValue.val(),
            ticks: skillTicks.val(),
            specialties: $(row).data("specialties").array
        };
        skillsToInsert.push(skillToInsert);
    });
    return skillsToInsert;
}

// This creates a new specialty row for an existing specialty, passed as `val`.
function makeSpecialtyRow(val, skillValue)
{
    "use strict";
    if (skillValue < 1) {
        skillValue = 1;
    }
    return `<tr>
<td><label for='spec-input-name-${val.id}'>Name<\label>
<input class='form-input' id='spec-input-name-${val.id}' value='${val.name}'>
</td>
<td><label for='spec-input-value-${val.id}'>Value<\label>
<input type='number' class='form-input' id='spec-input-value-${val.id}' value='${val.value}' min='1' max='${skillValue}'>
</td>
<td><button class='spec-input-delete-button' data-spec-id='${val.id}'>&mdash;</button>
</td>
</tr>`;
}

// This creates a new specialty row for a new specialty.
function makeNewSpecialtyRow(skillValue)
{
    "use strict";
    if (skillValue < 1) {
        skillValue = 1;
    }
    newSpecialtyId += 1;
    let specId = newSpecialtyId;
    return `<tr>
<td><label for='spec-input-new-name-${specId}'>Name<\label>
<input type='text' class='form-input' id='spec-input-new-name-${specId}' value=''
placeholder='Specialty Name'>
</td>
<td><label for='spec-input-new-value-${specId}'>Value<\label>
<input type='number' class='form-input' id='spec-input-new-value-${specId}' value='1' min='1' max='${skillValue}' placeholder='0'>
</td>
<td><button class='spec-input-delete-button' data-new-spec-id='${specId}'>&mdash;</button>
</td>
</tr>`;
}



// =======================================
// Event handlers.

// Called when the 'delete specialty row' button is clicked.
// 
function deleteSpecialtyFromModalHandler(evt)
{
    "use strict";
    // If the row refers to a specialty we have loaded from the DB, then put it on a list of 'to be deleted' specialties.
    let button = evt.target;
    let specId = $(button).data("specId");
    if (specId !== undefined) {
        editSpecialtiesModal.data("specsToDelete").push(specId);
    }
    // Now remove the rows from the specialties table.
    let row = $(button.parentElement.parentElement);
    row.remove();
}

// Called when the "Add Specialty" button is clicked in the specialties modal.
// Add a new row to the specialties table with initial values.
function addSpecialtyToModalHandler()
{
    "use strict";
    let specsTable = editSpecialtiesModal.find("tbody");
    let skillValue = parseInt(editSpecialtiesModal.data("skillValue"));
    specsTable.append(makeNewSpecialtyRow(skillValue));
    // Find the last row on the table (i.e. the one we've just inserted) and bind the event handler to it's delete button.
    let lastRow = specsTable.find("tr:last-child")[0];
    let lastSkillRow = $(lastRow.previousElementSibling);
    lastSkillRow.find(".spec-input-delete-button").click(deleteSpecialtyFromModalHandler);
}

// Called when the "save" button is clicked on the specialties modal.
// Write the updated specialties data back to the skill row, mark the skill as updated and dismiss the modal.
function saveSpecialtiesFromModalHandler()
{
    "use strict";
    
    // For each specialty they deleted, add it to the list to be deleted.
    let specsToDelete = editSpecialtiesModal.data("specsToDelete");
    let skillId = editSpecialtiesModal.data("skillId");
    $(specsToDelete).each(function (index, specId) { //jshint unused:true
        specialtiesToDelete.add(specId);
    });
    
    // For each row in the specialty table, find out if it was added or amended, and update it's values in the appropriate table.
    let pendingSpecialties = [];

    let specsTable = editSpecialtiesModal.find("tbody");
    let tableRows = specsTable.find("tr");
    tableRows.each(function (index, tr) { // jshint unused:true
        let inputs = $(tr).find("td input");
        let nameInput  = $(inputs[0]);
        let valueInput = $(inputs[1]);
        let spec = { name: nameInput.val(), value: parseInt(valueInput.val()) };

        let specId = $(tr).find("td button").data("specId");
        if (specId !== undefined) {
            spec.id = specId;
        }
        pendingSpecialties.push(spec);
    });
    
    let skillRow = $("#edit-char-skill-row-" + skillId);
    if (skillRow.length === 0) {
        skillRow = $("#edit-char-skill-new-row-" + skillId);
    }
    
    // Save the specialty data as a data attachment on the row.
    skillRow.data("specialties", {array: pendingSpecialties});
    skillRow.data("modified", true);

    // Update the summary text under the skill.
    let specsRow = $(skillRow[0].nextElementSibling);
    let specialtiesText = pendingSpecialties.map((s) => s.name + " +" + s.value).join(', ');
    specsRow.find(".specialty-summary").html(specialtiesText);
    
    editSpecialtiesModal.modal('hide');
}

// This is called when the form is submitted.
// Corral all the information together and send it via AJAX.
function submitHandler()
{
    "use strict";
    $("#char-error-div").hide();

    // Will be converted to JSON and passed to the appropriate action.
    let charAttributes = {
        charid: $("#char-id").val(),
        name:   $("#char-name").val(),
        game:   $("#char-game").val(),
        age:    $("#char-age").val(),
        gender: $("#char-gender").val(),
        str:    $("#char-strength").val(),
        con:    $("#char-constitution").val(),
        dex:    $("#char-dexterity").val(),
        spd:    $("#char-speed").val(),
        cha:    $("#char-charisma").val(),
        int:    $("#char-intelligence").val(),
        per:    $("#char-perception").val(),
        lck:    $("#char-luck").val(),
        skillsToUpdate: skillsToUpdate(),
        skillsToInsert: skillsToInsert(),
        skillsToRemove: skillsToRemove, 
        specialtiesToRemove: Array.from(specialtiesToDelete)
    };
    console.log("Submit: " + JSON.stringify(charAttributes, null, 4));
    $.post("actions.php", {
        action       : "updateCharacter",
        charData     : JSON.stringify(charAttributes)
    }, function (resultText) {
        console.log("Update: " + resultText);
        let result = null;
        try {
            result = JSON.parse(resultText);
        } catch (e) {
            // Probably php mixing error data in with the JSON result.
            // View the text which contains the php warnings.
            alert(resultText);
            return false;
        }
        // On success, pop up a modal and reload the page.
        // On error, add the errors to a box on the page (so the user can see what to correct).
        if (result.success) {
            let charId = $("#char-id").val();
            if (charId === 0) {
                charId = result.charid;
            }
            let name = $("#char-name").val();
            let action = $("#char-id").val() === 0 ? "created" : "updated";
            let text = name + " was " + action + " successfully.";
            let nameEnc = encodeURIComponent(name);
            successModal.find("#success-modal-results").html(text);
            // On modal exit, reload the page.
            successModal.on('hide.bs.modal', function () {
                window.location = `index.php?page=editCharacter&charid=${charId}&name=${nameEnc}`;
            });
            successModal.modal();
        } else {
            let html = "<ul class='error-list'><li>" +
                result.errors.join("</li><li>") +
                "</li></ul>";
            $("#char-error-div")
                .html(`<div class='alert alert-danger'>${html}</div>`)
                .show();
        }
    }).fail(function (xhr, error, text) {
        //jshint unused:true
        alert(error + text);
    });
    return false; // Prevent the submit.
}

// This is called when any edit specialty button is clicked.
// Pop-up a modal to edit the specialty.
function editSpecialtyHandler(evt)
{
    "use strict";
    // This is the array of specs which the user has chosen to delete, but has not yet pressed OK in the dialog. 
    editSpecialtiesModal.data("specsToDelete", []);
    
    let editButton = evt.target;
    let skillsRow = $(editButton.parentElement.parentElement.previousElementSibling);

    let skillId = skillsRow.data("skillId");
    if (skillId === undefined) {
        skillId = skillsRow.data("newSkillId");
    }

    let skillValueInput = $(skillsRow).find("#edit-char-skill-value-" + skillId);
    if (skillValueInput.length === 0) {
        skillValueInput = $(skillsRow).find("#edit-char-new-skill-value-" + skillId);
    }
    
    // Use the name and value to generate a title for the modal.
    let skillNameInput = $(skillsRow).find("#edit-char-skill-name-" + skillId);
    if (skillNameInput.length === 0) {
        skillNameInput = $(skillsRow).find("#edit-char-new-skill-name-" + skillId);
    }
    editSpecialtiesModal.find(".modal-title").html("Specialties for " + skillNameInput.val() + " (" + skillValueInput.val() + ")");
    
    let specsTable = editSpecialtiesModal.find("tbody");
    
    // Attach the skill we are currently working on.
    editSpecialtiesModal.data("skillId", skillId);
    editSpecialtiesModal.data("skillValue", parseInt(skillValueInput.val()));
    
    specsTable.empty();
    $($(skillsRow).data("specialties").array).each(function (index, specialty) {
        //jshint unused:true
        specsTable.append(makeSpecialtyRow(specialty, parseInt(skillValueInput.val())));
    });
    
    // Now bind all of the 'remove specialty' buttons.
    specsTable.find(".spec-input-delete-button").click(deleteSpecialtyFromModalHandler);
    // and display the modal.
    editSpecialtiesModal.modal();
}

// Called when the delete event is clicked.
// Remove the requested row and store the skill ID to be deleted.
function deleteSkillHandler(evt) 
{
    "use strict";
    let skillId = $(evt.target).data("skillId");
    // Find the row this button is on, and then delete that and the next row from the table.  (The next row is the one with the specialties on.)
    let skillRow = evt.target.parentElement.parentElement;
    let rows = $([skillRow, skillRow.nextElementSibling]);
    rows.remove();

    if (skillId !== undefined) {
        skillsToRemove.push({id: skillId, specialties: $(skillRow).data("specialties").array });
    }
}

// Called when the 'add skill' button is clicked.
// Add a new row to the skills table with defaults for the values.
function addSkillHandler() 
{
    "use strict";
    newSkillId += 1;
    let addHTML = `
<tr data-new-skill-id='${newSkillId}' data-specialties='{"array": []}' id='edit-char-skill-new-row-${newSkillId}'>
<td><label for='edit-char-new-skill-name-${newSkillId}'>Name</label>
<input type='text' class='form-control' value='' placeholder='Skill name'
id='edit-char-new-skill-name-${newSkillId}'>
</td>

<td><label for='edit-char-new-skill-value-${newSkillId}'>Value</label>
<input type='number' class='form-control' value='0' placeholder='0'
id='edit-char-new-skill-value-${newSkillId}'>
</td>

<td><label for='edit-char-new-skill-ticks-${newSkillId}'>Ticks</label>
<input type='number' class='form-control' value='0' placeholder='0'
id='edit-char-new-skill-ticks-${newSkillId}'>
</td>

<td><button type='button' class='btn btn-secondary edit-char-delete-button'>&mdash;</button>
</td>
</tr>

<tr><td colspan='4'><button type='button' class='btn btn-secondary edit-spec-button'>Edit</button><span class="specialty-summary"></span></td></tr>`;

    // Append the HTML
    $("#edit-char-skills tbody:last-child").append(addHTML);

    // Find the last 'remove skill' button on the table (i.e. the one we've just inserted) and bind the event handler to it.
    let lastRow = $("#edit-char-skills tbody tr:last-child")[0];
    $(lastRow).find("td .edit-spec-button").click(editSpecialtyHandler);
    let lastSkillRow = $(lastRow.previousElementSibling);
    lastSkillRow.find(".edit-char-delete-button").click(deleteSkillHandler);
}



// =======================================
// Top-level bindings to controls.

$("#char-form").submit(submitHandler);
$("#char-skill-add").click(addSkillHandler);
editSpecialtiesModal.find("#edit-char-specialty-add").click(addSpecialtyToModalHandler);
editSpecialtiesModal.find("#edit-char-specialties-save").click(saveSpecialtiesFromModalHandler);
// Bind to existing row buttons. If we add a row, we'll need to bind to that as well.
$(".edit-char-delete-button").click(deleteSkillHandler);
$(".edit-spec-button").click(editSpecialtyHandler);
