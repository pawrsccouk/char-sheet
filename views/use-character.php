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
        <div class="col-sm-8 use-char-value">
            <?php echo $_GET['name'] ?>
        </div>
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
<div class="modal fade" id="die-roll-modal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Dialog header and title bar -->

            <div class="modal-header">
                <h5 class="modal-title">Dice Roll</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Main dialog content -->

            <div class="modal-body">

                <!-- The dialog has two sets of collapsible panels, one for the stat selection and one for the result -->

                <div class="accordion" role="tablist" aria-multiselectable="false">

                    <!-- The first section -->
                    <div class="card">
                        <!-- This header has the link to open/close the card -->
                        <div class="card-header" role="tab" id="heading-choose">
                            <h5>
                                <a data-toggle="collapse" 
                                   data-parent="#accordion" 
                                   href="#collapse-choose" 
                                   aria-expanded="true" 
                                   aria-controls="collapse-choose">
                                    Choose what to roll
                                </a>
                            </h5>
                        </div>

                        <!-- This is the body text i.e. the card that holds the first pane content -->
                        <div id="collapse-choose" 
                             class="collapse" 
                             role="tabpanel" 
                             aria-labelledby="heading-choose">
                            <div class="card-block">
                                <!-- The actual content -->
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
                                            <td><input id="extra-d4s" type="number" value="1" min="0">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <table id="die-roll-skills">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <th class="roll-header">Skills</th>
                                            <th>&nbsp;</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                <select id="roll-add-skill">
                                    <option data-dummy='true'>Add Skill&hellip;</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- The second block in the accordian -->
                    <!-- The header used to open/close the block -->
                    <div class="card">
                        <div class="card-header" role="tab" id="heading-results">
                            <h5>
                                <a data-toggle="collapse" 
                                   data-parent="#accordion" 
                                   href="#collapse-results" 
                                   aria-expanded="true" 
                                   aria-controls="collapse-results">
                                    Results
                                </a>
                            </h5>
                        </div>

                        <!-- The block with the second section content -->
                        <div id="collapse-results" 
                           class="collapse" 
                           role="tabpanel" 
                           aria-labelledby="heading-results">
                            <div id="roll-results" class="card-block"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- The footer contains our action buttons -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" 
                        class="btn btn-primary"
                        id='die-roll-roll-dice'>Roll</button>
            </div>
        </div>
    </div>
</div>
