/* Fisheye interface: sort controls, library rail, photo view and lightbox, organize tools, upload, image order, paged loading. No library dependencies. */
(function feStart() {
	// the script may be loaded from the document head: wait until the page is there
	if( document.readyState == 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', feStart );
		return;
	}

	// marked as script requests so the server, and any optimising proxy in front of it, returns the bare fragment
	var FETCH_OPTIONS = { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } };

	var sort = document.querySelector( '[data-fe-submit]' );
	if( sort ) {
		sort.addEventListener( 'change', function() { sort.form.submit(); } );
	}

	// library rail: open or closed is remembered per browser, separately for the light pages and the stage
	var shell = document.querySelector( '[data-fe-shell]' );
	if( shell ) {
		var railKey = 'fe-rail-' + shell.getAttribute( 'data-fe-shell' );
		var setRail = function( pClosed ) {
			shell.classList.toggle( 'is-rail-closed', pClosed );
		};
		try {
			var stored = window.localStorage.getItem( railKey );
			if( stored ) {
				setRail( stored == 'closed' );
			}
		} catch( e ) {}
		Array.prototype.forEach.call( shell.querySelectorAll( '[data-fe-rail-toggle]' ), function( pToggle ) {
			pToggle.addEventListener( 'click', function( pEvent ) {
				pEvent.preventDefault();
				var closed = !shell.classList.contains( 'is-rail-closed' );
				setRail( closed );
				try {
					window.localStorage.setItem( railKey, closed ? 'closed' : 'open' );
				} catch( e ) {}
			} );
		} );
	}

	// photo view: the same markup is the photo page and, fetched as a fragment, the lightbox on gallery pages
	var lightbox = null;
	var playTimer = null;
	var PLAY_MS = 4000;

	function store( pStorage, pKey, pValue ) {
		try {
			if( pValue === undefined ) {
				return window[pStorage].getItem( pKey );
			}
			if( pValue === null ) {
				window[pStorage].removeItem( pKey );
			} else {
				window[pStorage].setItem( pKey, pValue );
			}
		} catch( e ) {}
		return null;
	}

	function currentView() {
		return ( lightbox && lightbox.open ) ? lightbox.querySelector( '[data-fe-view]' ) : document.querySelector( '[data-fe-view]' );
	}

	function isPlaying() {
		return store( 'sessionStorage', 'fe-play' ) == '1';
	}

	function setPlaying( pOn ) {
		store( 'sessionStorage', 'fe-play', pOn ? '1' : null );
		window.clearTimeout( playTimer );
		setupView();
	}

	// apply remembered state to whichever view is on screen, and arm the slideshow
	function setupView() {
		var view = currentView();
		if( !view ) {
			return;
		}
		var closed = store( 'localStorage', 'fe-info' ) == 'closed';
		view.classList.toggle( 'is-info-closed', closed );
		var info = view.querySelector( '[data-fe-info-toggle]' );
		if( info ) {
			info.setAttribute( 'aria-pressed', closed ? 'false' : 'true' );
		}
		// keep the current photo in the middle of the film strip
		var strip = view.querySelector( '.fe-strip' );
		var current = strip && strip.querySelector( '[aria-current]' );
		if( current ) {
			strip.scrollLeft = current.offsetLeft - ( strip.clientWidth - current.offsetWidth ) / 2;
		}
		var play = view.querySelector( '[data-fe-play]' );
		var next = view.getAttribute( 'data-fe-next' );
		window.clearTimeout( playTimer );
		if( isPlaying() && !next ) {
			store( 'sessionStorage', 'fe-play', null );
		}
		if( play ) {
			play.classList.toggle( 'is-on', isPlaying() );
		}
		if( isPlaying() && next ) {
			playTimer = window.setTimeout( function() { go( next ); }, PLAY_MS );
		}
	}

	// light or dark: remember the choice in a cookie and repaint without a reload
	document.addEventListener( 'click', function( pEvent ) {
		var choice = pEvent.target.closest ? pEvent.target.closest( '[data-fe-theme-set]' ) : null;
		if( !choice ) {
			return;
		}
		pEvent.preventDefault();
		var theme = choice.getAttribute( 'data-fe-theme-set' );
		var path = choice.closest( '[data-fe-theme]' ).getAttribute( 'data-fe-theme' ) || '/';
		document.cookie = 'fisheye_theme=' + theme + '; path=' + path + '; max-age=31536000; SameSite=Lax';
		Array.prototype.forEach.call( document.querySelectorAll( '[data-fe-themed], dialog.fe-lightbox' ), function( pRoot ) {
			pRoot.classList.toggle( 'fe-stage', theme == 'dark' );
		} );
		Array.prototype.forEach.call( document.querySelectorAll( '[data-fe-theme-set]' ), function( pLink ) {
			if( pLink.getAttribute( 'data-fe-theme-set' ) == theme ) {
				pLink.setAttribute( 'aria-current', 'true' );
			} else {
				pLink.removeAttribute( 'aria-current' );
			}
		} );
	} );

	function fragmentUrl( pUrl ) {
		return pUrl + ( pUrl.indexOf( '?' ) < 0 ? '?' : '&' ) + 'fragment=1';
	}

	// show another photo: in place inside the lightbox, as a page load otherwise
	function go( pUrl, pRefresh ) {
		if( !pUrl ) {
			return;
		}
		if( !lightbox || !lightbox.open || !window.fetch ) {
			window.location.href = pUrl;
			return;
		}
		lightbox.setAttribute( 'aria-busy', 'true' );
		fetch( fragmentUrl( pUrl ) + ( pRefresh ? '&refresh=1' : '' ), FETCH_OPTIONS ).then( function( pResponse ) {
			if( !pResponse.ok ) {
				throw new Error( pResponse.status );
			}
			return pResponse.text();
		} ).then( function( pHtml ) {
			if( pHtml.indexOf( 'data-fe-view' ) < 0 ) {
				throw new Error( 'not a photo' );
			}
			lightbox.innerHTML = pHtml;
			lightbox.removeAttribute( 'aria-busy' );
			// the address follows the photo, and Back still returns to the gallery in one step
			window.history.replaceState( { feLightbox: true }, '', pUrl );
			setupView();
		} ).catch( function() {
			window.location.href = pUrl;
		} );
	}

	function openLightbox( pUrl ) {
		if( !lightbox ) {
			lightbox = document.createElement( 'dialog' );
			// dark unless the viewer has chosen light
			lightbox.className = 'fe-app fe-lightbox' + ( /(?:^|;\s*)fisheye_theme=light(?:;|$)/.test( document.cookie ) ? '' : ' fe-stage' );
			document.body.appendChild( lightbox );
			lightbox.addEventListener( 'close', function() {
				window.clearTimeout( playTimer );
				store( 'sessionStorage', 'fe-play', null );
				lightbox.innerHTML = '';
				if( window.history.state && window.history.state.feLightbox ) {
					window.history.back();
				}
			} );
		}
		window.history.pushState( { feLightbox: true }, '', pUrl );
		lightbox.showModal();
		go( pUrl );
	}

	window.addEventListener( 'popstate', function() {
		if( lightbox && lightbox.open && !( window.history.state && window.history.state.feLightbox ) ) {
			lightbox.close();
		}
	} );

	document.addEventListener( 'click', function( pEvent ) {
		if( pEvent.defaultPrevented || pEvent.button || pEvent.metaKey || pEvent.ctrlKey || pEvent.shiftKey || pEvent.altKey || !pEvent.target.closest ) {
			return;
		}
		var link = pEvent.target.closest( 'a' );
		if( !link ) {
			return;
		}
		var view = link.closest( '[data-fe-view]' );
		if( !view ) {
			// a photo in a display mode opens in the lightbox
			if( link.closest( '[data-fe-lightbox]' ) && window.HTMLDialogElement && window.fetch && window.history.pushState ) {
				pEvent.preventDefault();
				openLightbox( link.href );
			}
			return;
		}
		var inLightbox = lightbox && lightbox.open;
		if( link.hasAttribute( 'data-fe-act' ) ) {
			pEvent.preventDefault();
			viewAction( view, link );
		} else if( link.hasAttribute( 'data-fe-rename' ) ) {
			pEvent.preventDefault();
			startRename( view );
		} else if( link.hasAttribute( 'data-fe-info-toggle' ) ) {
			pEvent.preventDefault();
			store( 'localStorage', 'fe-info', view.classList.contains( 'is-info-closed' ) ? 'open' : 'closed' );
			setupView();
		} else if( link.hasAttribute( 'data-fe-full' ) ) {
			pEvent.preventDefault();
			if( document.fullscreenElement ) {
				document.exitFullscreen();
			} else if( view.requestFullscreen ) {
				view.requestFullscreen().catch( function() {} );
			}
		} else if( link.hasAttribute( 'data-fe-play' ) ) {
			pEvent.preventDefault();
			setPlaying( !isPlaying() );
		} else if( link.hasAttribute( 'data-fe-close' ) && inLightbox ) {
			pEvent.preventDefault();
			lightbox.close();
		} else if( link.hasAttribute( 'data-fe-nav' ) && inLightbox ) {
			pEvent.preventDefault();
			go( link.href );
		}
	} );

	document.addEventListener( 'keydown', function( pEvent ) {
		var view = currentView();
		if( !view || pEvent.metaKey || pEvent.ctrlKey || pEvent.altKey || /^(INPUT|TEXTAREA|SELECT)$/.test( pEvent.target.tagName ) || pEvent.target.isContentEditable ) {
			return;
		}
		if( pEvent.key == 'ArrowLeft' ) {
			go( view.getAttribute( 'data-fe-prev' ) );
		} else if( pEvent.key == 'ArrowRight' ) {
			go( view.getAttribute( 'data-fe-next' ) );
		}
	} );

	// swipe between photos on touch screens
	var touchX = null;
	document.addEventListener( 'touchstart', function( pEvent ) {
		touchX = ( pEvent.touches.length == 1 && pEvent.target.closest && pEvent.target.closest( '.fe-view-img' ) ) ? pEvent.touches[0].clientX : null;
	}, { passive: true } );
	document.addEventListener( 'touchend', function( pEvent ) {
		var view = currentView();
		if( touchX === null || !view ) {
			return;
		}
		var moved = pEvent.changedTouches[0].clientX - touchX;
		touchX = null;
		if( Math.abs( moved ) > 60 ) {
			go( view.getAttribute( moved > 0 ? 'data-fe-prev' : 'data-fe-next' ) );
		}
	}, { passive: true } );

	setupView();

	// short notice at the foot of the screen; inside the lightbox when it is open, so it is not covered
	function say( pMessage, pIsError ) {
		if( !pMessage ) {
			return;
		}
		var old = document.querySelector( '.fe-toast' );
		if( old ) {
			old.parentNode.removeChild( old );
		}
		var toast = document.createElement( 'div' );
		toast.className = 'fe-toast' + ( pIsError ? ' is-error' : '' );
		toast.setAttribute( 'role', pIsError ? 'alert' : 'status' );
		toast.textContent = pMessage;
		var openDialog = document.querySelector( 'dialog[open]' );
		( openDialog || document.body ).appendChild( toast );
		window.setTimeout( function() {
			if( toast.parentNode ) {
				toast.parentNode.removeChild( toast );
			}
		}, pIsError ? 8000 : 4000 );
	}

	// a notice that should survive the page reload that follows an action
	function sayAfterReload( pMessage ) {
		store( 'sessionStorage', 'fe-notice', pMessage );
		window.location.reload();
	}

	// fill a gallery picker list; rows may carry nested children.
	// The gallery with pSkipId cannot be chosen: it is left out with everything nested in it,
	// or with pKeepNested it stays as a plain label so the galleries nested in it can still be chosen.
	function fillPicker( pList, pGalleries, pSkipId, pKeepNested ) {
		var add = function( pParent, pRows ) {
			pRows.forEach( function( pGallery ) {
				var skip = pSkipId && String( pGallery.gallery_id ) == String( pSkipId );
				if( skip && !( pKeepNested && pGallery.children && pGallery.children.length ) ) {
					return;
				}
				var item = document.createElement( 'li' );
				var link = document.createElement( skip ? 'span' : 'a' );
				if( skip ) {
					link.className = 'here';
				} else {
					link.href = '#';
					link.setAttribute( 'data-fe-destination', pGallery.gallery_id );
				}
				link.textContent = pGallery.title || ( '#' + pGallery.gallery_id );
				item.appendChild( link );
				if( pGallery.children && pGallery.children.length ) {
					var nested = document.createElement( 'ul' );
					add( nested, pGallery.children );
					if( nested.firstChild ) {
						item.appendChild( nested );
					}
				}
				pParent.appendChild( item );
			} );
		};
		pList.textContent = '';
		add( pList, pGalleries || [] );
		if( !pList.firstChild ) {
			var none = document.createElement( 'li' );
			none.className = 'none';
			none.textContent = pList.getAttribute( 'data-fe-none' );
			pList.appendChild( none );
		}
	}

	// Ask before something that cannot be undone. Resolves true or false. Falls back to the browser's own prompt.
	function ask( pMessage, pOkLabel, pCancelLabel ) {
		if( !window.HTMLDialogElement || !window.Promise ) {
			return { then: function( pDone ) { pDone( window.confirm( pMessage ) ); } };
		}
		return new Promise( function( pResolve ) {
			var box = document.createElement( 'dialog' );
			box.className = 'fe-app fe-confirm';
			var text = document.createElement( 'p' );
			text.textContent = pMessage;
			var foot = document.createElement( 'div' );
			foot.className = 'fe-confirm-foot';
			var cancel = document.createElement( 'button' );
			cancel.type = 'button';
			cancel.className = 'fe-btn';
			cancel.textContent = pCancelLabel || 'Cancel';
			var ok = document.createElement( 'button' );
			ok.type = 'button';
			ok.className = 'fe-btn fe-btn-danger';
			ok.textContent = pOkLabel || 'OK';
			foot.appendChild( cancel );
			foot.appendChild( ok );
			box.appendChild( text );
			box.appendChild( foot );
			document.body.appendChild( box );
			var answer = false;
			ok.addEventListener( 'click', function() { answer = true; box.close(); } );
			cancel.addEventListener( 'click', function() { box.close(); } );
			box.addEventListener( 'close', function() {
				box.parentNode.removeChild( box );
				pResolve( answer );
			} );
			box.showModal();
			cancel.focus();
		} );
	}

	// delete one photo from its edit form or its view: ask in a dialog, delete, then go to the gallery
	document.addEventListener( 'click', function( pEvent ) {
		var link = pEvent.target.closest ? pEvent.target.closest( '[data-fe-delete-photo]' ) : null;
		if( !link || !window.fetch || !window.URLSearchParams || pEvent.button || pEvent.metaKey || pEvent.ctrlKey ) {
			return;
		}
		pEvent.preventDefault();
		pEvent.stopPropagation();
		ask( link.getAttribute( 'data-fe-confirm' ), link.getAttribute( 'data-fe-confirm-ok' ), link.getAttribute( 'data-fe-confirm-cancel' ) ).then( function( pYes ) {
			if( !pYes ) {
				return;
			}
			link.setAttribute( 'aria-busy', 'true' );
			organize( link, 'delete', [ link.getAttribute( 'data-fe-delete-photo' ) ] ).then( function( pData ) {
				// the photo's own page is gone; show the notice on the gallery
				store( 'sessionStorage', 'fe-notice', pData.message );
				window.location.href = link.getAttribute( 'data-fe-after' );
			} ).catch( function( pError ) {
				link.removeAttribute( 'aria-busy' );
				say( pError.message, true );
			} );
		} );
	}, true );

	// "New gallery" in any gallery picker: create it, then choose it as if its row had been clicked
	function createFromPicker( pPicker ) {
		var name = pPicker.querySelector( '[data-fe-picker-name]' );
		var button = pPicker.querySelector( '[data-fe-picker-create]' );
		var title = name ? name.value.trim() : '';
		if( !title ) {
			if( name ) {
				name.focus();
			}
			return;
		}
		if( button.disabled ) {
			return;
		}
		button.disabled = true;
		organize( pPicker, 'newgallery', [], { title: title } ).then( function( pData ) {
			button.disabled = false;
			name.value = '';
			var list = pPicker.querySelector( '[data-fe-picker-list]' );
			fillPicker( list, [ pData.gallery ] );
			list.querySelector( '[data-fe-destination]' ).click();
		} ).catch( function( pError ) {
			button.disabled = false;
			say( pError.message, true );
		} );
	}
	document.addEventListener( 'click', function( pEvent ) {
		var create = pEvent.target.closest ? pEvent.target.closest( '[data-fe-picker-create]' ) : null;
		if( create ) {
			pEvent.preventDefault();
			createFromPicker( create.closest( '[data-fe-picker]' ) );
		}
	} );
	document.addEventListener( 'keydown', function( pEvent ) {
		if( pEvent.key == 'Enter' && pEvent.target.matches && pEvent.target.matches( '[data-fe-picker-name]' ) ) {
			pEvent.preventDefault();
			createFromPicker( pEvent.target.closest( '[data-fe-picker]' ) );
		}
	} );

	// post an organize action; pRoot carries the address, ticket and gallery
	function organize( pRoot, pAction, pItems, pExtra ) {
		var body = new URLSearchParams();
		body.append( 'action', pAction );
		body.append( 'tk', pRoot.getAttribute( 'data-fe-tk' ) );
		if( pRoot.getAttribute( 'data-fe-gallery' ) ) {
			body.append( 'gallery_id', pRoot.getAttribute( 'data-fe-gallery' ) );
		}
		( pItems || [] ).forEach( function( pId ) {
			body.append( 'items[]', pId );
		} );
		Object.keys( pExtra || {} ).forEach( function( pKey ) {
			body.append( pKey, pExtra[pKey] );
		} );
		return fetch( pRoot.getAttribute( 'data-fe-url' ), { method: 'POST', credentials: 'same-origin', headers: FETCH_OPTIONS.headers, body: body } ).then( function( pResponse ) {
			return pResponse.json().catch( function() {
				return { ok: false, message: 'The server did not answer as expected (' + pResponse.status + '). Reload the page and try again.' };
			} );
		} ).then( function( pData ) {
			if( !pData.ok ) {
				throw new Error( pData.message );
			}
			return pData;
		} );
	}

	// tools in the photo view that change the photo: rotate, set as cover
	function viewAction( pView, pLink ) {
		var action = pLink.getAttribute( 'data-fe-act' );
		var id = pView.getAttribute( 'data-fe-id' );
		if( !id || pView.hasAttribute( 'aria-busy' ) ) {
			return;
		}
		pView.setAttribute( 'aria-busy', 'true' );
		var extra = action == 'rotate' ? { degrees: pLink.getAttribute( 'data-fe-degrees' ) } : {};
		organize( pView, action, [ id ], extra ).then( function( pData ) {
			if( action == 'rotate' ) {
				// draw the view again with fresh image addresses
				var page = pView.getAttribute( 'data-fe-page' );
				if( lightbox && lightbox.open ) {
					go( page, true );
				} else {
					window.location.href = page + ( page.indexOf( '?' ) < 0 ? '?' : '&' ) + 'refresh=1';
				}
				return;
			}
			pView.removeAttribute( 'aria-busy' );
			if( action == 'cover' ) {
				pLink.classList.add( 'is-on' );
			}
			say( pData.message );
		} ).catch( function( pError ) {
			pView.removeAttribute( 'aria-busy' );
			say( pError.message, true );
		} );
	}

	// rename in place from the details panel: Enter saves, Escape or leaving the field cancels
	function startRename( pView ) {
		var title = pView.querySelector( '[data-fe-title]' );
		if( !title || title.querySelector( 'input' ) ) {
			return;
		}
		var before = title.textContent;
		var input = document.createElement( 'input' );
		input.type = 'text';
		input.maxLength = 160;
		input.value = before;
		title.textContent = '';
		title.appendChild( input );
		input.focus();
		input.select();
		var done = false;
		var finish = function( pText ) {
			if( !done ) {
				done = true;
				title.textContent = pText;
			}
		};
		input.addEventListener( 'keydown', function( pEvent ) {
			if( pEvent.key == 'Escape' ) {
				pEvent.stopPropagation();
				pEvent.preventDefault();
				finish( before );
			} else if( pEvent.key == 'Enter' ) {
				pEvent.preventDefault();
				var wanted = input.value.trim();
				if( !wanted || wanted == before ) {
					finish( before );
					return;
				}
				input.disabled = true;
				organize( pView, 'rename', [ pView.getAttribute( 'data-fe-id' ) ], { title: wanted } ).then( function( pData ) {
					finish( pData.title );
				} ).catch( function( pError ) {
					finish( before );
					say( pError.message, true );
				} );
			}
		} );
		input.addEventListener( 'blur', function() {
			if( !input.disabled ) {
				finish( before );
			}
		} );
	}

	// organize mode for editors: select, bulk actions, drag to reorder
	var grid = document.querySelector( '[data-fe-organize]' );
	var bulk = document.querySelector( '[data-fe-bulk]' );
	if( grid && bulk && window.fetch && window.URLSearchParams ) {
		var picker = document.querySelector( '[data-fe-picker]' );
		var lastPicked = null;
		var dragTiles = null;
		var dropAt = null;

		var allTiles = function() {
			return Array.prototype.slice.call( grid.querySelectorAll( '.fe-tile' ) );
		};
		var pickedTiles = function() {
			return allTiles().filter( function( pTile ) { return pTile.classList.contains( 'is-picked' ); } );
		};
		var idsOf = function( pTiles ) {
			return pTiles.map( function( pTile ) { return pTile.getAttribute( 'data-fe-id' ); } );
		};
		var setPicked = function( pTile, pOn ) {
			pTile.classList.toggle( 'is-picked', pOn );
			var mark = pTile.querySelector( '[data-fe-pick]' );
			if( mark ) {
				mark.setAttribute( 'aria-checked', pOn ? 'true' : 'false' );
			}
		};
		var refreshBulk = function() {
			var count = pickedTiles().length;
			bulk.hidden = !count;
			bulk.querySelector( '[data-fe-count]' ).textContent = count;
			grid.classList.toggle( 'has-picked', count > 0 );
			Array.prototype.forEach.call( bulk.querySelectorAll( '[data-fe-single]' ), function( pLink ) {
				pLink.setAttribute( 'aria-disabled', count == 1 ? 'false' : 'true' );
			} );
		};
		var clearPicked = function() {
			pickedTiles().forEach( function( pTile ) { setPicked( pTile, false ); } );
			lastPicked = null;
			refreshBulk();
		};
		var setBusy = function( pBusy ) {
			if( pBusy ) {
				grid.setAttribute( 'aria-busy', 'true' );
			} else {
				grid.removeAttribute( 'aria-busy' );
			}
		};

		// the select mark always selects; once something is selected a click anywhere on a tile does too
		grid.addEventListener( 'click', function( pEvent ) {
			var tile = pEvent.target.closest( '.fe-tile' );
			if( !tile || pEvent.button || pEvent.metaKey || pEvent.ctrlKey || pEvent.altKey ) {
				return;
			}
			var fav = pEvent.target.closest( '[data-fe-fav]' );
			if( fav ) {
				// the user's own favorite mark; no reload, no effect on the selection
				pEvent.preventDefault();
				var favOn = !fav.classList.contains( 'is-on' );
				organize( grid, 'favorite', idsOf( [ tile ] ), { on: favOn ? 1 : '' } ).then( function() {
					fav.classList.toggle( 'is-on', favOn );
					fav.setAttribute( 'aria-pressed', favOn ? 'true' : 'false' );
				} ).catch( function( pError ) {
					say( pError.message, true );
				} );
				return;
			}
			if( pEvent.target.closest( '[data-fe-coverset]' ) ) {
				pEvent.preventDefault();
				if( !tile.classList.contains( 'is-working' ) ) {
					run( 'cover', {}, [ tile ] );
				}
				return;
			}
			var turn = pEvent.target.closest( '[data-fe-turn]' );
			if( turn ) {
				// the tile's own rotate buttons act on that photo only and leave the selection alone
				pEvent.preventDefault();
				if( !tile.classList.contains( 'is-working' ) ) {
					run( 'rotate', { degrees: turn.getAttribute( 'data-fe-turn' ) }, [ tile ] );
				}
				return;
			}
			if( !pEvent.target.closest( '[data-fe-pick]' ) && !pickedTiles().length ) {
				return;
			}
			pEvent.preventDefault();
			var on = !tile.classList.contains( 'is-picked' );
			if( pEvent.shiftKey && lastPicked && lastPicked.parentNode == grid ) {
				// shift extends from the last tile touched
				var tiles = allTiles();
				var from = tiles.indexOf( lastPicked );
				var to = tiles.indexOf( tile );
				tiles.slice( Math.min( from, to ), Math.max( from, to ) + 1 ).forEach( function( pTile ) { setPicked( pTile, true ); } );
			} else {
				setPicked( tile, on );
			}
			lastPicked = tile;
			refreshBulk();
		} );

		document.addEventListener( 'keydown', function( pEvent ) {
			if( pEvent.key == 'Escape' && !document.querySelector( 'dialog[open]' ) && pickedTiles().length ) {
				clearPicked();
			}
		} );

		// run one action on the selection, or on the tiles given
		var run = function( pAction, pExtra, pTiles ) {
			var tiles = pTiles || pickedTiles();
			if( !tiles.length ) {
				return;
			}
			// a single tile shows its own progress; a bulk action holds the whole grid
			var setWorking = function( pOn ) {
				if( pTiles ) {
					pTiles.forEach( function( pTile ) { pTile.classList.toggle( 'is-working', pOn ); } );
				} else {
					setBusy( pOn );
				}
			};
			setWorking( true );
			organize( grid, pAction, idsOf( tiles ), pExtra ).then( function( pData ) {
				setWorking( false );
				if( pAction == 'rotate' ) {
					// a finished rotate lets go of the selection, so the next rotate cannot turn these again
					if( !pTiles ) {
						clearPicked();
					}
					if( pData.immediate ) {
						// same addresses, new pictures
						var stamp = Date.now();
						tiles.forEach( function( pTile ) {
							var image = pTile.querySelector( 'img' );
							if( pData.items.indexOf( parseInt( pTile.getAttribute( 'data-fe-id' ), 10 ) ) >= 0 ) {
								image.src = image.src.split( '?' )[0] + '?r=' + stamp;
							}
						} );
						say( pData.message );
					} else {
						sayAfterReload( pData.message );
					}
				} else if( pAction == 'cover' ) {
					allTiles().forEach( function( pTile ) { pTile.classList.toggle( 'is-cover', pTile == tiles[0] ); } );
					if( !pTiles ) {
						clearPicked();
					}
					say( pData.message );
				} else if( pAction == 'copy' ) {
					clearPicked();
					say( pData.message );
				} else {
					// move, remove and delete change what the gallery holds
					sayAfterReload( pData.message );
				}
			} ).catch( function( pError ) {
				setWorking( false );
				say( pError.message, true );
			} );
		};

		// destination dialog for move and copy
		var pickerAction = null;
		var pickerTimer = null;
		var loadPicker = function( pFind ) {
			var list = picker.querySelector( '[data-fe-picker-list]' );
			organize( grid, 'galleries', [], pFind ? { find: pFind } : {} ).then( function( pData ) {
				fillPicker( list, pData.galleries, grid.getAttribute( 'data-fe-gallery' ), true );
			} ).catch( function( pError ) {
				picker.close();
				say( pError.message, true );
			} );
		};
		if( picker && picker.showModal ) {
			picker.addEventListener( 'click', function( pEvent ) {
				var destination = pEvent.target.closest( '[data-fe-destination]' );
				if( destination ) {
					pEvent.preventDefault();
					picker.close();
					run( pickerAction, { destination: destination.getAttribute( 'data-fe-destination' ) } );
				} else if( pEvent.target.closest( '[data-fe-picker-cancel]' ) ) {
					picker.close();
				}
			} );
			picker.querySelector( '[data-fe-picker-find]' ).addEventListener( 'input', function( pEvent ) {
				window.clearTimeout( pickerTimer );
				var find = pEvent.target.value.trim();
				pickerTimer = window.setTimeout( function() { loadPicker( find ); }, 250 );
			} );
		}

		// the bar shows icons alone; the help button turns the names on, and the choice is remembered
		var bulkLabels = bulk.querySelector( '[data-fe-labels]' );
		var setBulkLabels = function( pOn ) {
			bulk.classList.toggle( 'has-labels', pOn );
			if( bulkLabels ) {
				bulkLabels.setAttribute( 'aria-pressed', pOn ? 'true' : 'false' );
			}
		};
		setBulkLabels( store( 'localStorage', 'fe-bulk-labels' ) == '1' );

		bulk.addEventListener( 'click', function( pEvent ) {
			if( pEvent.target.closest( '[data-fe-labels]' ) ) {
				pEvent.preventDefault();
				var on = !bulk.classList.contains( 'has-labels' );
				setBulkLabels( on );
				store( 'localStorage', 'fe-bulk-labels', on ? '1' : null );
				return;
			}
			var link = pEvent.target.closest( '[data-fe-act]' );
			if( !link ) {
				return;
			}
			pEvent.preventDefault();
			var action = link.getAttribute( 'data-fe-act' );
			if( link.getAttribute( 'aria-disabled' ) == 'true' || grid.hasAttribute( 'aria-busy' ) ) {
				return;
			}
			if( action == 'clear' ) {
				clearPicked();
			} else if( action == 'move' || action == 'copy' ) {
				if( picker && picker.showModal ) {
					pickerAction = action;
					var title = picker.querySelector( '[data-fe-picker-title]' );
					title.textContent = title.getAttribute( 'data-fe-' + action );
					picker.querySelector( '[data-fe-picker-find]' ).value = '';
					picker.showModal();
					loadPicker( '' );
				}
			} else if( link.hasAttribute( 'data-fe-confirm' ) ) {
				ask( link.getAttribute( 'data-fe-confirm' ), link.getAttribute( 'title' ), link.getAttribute( 'data-fe-confirm-cancel' ) ).then( function( pYes ) {
					if( pYes ) {
						run( action, {} );
					}
				} );
			} else {
				run( action, action == 'rotate' ? { degrees: link.getAttribute( 'data-fe-degrees' ) } : {} );
			}
		} );

		// drag to reorder, only while the gallery is shown in its own order
		if( grid.hasAttribute( 'data-fe-sortable' ) ) {
			var clearDrop = function() {
				Array.prototype.forEach.call( grid.querySelectorAll( '.is-drop-before,.is-drop-after' ), function( pTile ) {
					pTile.classList.remove( 'is-drop-before', 'is-drop-after' );
				} );
				dropAt = null;
			};
			var renumber = function() {
				var numbers = allTiles().map( function( pTile ) { return pTile.querySelector( '.fe-num' ); } );
				var first = Math.min.apply( null, numbers.map( function( pNumber ) { return parseInt( pNumber.textContent, 10 ) || 1; } ) );
				numbers.forEach( function( pNumber, pIndex ) { pNumber.textContent = first + pIndex; } );
			};
			grid.addEventListener( 'dragstart', function( pEvent ) {
				var tile = pEvent.target.closest ? pEvent.target.closest( '.fe-tile' ) : null;
				if( !tile ) {
					return;
				}
				// dragging a selected photo takes the whole selection with it
				dragTiles = tile.classList.contains( 'is-picked' ) ? pickedTiles() : [ tile ];
				dragTiles.forEach( function( pTile ) { pTile.classList.add( 'is-dragging' ); } );
				pEvent.dataTransfer.effectAllowed = 'move';
				try {
					pEvent.dataTransfer.setData( 'text/plain', tile.href );
				} catch( e ) {}
			} );
			grid.addEventListener( 'dragover', function( pEvent ) {
				if( !dragTiles ) {
					return;
				}
				var tile = pEvent.target.closest ? pEvent.target.closest( '.fe-tile' ) : null;
				clearDrop();
				if( !tile || dragTiles.indexOf( tile ) >= 0 ) {
					return;
				}
				pEvent.preventDefault();
				pEvent.dataTransfer.dropEffect = 'move';
				var box = tile.getBoundingClientRect();
				var before = pEvent.clientX < box.left + box.width / 2;
				tile.classList.add( before ? 'is-drop-before' : 'is-drop-after' );
				dropAt = { tile: tile, before: before };
			} );
			grid.addEventListener( 'drop', function( pEvent ) {
				if( !dragTiles || !dropAt ) {
					return;
				}
				pEvent.preventDefault();
				var moving = dragTiles;
				var target = dropAt.tile;
				var before = dropAt.before;
				clearDrop();
				setBusy( true );
				var extra = {};
				extra[before ? 'before' : 'after'] = target.getAttribute( 'data-fe-id' );
				organize( grid, 'reorder', idsOf( moving ), extra ).then( function() {
					var anchor = before ? target : target.nextElementSibling;
					while( anchor && moving.indexOf( anchor ) >= 0 ) {
						anchor = anchor.nextElementSibling;
					}
					moving.forEach( function( pTile ) { grid.insertBefore( pTile, anchor ); } );
					renumber();
				} ).catch( function( pError ) {
					say( pError.message, true );
				} ).then( function() {
					setBusy( false );
				} );
			} );
			grid.addEventListener( 'dragend', function() {
				if( dragTiles ) {
					dragTiles.forEach( function( pTile ) { pTile.classList.remove( 'is-dragging' ); } );
				}
				dragTiles = null;
				clearDrop();
			} );
		}
	}

	// a notice left by an action that reloaded the page
	say( store( 'sessionStorage', 'fe-notice' ) );
	store( 'sessionStorage', 'fe-notice', null );

	// upload page: every dropped or chosen file is sent on its own, one after another, with a progress row
	var upForm = document.querySelector( 'form.fe-upload' );
	var upInput = upForm && upForm.querySelector( '[data-fe-files]' );
	if( upInput && window.FormData && window.XMLHttpRequest ) {
		var upBox = upForm.querySelector( '[data-fe-uploadlist]' );
		var upList = upForm.querySelector( '[data-fe-filelist]' );
		var upDrop = upForm.querySelector( '[data-fe-drop]' );
		var upDone = upForm.querySelector( '[data-fe-uploaddone]' );
		var upMax = parseInt( upInput.getAttribute( 'data-fe-max' ), 10 ) || 0;
		var upQueue = [];
		var upBusy = false;
		var upCount = { total: 0, added: 0, skipped: 0, failed: 0 };
		var upText = function( pKey ) {
			return upBox.getAttribute( 'data-fe-' + pKey );
		};
		var upSize = function( pBytes ) {
			return pBytes >= 1048576 ? ( pBytes / 1048576 ).toFixed( 1 ) + ' MB' : Math.max( 1, Math.round( pBytes / 1024 ) ) + ' KB';
		};
		var upSummary = function() {
			var text = upCount.added + ' ' + upText( 'of' ) + ' ' + upCount.total + ' ' + upText( 'added' );
			if( upCount.skipped ) {
				text += ', ' + upCount.skipped + ' ' + upText( 'skipped' );
			}
			if( upCount.failed ) {
				text += ', ' + upCount.failed + ' ' + upText( 'failed' );
			}
			upForm.querySelector( '[data-fe-uploadsummary]' ).textContent = text;
			var finished = !upBusy && !upQueue.length;
			upDone.hidden = !( finished && upCount.added );
			if( finished && upCount.added && !upCount.failed && upDone.getAttribute( 'data-fe-oncomplete' ) == 'refreshparent' && window.opener ) {
				// opened as a helper window: hand back to the page that opened it
				try {
					window.opener.location.reload( true );
					window.close();
				} catch( e ) {}
			}
		};
		var upRow = function( pFile ) {
			var row = document.createElement( 'li' );
			row.innerHTML = '<span class="fa fal fa-image" aria-hidden="true"></span><span class="t"></span><span class="m"></span><span class="p"><i></i></span><span class="s"></span>';
			row.querySelector( '.t' ).textContent = pFile.name;
			row.querySelector( '.m' ).textContent = upSize( pFile.size );
			upList.appendChild( row );
			return row;
		};
		var upState = function( pRow, pClass, pStatus, pPercent ) {
			pRow.className = pClass;
			pRow.querySelector( '.fa' ).className = 'fa fal ' + ( pClass == 'ok' ? 'fa-check-circle' : ( pClass == 'bad' ? 'fa-exclamation-circle' : 'fa-image' ) );
			pRow.querySelector( '.s' ).textContent = pStatus;
			if( pPercent !== undefined ) {
				pRow.querySelector( '.p i' ).style.width = pPercent + '%';
			}
		};
		var upNext = function() {
			if( upBusy || !upQueue.length ) {
				upSummary();
				return;
			}
			upBusy = true;
			var job = upQueue.shift();
			// the form's own fields ride along with every file: target gallery, options, ticket
			var data = new FormData( upForm );
			data.append( 'file0', job.file, job.file.name );
			// how many files are still to go, this one included; a lone photo gets its thumbnails made at once
			data.append( 'fe_pending', upQueue.length + 1 );
			var request = new XMLHttpRequest();
			request.open( 'POST', upForm.getAttribute( 'action' ) );
			request.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
			request.upload.addEventListener( 'progress', function( pEvent ) {
				if( pEvent.lengthComputable ) {
					var percent = Math.round( pEvent.loaded / pEvent.total * 100 );
					upState( job.row, '', percent < 100 ? upText( 's-sending' ) + ' ' + percent + '%' : upText( 's-working' ), percent );
				}
			} );
			var finish = function( pOk, pMessage ) {
				if( pOk ) {
					upCount.added++;
					upState( job.row, 'ok', upText( 's-done' ), 100 );
				} else {
					upCount.failed++;
					upState( job.row, 'bad', pMessage || upText( 's-error' ) );
				}
				upBusy = false;
				upNext();
			};
			request.addEventListener( 'load', function() {
				var answer = null;
				try {
					answer = JSON.parse( request.responseText );
				} catch( e ) {}
				if( answer && answer.ok ) {
					finish( true );
				} else {
					finish( false, answer && answer.errors && answer.errors.length ? answer.errors.join( ' ' ) : upText( 's-error' ) + ' (' + request.status + ')' );
				}
			} );
			request.addEventListener( 'error', function() { finish( false ); } );
			request.addEventListener( 'abort', function() { finish( false ); } );
			upState( job.row, '', upText( 's-sending' ), 0 );
			request.send( data );
			upSummary();
		};
		var upAdd = function( pFiles ) {
			// by name, so a set of numbered files lands in the gallery in order
			var files = Array.prototype.slice.call( pFiles ).sort( function( pA, pB ) {
				return pA.name.localeCompare( pB.name, undefined, { numeric: true } );
			} );
			if( !files.length ) {
				return;
			}
			upBox.hidden = false;
			files.forEach( function( pFile ) {
				var row = upRow( pFile );
				upCount.total++;
				if( !pFile.size ) {
					upCount.skipped++;
					upState( row, 'bad', upText( 's-empty' ) );
				} else if( upMax && pFile.size > upMax ) {
					upCount.skipped++;
					upState( row, 'bad', upText( 's-large' ) );
				} else {
					upState( row, '', upText( 's-waiting' ), 0 );
					upQueue.push( { file: pFile, row: row } );
				}
			} );
			upNext();
		};
		upInput.addEventListener( 'change', function() {
			upAdd( upInput.files );
			upInput.value = '';
		} );
		// a file dropped beside the zone must not make the browser open it and leave the page
		[ 'dragenter', 'dragover', 'dragleave', 'drop' ].forEach( function( pType ) {
			window.addEventListener( pType, function( pEvent ) {
				if( !pEvent.dataTransfer || Array.prototype.indexOf.call( pEvent.dataTransfer.types || [], 'Files' ) < 0 ) {
					return;
				}
				pEvent.preventDefault();
				var over = upDrop.contains( pEvent.target );
				upDrop.classList.toggle( 'is-over', over && ( pType == 'dragenter' || pType == 'dragover' ) );
				if( pType == 'drop' && over ) {
					upAdd( pEvent.dataTransfer.files );
				}
			} );
		} );
		window.addEventListener( 'beforeunload', function( pEvent ) {
			if( upBusy || upQueue.length ) {
				pEvent.preventDefault();
				pEvent.returnValue = upText( 'leave' );
			}
		} );

		// choose another gallery to add to
		var upPicker = document.querySelector( '.fe-uploadpage [data-fe-picker]' );
		var upChange = upForm.querySelector( '[data-fe-target-change]' );
		if( upPicker && upPicker.showModal && upChange && window.fetch && window.URLSearchParams ) {
			upChange.hidden = false;
			var upPickerTimer = null;
			var upLoadPicker = function( pFind ) {
				var list = upPicker.querySelector( '[data-fe-picker-list]' );
				organize( upPicker, 'galleries', [], pFind ? { find: pFind } : {} ).then( function( pData ) {
					fillPicker( list, pData.galleries );
				} ).catch( function( pError ) {
					upPicker.close();
					say( pError.message, true );
				} );
			};
			upChange.addEventListener( 'click', function( pEvent ) {
				pEvent.preventDefault();
				upPicker.querySelector( '[data-fe-picker-find]' ).value = '';
				upPicker.showModal();
				upLoadPicker( '' );
			} );
			upPicker.querySelector( '[data-fe-picker-find]' ).addEventListener( 'input', function( pEvent ) {
				window.clearTimeout( upPickerTimer );
				var find = pEvent.target.value.trim();
				upPickerTimer = window.setTimeout( function() { upLoadPicker( find ); }, 250 );
			} );
			upPicker.addEventListener( 'click', function( pEvent ) {
				var destination = pEvent.target.closest( '[data-fe-destination]' );
				if( destination ) {
					pEvent.preventDefault();
					var id = destination.getAttribute( 'data-fe-destination' );
					var url = upPicker.getAttribute( 'data-fe-gallery-url' ) + id;
					Array.prototype.forEach.call( upForm.querySelectorAll( '[data-fe-target-id]' ), function( pField ) { pField.value = id; } );
					upForm.querySelector( '[data-fe-target-title]' ).textContent = destination.textContent;
					var none = upForm.querySelector( '[data-fe-target-none]' );
					if( none ) {
						none.hidden = true;
					}
					var crumb = document.querySelector( '[data-fe-target-link]' );
					if( crumb ) {
						crumb.textContent = destination.textContent;
						crumb.href = url;
					}
					upDone.href = url;
					upPicker.close();
				} else if( pEvent.target.closest( '[data-fe-picker-cancel]' ) ) {
					upPicker.close();
				}
			} );
		}
	}

	// photo details: rotate, refresh previews and reduce act at once; replacing the file asks first
	var photoEdit = document.querySelector( '[data-fe-photo]' );
	if( photoEdit && window.fetch && window.URLSearchParams ) {
		photoEdit.classList.add( 'has-script' );
		Array.prototype.forEach.call( photoEdit.querySelectorAll( '[data-fe-photo-tools]' ), function( pTools ) { pTools.hidden = false; } );
		var photoSize = photoEdit.querySelector( '[data-fe-photo-size]' );
		var showPhotoSize = function( pWidth, pHeight ) {
			if( photoSize && pWidth && pHeight ) {
				photoSize.setAttribute( 'data-fe-w', pWidth );
				photoSize.setAttribute( 'data-fe-h', pHeight );
				photoSize.textContent = pWidth + ' \u00d7 ' + pHeight;
			}
		};
		photoEdit.addEventListener( 'click', function( pEvent ) {
			var button = pEvent.target.closest( '[data-fe-photo-act]' );
			if( !button || photoEdit.hasAttribute( 'aria-busy' ) ) {
				return;
			}
			var confirmText = button.getAttribute( 'data-fe-confirm' );
			if( confirmText && !button.feAsked ) {
				// ask in the page's own dialog, then come back through this handler
				ask( confirmText, button.textContent.trim(), button.getAttribute( 'data-fe-confirm-cancel' ) ).then( function( pYes ) {
					if( pYes ) {
						button.feAsked = true;
						button.click();
					}
				} );
				return;
			}
			button.feAsked = false;
			var action = button.getAttribute( 'data-fe-photo-act' );
			var extra = {};
			if( action == 'rotate' ) {
				extra.degrees = button.getAttribute( 'data-fe-degrees' );
			} else if( action == 'resize' ) {
				extra.size = photoEdit.querySelector( '[data-fe-photo-resize]' ).value;
			}
			photoEdit.setAttribute( 'aria-busy', 'true' );
			organize( photoEdit, action, [ photoEdit.getAttribute( 'data-fe-id' ) ], extra ).then( function( pData ) {
				photoEdit.removeAttribute( 'aria-busy' );
				// same address, new picture
				var image = photoEdit.querySelector( '[data-fe-photo-img]' );
				if( image && pData.immediate !== false ) {
					image.src = image.src.split( '?' )[0] + '?r=' + Date.now();
				}
				if( action == 'rotate' && photoSize && extra.degrees != '180' ) {
					showPhotoSize( photoSize.getAttribute( 'data-fe-h' ), photoSize.getAttribute( 'data-fe-w' ) );
				} else if( action == 'resize' ) {
					showPhotoSize( pData.width, pData.height );
				}
				say( pData.message );
			} ).catch( function( pError ) {
				photoEdit.removeAttribute( 'aria-busy' );
				say( pError.message, true );
			} );
		} );
		var replaceInput = photoEdit.querySelector( '[data-fe-replace]' );
		var replaceBox = photoEdit.querySelector( '[data-fe-replace-confirm]' );
		if( replaceInput && replaceBox ) {
			replaceInput.addEventListener( 'change', function() {
				var file = replaceInput.files && replaceInput.files[0];
				replaceBox.hidden = !file;
				if( file ) {
					replaceBox.querySelector( '[data-fe-replace-name]' ).textContent = file.name;
				}
			} );
			replaceBox.querySelector( '[data-fe-replace-cancel]' ).addEventListener( 'click', function() {
				replaceInput.value = '';
				replaceBox.hidden = true;
			} );
		}
	}

	// gallery settings: choose the galleries this one sits inside
	var parentList = document.querySelector( '.fe-settings [data-fe-parents]' );
	var parentPicker = document.querySelector( '.fe-settings [data-fe-picker]' );
	var parentAdd = document.querySelector( '.fe-settings [data-fe-parent-add]' );
	var parentRow = document.querySelector( '.fe-settings [data-fe-parent-row]' );
	if( parentList && parentAdd && parentRow && parentRow.content && parentPicker && parentPicker.showModal && window.fetch && window.URLSearchParams ) {
		parentAdd.hidden = false;
		var parentTimer = null;
		var loadParents = function( pFind ) {
			organize( parentPicker, 'galleries', [], pFind ? { find: pFind } : {} ).then( function( pData ) {
				fillPicker( parentPicker.querySelector( '[data-fe-picker-list]' ), pData.galleries, parentList.getAttribute( 'data-fe-self' ) );
			} ).catch( function( pError ) {
				parentPicker.close();
				say( pError.message, true );
			} );
		};
		parentAdd.addEventListener( 'click', function() {
			parentPicker.querySelector( '[data-fe-picker-find]' ).value = '';
			parentPicker.showModal();
			loadParents( '' );
		} );
		parentPicker.querySelector( '[data-fe-picker-find]' ).addEventListener( 'input', function( pEvent ) {
			window.clearTimeout( parentTimer );
			var find = pEvent.target.value.trim();
			parentTimer = window.setTimeout( function() { loadParents( find ); }, 250 );
		} );
		parentPicker.addEventListener( 'click', function( pEvent ) {
			var destination = pEvent.target.closest( '[data-fe-destination]' );
			if( destination ) {
				pEvent.preventDefault();
				var id = destination.getAttribute( 'data-fe-destination' );
				var box = null;
				Array.prototype.forEach.call( parentList.querySelectorAll( 'input[type=checkbox]' ), function( pBox ) {
					if( pBox.value == id ) {
						box = pBox;
					}
				} );
				if( box ) {
					box.checked = true;
				} else {
					var row = parentRow.content.cloneNode( true );
					row.querySelector( 'input' ).value = id;
					// the link holds only this gallery's own title; nested ones are in a list beside it
					row.querySelector( '[data-fe-parent-title]' ).textContent = destination.textContent;
					parentList.appendChild( row );
				}
				var none = document.querySelector( '.fe-settings [data-fe-parents-none]' );
				if( none ) {
					none.hidden = true;
				}
				parentPicker.close();
			} else if( pEvent.target.closest( '[data-fe-picker-cancel]' ) ) {
				parentPicker.close();
			}
		} );
	}

	// image order form: renumber, select all, and a mark on what has been changed
	var orderForm = document.querySelector( '.fe-orderform' );
	if( orderForm ) {
		var dirty = orderForm.querySelector( '[data-fe-dirty]' );
		var markChanged = function( pInput ) {
			if( pInput.type == 'text' ) {
				pInput.classList.toggle( 'is-changed', pInput.value != pInput.defaultValue );
			}
			if( dirty ) {
				dirty.hidden = false;
			}
		};
		orderForm.addEventListener( 'input', function( pEvent ) { markChanged( pEvent.target ); } );
		orderForm.addEventListener( 'change', function( pEvent ) {
			var all = pEvent.target.getAttribute( 'data-fe-checkall' );
			if( all ) {
				Array.prototype.forEach.call( orderForm.querySelectorAll( 'input[name="' + all + '"]' ), function( pBox ) { pBox.checked = pEvent.target.checked; } );
			} else {
				markChanged( pEvent.target );
			}
		} );
		orderForm.addEventListener( 'click', function( pEvent ) {
			if( pEvent.target.closest && pEvent.target.closest( '[data-fe-renumber]' ) ) {
				// positions 10, 20, 30 in the order the rows are shown; nothing is stored until Save
				Array.prototype.forEach.call( orderForm.querySelectorAll( '[data-fe-position]' ), function( pInput, pIndex ) {
					pInput.value = ( pIndex + 1 ) * 10;
					markChanged( pInput );
				} );
			}
		} );
		// Drag a row by its handle. The position numbers stay in their places on the page and are handed
		// to the rows in their new order, so a row dragged from 40 to 10 pushes 10, 20 and 30 down one place.
		var orderRows = function() {
			return Array.prototype.slice.call( orderForm.querySelectorAll( 'tr[data-fe-row]' ) );
		};
		var positionOf = function( pRow ) {
			return pRow.querySelector( '[data-fe-position]' );
		};
		// the numbers as they stand, top to bottom; empty boxes are numbered on after the highest first
		var readSlots = function() {
			var inputs = orderRows().map( positionOf );
			var highest = 0;
			inputs.forEach( function( pInput ) {
				var value = parseFloat( pInput.value );
				if( !isNaN( value ) && value > highest ) {
					highest = value;
				}
			} );
			return inputs.map( function( pInput ) {
				if( pInput.value.trim() === '' ) {
					highest = Math.floor( highest / 10 ) * 10 + 10;
					return String( highest );
				}
				return pInput.value;
			} );
		};
		var writeSlots = function( pSlots ) {
			orderRows().forEach( function( pRow, pIndex ) {
				var input = positionOf( pRow );
				if( input.value != pSlots[pIndex] ) {
					input.value = pSlots[pIndex];
				}
				markChanged( input );
			} );
		};
		var moving = null;
		var movingSlots = null;
		var pointerY = 0;
		var scrollTimer = null;
		// put the moving row where the pointer is
		var placeMoving = function() {
			var rows = orderRows();
			for( var i = 0; i < rows.length; i++ ) {
				if( rows[i] == moving ) {
					continue;
				}
				var box = rows[i].getBoundingClientRect();
				if( pointerY >= box.top && pointerY < box.bottom ) {
					var after = pointerY > box.top + box.height / 2;
					rows[i].parentNode.insertBefore( moving, after ? rows[i].nextSibling : rows[i] );
					break;
				}
			}
		};
		var endMove = function() {
			if( !moving ) {
				return;
			}
			window.clearInterval( scrollTimer );
			moving.classList.remove( 'is-moving' );
			orderForm.querySelector( '.fe-ordertable' ).classList.remove( 'is-sorting' );
			moving = null;
			writeSlots( movingSlots );
		};
		orderForm.addEventListener( 'pointerdown', function( pEvent ) {
			var grip = pEvent.target.closest ? pEvent.target.closest( '[data-fe-grip]' ) : null;
			if( !grip || pEvent.button ) {
				return;
			}
			pEvent.preventDefault();
			grip.focus();
			moving = grip.closest( 'tr' );
			movingSlots = readSlots();
			pointerY = pEvent.clientY;
			moving.classList.add( 'is-moving' );
			orderForm.querySelector( '.fe-ordertable' ).classList.add( 'is-sorting' );
			// keep rows coming while the pointer rests near the top or bottom edge
			scrollTimer = window.setInterval( function() {
				var edge = 110;
				var step = pointerY < edge ? -18 : ( pointerY > window.innerHeight - edge ? 18 : 0 );
				if( step ) {
					window.scrollBy( 0, step );
					placeMoving();
				}
			}, 30 );
		} );
		document.addEventListener( 'pointermove', function( pEvent ) {
			if( moving ) {
				pointerY = pEvent.clientY;
				placeMoving();
			}
		} );
		document.addEventListener( 'pointerup', endMove );
		document.addEventListener( 'pointercancel', endMove );
		// the same move from the keyboard: arrows on a focused handle
		orderForm.addEventListener( 'keydown', function( pEvent ) {
			var grip = pEvent.target.closest ? pEvent.target.closest( '[data-fe-grip]' ) : null;
			if( !grip || ( pEvent.key != 'ArrowUp' && pEvent.key != 'ArrowDown' ) ) {
				return;
			}
			pEvent.preventDefault();
			var rows = orderRows();
			var row = grip.closest( 'tr' );
			var other = rows[rows.indexOf( row ) + ( pEvent.key == 'ArrowUp' ? -1 : 1 )];
			if( other ) {
				var slots = readSlots();
				row.parentNode.insertBefore( row, pEvent.key == 'ArrowUp' ? other : other.nextSibling );
				writeSlots( slots );
				grip.focus();
			}
		} );

		// Enter in a field moves to the same field of the next row instead of saving half-typed work
		orderForm.addEventListener( 'keydown', function( pEvent ) {
			if( pEvent.key == 'Enter' && pEvent.target.type == 'text' ) {
				pEvent.preventDefault();
				var row = pEvent.target.closest( 'tr' );
				var cell = pEvent.target.closest( 'td' );
				var next = row && row.nextElementSibling;
				while( next && !next.cells[cell.cellIndex] ) {
					next = next.nextElementSibling;
				}
				var field = next && next.cells[cell.cellIndex] && next.cells[cell.cellIndex].querySelector( 'input[type=text]' );
				if( field ) {
					field.focus();
					field.select();
				}
			}
		} );
	}

	// rail tree: nested galleries are fetched the first time an item is opened; the heading control opens everything
	var railFetch = function( pRail, pQuery ) {
		return fetch( pRail.getAttribute( 'data-fe-rail-url' ) + pQuery, FETCH_OPTIONS ).then( function( pResponse ) {
			if( !pResponse.ok ) {
				throw new Error( pResponse.status );
			}
			return pResponse.text();
		} );
	};
	var setNode = function( pNode, pOpen ) {
		pNode.classList.toggle( 'is-open', pOpen );
		var twist = pNode.querySelector( '[data-fe-twist]' );
		if( twist ) {
			twist.setAttribute( 'aria-expanded', pOpen ? 'true' : 'false' );
		}
	};
	document.addEventListener( 'click', function( pEvent ) {
		if( pEvent.defaultPrevented || pEvent.button || pEvent.metaKey || pEvent.ctrlKey || pEvent.shiftKey || pEvent.altKey || !pEvent.target.closest || !window.fetch ) {
			return;
		}
		var rail = pEvent.target.closest( '[data-fe-rail-url]' );
		var twist = rail && pEvent.target.closest( '[data-fe-twist]' );
		var all = rail && pEvent.target.closest( '[data-fe-rail-all]' );
		if( twist ) {
			pEvent.preventDefault();
			var node = twist.closest( '[data-fe-node]' );
			var nested = null;
			Array.prototype.forEach.call( node.children, function( pChild ) {
				if( pChild.tagName == 'UL' ) {
					nested = pChild;
				}
			} );
			if( nested ) {
				setNode( node, !node.classList.contains( 'is-open' ) );
			} else if( !twist.hasAttribute( 'aria-busy' ) ) {
				twist.setAttribute( 'aria-busy', 'true' );
				railFetch( rail, node.getAttribute( 'data-fe-node' ) + '&rail=1' ).then( function( pHtml ) {
					var holder = document.createElement( 'ul' );
					holder.innerHTML = pHtml;
					node.appendChild( holder );
					twist.removeAttribute( 'aria-busy' );
					setNode( node, true );
				} ).catch( function() {
					// the twist is also a plain link to the gallery
					window.location.href = twist.href;
				} );
			}
		} else if( all ) {
			pEvent.preventDefault();
			var tree = rail.querySelector( '[data-fe-tree]' );
			var setAll = function( pOpen ) {
				Array.prototype.forEach.call( tree.querySelectorAll( '[data-fe-node]' ), function( pNode ) { setNode( pNode, pOpen ); } );
				all.setAttribute( 'aria-pressed', pOpen ? 'true' : 'false' );
				all.title = all.getAttribute( pOpen ? 'data-fe-collapse' : 'data-fe-expand' );
			};
			if( all.getAttribute( 'aria-pressed' ) == 'true' ) {
				setAll( false );
			} else if( tree.hasAttribute( 'data-fe-full' ) ) {
				setAll( true );
			} else if( !all.hasAttribute( 'aria-busy' ) ) {
				all.setAttribute( 'aria-busy', 'true' );
				railFetch( rail, tree.getAttribute( 'data-fe-tree' ) + '&rail=all' ).then( function( pHtml ) {
					tree.innerHTML = pHtml;
					tree.setAttribute( 'data-fe-full', '' );
					setAll( true );
				} ).catch( function() {} ).then( function() {
					all.removeAttribute( 'aria-busy' );
				} );
			}
		}
	} );

	var more = document.querySelector( '[data-fe-more]' );
	var list = document.querySelector( '[data-fe-items]' );
	if( !more || !list || !window.fetch ) {
		return;
	}

	var page = parseInt( more.getAttribute( 'data-fe-page' ), 10 );
	var pages = parseInt( more.getAttribute( 'data-fe-pages' ), 10 );
	var busy = false;
	var failed = false;

	function pageUrl( pPage ) {
		return more.href.replace( /([?&])page=\d+/, '$1page=' + pPage );
	}

	function loadMore() {
		if( busy || failed || page > pages ) {
			return;
		}
		busy = true;
		more.setAttribute( 'aria-busy', 'true' );
		fetch( pageUrl( page ) + '&fragment=1', FETCH_OPTIONS ).then( function( pResponse ) {
			if( !pResponse.ok ) {
				throw new Error( pResponse.status );
			}
			return pResponse.text();
		} ).then( function( pHtml ) {
			// a <template> parses table rows correctly outside of a table
			var holder = document.createElement( 'template' );
			holder.innerHTML = pHtml;
			list.appendChild( holder.content );
			page++;
			if( page > pages ) {
				more.parentNode.removeChild( more );
			} else {
				more.href = pageUrl( page );
			}
		} ).catch( function() {
			// leave the control as a plain link to the next page
			failed = true;
		} ).then( function() {
			busy = false;
			more.removeAttribute( 'aria-busy' );
		} );
	}

	more.addEventListener( 'click', function( pEvent ) {
		if( !failed ) {
			pEvent.preventDefault();
			loadMore();
		}
	} );

	if( window.IntersectionObserver ) {
		new IntersectionObserver( function( pEntries ) {
			if( pEntries[0].isIntersecting ) {
				loadMore();
			}
		}, { rootMargin: '600px' } ).observe( more );
	}
})();
