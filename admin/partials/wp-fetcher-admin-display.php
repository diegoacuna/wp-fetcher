<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://github.com/diegoacuna/
 * @since      1.0.0
 *
 * @package    Wp_Fetcher
 * @subpackage Wp_Fetcher/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">
        <h2>WP Fetcher Options</h2>
        <form action="options.php" method="POST">
            <?php settings_fields( 'wp-fetcher-general-options' ); ?>
            <?php do_settings_sections( $this->plugin_name ); ?>
            <!-- Test button of url -->
            <p>
              If you want to test if the given url can be correctly parsed by the
              plugin, press the "Test URL!" button. Is everything is correct you
              should see an alert window with a success message and reporting the
              format of the parsed data (XML or JSON).
            </p>
            <button class="button button-primary test-url">Test URL!</button>
            <p>
              To save changes press the button below. Make sure your url is valid!
            </p>
            <?php submit_button(); ?>
        </form>
    </div>