function insetBy(rect, x, y)
{
    "use strict";
    rect.x += x;
    rect.y += y;
    rect.width -= 2*x;
    rect.height -= 2*y;
    return rect;
}

// This draws the ticks into the canvas object represented by `gc`.
// `gc`     is the GC of the canvas to draw into.
// `bounds` is a rect object with {x, y, width, height}.
// `ticks` is the number of ticks (out of 20) which are filled-in.
function drawTicks(gc, bounds, ticks)
{
    "use strict";

    // 4 rows of 5 boxes.
    // 1 find which is smaller, width / 5, or height / 4
    let boxSize = Math.min(bounds.width / 5, bounds.height / 4);

    gc.lineWidth = 1;
    gc.strokeStyle = "darkblue";

    let ticksDrawn = 0;

    for (let iRow = 0; iRow < 4; iRow += 1) {
        for (let iCol = 0; iCol <= 4; iCol += 1) {
            let x = bounds.x + iCol * boxSize, y = bounds.y + iRow * boxSize;
            let rectBox = { x: x, y: y, width: boxSize, height: boxSize };
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
        drawTicks(value.getContext("2d"), 
                  { x: 0, y: 0, width: 50, height: 50 },
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
    let tgt = $(evt.currentTarget);
    let selected = tgt.attr("data-selected") === "1";
    tgt.attr("data-selected", selected ? "0" : "1");
});

updateTicks();

// Show the modal when the roll button is clicked.
$("#make-die-roll").click(function () {
    "use strict";
    console.log("Die roll clicked.");
    $("#die-roll-modal").modal();
});
