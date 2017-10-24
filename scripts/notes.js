function parseResult(resultText)
{
    "use strict";
    let result = null;
    try {
        result = JSON.parse(resultText);
    } catch (e) {
        // Probably php mixing error data in with the JSON result.
        // View the text which contains the php warnings.
        alert(resultText);
        return null;
    }
    return result;
}



// Save the current notes and load the new character's notes whenever the character dropdown changes.
function characterChanged()
{
    "use strict";
    
    $("#notes-error-block").addClass("hidden");
    
    $.post("actions.php", {
        action: "updateNotes",
        charId: parseInt($("#notes-textarea").data("charid")),
        notes : $("#notes-textarea").val()
    }, function (resultText) {
        let result = parseResult(resultText);
        if (result && result.success) {
            // Get the character ID from the selected option and trigger a reload of the page with the new ID in the URL.
            let opts = $("#character-select")[0].selectedOptions;
            if (opts && opts.length > 0) {
                let newId = parseInt($(opts[0]).data("id"));
                window.location = "index.php?page=notes&charid=" + newId;
            }
        } else {
            // Get the ID of the 'error block', display it and add the errors to it.
            let errHtml = "<ul><li>" + result.errors.join("</li><li>") + "</li></ul>";
            $("#notes-error-block").removeClass("hidden").html(errHtml);
        }
    });
}

$("#character-select").change(characterChanged);