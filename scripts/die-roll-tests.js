/* globals DieRoll */

function assertArrayEqual(array, string)
{
    "use strict";
    // Note this depends on JSON.stringify format, which may change.
    // TODO: find a way to deep-compare arrays.
    let codeStr = JSON.stringify(array);
    console.assert(codeStr === string, "Error: ", codeStr, " should equal ", string);
}


function assertAllInRange(numRolls, min, max, fn)
{
    "use strict";
    let codeFreq = {};
    for( let i = 0; i < numRolls; i++) {
        let result = fn();

        if (!codeFreq[result]) {
            codeFreq[result] = 1;
        } else {
            codeFreq[result] = codeFreq[result] + 1;
        }
        console.assert(result >= min && result <= max,
                       result, " must be between ", min, " and ", max);
    }
    return codeFreq;
}


// Test the group method.
assertArrayEqual(DieRoll.group(2, []), "[]");
assertArrayEqual(DieRoll.group(2, [1]), "[]");
assertArrayEqual(DieRoll.group(2, [1, 2]), "[[1,2]]");
assertArrayEqual(DieRoll.group(2, [1, 2, 3]), "[[1,2]]");
assertArrayEqual(DieRoll.group(2, [1, 2, 3, 4]), "[[1,2],[3,4]]");
assertArrayEqual(DieRoll.group(1, [1, 2, 3, 4]), "[[1],[2],[3],[4]]");
assertArrayEqual(DieRoll.group(3, [1, 2, 3, 4, 5, 6, 7]), "[[1,2,3],[4,5,6]]");


// Test the rollDie method.
let codeFreq = assertAllInRange(50, 1, 4, () => { 
    "use strict"; 
    return DieRoll.rollDie(4); 
});

// Technically this is unlikely, but not impossible.  If it happens a lot, then I'll need to check.
console.assert(codeFreq[0] === undefined, "No rolls should produce zero.");
console.assert(codeFreq[1] > 0, "At least one roll should be a 1.");
console.assert(codeFreq[2] > 0, "At least one roll should be a 2.");
console.assert(codeFreq[3] > 0, "At least one roll should be a 3.");
console.assert(codeFreq[4] > 0, "At least one roll should be a 4.");
console.assert(codeFreq[5] === undefined, "No D4 rolls should produce a 5.");

codeFreq = assertAllInRange(50, 1, 6, () => { 
    "use strict"; 
    return DieRoll.rollDie(6); 
});

console.assert(codeFreq[0] === undefined, "No rolls should produce zero.");
console.assert(codeFreq[1] > 0, "At least one roll should be a 1.");
console.assert(codeFreq[2] > 0, "At least one roll should be a 2.");
console.assert(codeFreq[3] > 0, "At least one roll should be a 3.");
console.assert(codeFreq[4] > 0, "At least one roll should be a 4.");
console.assert(codeFreq[5] > 0, "At least one roll should be a 5.");
console.assert(codeFreq[6] > 0, "At least one roll should be a 6.");
console.assert(codeFreq[7] === undefined, "No D6 rolls should produce a 7.");


console.assert(DieRoll.rollD6(true).length % 2 === 0, "RollD6 odd-numbered return array");
console.assert(DieRoll.rollD6(true).length > 0, "RollD6 empty return array");


let result = DieRoll.sanitiseHTML("");
console.assert(result === "", "sanitizeHTML empty string gives: " + result);
result = DieRoll.sanitiseHTML("<html>");
console.assert(result === "&lt;html&gt;", "sanitizeHTML result" + result + " invalid.");
result = DieRoll.sanitiseHTML("Me & you");
console.assert(result === "Me &amp; you", "Result: " + result + " should be Me &amp; you");


console.assert(DieRoll.isBotch([1, 1]) === false, "isBotch([1, 1]) returned true.");
console.assert(DieRoll.isBotch([1, 2]) === true , "isBotch([1, 2]) returned false.");
console.assert(DieRoll.isBotch([2, 1]) === true , "isBotch([2, 1]) returned false.");
console.assert(DieRoll.isBotch([2, 2]) === false, "isBotch([2, 2]) returned true.");
console.assert(DieRoll.isBotch([1, 2, 3]) === true , "isBotch([1, 2, 3]) returned false.");
console.assert(DieRoll.isBotch([1, 2, 3, 4]) === true ,
               "isBotch([1, 2, 3, 4]) returned false.");
console.assert(DieRoll.isBotch([3, 1, 2]) === false , 
               "isBotch([3, 1, 2]) returned true.");
console.assert(DieRoll.isBotch([3, 2, 1]) === false , 
               "isBotch([3, 2, 1]) returned true.");

console.log("Result for a 2D6 + stat (strength:23) no adds.");
let d = new DieRoll();
d.stat = { name: "Strength", value: 23 };
d.extraD4s = 0;
d.roll();
console.log(d.resultAsHTML);

console.log("Result for 2D6 + stat (speed:11) and skill (Archery:4) no specialty default adds.");
d = new DieRoll();
d.stat = { name: "Speed", value: 11 };
d.skills = [ {name: "Archery", value: 4 }  ];
d.roll();
console.log(d.resultAsHTML);

console.log("Result for 2D6 + stat (per: 8) + skills (Plumbing: 3), (Chic: 2) no specialties default adds.");
d = new DieRoll();
d.stat = { name: "Perception", value: 8 };
d.skills = [ { name: "Plumbing", value: 3 }, { name: "Chic", value: 2 }  ];
d.roll();
console.log(d.resultAsHTML);

console.log("Result for 2D6 + stat (Con:11), skill (Firearms:4), specialty (Snap Shot: 3) default adds.");
d = new DieRoll();
d.stat = { name: "Constitution", value: 11 };
d.skills = [ {name: "Firearms", value: 4 }  ];
d.addSpecialty("Firearms", "Snap Shot", 3);
d.addSpecialty("Firearms", "Long range", 1);
d.roll();
console.log(d.resultAsHTML);
