/* global sportspost_pointers, sportsPostPlayerLinkL10n, ajaxurl */

jQuery( document ).ready( function( $ ) {
	
	var sportspost_wizard_completed = false;
	
	// Helper function to activate pointers
	function sportspost_open_pointer( pointer_id, close_text, close_callback ) {
		if ( typeof sportspost_pointers[ pointer_id ] == 'undefined' || sportspost_wizard_completed ) {
			return;
		}
		var pointer = sportspost_pointers[ pointer_id ],
			options = $.extend( pointer.options, {
				close: function() {
					$.post( ajaxurl, {
						pointer: pointer_id,
						action: 'dismiss-wp-pointer'
					});
					if ( typeof close_callback == 'function' ) {
						close_callback();
					}
				},
				buttons: function( event, t ) {
					var button = $('<a class="button button-primary" href="#">' + close_text + '</a>');
		
					return button.bind( 'click.pointer', function(e) {
						e.preventDefault();
						t.element.pointer('close');
					});
				},
			});
		$( pointer.target ).pointer( options ).pointer( 'open' );
	}
	
	
	// Wizard with admin pointer for Editor screen
	$( document ).on( 'content_editor_ready',  function() {
		
		// Step 1: Pointer for setting Affiliate ID
		sportspost_open_pointer( 'sportspost-settings', sportsPostPlayerLinkL10n.next, function() {

			// Step 2: Pointer for button in TinyMCE editor toolbar
			sportspost_open_pointer( 'sportspost-playerlink-button', sportsPostPlayerLinkL10n.next, function() {
				$( sportspost_pointers['sportspost-playerlink-button']['target'] ).click();
			});
			
		});
			
	});
	
	// Wizard with admin pointer for Player Link dialog
	$( document ).on( 'playerlink-open', function() {
		
		// Close button pointer if open
		if ( $( sportspost_pointers['sportspost-playerlink-button']['target'] ).pointer() ) {
			$( sportspost_pointers['sportspost-playerlink-button']['target'] ).pointer( 'close' );
		}
		
		// Step 1: League
		sportspost_open_pointer( 'sportspost-player-league', sportsPostPlayerLinkL10n.next, function() {
			
			// Step 2: Player name	
			sportspost_open_pointer( 'sportspost-player-search', sportsPostPlayerLinkL10n.next, function() {
				if ( $( '#player-search-field' ).val().length < 3 ) {
					$( '#player-search-field' ).val( 'Alan' );
					$( '#player-search-field' ).keyup();
				}
				
				// Step 3: Player search result
				sportspost_open_pointer( 'sportspost-player-search-results', sportsPostPlayerLinkL10n.next, function() {
					
					// Step 4: Preview Link
					sportspost_open_pointer( 'sportspost-player-link-preview', sportsPostPlayerLinkL10n.next, function() {
						
						// Step 5: Add link and close dialog
						sportspost_open_pointer( 'sportspost-player-link-submit', sportsPostPlayerLinkL10n.close, function() {
							sportspost_wizard_completed = true;
						});
					});
				});
			});
		});
	});

	// Close pointers if closing dialog
	$( document ).on( 'playerlink-close', function() {
		$( '#player-league,#player-search-field,#player-search-results,#player-link-preview,#sportspost-player-link-submit' ).each( function() {
			if ( $( this ).pointer() ) {
				$( this ).pointer( 'close' );
			}
		});
	});
	
});
