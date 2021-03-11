<?php settings_errors(); ?>

<h1>Configuración de recordatorios de pedidos</h1>

<form method="post" action="options.php">

    <?php settings_fields('rtoc_settings_group'); ?>
    <?php do_settings_sections('rtoc-settings'); ?>
    <?php submit_button(); ?>

</form>