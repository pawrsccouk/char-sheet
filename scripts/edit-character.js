
// The IDs of skills which the user has deleted.
let skillsToRemove = [];

// Javascript for actions on the Characters page.
// Search for existing skills that have been changed since the page was generated and return a JSON array of skill attributes to change.
function skillsToUpdate()
{
    "use strict";
    let skillsToUpdate = [];
    // Each skill in the table rows has three important td elements:
    // edit-char-skill-name-<id>, edit-char-skill-value-<id> and edit-char-skill-ticks-<id>. If any of those have been updated, we need to amend the skill.
    $("#edit-char-skills tr[data-skill-id]").each(function () {
        // Each updates `this` to the current element each time the function is called.
        let skillId = $(this).data("skillId");
        let skillName  = $(this).find("#edit-char-skill-name-" + skillId);
        let skillValue = $(this).find("#edit-char-skill-value-"+ skillId);
        let skillTicks = $(this).find("#edit-char-skill-ticks-"+ skillId);
        if ((skillName.val()            !== skillName.data("originalValue") ) ||
            (parseInt(skillValue.val()) !== skillValue.data("originalValue")) ||
            (parseInt(skillTicks.val()) !== skillTicks.data("originalValue")) ) {

            let skillToUpdate = {
                skillid: skillId,
                name: skillName.val(),
                value: skillValue.val(),
                ticks: skillTicks.val()
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
    $("#edit-char-skills tr[data-new-skill-id]").each(function () {
        let skillId = $(this).data("newSkillId");
        let skillName  = $(this).find("#edit-char-new-skill-name-" + skillId);
        let skillValue = $(this).find("#edit-char-new-skill-value-"+ skillId);
        let skillTicks = $(this).find("#edit-char-new-skill-ticks-"+ skillId);

        // Each updates `this` to the current element each time the function is called.
        let skillToInsert = {
            name: skillName.val(),
            value: skillValue.val(),
            ticks: skillTicks.val()
        };
        skillsToInsert.push(skillToInsert);
    });
    return skillsToInsert;
}

let successModal = $("#success-modal");

$("#char-form").submit(function () {
    "use strict";
    $("#char-error-div").hide();

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
        skillsToRemove: skillsToRemove
    };

    alert(JSON.stringify(skillsToRemove));
    
    $.post("actions.php", {
        action       : "updateCharacter",
        charData     : JSON.stringify(charAttributes)
    }, function (resultText) {
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
});

// Called when the delete event is clicked, remove the requested row and store the skill ID for a future update.
function deleteEventHandler(evt) {
    "use strict";
    // 'this' is the button that was clicked.
    let skillId = $(evt.target).data("skillId");
    if (skillId !== undefined) {
        skillsToRemove.push(skillId);
    }
    // Find the row this button is on, and then delete that and the next row from the table.  (The next row is the one with the specialties on.)
    
    let rows = $([evt.target.parentElement.parentElement, evt.target.parentElement.parentElement.nextElementSibling]);
    console.log(rows);
    rows.remove();
}

// Bind to existing row buttons. If we add a row, we'll need to bind to that as well.
$(".edit-char-delete-button").click(deleteEventHandler);


// When the 'add skill' button is clicked, add a new row to the table with defaults for the values.

let newSkillId = 0; // The pseudo-id used to keep new skill rows unique.
$("#char-skill-add").click(function () {
    "use strict";
    newSkillId += 1;
    let addHTML = `
<tr data-new-skill-id='${newSkillId}'>
<td>
<label for='edit-char-new-skill-name-${newSkillId}'>Name</label>
<input type='text'
class='form-control'
id='edit-char-new-skill-name-${newSkillId}'
value=''
placeholder='Skill name'>
</td>
<td>
<label for='edit-char-new-skill-value-${newSkillId}'>Value</label>
<input type='number'
class='form-control'
id='edit-char-new-skill-value-${newSkillId}'
value='0'
placeholder='0'>

</td>
<td>
<label for='edit-char-new-skill-ticks-${newSkillId}'>Ticks</label>
<input type='number'
class='form-control'
id='edit-char-new-skill-ticks-${newSkillId}'
value='0'
placeholder='0'>
</td>
<td>
<button type='button' id='char-edit-delete-new-${newSkillId}'
class='btn btn-secondary edit-char-delete-button'>
&mdash;
</button>
</td>
</tr>
<!-- The (empty) row for the specialties -->
<tr>
<td colspan='4'>&nbsp;</td>
</tr>
`;
    // Append the HTML
    $("#edit-char-skills tbody:last-child").append(addHTML);
    
    // Find the last 'remove skill' button on the table (i.e. the one we've just inserted) and bind the event handler to it.
    let lastRow = $("#edit-char-skills tbody tr:last-child")[0];
    let lastSkillRow = $(lastRow.previousElementSibling);
    lastSkillRow.find(".edit-char-delete-button").click(deleteEventHandler);
});

