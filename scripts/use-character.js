let dieRollModal = $("#die-roll-modal");

function insetBy(rect, x, y) {
    "use strict";
    rect.x += x;
    rect.y += y;
    rect.width -= 2 * x;
    rect.height -= 2 * y;
    return rect;
}

// This draws the ticks into the canvas object represented by `gc`.
// `gc`     is the GC of the canvas to draw into.
// `bounds` is a rect object with {x, y, width, height}.
// `ticks` is the number of ticks (out of 20) which are filled-in.
function drawTicks(gc, bounds, ticks) {
    "use strict";

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
function updateTicks() {
    "use strict";
    $(".ticks-canvas").each(function (index, value) { // jshint unused:true
        drawTicks(value.getContext("2d"), {
                x: 0,
                y: 0,
                width: 50,
                height: 50
            },
            $(value).data("ticks"));
    });
}

// Select the stat in the element given by `target` to be alternately selected and not selected. `otherElement` is the other element in the pair (each stat is identified as Label: Value) and we need to toggle the select on both.
function toggleStatSelect(target, otherElement) {
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
    let tgt = $(evt.currentTarget);
    let selected = tgt.attr("data-selected") === "1";
    tgt.attr("data-selected", selected ? "0" : "1");
});

updateTicks();

function specialtyBoxes(specsJSON) {
    "use strict";
    return specsJSON.array.map(function (spec) {
        let cbId = `roll-skill-${specsJSON.skillId}-${spec.id}`;
        return `<input type='checkbox' id='${cbId}'>
<label for='${cbId}'>${spec.name} +${spec.value}</label>`;
    }).join("<br>");
}

function removeSkillHandler(evt) {
    "use strict";
    // Remove the skill row from the skills dialog
    let skillRow = evt.target.parentElement.parentElement;
    skillRow.remove();
    // and add it back to the 'Add Skill' selector's options.
    let skillName = $(evt.target).data("skill");
    let options = $($("#roll-add-skill")[0].options);
    options.last().after("<option>" + skillName + "</option>");
}

// Sets the initial conditions of the die roll modal based on the pre-selected stats and skills.
function prepareDieRollModal(modal) {
    "use strict";

    // Add Stats
    let stat, statVal = 0;
    let statLabel = $(".use-stat-label[data-selected='1']");
    let statValue = $(".use-stat-value[data-selected='1']");
    if (statLabel.length > 0) {
        stat = statLabel.html();
        statVal = parseInt(statValue.val());
    }
    let statSelect = dieRollModal.find("#stat-select")[0];
    if (stat) {
        for (let i = 0, c = statSelect.options.length; i < c; i += 1) {
            statSelect.options[i].selected = (statSelect.options[i].innerText === stat);
        }
    } else {
        statSelect.options[0].selected = true;
    }

    // Add Skills
    let tbody = modal.find("#die-roll-skills tbody");
    tbody.empty();
    let includedSkills = new Set();
    $("#use-skill-table tbody tr[data-selected='1']").each(function (i, tr) {
        // jshint unused:true
        let skillRowId = 'roll-remove-skill-' + $(tr).data("specialties").skillId;
        let skillName = $(tr).find(".use-skill-name").html();
        let skillValue = $(tr).find(".use-skill-value center").html();
        let skill = `
<tr>
  <td><button type='button' class='btn btn-secondary' 
            id='${skillRowId}' data-skill='${skillName}'>-</button></td>
  <td>${skillName} +${skillValue}</td>
  <td>${specialtyBoxes($(tr).data("specialties"))}</td>
</tr>`;
        if (tbody[0].lastElementChild) {
            $(tbody[0].lastElementChild).before(skill);
        } else {
            tbody[0].innerHTML = skill;
        }
        includedSkills.add(skillName);
    });

    // Bind all the 'remove skill' buttons to the handler.
    tbody.find("button").click(removeSkillHandler);

    // Prepare the list of the remaining skills to add.
    // jshint unused:true
    $("#roll-add-skill option:not(:first-child)").remove();
    let otherSkills = $("#use-skill-table tbody tr")
        .filter((i, tr) => !includedSkills.has(tr.cells[0].innerText))
        .map((i, tr) => "<option>" + tr.cells[0].innerText + "</option>");
    $("#roll-add-skill option:first-child").after($.makeArray(otherSkills));
}

function addSkillChangeHandler(evt) {
    "use strict";

    // This finds the row for the skill named `skillName` and creates a JSON object with the data for it.  There should always be exactly one skill matching the name.
    function dataForSkill(skillName) { // jshint unused:true
        let skillRow = $("#use-skill-table tbody tr")
            .filter((i, tr) => tr.cells[0].innerText === skillName)[0];
        return {
            name: skillRow.cells[0].innerText,
            value: skillRow.cells[1].innerText,
            specialties: $(skillRow).data("specialties")
        };
    }

    if (!evt.target.selectedOptions[0].dataset.dummy) {

        // Find the skill data and add the row to the skills table.
        let skillData = dataForSkill(evt.target.selectedOptions[0].innerText);
        let skillRowId = 'roll-remove-skill-' + skillData.specialties.skillId;
        let skill = `
<tr>
  <td><button type='button' class='btn btn-secondary' 
              id='${skillRowId}' data-skill='${skillData.name}'>-</button></td>
  <td>${skillData.name} +${skillData.value}</td>
  <td>${specialtyBoxes(skillData.specialties)}</td>
</tr>`;
        let tbody = $("#die-roll-skills tbody");
        if (tbody[0].lastElementChild) {
            $(tbody[0].lastElementChild).after(skill);
        } else {
            tbody[0].innerHTML = skill;
        }

        // Rebind all the buttons to the same handler, removing any bindings already in place or the handler will be called multiple times.
        tbody.find("button")
            .off("click")
            .click(removeSkillHandler);

        // Now remove the row from the select's options.
        $(evt.target.selectedOptions[0]).remove();
    }
}
// If the 'Add skill' combo in the die roll modal is changed, add the skill to the list and remove it from the combo.
$("#roll-add-skill").change(addSkillChangeHandler);


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
