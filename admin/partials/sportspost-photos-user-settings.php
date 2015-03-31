<?php
/**
 * The template for the Icon Sportswire user preferences dialog.
 *
 * @since   2.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin/partials
 */
?>
	<script type="text/html" id="tmpl-sportspost-photos-user-settings">
		<form id="sportspos-user-settings-form">
			<label class="setting default_client">
				<span><?php _e( 'Default client', 'sportspost'); ?></span>
				<select id="iconsportswire_default_client" name="iconsportswire_default_client">
				<# _.each( data.clients, function( client ) { #>
					<option value="{{ client.client_id }}" <# if( data.settings.iconsportswire_default_client == client.client_id ) { #>selected="selected"<# } #>>{{ client.client_name }}</option>
				<# }); #>
				</select>
			</label>
			<label class="setting default_league">
				<span><?php _e( 'Default league', 'sportspost'); ?></span>
				<select id="iconsportswire_default_league" name="iconsportswire_default_league">
				<# _.each( data.leagues, function( label, league) { #>
					<option value="{{ league }}" <# if( data.settings.iconsportswire_default_league == league ) { #>selected="selected"<# } #>>{{ label }}</option>
				<# }); #>
				</select>
			</label>
			<label class="setting default_panel">
				<span><?php _e( 'Default panel', 'sportspost'); ?></span>
				<select id="iconsportswire_default_panel" name="iconsportswire_default_panel">
					<option value="search" <# if( data.settings.iconsportswire_default_panel == 'search' ) { #>selected="selected"<# } #>><?php _e( 'Search', 'sportspost'); ?></option>
					<option value="browse" <# if( data.settings.iconsportswire_default_panel == 'browse' ) { #>selected="selected"<# } #>><?php _e( 'Browse', 'sportspost'); ?></option>
				</select>
			</label>
			<?php /*
			<label class="setting quick_download">
				<span><?php _e( 'Quick download', 'sportspost'); ?></span>
				<input type="checkbox" id="iconsportswire_quick_download" name="iconsportswire_quick_download" value="1" <# if( data.settings.iconsportswire_quick_download == '1' ) { #>checked="checked"<# } #> /> <label for="iconsportswire_quick_download"><?php _e( 'Avoid confirmation for photo downloads', 'sportspost'); ?></label>
			</label>
			*/ ?>
		</form>
	</script>
