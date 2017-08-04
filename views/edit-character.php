<div class="container">
    <div class="row">
        <?php
        if ($_GET['charid'] == 0) {
            echo "<h1 class='page-top'>New character</h1>";
        } else {
            echo "<h1 class='page-top'>Edit {$_GET['name']}</h1>";
        }
        ?>
    </div>

    <form id="char-form">
        <?php get_character_attributes('edit'); ?>

        <div class="section-space">&nbsp;</div>

        <h2 class='edit-char-header'>Stats</h2>
        <hr class='edit-char-hr'>

        <?php get_character_stats('edit'); ?>

        <div class="section-space">&nbsp;</div>

        <h2 class='edit-char-header'>Skills</h2>
        <hr class='edit-char-hr'>
        
        <?php get_character_skills('edit'); ?>
        
        <div class="section-space">&nbsp;</div>

        <div id="char-error-div" style="display:none"></div>

        <button type="submit" class="btn btn-primary big-button col-md-3">Submit</button>
    </form>
</div>
