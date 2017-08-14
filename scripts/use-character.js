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

updateTicks();