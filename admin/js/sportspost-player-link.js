/* global tinymce, sportspost_data, wpActiveEditor */
var sportsPostPlayerLink;

( function( $ ) {
	var editor, searchTimer, River, Query,
		inputs = {},
		rivers = {},
		isTouch = ( 'ontouchend' in document );

	sportsPostPlayerLink = {
		timeToTriggerRiver: 150,
		minRiverAJAXDuration: 200,
		riverBottomThreshold: 5,
		keySensitivity: 100,
		lastSearch: '',
		textarea: '',

		init: function() {
			inputs.wrap = $('#sportspost-player-link-wrap');
			inputs.dialog = $( '#sportspost-player-link' );
			inputs.backdrop = $( '#sportspost-player-link-backdrop' );
			inputs.submit = $( '#sportspost-player-link-submit' );
			inputs.close = $( '#sportspost-player-link-close' );
			// URL
			inputs.url = $( '#player-url-field' );
			inputs.preview = $( '#player-link-preview' );
			inputs.nonce = $( '#_ajax_sportspost_player_link_nonce' );
			// Secondary options
			inputs.title = $( '#player-link-title-field' );
			// Advanced Options
			inputs.openInNewTab = $( '#player-link-target-checkbox' );
			inputs.search = $( '#player-search-field' );
			inputs.league = $( '#player-league' );
			// Build Rivers
			rivers.search = new River( $( '#player-search-results' ) );
			rivers.elements = inputs.dialog.find( '.query-results' );

			// Get search notice text
			inputs.queryNotice = $( '#query-notice-message' );
			inputs.queryNoticeTextDefault = inputs.queryNotice.find( '.query-notice-default' );
			inputs.queryNoticeTextHint = inputs.queryNotice.find( '.query-notice-hint' );

			// Bind event handlers
			inputs.dialog.keydown( sportsPostPlayerLink.keydown );
			inputs.dialog.keyup( sportsPostPlayerLink.keyup );
			inputs.submit.click( function( event ) {
				event.preventDefault();
				sportsPostPlayerLink.update();
			});
			inputs.close.add( inputs.backdrop ).add( '#sportspost-player-link-cancel a' ).click( function( event ) {
				event.preventDefault();
				sportsPostPlayerLink.close();
			});

			rivers.elements.on( 'river-select', sportsPostPlayerLink.updateFields );

			// Display 'hint' message when search field or 'query-results' box are focused
			inputs.search.on( 'focus.playerlink', function() {
				inputs.queryNoticeTextDefault.hide();
				inputs.queryNoticeTextHint.removeClass( 'screen-reader-text' ).show();
			} ).on( 'blur.playerlink', function() {
				inputs.queryNoticeTextDefault.show();
				inputs.queryNoticeTextHint.addClass( 'screen-reader-text' ).hide();
			} );

			inputs.search.keyup( function() {
				var self = this;

				window.clearTimeout( searchTimer );
				searchTimer = window.setTimeout( function() {
					sportsPostPlayerLink.searchInternalLinks.call( self );
				}, 500 );
			});
			
			inputs.league.change( function() {
				rivers.search.change( '' );
				sportsPostPlayerLink.lastSearch = '';
				inputs.url.val( '' ).change();
				inputs.title.val( '' );
				inputs.submit.prop( 'disabled', true );
				inputs.search.keyup();
			});
			
			inputs.url.change( function() {
				var href = inputs.url.val() != '' ? inputs.url.val() : '#';
				inputs.preview.attr( 'href', href );
			});

		},

		open: function( editorId ) {
			var ed, begin, end, textarea, selection;

			sportsPostPlayerLink.range = null;

			if ( editorId ) {
				window.wpActiveEditor = editorId;
			}

			if ( ! window.wpActiveEditor ) {
				return;
			}

			this.textarea = $( '#' + window.wpActiveEditor ).get( 0 );

			if ( typeof tinymce !== 'undefined' ) {
				ed = tinymce.get( wpActiveEditor );

				if ( ed && ! ed.isHidden() ) {
					editor = ed;
				} else {
					editor = null;
				}

				if ( editor && tinymce.isIE ) {
					editor.windowManager.bookmark = editor.selection.getBookmark();
				}
				
				if ( editor && editor.selection.getContent() != '' ) {
					inputs.search.val( editor.selection.getContent() ).keyup();
				}
			}

			if ( ! sportsPostPlayerLink.isMCE() && document.selection ) {
				this.textarea.focus();
				this.range = document.selection.createRange();
			}
			
			if ( ! sportsPostPlayerLink.isMCE() ) {
				textarea    = this.textarea;
				begin       = textarea.selectionStart;
				end         = textarea.selectionEnd;
				selection   = textarea.value.substring( begin, end );
				if ( selection != '' ) {
					inputs.search.val( selection ).keyup();
				}
			}

			inputs.wrap.show();
			inputs.backdrop.show();

			sportsPostPlayerLink.refresh();
			$( document ).trigger( 'playerlink-open', inputs.wrap );
		},

		isMCE: function() {
			return editor && ! editor.isHidden();
		},

		refresh: function() {
			// Refresh rivers (clear links, check visibility)
			rivers.search.refresh();

			if ( sportsPostPlayerLink.isMCE() ) {
				sportsPostPlayerLink.mceRefresh();
			} else {
				sportsPostPlayerLink.setDefaultValues();
			}

			if ( isTouch ) {
				// Close the onscreen keyboard
				inputs.url.focus().blur();
			} else {
				// Focus the URL field and highlight its contents.
				// If this is moved above the selection changes,
				// IE will show a flashing cursor over the dialog.
				inputs.url.focus()[0].select();
			}

		},

		mceRefresh: function() {
			var e;

			// If link exists, select proper values.
			if ( e = editor.dom.getParent( editor.selection.getNode(), 'A' ) ) {
				// Set URL and description.
				inputs.url.val( editor.dom.getAttrib( e, 'href' ) );
				inputs.title.val( editor.dom.getAttrib( e, 'title' ) );
				// Set open in new tab.
				inputs.openInNewTab.prop( 'checked', ( '_blank' === editor.dom.getAttrib( e, 'target' ) ) );
				// Update save prompt.
				inputs.submit.val( sportspost_data.update );

			// If there's no link, set the default values.
			} else {
				sportsPostPlayerLink.setDefaultValues();
			}
		},

		close: function() {
			if ( ! sportsPostPlayerLink.isMCE() ) {
				sportsPostPlayerLink.textarea.focus();

				if ( sportsPostPlayerLink.range ) {
					sportsPostPlayerLink.range.moveToBookmark( sportsPostPlayerLink.range.getBookmark() );
					sportsPostPlayerLink.range.select();
				}
			} else {
				editor.focus();
			}

			inputs.backdrop.hide();
			inputs.wrap.hide();
			$( document ).trigger( 'playerlink-close', inputs.wrap );
		},

		getAttrs: function() {
			return {
				href: inputs.url.val(),
				title: inputs.title.val(),
				target: inputs.openInNewTab.prop( 'checked' ) ? '_blank' : ''
			};
		},

		update: function() {
			if ( sportsPostPlayerLink.isMCE() )
				sportsPostPlayerLink.mceUpdate();
			else
				sportsPostPlayerLink.htmlUpdate();
		},

		htmlUpdate: function() {
			var attrs, html, begin, end, cursor, title, selection, text,
				textarea = sportsPostPlayerLink.textarea;

			if ( ! textarea )
				return;

			attrs = sportsPostPlayerLink.getAttrs();

			// If there's no href, return.
			if ( ! attrs.href || attrs.href == 'http://' )
				return;

			// Build HTML
			html = '<a href="' + attrs.href + '"';

			if ( attrs.title ) {
				title = attrs.title.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
				html += ' title="' + title + '"';
			}

			if ( attrs.target ) {
				html += ' target="' + attrs.target + '"';
			}

			html += '>';

			// Insert HTML
			if ( document.selection && sportsPostPlayerLink.range ) {
				// IE
				// Note: If no text is selected, IE will not place the cursor
				//       inside the closing tag.
				textarea.focus();
				text = sportsPostPlayerLink.range.text != '' ? sportsPostPlayerLink.range.text : title;
				sportsPostPlayerLink.range.text = html + text + '</a>';
				sportsPostPlayerLink.range.moveToBookmark( sportsPostPlayerLink.range.getBookmark() );
				sportsPostPlayerLink.range.select();

				sportsPostPlayerLink.range = null;
			} else if ( typeof textarea.selectionStart !== 'undefined' ) {
				// W3C
				begin       = textarea.selectionStart;
				end         = textarea.selectionEnd;
				selection   = textarea.value.substring( begin, end );
				text        = selection != '' ? selection : title;
				html        = html + text + '</a>';
				cursor      = begin + html.length;

				// If no text is selected, place the cursor inside the closing tag.
				if ( begin == end )
					cursor -= '</a>'.length;

				textarea.value = textarea.value.substring( 0, begin ) + html +
					textarea.value.substring( end, textarea.value.length );

				// Update cursor position
				textarea.selectionStart = textarea.selectionEnd = cursor;
			}

			sportsPostPlayerLink.close();
			textarea.focus();
		},

		mceUpdate: function() {
			var link, selection, title, html,
				attrs = sportsPostPlayerLink.getAttrs();

			sportsPostPlayerLink.close();
			editor.focus();

			if ( tinymce.isIE ) {
				editor.selection.moveToBookmark( editor.windowManager.bookmark );
			}

			link = editor.dom.getParent( editor.selection.getNode(), 'a[href]' );

			// If the values are empty, unlink and return
			if ( ! attrs.href || attrs.href == 'http://' ) {
				editor.execCommand( 'unlink' );
				return;
			}

			if ( link ) {
				editor.dom.setAttribs( link, attrs );
			} else {
				selection = editor.selection.getContent();
				if ( selection != '' ) {
					editor.execCommand( 'mceInsertLink', false, attrs );
				}
				else {
					html = '<a href="' + attrs.href + '"';
					if ( attrs.title ) {
						title = attrs.title.replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
						html += ' title="' + title + '"';
					}
		
					if ( attrs.target ) {
						html += ' target="' + attrs.target + '"';
					}
					html += '>';
					html += title;
					html += '</a>';
					editor.execCommand( 'mceInsertContent', false, html );
				}
			}

			// Move the cursor to the end of the selection
			editor.selection.collapse();
		},

		updateFields: function( e, li ) {
			inputs.url.val( li.children( '.item-permalink' ).val() ).change();
			inputs.title.val( li.hasClass( 'no-title' ) ? '' : li.children( '.item-title' ).text() );
			inputs.submit.prop( 'disabled', false );
		},

		setDefaultValues: function() {
			var selection = editor && editor.selection.getContent(),
				urlRegexp = /^(https?|ftp):\/\/[A-Z0-9.-]+\.[A-Z]{2,4}[^ "]*$/i;

			if ( selection && urlRegexp.test( selection ) ) {
				// Selection is URL
				inputs.url.val( selection.replace( /&amp;|&#0?38;/gi, '&' ) ).change();
			} else {
				// Set URL to default.
				inputs.url.val( '' ).change();
			}

			// Set description to default.
			inputs.title.val( '' );
			inputs.submit.prop( 'disabled', false );

			// Update save prompt.
			inputs.submit.val( sportspost_data.save );
		},

		searchInternalLinks: function() {
			var t = $( this ), waiting,
				search = t.val();

			if ( search.length > 2 ) {
				rivers.search.show();

				// Don't search if the keypress didn't change the title.
				if ( sportsPostPlayerLink.lastSearch == search )
					return;

				sportsPostPlayerLink.lastSearch = search;
				waiting = t.parent().parent().find('.spinner').show();

				rivers.search.change( search );
				rivers.search.ajax( function() {
					waiting.hide();
				});
			} else {
				rivers.search.hide();
			}
		},

		next: function() {
			rivers.search.next();
		},

		prev: function() {
			rivers.search.prev();
		},

		keydown: function( event ) {
			var fn, id,
				key = $.ui.keyCode;

			if ( key.ESCAPE === event.keyCode ) {
				sportsPostPlayerLink.close();
				event.stopImmediatePropagation();
			} else if ( key.TAB === event.keyCode ) {
				id = event.target.id;

				// sportspost-player-link-submit must always be the last focusable element in the dialog.
				// following focusable elements will be skipped on keyboard navigation.
				if ( id === 'sportspost-player-link-submit' && ! event.shiftKey ) {
					inputs.close.focus();
					event.preventDefault();
				} else if ( id === 'sportspost-player-link-close' && event.shiftKey ) {
					inputs.submit.focus();
					event.preventDefault();
				}
			}

			if ( event.keyCode !== key.UP && event.keyCode !== key.DOWN ) {
				return;
			}

			if ( document.activeElement &&
				( document.activeElement.id === 'player-link-title-field' || document.activeElement.id === 'player-url-field' ) ) {
				return;
			}

			fn = event.keyCode === key.UP ? 'prev' : 'next';
			clearInterval( sportsPostPlayerLink.keyInterval );
			sportsPostPlayerLink[ fn ]();
			sportsPostPlayerLink.keyInterval = setInterval( sportsPostPlayerLink[ fn ], sportsPostPlayerLink.keySensitivity );
			event.preventDefault();
		},

		keyup: function( event ) {
			var key = $.ui.keyCode;

			if ( event.which === key.UP || event.which === key.DOWN ) {
				clearInterval( sportsPostPlayerLink.keyInterval );
				event.preventDefault();
			}
		},

		delayedCallback: function( func, delay ) {
			var timeoutTriggered, funcTriggered, funcArgs, funcContext;

			if ( ! delay )
				return func;

			setTimeout( function() {
				if ( funcTriggered )
					return func.apply( funcContext, funcArgs );
				// Otherwise, wait.
				timeoutTriggered = true;
			}, delay );

			return function() {
				if ( timeoutTriggered )
					return func.apply( this, arguments );
				// Otherwise, wait.
				funcArgs = arguments;
				funcContext = this;
				funcTriggered = true;
			};
		},
	};

	River = function( element, search ) {
		var self = this;
		this.element = element;
		this.ul = element.children( 'ul' );
		this.contentHeight = element.children( '#player-link-selector-height' );
		this.waiting = element.find('.river-waiting');

		this.change( search );
		this.refresh();

		$( '#sportspost-player-link .query-results, #sportspost-player-link #player-link-selector' ).scroll( function() {
			self.maybeLoad();
		});
		element.on( 'click', 'li', function( event ) {
			self.select( $( this ), event );
		});
	};

	$.extend( River.prototype, {
		refresh: function() {
			this.deselect();
			this.visible = this.element.is( ':visible' );
		},
		show: function() {
			if ( ! this.visible ) {
				this.deselect();
				this.element.show();
				this.visible = true;
			}
		},
		hide: function() {
			this.element.hide();
			this.visible = false;
		},
		// Selects a list item and triggers the river-select event.
		select: function( li, event ) {
			var liHeight, elHeight, liTop, elTop;

			if ( li.hasClass( 'unselectable' ) || li == this.selected )
				return;

			this.deselect();
			this.selected = li.addClass( 'selected' );
			// Make sure the element is visible
			liHeight = li.outerHeight();
			elHeight = this.element.height();
			liTop = li.position().top;
			elTop = this.element.scrollTop();

			if ( liTop < 0 ) // Make first visible element
				this.element.scrollTop( elTop + liTop );
			else if ( liTop + liHeight > elHeight ) // Make last visible element
				this.element.scrollTop( elTop + liTop - elHeight + liHeight );

			// Trigger the river-select event
			this.element.trigger( 'river-select', [ li, event, this ] );
		},
		deselect: function() {
			if ( this.selected )
				this.selected.removeClass( 'selected' );
			this.selected = false;
		},
		prev: function() {
			if ( ! this.visible )
				return;

			var to;
			if ( this.selected ) {
				to = this.selected.prev( 'li' );
				if ( to.length )
					this.select( to );
			}
		},
		next: function() {
			if ( ! this.visible )
				return;

			var to = this.selected ? this.selected.next( 'li' ) : $( 'li:not(.unselectable):first', this.element );
			if ( to.length )
				this.select( to );
		},
		ajax: function( callback ) {
			var self = this,
				delay = this.query.page == 1 ? 0 : sportsPostPlayerLink.minRiverAJAXDuration,
				response = sportsPostPlayerLink.delayedCallback( function( results, params ) {
					self.process( results, params );
					if ( callback )
						callback( results, params );
				}, delay );

			this.query.ajax( response );
		},
		change: function( search ) {
			if ( this.query && this._search == search )
				return;

			this._search = search;
			this.query = new Query( search );
			this.element.scrollTop( 0 );
		},
		process: function( results, params ) {
			var list = '', alt = true, classes = '';

			if ( ! results ) {
				list += '<li class="unselectable no-matches-found"><span class="item-title"><em>' +
					sportspost_data.no_matches_found + '</em></span></li>';
			} else {
				$.each( results['results'], function() {
					var permalink = sportspost_data.link_prefix;
					permalink += sportspost_data[ 'league_name_' + $( '#player-league' ).val() ];
					permalink += sportspost_data.id_prefix;
					permalink += this['player-key'];
					permalink += sportspost_data.id_suffix;
					if ( sportspost_data.affiliate_reference_id ) {
						permalink += permalink.indexOf( '?' ) > -1 ? '&amp;' : '?';
						permalink += 'aff=' + sportspost_data.affiliate_reference_id;
					}
					classes = alt ? 'alternate' : '';
					classes += this['first-name'] ? '' : ' no-title';
					list += classes ? '<li class="' + classes + '">' : '<li>';
					list += '<input type="hidden" class="item-permalink" value="' + permalink + '" />';
					list += '<span class="item-title">';
					list += this['first-name'] ? this['first-name'] + ' ' + this['last-name'] : sportspost_data.no_title;
					list += '</span><span class="item-info">' + this['team-city'] + ( this['team-city'] != this['team-name'] ? ' ' + this['team-name'] : '' ) + '</span></li>';
					alt = ! alt;
				});
			}
			this.ul.html( list );
		},
		maybeLoad: function() {
			var self = this,
				el = this.element,
				bottom = el.scrollTop() + el.height();

			if ( ! this.query.ready() || bottom < this.contentHeight.height() - sportsPostPlayerLink.riverBottomThreshold )
				return;

			setTimeout(function() {
				var newTop = el.scrollTop(),
					newBottom = newTop + el.height();

				if ( ! self.query.ready() || newBottom < self.contentHeight.height() - sportsPostPlayerLink.riverBottomThreshold )
					return;

				self.waiting.show();
				el.scrollTop( newTop + self.waiting.outerHeight() );

				self.ajax( function() {
					self.waiting.hide();
				});
			}, sportsPostPlayerLink.timeToTriggerRiver );
		}
	});

	Query = function( search ) {
		this.allLoaded = false;
		this.querying = false;
		this.search = search;
	};

	$.extend( Query.prototype, {
		ready: function() {
			return ! ( this.querying || this.allLoaded );
		},
		ajax: function( callback ) {
			
			var self = this,
				query = {
					'type' : 'players',
					'league' : 'l.' + $( '#player-league' ).val() + '.com',
					'name-fragment' : this.search.trim(),
					'input-publisher' : 'sportsforecaster.com',
					'output-publishers' : sportspost_data.output_publishers,
					'format' : 'sportsjson'
				};

			this.querying = true;

			$.get( sportspost_data.api_endpoint, query, function( r ) {
				self.querying = false;
				self.allLoaded = true;
				callback( r, query );
			}, 'json' );
			
		}
	});

	$( document ).ready( sportsPostPlayerLink.init );
})( jQuery );
