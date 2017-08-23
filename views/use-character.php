<div class="container">

    <div class="row" id="logo-row">
        <div class="col-md-12">
            <center>
                <img src="views/vizcraft-logo.svg" id="vizcraft-logo" alt="Vizcraft Game Logo">
            </center>
        </div>
    </div>
    <div class="row" id="logo-text-row">
        <div class="col-md-12">
            <center>The Role-Playing Game &mdash; Character Sheet</center>
        </div>
    </div>

    <!-- Top-row attributes, just name and AKA -->
    <div class="row use-attribute-row">
        <div class="col-sm-4 use-char-label">Character Name</div>
        <div class="col-sm-8 use-char-value"><?php echo $_GET['name'] ?></div>
    </div>
    <div class="row use-attribute-row">
        <div class="col-sm-4 use-char-label">A.K.A.</div>
        <div class="col-sm-8 use-char-value"></div>
    </div>

    <div class="section-space"></div>

    <div class='use-stats-block'>
        <!-- Headings -->
        <div class='row use-attribute-row'>
            <div class='col-sm-2'>&nbsp;</div>
            <div class='col-sm-1 use-char-label'>Max</div>
            <div class='col-sm-2 use-char-label'>Current</div>
            <div class='col-sm-2'>&nbsp;</div>
            <div class='col-sm-2'>&nbsp;</div>
            <div class='col-sm-1 use-char-label'>Max</div>
            <div class='col-sm-2 use-char-label'>Current</div>
        </div>
        <!-- Skill values -->
        <?php get_character_stats_use(); ?>
    </div>

    <div class="section-space"></div>

    <div class='use-skills-block'>
        <div class='row'>
            <!-- Headings -->
            <table id='use-skill-table'>
                <thead>
                    <tr class='use-attribute-row'>
                        <th class='use-char-label use-skill-name'>Skill</th>
                        <th class='use-char-label use-skill-value'>Level</th>
                        <th class='use-char-label use-skill-ticks'>Ticks</th>
                        <th class='use-char-label use-skill-specialties'>Specialties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php get_character_skills_use(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-space"></div>

    <div class="use-attributes-block">
        <div class="row use-attribute-row">
            <div class='col-sm-12 use-char-label'>Personal Details</div>
        </div>
        <?php get_character_attributes_use(); ?>
    </div>
</div>



<!-- A modal initially hidden and used to display die rolls -->
<div class="modal fade"
     id="die-roll-modal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dice Roll</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table id="die-roll-stats-misc">
                    <tbody>
                        <tr>
                            <td class="roll-label">Stat:</td>
                            <td>
                                <select id="stat-select">
                                    <option>None</option>
                                    <optgroup label="Physical">
                                        <option>Strength</option>
                                        <option>Dexterity</option>
                                        <option>Constitution</option>
                                        <option>Speed</option>
                                    </optgroup>
                                    <optgroup label="Mental">
                                        <option>Charisma</option>
                                        <option>Intelligence</option>
                                        <option>Luck</option>
                                        <option>Perception</option>
                                    </optgroup>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="roll-label">Static Adds:</td>
                            <td><input id="static-adds" type="number" value="0"></td>
                        </tr>
                        <tr>
                            <td class="roll-label">Extra D4s:</td>
                            <td><input id="extra-d4s" type="number" value="1" min="0"</td>
                        </tr>
                    </tbody>
                </table>

                <table id="die-roll-skills">
                    <thead>
                        <tr>
                            <th class="roll-header">Skills</th>
                            <th>&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <select id="roll-add-skill">
                    <option>Add Skill&hellip;</option>
                </select>

            </div>
            <div class="modal-footer">
                <button type="button" 
                        class="btn btn-secondary"
                        data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Roll</button>
            </div>
        </div>
    </div>
</div>
