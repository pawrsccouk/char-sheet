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

        <table id='edit-char-skills'>
            <tbody>
                <?php get_character_skills('edit'); ?>
            </tbody>
        </table>
        <!-- The button to add new table rows. -->
        <button id='char-skill-add' 
                class='btn btn-secondary'
                type='button'>
            +
        </button>

        <div class="section-space">&nbsp;</div>

        <div id="char-error-div" style="display:none"></div>

        <button type="submit" class="btn btn-primary big-button col-md-3">Submit</button>
    </form>
</div>




<!-- a modal dialog initially hidden and used to handle editing specialties -->
<div class="modal fade" id="char-edit-specialties-modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <!-- Header has a title and a close button -->
            <div class="modal-header">
                <h5 id='char-edit-specialties-title' class="modal-title">Specialties</h5>
                <button type="button" 
                        class="close" 
                        data-dismiss="modal" 
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <table>
                    <tbody>
                    </tbody>
                </table>
                <button type="button"
                        class="btn btn-primary"
                        id="edit-char-specialty-add">
                    +
                </button>
            </div>

            <!-- Footer has a close button and a cancel button -->
            <div class="modal-footer">
                <button type="button" 
                        class="btn btn-primary"
                        id="edit-char-specialties-save">
                    Save
                </button>
                <button type="button" 
                        class="btn btn-secondary" 
                        data-dismiss="modal">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>


<script type='text/javascript'>
    let specialties = <?php echo get_all_specialties('edit') ?>
</script>
