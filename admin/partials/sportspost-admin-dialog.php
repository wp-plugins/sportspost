<?php
/**
 * The insert/edit player link dialog
 *
 * @since   1.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin/partials
 */
?>
		<div id="sportspost-player-link-backdrop" style="display: none"></div>
		<div id="sportspost-player-link-wrap" class="wp-core-ui<?php echo $search_panel_visible; ?>" style="display: none">
		<form id="sportspost-player-link" tabindex="-1">
		<?php wp_nonce_field( 'sportpost-player-linking', '_ajax_sportspost_player_link_nonce', false ); ?>
		<div id="link-modal-title">
        	<img src="<?php echo plugins_url( 'admin/img/icon-player.png', dirname( dirname( __FILE__ ) ) );?>" id="player-icon-title" />
			<?php _e( 'Insert/edit player link', 'sportspost' ) ?>
			<button type="button" id="sportspost-player-link-close"><span class="screen-reader-text"><?php _e( 'Close' ); ?></span></button>
	 	</div>
		<div id="player-link-selector">
			<div id="player-link-options">
				<div>
					<label><span><?php _e( 'URL' ); ?></span><input id="player-url-field" type="text" name="href" readonly="readonly" /></label>
                    <a id="player-link-preview" href="#" class="dashicons dashicons-visibility" title="<?php _e( 'Preview link', 'sportspost' ) ?>" target="_blank"></a>
				</div>
				<div>
					<label><span><?php _e( 'Title' ); ?></span><input id="player-link-title-field" type="text" name="linktitle" /></label>
				</div>
				<div class="link-target">
					<label><span>&nbsp;</span><input type="checkbox" id="player-link-target-checkbox" <?php checked( $this->settings['target_blank'], 1) ?> /> <?php _e( 'Open link in a new window/tab' ); ?></label>
				</div>
			</div>
			<div id="player-search-panel">
				<div class="link-search-wrapper">
					<label>
						<span class="search-label"><?php _e( 'Search', 'sportspost' ); ?></span>
						<input type="search" id="player-search-field" class="link-search-field" autocomplete="off" />
					</label>
					<label>
                        <span class="search-label"><?php _e( 'League', 'sportspost' ); ?></span>
						<?php $this->display_league_dropdown( 'league', 'player-league', $this->settings['default_sports_league'] ); ?>
					</label>
					<span class="spinner"></span>
				</div>
				<div id="player-search-results" class="query-results" tabindex="0">
					<ul></ul>
					<div class="river-waiting">
						<span class="spinner"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="submitbox">
			<div id="sportspost-player-link-cancel">
				<a class="submitdelete deletion" href="#"><?php _e( 'Cancel' ); ?></a>
			</div>
			<div id="sportspost-player-link-update">
				<input type="submit" value="<?php esc_attr_e( 'Add Player Link', 'sportspost' ); ?>" class="button button-primary" id="sportspost-player-link-submit" name="wp-player-link-submit">
			</div>
		</div>
		</form>
		</div>
