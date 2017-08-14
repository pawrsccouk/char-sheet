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
               <!-- Headings -->
        <div class='row use-attribute-row'>
            <div class='col-sm-3 use-char-label'>Skill</div>
            <div class='col-sm-1 use-char-label'>Level</div>
            <div class='col-sm-1 use-char-label'>Ticks</div>
            <div class='col-sm-7 use-char-label'>Specialties</div>
        </div>

        <?php get_character_skills_use(); ?>
    </div>

    <div class="section-space"></div>
    
    <div class="use-attributes-block">
        <div class="row use-attribute-row">
            <div class='col-sm-12 use-char-label'>Personal Details</div>
        </div>
        <?php get_character_attributes_use(); ?>
    </div>
</div>