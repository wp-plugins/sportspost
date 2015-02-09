jQuery( document ).ready( function( $ ) {
	
	function sportspost_generate_sample_url() {
		var permalink = $( '#link_prefix' ).val();
		permalink +=  $( '#league_name_' + $( '#default_sports_league' ).val() ).val();
		permalink += $( '#id_prefix' ).val();
		permalink += '42';
		permalink += $( '#id_suffix' ).val();
		if ( $( '#affiliate_reference_id').val() ) {
			permalink += permalink.indexOf( '?' ) > -1 ? '&' : '?';
			permalink += 'aff=' + $( '#affiliate_reference_id').val();
		}
		$( '#sample_url' ).val( permalink );
	}
	
	$( '[name^=sportspost_settings]' ).on( 'change keyup', function() {
		sportspost_generate_sample_url();
	});
	
	sportspost_generate_sample_url();
	
});
