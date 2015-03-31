(function ($) {	

	// for debug : trace every event
	/*	
	var originalTrigger = wp.media.view.MediaFrame.Post.prototype.trigger;
	wp.media.view.MediaFrame.Post.prototype.trigger = function(){
		console.log( 'Event Triggered:', argument s);
		originalTrigger.apply( this, Array.prototype.slice.call( arguments ) );
	}
	*/
	
	var enableImport = true;
	
	var SportsPostUserSettings = Backbone.Model.extend({
	});
	
	wp.media.view.SportsPostUserDialog = Backbone.View.extend({
		tagName: 'div',
		className: 'sportspost-user-settings',
		template: wp.media.template( 'sportspost-photos-user-settings' ),
		render: function( ) {
			var view = this;
			this.$el.html( this.template( this.model.toJSON() ) );
			$( this.$el ).dialog({
				dialogClass: 'wp-dialog sportspost-dialog sportspost-dialog-preferences',
				title: 'Icon Sportswire settings',
				closeOnEscape: true,
				autoOpen: true,
				width: 500,
				modal: true,
				buttons: {
					'Save': function() {
						var that = this;
						$( '.sportspost-dialog-connect input, .sportspost-dialog-preferences button' ).prop( 'readonly', true);
						$.post(
							ajaxurl, 
							{
								action: 'sportspost_user_preferences_save',
								nonce: sportspost_nonce.nonce,
								source: 'Icon_Sportswire', // Todo
								default_client: $( '#iconsportswire_default_client' ).val(),
								default_league: $( '#iconsportswire_default_league' ).val(),
								default_panel: $( '#iconsportswire_default_panel' ).val(),
								quick_download: $( '#iconsportswire_quick_download' ).is( ':checked' )? '1' : '0',
							}, 
							function( data ) {
								if( data.message == 'success' ) {
									view.trigger( 'change:connected', data );
									alert( 'Preferences saved successfully' );
								}
								else {
									alert( data.message );
									$( '.sportspost-dialog-preferences input, .sportspost-dialog-preferences button, .sportspost-dialog-preferences select' ).prop( 'readonly', false);
								}
							},
							'json'
						);
						$( this ).dialog( 'close' );
						$( this ).dialog( 'destroy' ).remove();
					},
					'Cancel': function() {
						$( this ).dialog( 'close' );
						$( this ).dialog( 'destroy' ).remove();
					}
				}
			});
			this.delegateEvents( this.events )
			return this;
		},
		initialize : function(){
			_.bindAll( this, 'render' )
			this.render().el;
		},
	});	
	
	var SportsPostUserAccount = Backbone.Model.extend({
		username: '',
		password: ''
	});

	wp.media.view.SportsPostConnectDialog = Backbone.View.extend({
		tagName: 'div',
		className: 'sportspost-user-connect',
		template: wp.media.template( 'sportspost-photos-user-connect' ),
		render: function( ) {
			var view = this;
			this.$el.html( this.template( this.model.toJSON() ) );
			this.$el.dialog({
				dialogClass: 'wp-dialog sportspost-dialog sportspost-dialog-connect',
				title: 'Icon Sportswire account',
				closeOnEscape: true,
				autoOpen: true,
				modal: true,
				buttons: {
					'Connect': function() {
						var that = this;
						$( '.sportspost-dialog-connect input, .sportspost-dialog-connect button' ).prop( 'readonly', true);
						$.post(
							ajaxurl, 
							{
								action: 'sportspost_connect',
								nonce: sportspost_nonce.nonce,
								source: 'Icon_Sportswire', // Todo
								username: $( '#iconsportswire_username' ).val(),
								password: $( '#iconsportswire_password' ).val(),
							}, 
							function( data ) {
								if( data.message == 'success' ) {
									view.trigger( 'change:connected', data );
									$( that ).dialog( 'close' );
									$( that ).dialog( 'destroy' ).remove()
								}
								else {
									alert( data.message );
									$( '.sportspost-dialog-connect input, .sportspost-dialog-connect button' ).prop( 'readonly', false);
								}
							},
							'json'
						);
					},
					'Cancel': function() {
						$( this ).dialog( 'close' );
						$( this ).dialog( 'destroy' ).remove()
					}
				}
			});
			this.delegateEvents( this.events )
			return this;
		},
		initialize : function(){
			_.bindAll( this, 'render' )
			this.render().el;
		},
	});	
	
	
	var SportsPostImage = Backbone.Model.extend({
	});

	var SportsPostImages = Backbone.Collection.extend({
		model: SportsPostImage
	});
	
	var SelectedImages = Backbone.Collection.extend({
		model: SportsPostImage
	});
	
	var SportsPostImageView = Backbone.View.extend({
		tagName: 'li',
		className: 'sportspost-image attachment',
		template: wp.media.template( 'sportspostimage' ),
		render: function () {
			this.$el.html( this.template( this.model.toJSON() ) );
			return this;
		}
	});
	
	var SportsPostImageSidebar = Backbone.View.extend({
		tagName: 'div',
		className: 'sportspost-sidebar media-sidebar'
	});
	
	var SportsPostImageSettings = Backbone.View.extend({
		tagName: 'div',
		className: 'sportspost-settings',
		template: wp.media.template( 'sportspostimage-settings' ), 

		events: {
			'click button':	'updateHandler',
			'change [data-setting]': 'updateSetting',
			'change [data-setting] input': 'updateSetting',
			'change [data-setting] select': 'updateSetting',
			'change [data-setting] textarea': 'updateSetting',
		},
		
		render: function () {
			this.$el.html( this.template( this.model.toJSON() ) );
			
			var imgdata = this.model.toJSON();
			
			var that = this;
			
			var img = new Image();
			$( img ).load( function() {
				$( this ).attr( 'id', 'img-sportspost' );
				$( this ).attr( 'draggable', 'false' );
				$( this ).css( 'display', 'none' );
				$( this ).hide();
				$( '#sportspostload' ).hide();
				$( '.thumbnail' ).empty();
				$( '.thumbnail' ).append( this );
				$( this ).fadeIn( function() { 
					$( '#sportspost-button' ).removeAttr( 'disabled' ); });
					newimage = document.getElementById( $( this ).attr( 'id' ) );
					if ( newimage != null ) {
						var height = document.getElementById( $( this ).attr( 'id' ) ).naturalHeight;
						var width = document.getElementById( $( this ).attr( 'id' ) ).naturalWidth;
						that.updateModel( 'height', height ); 
						that.updateModel( 'width', width ); 
					}
				}).error( function () {
			
			}).attr( 'src', imgdata.selected_image.dataset.full );
			
			return this;
		},
		updateHandler: function( event ) {
			var $setting = $( event.target ).closest( '[data-setting]' ),
				value = event.target.value,
				userSetting;
			event.preventDefault();
			if ( $setting.hasClass( 'button-group' ) ) {
				$buttons = $setting.find( 'button' ).removeClass( 'active' );
				$buttons.filter( '[value="' + value + '"]' ).addClass( 'active' );
			}
		},
		updateSetting: function( event ) {
			var $setting = $( event.target ).closest( '[data-setting]' ),
				setting, value;
					
			if ( ! $setting.length )
				return;

			setting = $setting.data( 'setting' );
			value = event.target.value;
	
			if ( event.target.type == 'checkbox' ) {
				if ( $( event.target ).is( ':checked' ) ) {
					value = 'on';
				} else value = 'off';
			}
			
			if ( event.target.name == 'sportspost-link' ) {
				var linkTo = event.target.value,
					$input = this.$( '.link-to-custom' );
				if ( 'none' === linkTo || 'post' === linkTo || 'file' === linkTo ) {
					$input.addClass( 'hidden' );
				}
				else if ( 'custom' === linkTo ) {
					$input.val( 'http://' );
					$input.removeClass( 'hidden' );
				}
			}
			
			this.updateModel( setting, value );
		},
		updateModel: function( setting, value ) {
			var selectedimages = this.model.get( 'custom_data' );
	
			if (selectedimages) {
				var selection = new SelectedImages( selectedimages.models );
				
				var sportspost = selection.get( $( '#sportspost-id' ).val() );
				selection.remove( $( '#sportspost-id' ).val() );
				if ( sportspost.get( 'setting-' + setting ) !== value ) {
					sportspost.set( 'setting-' + setting, value );
					selection.add( sportspost );	
					this.model.set( 'custom_data', selection );
					
				}
			}
			else var selection = new SelectedImages();
		},
	
	});
			
	wp.media.controller.SportsPostMedia = wp.media.controller.State.extend({
	
		initialize: function(){
			this.props = new Backbone.Model({
				custom_data: '',
				method: '',
				param: '',
				images: '',
				selected_id: '',
				selected_image: '',
				page: '',
				pagin: '',
				altpage: '',
				folder_path: '',
				connected: 'wait',
				account_type: '',
				subtitle: '',
				status: ''
			});
			this.props.bind( 'change:custom_data', this.refresh, this );
			this.props.bind( 'change:connected', this.refresh, this );
			this.props.bind( 'change:status', this.refresh, this );
		},
		
		refresh: function() {
			this.frame.toolbar.get().refresh();
		},
		
		importAction: function() {
			
			if ( ! enableImport ) return;
			
			if ( $( '#sportspost-import-button' ).is( '[disabled=disabled]' ) ) return;
			
			$( 'div.sportspost' ).addClass( 'sportspost-media-overlay' );	
			$( '#method' ).attr( 'disabled', 'disabled' );
			$( '#param' ).attr( 'disabled', 'disabled' );
			$( '#pagination' ).attr( 'disabled', 'disabled' );
			$( '#sportspost-import-button' ).attr( 'disabled', 'disabled' );
			$( '#sportspost-button' ).attr( 'disabled', 'disabled' );
			$( '#sportspost-import-button' ).text( 'Importing...' );
			
			var selectedimages = this.props.get( 'custom_data' );
			var selection = new SelectedImages( selectedimages.models );
			
			var that = this;
			var count = 0;

			var htmlstr = '';
			var jqHXRs = [];

			selection.each( function(model){
				count++;
				var modelAttr = model.attributes;
				modelAttr['setting-import'] = 'on';
				var fields = $.param( modelAttr );
				data = 'action=sportspost_pre_insert&nonce=' + sportspost_nonce.nonce + '&imgsrc=' + encodeURI(model.get( 'data-full' ) ) + '&postid=' + $( '#post_ID' ).val() + '&' + fields;
				var full = model.get( 'data-full' );
				var imgstr;
				jqHXRs.push(
					$.post( ajaxurl, data,
						function( data ){
							if(data.message == 'success' ) {
								full = data.imgsrc;
								that.props.set( 'status', data.status );
								that.trigger( 'change:connected' );
							}
						}
					, 'json' )
				);
			});
			
			$.when.apply(this, jqHXRs).done(function(){
				
				that.props.set( 'custom_data', '' );
				that.props.set( 'selected_id', '' );
				that.props.set( 'selected_image', '' );
				$( '#sportspost-import-button' ).text( count + ( ( count > 1 ) ? ' images' : ' image' ) + ' imported' );
				
				setTimeout(function() {
					 $( 'ul#sportspostimages li' ).removeClass( 'selected' ).removeClass( 'details' );
					 $( '.sportspost-sidebar' ).empty();
					 $( 'div.sportspost' ).removeClass( 'sportspost-media-overlay' );	
					 $( '#method' ).removeAttr( 'disabled' )
					 $( '#param' ).removeAttr( 'disabled' );
					 $( '#pagination' ).removeAttr( 'disabled' );
					 $( '#sportspost-import-button' ).text( wp.media.view.l10n.sportspostImportButton );
				}, 2000);

			});
		},
		
		insertAction: function(){
			if( $( '#sportspost-button' ).is( '[disabled=disabled]' ) ) return;
			$( '#sportspost-button' ).attr( 'disabled', 'disabled' );
			if( enableImport ) $( '#sportspost-import-button' ).attr( 'disabled', 'disabled' );
			$( '#sportspost-button' ).text( 'Inserting...' );
			var sportspostimage = this.props.get( 'custom_data' );
			var selectedimages = this.props.get( 'custom_data' );
			var selection = new SelectedImages( selectedimages.models );
			var selcount = selection.length;
			var that = this;
			var count = 0;
			var htmlstr = '';
			var jqHXRs = [];
			var errors = false;
			
			selection.each( function( model ){
				var imgwidth = model.get( 'setting-width' );
				var imgheight = model.get( 'setting-height' );
				if ( model.get( 'setting-width' ) == '0' ) {
					var img = new Image();
					$(img).load( function () {
						$( this ).attr( 'id', 'img-sportspost' );
						$( this ).attr( 'draggable', 'false' );
						$( this ).css( 'display', 'none' );
						$( this ).hide();
						newimage = document.getElementById( $( this ).attr( 'id' ) );
						if(newimage != null) {
							imgheight = document.getElementById( $( this ).attr( 'id' ) ).naturalHeight;
							imgwidth = document.getElementById( $( this ).attr( 'id' ) ).naturalWidth; 
						}
						}).error(function () {
					
					}).attr( 'src', model.get( 'data-full' ) );
				} 
								
				var modelAttr = model.attributes;
				var fields = $.param(modelAttr);
		
				data = 'action=sportspost_pre_insert&nonce=' + sportspost_nonce.nonce + '&imgsrc=' + encodeURI(model.get( 'data-full' ) ) + '&postid=' + $( '#post_ID' ).val() + '&' + fields;
				
				var full = model.get( 'data-full' );
				var imgstr;
				var linkto;
				
				jqHXRs.push(
					$.post(ajaxurl, data,
						function(data){
							if( data.error ) {
								alert( data.message );
								errors = true;
							}
							else if( data.message == 'success' ) {
								that.props.set( 'status', data.status );
								that.trigger( 'change:connected' );
								full = data.imgsrc,
								linkto = data.linkto;
								imgwidth = (data.imgwidth) ? data.imgwidth : imgwidth,
								imgheight = (data.imgheight) ? data.imgheight : imgheight;
								imgstr = '<img src="' + full + '" width="' + imgwidth + '" height="' + imgheight + '" alt="' + model.get( 'setting-alt' ) + '" title="' + model.get( 'setting-title' ) + '" class="align' + model.get( 'setting-align' ) + '" />';
							
								if ( linkto != '' ) 
									imgstr = '<a href="' + linkto + '">' + imgstr + '</a>';
								
								if (model.get( 'setting-caption' ) != '' ) 
									imgstr = '[caption width="' +imgwidth + '" align="align' + model.get( 'setting-align' ) + '"]' + imgstr + ' ' + model.get( 'setting-caption' ) + '[/caption]';
							
								htmlstr = htmlstr + imgstr + "\n\n";
							}
						}
					, 'json' )
				);
				
			});
			
			$.when.apply( this, jqHXRs ).done( function() {
				$( '#sportspost-button' ).text( wp.media.view.l10n.sportspostInsertButton );
				$( '#sportspost-button' ).removeAttr( 'disabled' );
				htmlstr = htmlstr.replace( /^\s+|\s+$/g, '' );
				if ( ! errors ) {
					wp.media.editor.insert( htmlstr );
					that.props.set( 'custom_data', '' );
					that.props.set( 'selected_id', '' );
					that.props.set( 'selected_image', '' );
					that.frame.close();
				}
			});	 
		}
		
	});
	
	wp.media.view.Toolbar.SportsPostMedia = wp.media.view.Toolbar.extend({
		className: 'media-toolbar sportspost-toolbar',
		initialize: function() {
			var that = this;
			_.defaults( this.options, {
				event: 'sportspost_event_insert',
				close: false,
				items: {
					sportspost_event_insert: {
						text: wp.media.view.l10n.sportspostInsertButton,
						style: 'primary',
						id: 'sportspost-button',
						priority: 80,
						requires: false,
						click: this.insertAction
					},
					sportspost_event_import: {
						text: wp.media.view.l10n.sportspostImportButton,
						style: 'secondary',
						id: 'sportspost-import-button',
						priority: 100,
						requires: false,
						click: this.importAction
					},
					sportspost_event_connect: {
						text: 'Connect',
						style: 'primary',
						id: 'sportspost-connect-button',
						priority: 10,
						requires: false,
						click: function( event ) {
							event.preventDefault();
							var dialog = new wp.media.view.SportsPostConnectDialog({
								model: new SportsPostUserAccount(),
							});
							dialog.on( 'change:connected', that.refreshConnected, that );
						}
					},
					sportspost_event_register: {
						text: 'Register',
						style: 'secondary',
						id: 'sportspost-register-button',
						priority: 20,
						requires: false,
						click: function( e ) {
							e.preventDefault();
							window.open( 'http://www.iconsportswire.com/login', '_blank' );
						}
					},
					sportspost_event_disconnect: {
						text: 'Disconnect',
						style: 'secondary',
						id: 'sportspost-disconnect-button',
						priority: 30,
						requires: false,
						click: function( e ) {
							var that = this;
							e.preventDefault();
							var r = confirm( 'Your account credentials will be removed.\nDo you really want to disconnect?' );
							if ( r == true ) {
								$( '#sportspost-spin-status' ).show();
								$.post(
									ajaxurl,
									{ 
										action:'sportspost_disconnect',
										nonce: sportspost_nonce.nonce,
										source: that.controller.state().id 
									}, 
									function( data ) {
										if( data.message == 'success' ) {
											that.controller.state().props.set( 'connected', false );
											that.controller.state().props.set( 'status', '' );
											that.controller.state().trigger( 'change:connected' );
										}
										$( '#sportspost-spin-status' ).hide();
									}
								, 'json' );
								return false;
							}
						}

					},
					sportspost_event_preferences: {
						text: 'Preferences',
						style: 'secondary',
						id: 'sportspost-preferences-button',
						priority: 40,
						requires: false,
						click: function() {
						$.post(
							ajaxurl, 
							{
								action: 'sportspost_user_preferences_get',
								nonce: sportspost_nonce.nonce,
								source: 'Icon_Sportswire', // Todo
							}, 
							function( data ) {
								var model = new SportsPostUserSettings();
								model.set( 'leagues', data.leagues );
								model.set( 'clients', data.clients );
								model.set( 'settings', data.settings );
								if( data.message == 'success' ) {
									dialog = new wp.media.view.SportsPostUserDialog({
										model: model
									});
									dialog.on( 'change:connected', that.refreshConnected, that );
								}
								else {
									alert( data.message );									
								}
							},
							'json'
						);
						}
					},
					sportspost_event_buy: {
						text: 'Buy Credits',
						style: 'secondary',
						id: 'sportspost-buy-button',
						priority: 50,
						requires: false,
						click: function( e ) {
							e.preventDefault();
							window.open( 'https://www.iconsportswire.com/addcredits.php', '_blank' );
						}
					},
					sportspost_event_visit: {
						text: 'Visit Site',
						style: 'secondary',
						id: 'sportspost-visit-button',
						priority: 60,
						requires: false,
						click: function( e ) {
							e.preventDefault();
							window.open( 'http://www.iconsportswire.com/browse', '_blank' );
						}
					},
				}
			});
			

			wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
			
			var status = $( '<div/>', {
				id: 'sportspost-status'
			});
			this.$el.children( '.media-toolbar-primary' ).append(status);

			var filter = this.$el.find( '.sportspost' ),
				spinner = $( '<span/>', {
				class: 'spinner',
				id: 'sportspost-spin-status'
			});
			this.$el.find( '.media-toolbar-primary' ).append(spinner);

		},
		
		refreshConnected: function( data ) {
			this.controller.state().props.set( 'status', data.status );
			this.controller.state().props.set( 'account_type', data.settings.iconsportswire_account_type );
			this.controller.state().props.set( 'connected', true );
			$( '#sportspost-toolbar' ).show();
			this.refresh();
		},
	
		refresh: function() {
			
			var connected = this.controller.state().props.get( 'connected' ),
				account_type = this.controller.state().props.get( 'account_type' );
			if ( connected == 'wait' ) {
				$( '#sportspost-spin-status' ).show();
				$( '#sportspost-connect-button' ).hide();
				$( '#sportspost-disconnect-button' ).hide();
				$( '#sportspost-preferences-button' ).hide();
				$( '#sportspost-buy-button' ).hide();
				$( '#sportspost-visit-button' ).hide();
				$( '#sportspost-register-button' ).hide();
				$( '#sportspost-status' ).hide();
				$( '#sportspost-spin-status' ).show();
			}
			else if ( connected == true) {
				$( '#sportspost-connect-button' ).hide();
				$( '#sportspost-disconnect-button' ).show();
				$( '#sportspost-preferences-button' ).show();
				$( '#sportspost-register-button' ).hide();
				if ( account_type == 1 ) {
					$( '#sportspost-visit-button' ).show();
					$( '#sportspost-buy-button' ).hide();
				}
				if ( account_type == 2 ) {
					$( '#sportspost-buy-button' ).show();
					$( '#sportspost-visit-button' ).hide();
				}
				$( '#sportspost-status' ).show();
				$( '#sportspost-spin-status' ).hide();
			}
			else {
				$( '#sportspost-connect-button' ).show();
				$( '#sportspost-disconnect-button' ).hide();
				$( '#sportspost-preferences-button' ).hide();
				$( '#sportspost-register-button' ).show();
				$( '#sportspost-buy-button' ).hide();
				$( '#sportspost-visit-button' ).hide();
				$( '#sportspost-status' ).hide();
				$( '#sportspost-spin-status' ).hide();
			}
			
			var status = this.controller.state().props.get( 'status' );
			if ( status ) {
				$( '#sportspost-status' ).html( status );
			}
		
			if ( enableImport ) $( '#sportspost-import-button' ).show();
				
			var custom_data = this.controller.state().props.get( 'custom_data' );
		
			if ( custom_data ) {
				var selection = new SelectedImages( custom_data.models );
			} else var selection = new SelectedImages();
			 
			var show = false;
			if ( selection.length > 0 ) show = true;
				 
			this.get( 'sportspost_event_insert' ).model.set( 'disabled', ! show );
			if ( ! show ) $( '#sportspost-button' ).attr( 'disabled','disabled' );
			
			if ( ! show && enableImport ) {
				$( '#sportspost-import-button' ).attr( 'disabled', 'disabled' );
				$( '#sportspost-import-button' ).text( wp.media.view.l10n.sportspostImportButton );
			}
			else if	( show && enableImport ) {
				$( '#sportspost-import-button' ).removeAttr( 'disabled' );
				var imgtext = (selection.length > 1) ? ' images' : ' image';
				$( '#sportspost-import-button' ).text( wp.media.view.l10n.sportspostImportButton + ' ' + selection.length + imgtext );
			}			
			
			wp.media.view.Toolbar.prototype.refresh.apply( this, arguments );
			return this;
		},
		
		insertAction: function(){
			this.controller.state().insertAction();
		},
		
		importAction: function(){
			this.controller.state().importAction();
		},
		
		
	});
	
	wp.media.view.SportsPostMedia = wp.media.View.extend({

		events: {
			'change select#method': 'setFilter',
			'change input#param': 'setParam',
			'change select#paramselect': 'setParam',
			'click .sportspost-image img.image': 'toggleSelectionHandler',
			'click .sportspost-image img.folder': 'selectFolder',
			'click a#backfolder': 'backFolder',
			'click .sportspost-image a.check': 'removeFromSelection',
			'click .sportspost-image a': 'preventDefault',
			'click input#pagination': 'getPagination',
		},
	
		initialize: function() {
/*			if ( this.model.get( 'initialized' ) ) {
				this.model.get( 'initialized' ) = false;
			}*/
			this.selection = new SelectedImages();
			this.sourceDetails = this.options.sourceDetails;
			this.source = this.options.source;
			this.imageError = '';
			this.createToolbar();
			this.createSidebar();
			if ( ! this.model.get( 'initialized' ) ) {
				this.checkConnection();
		    	var toolbar = this.$el.find( '#sportspost-toolbar' );
		    	$( toolbar ).hide();
			}
			this.model.set( 'initialized', true );
		},
			
		createToolbar: function() {
			
			var images;

			var content = this.$el.find( '.sportspost' );
			
			var toolbar = $( '<div/>', {
					id: 'sportspost-toolbar'
				});
			this.$el.append( toolbar );
			this.$el.find( '#sportspost-toolbar' ).append ( this.createSelect() );

			var subtitle = $( '<div/>', {
					id: 'sportspost-subtitle'
				});
			this.$el.append( subtitle );
			
			var filter = this.$el.find( '.sportspost' ),
				paraminput = $( '<input/>', {
				id: 'param',
				type: 'text'
			});
			
			this.$el.find( '#sportspost-toolbar' ).append(paraminput);
			$( '#param' ).hide();

			var filter = this.$el.find( '.sportspost' ),
				paramselect = $( '<select/>', {
				id: 'paramselect',
				html: ''
			});
			this.$el.find( '#sportspost-toolbar' ).append(paramselect);
			$( '#paramselect' ).hide();

			var filter = this.$el.find( '.sportspost' ),
				spinner = $( '<span/>', {
				class: 'spinner',
				id: 'sportspostspin'
			});
			this.$el.find( '#sportspost-toolbar' ).append(spinner);

			$( '#sportspostspin' ).show();
			var filter = this.$el.find( '.sportspost' ),
				sportspostmsg = $( '<div/>', {
				id: 'sportspost-msg'
			});
			this.$el.append( sportspostmsg );
			$( '#sportspost-msg' ).hide();

			if ( this.model.get( 'method' ) ) {
				stream = this.sourceDetails.settings[ this.model.get( 'method' ) ];
				this.filterType = this.model.get( 'method' );
			} else {
				for ( var key in this.sourceDetails.settings ) break;
				stream = this.sourceDetails.settings[ key ];
				this.filterType = key;	
			}

			var filter = this.$el.find( '.sportspost' ),
				page = $( '<input/>', {
				id: 'page',
				type: 'hidden',
				value: '1'
			});
			this.$el.append( page );
			if ( this.model.get( 'page'  ) ) {
				$( page ).val( this.model.get( 'page' ) );
			}
			
			var filter = this.$el.find( '.sportspost' ),
				altpage = $( '<input/>', {
				id: 'altpage',
				type: 'hidden',
				value: ''
			});
			this.$el.append( altpage );
			if (this.model.get( 'altpage' ) ) {
				$(altpage).val( this.model.get( 'altpage' ) );
			}

			var connected = $( '<input/>', {
				id: 'connected',
				type: 'hidden',
				value: ''
			});
			this.$el.append( connected );
			if ( this.model.get( 'connected' ) ) {
				$( connected ).val ( this.model.get( 'connected' ) );
			}

			var show = this.displayParam( stream );
			
			if ( this.model.get( 'param' ) ) {
				var param = this.$el.find( '#param' );
				$( param ).val( this.model.get( 'param' ) );
				// $(param).show();
			}
			
			var filter = this.$el.find( '.sportspost' ),
				imagelist = $( '<ul/>', {
				id: 'sportspostimages'
			});
			this.$el.append(imagelist);
			
			this.clearImages();
			var filter = this.$el.find( '.sportspost' ),
				paginli = $( '<li/>', {
				id: 'pagin'
			});
			var filter = this.$el.find( '.sportspost' ),
				pagin = $( '<input/>', {
				id: 'pagination',
				type: 'button',
				class: 'button',
				value: 'Load More'
			});
			$( paginli ).append( pagin );		
			this.$el.find( '#sportspostimages' ).append( paginli );	
						
			if ( this.model.get( 'images' ) ) {
					this.collection = new SportsPostImages(images);
					this.collection.reset(this.model.get( 'images' ) );
					if (this.model.get( 'pagin' ) ) {
						this.$el.find( '#pagination' ).hide(); 
					} else this.$el.find( '#pagination' ).show();
					if (this.model.get( 'method' ) ) this.displayPag(this.model.get( 'method' ) );
					
					if (this.model.get( 'folder_path' ) ) this.createBackLink(this.model.get( 'folder_path' ) );
			} else {
				this.$el.find( '#pagination' ).hide();
				this.collection = new SportsPostImages( images );
				if ( show.check ) {
					images = this.getImages(this, this.source, key, show.param, 1, '', '', '' );
				} 
			}

			this.on( 'change:filterType', this.filterByType, this );
			this.on( 'change:paramValue', this.filterByParam, this );

			this.collection.on( 'reset', this.render, this);
		},

		clearImages: function() {
			this.$el.find( 'ul#sportspostimages li#pagin' ).prevAll().remove();
		},
		
		selectFolder: function( event ) {
			var path = $( '#' + event.target.id ).data( 'link' );
			this.currentFolder = $( '#' + event.target.id ).data( 'photo-set-name' );
			this.selectFolderHandler( path );
		},
		
		createBackLink: function(link) {
			var backlink = $( '<a/>', {
				class: 'back-link',
				id: 'backfolder',
				href: '#',
				text: 'Back to List of Photo Sets',
				'data-link': link
			});
			this.$el.find( '#sportspost-toolbar' ).append( backlink );
		},
		
		selectFolderHandler: function( path ) {
			this.model.set( 'folder_path', '' );
			if ( path != '/' ) {
				var paths = path.split( '/' );
				if ( paths.length > 2 ) {
					paths.pop(); 
					var backpath = '/';
					if ( paths.length > 1 ) backpath = paths.join( '/' );
					
					if ( $( '#backfolder' ).length == 0) {
						this.createBackLink( backpath );
					} else {
						$( '#backfolder' ).data( 'link', backpath );
					}
					this.model.set( 'folder_path', backpath );
					
					this.$el.find( '#backfolder' ).show();
				}
				else {
					this.$el.find( '#backfolder' ).hide();
				}
			} else {
				this.$el.find( '#backfolder' ).hide();
			}
			
			this.setParamManual( path );
		},
		
		backFolder: function(event) {
			event.preventDefault();
			this.clearSidebar();
			this.clearSidebar();
			this.model.set( 'selected_id', '' );
			this.model.set( 'selected_image', '' );
			this.model.set( 'custom_data', '' );
			this.setButtonStates( '' );
			
			var path = $(event.target).data( 'link' );
			this.selectFolderHandler(path);
		},	
		
		toggleSelectionHandler: function( event ) {
			if ($( 'div.sportspost' ).hasClass( 'sportspost-media-overlay' ) ) return;
			var method = '';
			if ( event.shiftKey )
				method = 'between';
			else if ( event.ctrlKey || event.metaKey )
				method = 'toggle';

			this.toggleSelection( method, event );
		},
		
		toggleSelection: function( method, event ) {
			$( '#sportspost-button' ).attr( 'disabled', 'disabled' );
			
			$( 'ul#sportspostimages li' ).removeClass( 'details' );
			if ( $( '#' + event.target.id ).closest( 'li' ).hasClass( 'selected' ) ) {
				$( '#' + event.target.id ).closest( 'li' ).addClass( 'details' );
			} else {
				if ( method == '' ) {
					$( 'ul#sportspostimages li' ).removeClass( 'selected' );
					this.selection.reset();
				}
				$( '#' + event.target.id ).closest( 'li' ).addClass( 'selected details' );
			}
			this.$el.find( '.sportspost-sidebar' ).empty();
			
			check_id = event.target.id;
			var sportspost = $( event.target ).getAttributes();
			sportspost['setting-title'] = sportspost['title'];
			sportspost['setting-alt'] = sportspost['title'];
			sportspost['setting-caption'] = sportspost['data-caption'];
			sportspost['setting-align'] = 'none';
			sportspost['setting-link-to'] = 'none';
			sportspost['setting-link-to-custom'] = '';
			sportspost['setting-width'] = '0';
			sportspost['setting-height'] = '0';	
			
			var defaults = wp.media.view.l10n.sportspost_defaults;
			_.each( defaults, function ( value, key ) {
				sportspost[ key ] = value;
			}, this);
			
			this.selection.add( sportspost );
			this.custom_update( this.selection, event );
			this.populateSidebar( this.model ); 
			this.setButtonStates( this.selection );
		},
		
		preventDefault: function( event ) {
			event.preventDefault();
		},
		
		clearSelection: function( selection, selected ) {
			if ( selected == true ) {
				this.model.set( 'selected_id', '' );
				this.model.set( 'selected_image', '' );
			}
			if ( selection ) {
				this.model.set( 'custom_data', selection );
				this.setButtonStates( selection );
			}
		},
		
		setButtonStates: function(selection) {
			var show = false;
			if ( selection != '' && selection.length > 0 ) show = true;
			if ( ! show ) $( '#sportspost-button' ).attr( 'disabled', 'disabled' );
			if ( ! show && enableImport ) {
				$( '#sportspost-import-button' ).attr( 'disabled', 'disabled' );
				$( '#sportspost-import-button' ).text( wp.media.view.l10n.sportspostImportButton );
			}
			else if ( show && enableImport ) {
				$( '#sportspost-import-button' ).removeAttr( 'disabled' );
				var imgtext = ( selection.length > 1 ) ? ' images' : ' image';
				$( '#sportspost-import-button' ).text( wp.media.view.l10n.sportspostImportButton + ' ' + selection.length + imgtext );
			}
	
		},
			
		removeFromSelection: function( event ) {
			if ( $( 'div.sportspost' ).hasClass( 'sportspost-media-overlay' ) ) return;
				
			var check_id = event.currentTarget.id;
			if ( event.currentTarget.tagName == 'A' ) {
				var imageid = check_id.substring( 11 );
			} else {
				var imageid = check_id.substring( 6 );
			} 
					
			this.selection.remove( this.selection.get(imageid) );
			
			$( '#' + event.target.id ).closest( 'li' ).removeClass( 'selected' );
			$( '#' + event.target.id ).closest( 'li' ).removeClass( 'details' );
			
			var ifselected = false;
			if ( this.model.get( 'selected_id' ) == imageid ) {
				ifselected = true;
				this.$el.find( '.sportspost-sidebar' ).empty();
			}
			this.clearSelection( this.selection, ifselected );
		},
		
		clearSidebar: function() {
			this.clearImages();
			this.clearSelection();
			this.model.set( 'selected_id', '' );
			this.model.set( 'selected_image', '' );
			//$( 'ul#sportspostimages li' ).removeClass( 'selected' );
			$( '.sportspost-sidebar' ).empty();
		},
			
		render: function () {
			var that = this;
			if ( this.collection ) {
				this.$el.find( '#sportspost-subtitle' ).html( '<h2>' + this.model.get( 'subtitle' ) + '</h2>' );
				if ( this.collection.models.length > 0) {
					this.clearImages();
					_.each( this.collection.models, function( item ) {
							this.$el.find( '#pagin' ).before( that.renderImage( item ) );
						}, this );
					this.$el.find( '#sportspostspin' ).hide();
				} else {
					if (this.imageError != '' ) {
						$( '#sportspost-msg' ).text( this.imageError );
						$( '#sportspost-msg' ).show();
						this.$el.find( '#sportspostspin' ).hide();
					}
				}
				this.imageError = '';
			}
			if ( this.model.get( 'selected_image' ) ) {
				var that = this;
				var selectedimg = this.$el.find( 'img#' + this.model.get( 'selected_id' ) );
				$( selectedimg ).closest( 'li' ).addClass( 'details' );
				this.populateSidebar( this.model );
				if ( this.model.get( 'custom_data' ) ) {
					var selectedimages = this.model.get( 'custom_data' );
					var selection = new SelectedImages(selectedimages.models );
					selection.each(function(model){
						var selectimg = that.$el.find( 'img#' + model.get( 'id' ) );
						$( selectimg ).closest( 'li' ).addClass( 'selected' );
					});
					
				}
				$( '#sportspost-button' ).removeAttr( 'disabled' );
			}
		},
	 
		renderImage: function (item) {
			var imageView = new SportsPostImageView({
				model: item
			});
			return imageView.render().el;
		},
		
		custom_update: function( selection, event ) {
			this.model.set( 'selected_id', event.target.id );
			this.model.set( 'selected_image', event.target );
			this.model.set( 'custom_data', selection);
		},
		
		createSelect: function () {
			var filter = this.$el.find( '.sportspost' ),
				select = $( '<select/>', {
					html: '',
					id: 'method'
				});
			var that = this;
			if ( typeof wp.media.view.l10n.sportspost_default_filters.method != 'undefined' && ! this.model.get( 'initialized' ) ) {
				that.model.set( 'method', wp.media.view.l10n.sportspost_default_filters.method );
			}
			_.each( this.sourceDetails.settings, function( settings, method ) {
				if ( that.model.get( 'method' ) && (that.model.get( 'method' ) == method ) ) {
					var option = $( '<option/>', {
						value: method,
						text: settings.name,
						selected: 'selected'
					}).appendTo( select );
				} else {
					var option = $( '<option/>', {
						value: method,
						text: settings.name
					}).appendTo( select );
				} 
			});
			return select;
		},
		
		checkConnection: function() {
			var that = this;
			if ( that.model.get( 'connected' ) == 'wait' ) {
				$.post(
					ajaxurl, 
					{
						action: 'sportspost_check',
						nonce: sportspost_nonce.nonce,
						source: this.source
					}, 
					function( data ) {
						if( data.message == 'success' ) {
							that.filterType = $( '#method option:selected' ).val();
							that.trigger( 'change:filterType' );
							that.model.set( 'status', data.status );
							that.model.set( 'account_type', data.settings.iconsportswire_account_type );
							that.model.set( 'connected', true);
					    	var toolbar = that.$el.find( '#sportspost-toolbar' );
					    	$( toolbar ).show();
							that.trigger( 'change:connected' );
						}
						else {
							that.model.set( 'status', '' );
							that.model.set( 'account_type', '' );
							that.model.set( 'connected', false );
							that.trigger( 'change:connected' );
						}
					},
					'json'
				);
			}
		},
		
		getImages: function( collection, source, method, param, page, altpage, paramlabel, photoset ) {
			this.$el.find( '#sportspostspin' ).show();
			this.$el.find( '#sportspost-msg' ).hide();
			this.$el.find( '#pagination' ).val( 'Loading...' );
			this.$el.find( '#pagination' ).attr( 'disabled', 'disabled' );
			if (page == 1) {
				this.$el.find( '#pagination' ).hide();
				this.clearImages();
			}
			var images;
			var that = this;
			$.post(
				ajaxurl, 
				{
					action: 'sportspost_load_images',
					source: source,
					method: method,
					param: param,
					page: page,
					altpage: altpage,
					paramlabel: paramlabel,
					photoset: photoset
				}, 
				function( response ) {
					that.model.set( 'subtitle', response.title );
					if ( response.error ) {
						collection.imageError = response.message;
						collection.$el.find( '#pagination' ).hide(); 
					}
					else {
						images = response.images;
						if ( method == collection.filterType ) {
							if (page == 1) {
								collection.model.set( 'images', images );
							}
							else {
								original = collection.model.get( 'images' );
								collection.model.set( 'images', original.concat( images ) );
							}
						}
					}
					if( method == collection.filterType ) {
						if ( page == 1 ) collection.collection.reset( images );
						else {
							var pagCollection = new SportsPostImages( images );
							collection.collection.add( pagCollection.models );
							_.each( pagCollection.models, function ( item ) {
								collection.$el.find( '#pagin' ).before( collection.renderImage( item ) );
							}, this);
						}
						collection.displayPag( method );
						collection.$el.find( '#pagination' ).val( 'Load More' );
						collection.$el.find( '#sportspostspin' ).hide();
						if ( response.pagin == 'end' ) collection.model.set( 'pagin', 'end' );
						else {
							if (!response.error) {
								collection.$el.find( '#pagination' ).removeAttr( 'disabled' );
								collection.model.set( 'pagin', '' );
							} else 	collection.$el.find( '#pagination' ).hide(); 
						}
						if ( response.altpage ) {
							collection.model.set( 'altpage', response.altpage );
							$( '#altpage' ).val( response.altpage );
						}
					}
					else collection.$el.find( '#sportspostspin' ).hide();
				}
			, 'json' );
			

		},

		getPagination: function() {
			var page = $( '#page' ).val();
			page++;
			$( '#page' ).val(page);
			this.model.set( 'page', page);
			var altpage = $( '#altpage' ).val();
			this.model.set( 'altpage', altpage );
			this.getImages( this, this.source, this.$el.find( '#method' ).val(), this.model.get( 'param' ), page, altpage, '', '' );
		},
		
		displayPag: function(method) {
			this.$el.find( '#pagination' ).show();
			stream = this.sourceDetails.settings[ method ];
			if ( stream.nopagin ) this.$el.find( '#pagination' ).hide(); 
		},
		
		displayParam: function( stream ) {
			var show = new Object();
			show['check'] = false;
			show['param'] = '';

			paraminput = this.$el.find( '#param' ).hide();
			paramselect = this.$el.find( '#paramselect' ).hide();
			if( stream.param ) {
				this.$el.find( '#pagination' ).attr( 'disabled', 'disabled' );
				var param_value = (stream.param_default) ? stream.param_default : '';
				if ( typeof wp.media.view.l10n.sportspost_default_filters.paramselect != 'undefined' && stream.param_dynamic && ! this.model.get( 'initialized' ) ) {
					param_value = wp.media.view.l10n.sportspost_default_filters.paramselect;
				}
				show['param'] = param_value;
				this.$el.find( '#param' ).val( param_value );
				this.$el.find( '#param' ).attr( 'placeholder', stream.param_desc );
				if ( stream.param_disabled ) this.$el.find( '#param' ).attr( 'disabled', 'disabled' );
				if ( stream.param_type == 'text' ) {
					this.$el.find( '#param' ).show();
					this.$el.find( '#paramselect' ).hide();
				}
				else {
					var that = this;
					if ( stream.param_dynamic && ! stream.param_choices && this.model.get( 'initialized' ) ) {
						this.$el.find( '#sportspostspin' ).show();
						$.post(
							ajaxurl, 
							{
								action: 'sportspost_param_choices',
								source: that.source,
								method: that.$el.find( '#method' ).val(),
							}, 
							function( response ){
								if ( typeof wp.media.view.l10n.sportspost_default_filters.paramselect != 'undefined' ) {
									that.paramValue = wp.media.view.l10n.sportspost_default_filters.paramselect;
									that.model.set( 'param', that.paramValue );
								}
								that.populateParamSelect( response.choices, paramselect );
								stream.param_choices = response.choices;
								that.sourceDetails.settings[ that.$el.find( '#method' ).val() ] = stream;
								if ( typeof wp.media.view.l10n.sportspost_default_filters.paramselect != 'undefined' ) {
									that.setParamManual( wp.media.view.l10n.sportspost_default_filters.paramselect );
								}
							},
							'json'
						);					
					} else if ( stream.param_dynamic && ! stream.param_choices && ! that.model.get( 'initialized' ) ) {
						this.populateParamSelect([], paramselect);
					} else {
						this.populateParamSelect( stream.param_choices, paramselect );
					}
				}
			}
			this.$el.find( '#pagination' ).show();
			if ( stream.nopagin ) this.$el.find( '#pagination' ).hide(); 
			
			show['check'] = ( stream.param_default ) ? stream.param_default : ! stream.param;
			return show;
		},
		
		populateParamSelect: function(choices, paramselect) {
			var that = this;
			_.each(choices, function (value, key) {
				if (that.model.get( 'param' ) && (that.model.get( 'param' ) == key) ) {
					var option = $( '<option/>', {
						value: key,
						text: value,
						selected: 'selected'
					}).appendTo(paramselect);
				} else {
					var option = $( '<option/>', {
						value: key,
						text: value
					}).appendTo(paramselect);
				} 
			});
			this.$el.find( '#param' ).hide();
			this.$el.find( '#paramselect' ).show();
			this.$el.find( '#sportspostspin' ).hide();
		},		
		
		createSidebar: function() {
			var sidebar = new SportsPostImageSidebar();
			this.$el.append(sidebar.render().el);
		},
		
		populateSidebar: function(item) {
			var imageSettings = new SportsPostImageSettings({
				model: item
			});
			this.$el.find( '.sportspost-sidebar' ).append( imageSettings.render().el );
		},
		
		setFilter: function( e ) {
			this.clearSidebar();
			this.filterType = e.currentTarget.value;
			this.model.set( 'method', e.currentTarget.value );
			this.model.set( 'param', '' );
			this.$el.find( '#backfolder' ).hide();
			this.trigger( 'change:filterType' );
			stream = this.sourceDetails.settings[ e.currentTarget.value ];
			if ( stream.param_type == 'select' ) {
				var current_selection = this.$el.find( '#paramselect option:selected' ).val();
				if ( current_selection ) {
					this.setParamManual( current_selection );
				}
			}
		},
		
		setParam: function ( e ) {
			if ( e.currentTarget.value != '' ) {
				this.setParamManual( e.currentTarget.value );
			}
		},
		
		setParamManual: function( paramValue ) {
			this.clearSidebar();
			this.paramValue = paramValue;
			this.model.set( 'param', paramValue );
			$( '#param' ).val( paramValue );
			if ( paramValue != '' ) {
				this.trigger( 'change:paramValue' );
			}
		},
		
		filterByType: function () {
			this.clearSidebar();
			this.clearSubTitle();
			this.model.set( 'selected_id', '' );
			this.model.set( 'selected_image', '' );
			this.model.set( 'custom_data', '' );
			this.setButtonStates( '' );
			this.$el.find( '#sportspostspin' ).hide();
			this.$el.find( '#pagination' ).val( 'Load More' );
			this.$el.find( '#page' ).val( '1' );
			this.$el.find( '#altpage' ).val( '' );
			stream = this.sourceDetails.settings[ this.filterType ];
			var show = this.displayParam( stream );
			if ( show.check ) {
				var images = this.getImages(this, this.source, this.filterType, show.param, 1, '', '', '' );
			} else { 
				this.$el.find( '#pagination' ).hide();
				this.collection.reset(); 
			}
		},
		
		filterByParam: function() {
			this.clearSubTitle();
			this.$el.find( '#page' ).val( '1' );
			this.$el.find( '#altpage' ).val( '' );
			if ( this.paramValue.indexOf( '/' ) == -1 ) {
				this.$el.find( '#backfolder' ).hide();
			}
			var method = this.$el.find( '#method' ).val()
			var paramLabel = this.$el.find( '#paramselect option:selected' ).text();
			var photoSet = ( typeof this.currentFolder != 'undefined' )? this.currentFolder : '';
			var images = this.getImages( this, this.source, method, this.paramValue, 1, '', paramLabel, photoSet );
		},
		
		clearSubTitle: function() {
			this.model.set( 'subtitle', '' );
			$( '#sportspost-subtitle' ).html( '' );
		}
	
	});
	

	var oldMediaFrame = wp.media.view.MediaFrame.Post;
	wp.media.view.MediaFrame.Post = oldMediaFrame.extend({
	
		initialize: function() {
			oldMediaFrame.prototype.initialize.apply( this, arguments );
			
			var sportspost_sources = wp.media.view.l10n.sportspost;
			var mediaframe = this;
		
			var priority = 200;
			$.each( sportspost_sources, function(source, source_details ) {
				mediaframe.states.add([
					new wp.media.controller.SportsPostMedia({
						id: source,
						menu: wp.media.view.l10n.sportspost_menu,
						content: source + '-custom',
						title: wp.media.view.l10n.sportspost_menu_prefix + source_details.name,
						priority: priority + 100,
						toolbar: source + '-action',
						type: 'link'
					})
				]);

				mediaframe.on( 'content:render:'+ source + '-custom', _.bind( mediaframe.customSportsPostContent, mediaframe, source, source_details ) );
				mediaframe.on( 'toolbar:create:'+ source + '-action', mediaframe.createSportsPostToolbar, mediaframe );
				//mediaframe.on( 'toolbar:render:'+ source + '-action', mediaframe.renderCustomToolbar, mediaframe );
				
				// Hack to let "Icon Sportswire" menu stand on its own line
				mediaframe.on( 'uploader:ready', function() {
					$( '.media-menu-item:contains(' + source_details.name + ')' ).html(
						wp.media.view.l10n.sportspost_menu_prefix +
						'<span style="white-space: nowrap;">' +
						source_details.name +
						'</span>'
					);
				});
			});
			
			
		},
		
		createSportsPostToolbar: function(toolbar){
			toolbar.view = new wp.media.view.Toolbar.SportsPostMedia({
				controller: this,
			});
		},
	
		customSportsPostContent: function(source, source_details){
			this.$el.addClass( 'hide-router' );
	
			var view = new wp.media.view.SportsPostMedia({
				controller: this,
				model: this.state().props,
				className: 'sportspost media-' + source,
				sourceDetails: source_details,
				source: source
			});
	
			this.content.set( view );
		}
	
	});
	
	

} ( jQuery) );

(function( $ ) {
	$.fn.getAttributes = function() {
		var attributes = {}; 

		if( this.length ) {
			$.each( this[0].attributes, function( index, attr ) {
				attributes[ attr.name ] = attr.value;
			} ); 
		}

		return attributes;
	};
})( jQuery );