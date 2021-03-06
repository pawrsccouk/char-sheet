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
            <center>The Role-Playing Game &mdash; Character Notes</center>
        </div>
    </div>

    <!-- Check the character exists and fail with an error if not. -->
    <?php echo assert_character_exists() ?>

    <!-- Top-row attributes, just name and AKA -->
    <div class="section-space"></div>

    <!-- This block holds the error info and is invisible by default. -->
    <div id="notes-error-block" class="alert alert-danger hidden"></div>

    <!-- This is the form with the text box on it. -->
    <div class="row">
        <form id="notes-form" class="col-md-12">
           <?php
                echo "<textarea id='notes-textarea' data-charId='${_GET['charid']}'>" . get_notes() . "</textarea>";
            ?>
        </form>
    </div>

</div>