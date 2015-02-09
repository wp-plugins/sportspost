<?php

/**
 * The settins page for the plugin
 *
 * @since      1.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin/partials
 */
?>

<div class="wrap">
    <h2><?php _e( 'SportsPost Settings', 'sportspost' ); ?></h2>
    <form action="<?php echo admin_url( 'options.php' ) ?>" method="post">
		<?php settings_fields( 'sportspost_settings_group' ); ?>
        <?php do_settings_sections( 'sportspost_settings_page' ); ?>
        <p class="submit">
			<?php submit_button( __( 'Save Changes', 'sportspost'), 'primary', 'sportspost_settings[submit]', false ); ?>
            <?php submit_button( __( 'Reset settings to default values', 'sportspost'), 'secondary', 'sportspost_settings[reset]', false ); ?>
        </p>
    </form>
    <p><?php _e( 'Log in at <a href="http://sportsforecaster.com/affiliates" target="_blank">SportsForecaster/affiliates</a> to manage your affiliate account.', 'sportspost' ); ?></p>
</div>
