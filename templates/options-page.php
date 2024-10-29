<?php
// Options Page
?>
<div class="wrap">
    <h1><?php _e( 'Adenergizer Settings', 'adenergizer' ); ?></h1>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'adenergizer_settings' );
        do_settings_sections( 'adenergizer_settings' );
        submit_button();
        ?>
    </form>
</div>
