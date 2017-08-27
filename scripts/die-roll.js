/* exported DieRoll */

//
//  DieRoll.js
//  CharSheet
//
//  Created by Patrick Wallace on 24/08/2017.
//
//  This holds the various stats and skills that make up a die roll, and then performs the rolling.


// This governs the state the object is in. We are either collecting results for the roll or we have made the roll and are returning the results.
class State 
{    
    constructor() {
        this.rolled = false;
        this._resultForDisplay = undefined;
        this._resultForLog = undefined;
    }

    roll(resultForDisplay, resultForLog) 
    {
        this.rolled = true;
        this._resultForDisplay = resultForDisplay;
        this._resultForLog = resultForLog;
    }

    // The result as an HTML format string for display to the user
    get resultForDisplay()
    {
        if (!this.rolled) {
            throw new State.StateException("You cannot access resultForDisplay until you have rolled the dice.");
        }
        return this._resultForDisplay;
    }

    // The result as a simple string for displaying in the log.
    get resultForLog()
    {
        if (!this.rolled) {
            throw new State.StateException("You cannot access resultForLog until you have rolled the dice.");
        }
        return this._resultForLog;
    }
}

// Exception thrown when we access data in the wrong state.
State.StateException = class StateException 
{
    constructor(message) {
        this.message = message;
        this.name = "StateException";
    }
};


/// This object handles one roll of the dice, including both the stats and skills to roll and the final result.
///
/// It contains input properties to specify which stats and skills to include.
/// Once you call the roll() method, it also contains output properties indicating the result of the roll.
/// Primarily you use the *total* field to get the final value and the *resultAsHTML* field to get the final result
/// formatted to display to the user.

class DieRoll {
    // MARK: Input Properties

    //typealias StatInfo = { name: String, value: Int16 }

    /// The stat whose value (if set) will be added to the die roll.
    get stat() {
        return this._stat;
    }
    set stat({ name, value }) {
        console.assert(name, "Stat: name must have a value");
        console.assert(Number.isInteger(value), "Value " + value + " is not an integer.");
        this._stat = { name, value };
        this.resetState();
    }

    /// Set of skills whose values will be rolled and included in this die roll.
    get skills() {
        return this._skills;
    }

    /// Add a skill to the list.
    addSkill(name, value)
    {
        console.assert(name !== "", "All skills must have a name.");
        console.assert(Number.isInteger(value), "Skill value " + value + "is not an integer.");
        this._skills.push( { name, value } );
        this.resetState();
    }

    /// A collection of specialties to be included when making the die roll.
    ///
    /// This is a dictionary whose key is the name of the skill and whose value is the specialty selected. If there is no value for a given key, it means that skill had no specialty or the specialty wasn't relevant.
    get specialties() {
        return this._specialties;
    }

    addSpecialty(skillName, specialtyName, specialtyValue)
    {
        console.assert(this.skills.filter((s) => s.name === skillName).length > 0, "addSpecialty: The skill " + skillName + " has not been added yet.");
        console.assert(skillName, "addSpecialty: skillName is not a valid string");
        console.assert(specialtyName, "addSpecialty: specialtyName is not a valid string");
        console.assert(Number.isInteger(specialtyValue), "addSpecialty: specialtyValue " + specialtyValue + " is not an integer");
        let specObj = { name: specialtyName, value: specialtyValue };
        if (!this._specialties[skillName]) {
            this._specialties[skillName] = [specObj];
        } else {
            this._specialties[skillName].push(specObj);
        }
        this.resetState();
    }

    /// A total value to be added to the final die roll.
    get adds() {
        return this._adds;
    }
    set adds(a) {
        console.assert(Number.isInteger(a), "Adds value " + a + " is not an integer.");
        this._adds = a;
        this.resetState();
    }

    /// Any extra D4s to roll and add to the total.
    ///
    /// The default is usually 1d4 per roll, except for magic skills. However this can vary and some GMs will add extra d4s as a circumstance bonus so the user can specify it here.
    get extraD4s() {
        return this._extraD4s;
    }
    set extraD4s(e) {
        console.assert(Number.isInteger(e), "ExtraD4s value " + e + " is not an integer.");
        this._extraD4s = e;
        this.resetState();
    }

    // This is called when any data has been changed to reset the state of the object to 'preparing'
    resetState() {
        this._state = new State();
    }

    get resultAsHTML() {
        return this._state.resultForDisplay;
    }

    // MARK: Public API

    /// Create a new LogEntry object containing the stats and skills rolled on and the result of the roll.  The log entry is added to Core Data automatically.
    /*
    addLogEntry() {
        /// Returns a summary of a die roll suitable for adding to the logs.
        ///
        /// - returns: A text string in the format: "stat + skill1/skill2..." It will fit on one line.
        function getSummary() {
            let statStr = "<No stat>";

            if (this.stat.name) {
                var prefix = this.stat.name.substring(0, 3);
                if (prefix === "Luc") {
                    prefix = "Lck";
                }
                statStr = prefix;
            }
            let skillNames = this.skills.array.map(s => s.name);
            let skillStr = skillNames.join("/");
            return `${statStr} + ${skillStr}`;
        };

        switch state {
            case .rolled(let (_, resultForLog)):
                let entry = charSheet.addLogEntry()
                entry.summary = getSummary()
                entry.change = resultForLog
                return entry;
            case .preparing:
                assert(false, "Invalid state: Can't add log entry until the dice have been rolled.")
        }
    }
*/

    /// Roll the D6s and D4s requested and store the results.
    roll() {
        // D6 rolls
        let d6Rolls = DieRoll.rollD6(true);
        // D4 rolls.
        let dieRollsPerSkill = {};
        this.skills.forEach(skill => {
            // Zero-value skills are allowed. They roll no dice but avoid failure penalties.
            if (skill.value === 0) {
                dieRollsPerSkill[skill.name] = [];
            } else {
                let sks = [];
                for (let i = 0; i < skill.value; i++) {
                    sks.push(DieRoll.rollDie(4));
                }
                dieRollsPerSkill[skill.name] = sks;
            }
        });
        let extraD4Rolls = [];
        for (let i = 0; i < this.extraD4s; i++) {
            extraD4Rolls.push(DieRoll.rollDie(4));
        }

        // Totals and display values.
        let total = this._calculateTotal(d6Rolls, dieRollsPerSkill, extraD4Rolls);
        let htmlResult = this._showResultAsHTML(total, d6Rolls, dieRollsPerSkill, extraD4Rolls);
        let logResult = this._getLogDetail(total, d6Rolls, dieRollsPerSkill, extraD4Rolls);

        this._state.roll(htmlResult, logResult);
    }



    // Private API

    /// The total value of all the rolled dice, stats and static adds used for this roll.
    _calculateTotal(d6Rolls, dieRollsPerSkill, extraD4Rolls) 
    {
        if (DieRoll.isBotch(d6Rolls)) {
            return 0;
        }
        let total = d6Rolls.reduce((x, y) => x + y);

        total += this.stat ? this.stat.value : 0;

        for (let skill of this.skills) {
            console.assert(skill.name, `Skill ${skill} has no name!`);
            if (skill.name) {
                let rolls = dieRollsPerSkill[skill.name];
                if (rolls) {
                    total += rolls.reduce((x, y) => x + y, 0);
                }
                let specs = this.specialties[skill.name] || [];
                total += specs.reduce((a, s) => a + s.value, 0);
            }
        }
        total += this.adds;
        total += extraD4Rolls.reduce((x, y) => x + y, 0);
        return total;
    }




    /// Returns an HTML document with the contents of the last die roll in a readable format.
    ///
    /// This doesn't trigger a die roll, just provides a detailed description of the last roll made.
    _showResultAsHTML(total, d6Rolls, dieRollsPerSkill, extraD4Rolls) 
    {
        let log = `<div class='result'><b>D6 Roll:</b>\n<br/>\n`;

        // For the D6 rolls, first check for a botch and return immediately if so.
        if (DieRoll.isBotch(d6Rolls)) {
            log += `<div>${d6Rolls[0]} + ${d6Rolls[1]} (<b class='botch'>Botch!</b>)</div></div>`;
            return log;
        }

        // Format the D6 rolls.
        console.assert(d6Rolls.length % 2 === 0, "Uneven number of d6 rolls");
        let d6Results = DieRoll.group(2, d6Rolls).map(roll => {
            let doubleStr = (roll[0] === roll[1] ? " (Double!)" : "");
            return `${roll[0]} + ${roll[1]}${doubleStr}`;
        });
        log += "<div>";
        log += d6Results.join(", ");
        let d6Total = d6Rolls.reduce((x, y) => x + y);
        log += `&nbsp;&nbsp;= ${d6Total}</div>\n`;

        // Add the stat if necessary.
        if (this.stat) {
            log += `<b>Stats:</b><br/><div>${this.stat.name} = ${this.stat.value}</div>\n`;
        }

        // Now add the skill rolls.
        if (this.skills.length > 0) {
            log += "<b>Skills:</b><br/>\n<div>";
            let skillLines = this.skills.map(skill => {
                console.assert(skill.name, `Skill ${skill} has no name`);

                let rollsForSkill = [];
                if (dieRollsPerSkill[skill.name]) {
                    rollsForSkill = dieRollsPerSkill[skill.name];
                }

                let specStr = "", finalTotal = 0, specs = this.specialties[skill.name];
                if (specs) {
                    specStr = specs.map(s => `<br/><span>(+ ${s.name} = ${s.value})</span>`)
                    .join("");
                }
                finalTotal += rollsForSkill.reduce((x, y) => x + y);
                let rollsText = rollsForSkill.map(e => e.toString()).join(" + ");
                let safeName = DieRoll.sanitiseHTML(skill.name);
                if (rollsForSkill.isEmpty) { 
                    // Zero-level skills. Don't show the rolls as there aren't any.
                    return `${safeName} (${rollsForSkill.length}) = ${finalTotal} ${specStr}`;
                } else {
                    return `${safeName} (${rollsForSkill.length}) = ${rollsText} = ${finalTotal} ${specStr}`;
                }
            });
            log += skillLines.join("<br/>\n");
            log += "</div>\n";
        }

        // Add extra d4s.
        if (this.extraD4s !== 0) {
            let extraD4Text = extraD4Rolls.map(r => r.toString()).join(" + ");
            let extraD4Value = extraD4Rolls.reduce((x, y) => x + y, 0);
            log += `<b>Extra D4s:</b><br/>\n<div>${extraD4Text} = ${extraD4Value}</div>\n`;
        }

        // Add any final adds.
        if (this.adds !== 0) {
            log += `<b>Adds:</b><br/>\n<div>${this.adds}</div><br/>\n`;
        }
        log += `<br/><hr/>\n<b>Total = ${total}</b></div>`;
        return log;
    }





    /// Returns the detail text of a die roll in a short format for adding to the logs.
    ///
    /// - returns: A multi-line text string with the full detail of the roll.
    _getLogDetail(total, d6Rolls, dieRollsPerSkill, extraD4Rolls) 
    {
        function summarise(skill, specialties) 
        {
            let d4Rolls = [];
            if (dieRollsPerSkill[skill.name]) {
                d4Rolls = dieRollsPerSkill[skill.name];
            }
            let d4rollStr = d4Rolls.map(s => s.toString()).join(" + ");
            let specValue = 0;
            if (specialties[skill.name]) {
                specValue = specialties[skill.name]
                    .reduce((a,s) => a + s.value, 0);
            }
            return `${skill.name}: ${d4rollStr} +${specValue}`;
        }

        let statText = "Stat: None";
        if (this.stat) {
            statText = `Stat: ${this.stat.name} = ${this.stat.value}`;
        }

        let d6s = d6Rolls.map(r => r.toString()).join(" + ");
        let d6str = `D6 Rolls: ${d6s}`;
        let skillResults = this.skills.map(x => summarise(x, this.specialties)).join("\n");

        let extraD4Text = extraD4Rolls.map(r => r.toString()).join(" + ");

        return `${d6str}\n${statText}\n${skillResults}\nExtraD4s: ${extraD4Text}\nAdds: ${this.adds}\nTotal: ${total}`;
    }




    // MARK: - Housekeeping

    constructor() 
    {
        this._specialties = {};
        this._adds = 0;
        this._skills = [];
        this._extraD4s = 1;
        this.resetState();
    }
}


// Here are global functions which don't have a 'this' parameter.
// I will attach them to the DieRoll object to avoid namespace pollution.


/// Given an array, split it into groups and return an array of all the groups.
///
/// - parameter groupSize: The size of each group. Must be greater than zero or we return an empty array.
/// - parameter collection: The collection to split into groups.
///
/// If the number of items in the collection isn't an exact multiple of `groupSize` then the remaining items are simply ignored.  For example:
///   group(2, [1, 2, 3, 4]) would return [[1, 2], [3, 4]]
///   group(2, [1, 2, 3]) would return [[1, 2]] and 3 would be missing.
///
/// - todo: Switch to using sequences instead of fixed arrays. Currently it requires the size of the array, so it cannot be lazy.
DieRoll.group = function group(groupSize, collection) {
    "use strict";
    console.assert(groupSize > 0, 
                   `groupSize(${groupSize}) must be greater than zero`);
    if (groupSize <= 0) {
        return [];
    }

    let result = [];
    //for (let i in stride(0, count, groupSize)) {
    for (let i = 0, ic = collection.length; i < ic; i += groupSize) {
        // If we have enough entries to make a full group, then copy the values out of the slice and into a home of their own.
        if ((i + (groupSize - 1)) < ic) {
            let arr = [];
            //for (let t in collection[i...i + (groupSize - 1)]) {
            for (let t = i, tc = i + groupSize; t < tc; t++) {
                arr.push(collection[t]);
            }
            result.push(arr);
        }
    }
    return result;
};

/// Returns true if the first two die rolls in the array provided indicate a botch.
///
/// - parameter d6Rolls: An array holding at least 2 integers, each being the result of a d6 roll.
DieRoll.isBotch = function isBotch(d6Rolls) 
{
    "use strict";
    console.assert(d6Rolls.length >= 2, "Not enough d6");
    return (d6Rolls[0] + d6Rolls[1]) === 3;
};

/// Rolls a die with numSides sides and returns the result.
DieRoll.rollDie = function rollDie(numSides) 
{
    "use strict";
    //Snipped from Javascript docs: The maximum is inclusive and the minimum is inclusive 
    let min = Math.ceil(1);
    let max = Math.floor(numSides);
    return Math.floor(Math.random() * (max - min + 1)) + min;
};

/// Simulate rolling 2D6, optionally rerolling on a double.
/// Rolling a botch (2+1 or 1+2) will abort immediately.
///
/// - parameter doublesReroll: If true and the dice both have the same value, then roll the dice again and include the result. This can happen repeatedly.
/// - returns: An array holding all the die rolls that were made.
DieRoll.rollD6 = function rollD6(doublesReroll) {
    "use strict";
    let results = [], firstRoll = true, n1 = 0, n2 = 0;
    do {
        [n1, n2] = [DieRoll.rollDie(6), DieRoll.rollDie(6)];
        results.push(n1);
        results.push(n2);

        // If the first roll was a botch, then return immediately.
        if (firstRoll && DieRoll.isBotch(results)) {
            return results;
        }
        firstRoll = false;
    } while (doublesReroll && (n1 === n2));
    return results;
};

/// Converts characters in the input which would interferere with HTML formatting into the equivalent escape sequences.
///
/// - parameter   input: A string to convert. This must not include any HTML markup as it will be replaced with &lt; &gt; etc.
/// - returns: A string suitable to be embedded in an HTML document.
DieRoll.sanitiseHTML = function sanitiseHTML(input) {
    "use strict";
    let output = "";
    for (let c of input) {
        switch (c) {
            case "<":
                output += "&lt;";
                break;
            case ">":
                output += "&gt;";
                break;
            case "&":
                output += "&amp;";
                break;
            case "\n":
                output += "<br/>";
                break;
            default:
                output += c;
                break;
                 }
    }
    return output;
};
