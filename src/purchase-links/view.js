( function () {
	const closeAll = () => {
		document
			.querySelectorAll( '.book-cpt-purchase-links.is-open' )
			.forEach( ( block ) => {
				block.classList.remove( 'is-open' );
				const toggle = block.querySelector(
					'.book-cpt-purchase-links__toggle'
				);
				const menu = block.querySelector(
					'.book-cpt-purchase-links__menu'
				);
				if ( toggle ) {
					toggle.setAttribute( 'aria-expanded', 'false' );
				}
				if ( menu ) {
					menu.hidden = true;
				}
			} );
	};

	const getCheckoutUrl = ( block ) =>
		block.getAttribute( 'data-sdd-checkout-url' ) ||
		'/wp-json/simple-digital-downloads/v1/checkout';

	const showSddError = ( block, message ) => {
		const errorEl = block.querySelector(
			'.book-cpt-purchase-links__sdd-error'
		);
		if ( ! errorEl ) {
			window.alert( message );
			return;
		}

		errorEl.textContent = message;
		errorEl.hidden = false;
	};

	const hideSddError = ( block ) => {
		const errorEl = block.querySelector(
			'.book-cpt-purchase-links__sdd-error'
		);
		if ( errorEl ) {
			errorEl.hidden = true;
		}
	};

	const setSddOverlayState = ( block, open, downloadId ) => {
		const overlay = block.querySelector(
			'.book-cpt-purchase-links__sdd-overlay'
		);
		if ( ! overlay ) {
			return;
		}

		overlay.hidden = ! open;
		overlay.setAttribute( 'aria-hidden', open ? 'false' : 'true' );
		if ( open && downloadId ) {
			overlay.setAttribute( 'data-download-id', downloadId );
		}

		document.body.classList.toggle(
			'book-cpt-sdd-modal-open',
			open
		);
	};

	const handlePaidCheckout = ( button, block ) => {
		const downloadId = button.getAttribute( 'data-download-id' );
		if ( ! downloadId ) {
			return;
		}

		hideSddError( block );
		button.disabled = true;

		fetch( getCheckoutUrl( block ), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( {
				download_id: parseInt( downloadId, 10 ),
				cancel_url: window.location.href,
			} ),
		} )
			.then( ( response ) =>
				response.json().then( ( data ) => ( {
					ok: response.ok,
					data,
				} ) )
			)
			.then( ( result ) => {
				if ( result.ok && result.data.checkout_url ) {
					window.location.href = result.data.checkout_url;
					return;
				}

				throw new Error(
					result.data.message || 'Checkout failed.'
				);
			} )
			.catch( ( error ) => {
				button.disabled = false;
				showSddError(
					block,
					error.message ||
						'Something went wrong. Please try again.'
				);
			} );
	};

	const handleFreeCheckout = ( block ) => {
		const overlay = block.querySelector(
			'.book-cpt-purchase-links__sdd-overlay'
		);
		const emailInput = block.querySelector(
			'.book-cpt-purchase-links__sdd-email'
		);
		const submitButton = block.querySelector(
			'.book-cpt-purchase-links__sdd-submit'
		);
		const downloadId = overlay?.getAttribute( 'data-download-id' );

		if ( ! overlay || ! emailInput || ! downloadId ) {
			return;
		}

		const email = emailInput.value.trim();
		if ( ! email || ! email.includes( '@' ) ) {
			showSddError(
				block,
				'Please enter a valid email address.'
			);
			return;
		}

		hideSddError( block );
		if ( submitButton ) {
			submitButton.disabled = true;
		}

		fetch( getCheckoutUrl( block ), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify( {
				download_id: parseInt( downloadId, 10 ),
				email,
			} ),
		} )
			.then( ( response ) =>
				response.json().then( ( data ) => ( {
					ok: response.ok,
					data,
				} ) )
			)
			.then( ( result ) => {
				if ( result.ok && result.data.delivery_url ) {
					window.location.href = result.data.delivery_url;
					return;
				}

				throw new Error(
					result.data.message || 'Something went wrong.'
				);
			} )
			.catch( ( error ) => {
				if ( submitButton ) {
					submitButton.disabled = false;
				}
				showSddError(
					block,
					error.message ||
						'Something went wrong. Please try again.'
				);
			} );
	};

	document.addEventListener( 'click', ( event ) => {
		const checkoutButton = event.target.closest(
			'.book-cpt-purchase-links__link--checkout'
		);
		if ( checkoutButton ) {
			event.preventDefault();
			const block = checkoutButton.closest(
				'.book-cpt-purchase-links'
			);
			const isFree = checkoutButton.getAttribute( 'data-free' ) === '1';
			closeAll();

			if ( isFree ) {
				setSddOverlayState(
					block,
					true,
					checkoutButton.getAttribute( 'data-download-id' )
				);
				block.querySelector(
					'.book-cpt-purchase-links__sdd-email'
				)?.focus();
				return;
			}

			handlePaidCheckout( checkoutButton, block );
			return;
		}

		const closeButton = event.target.closest(
			'.book-cpt-purchase-links__sdd-close, .book-cpt-purchase-links__sdd-backdrop'
		);
		if ( closeButton ) {
			event.preventDefault();
			setSddOverlayState(
				closeButton.closest( '.book-cpt-purchase-links' ),
				false
			);
			return;
		}

		const submitButton = event.target.closest(
			'.book-cpt-purchase-links__sdd-submit'
		);
		if ( submitButton ) {
			event.preventDefault();
			handleFreeCheckout(
				submitButton.closest( '.book-cpt-purchase-links' )
			);
			return;
		}

		const toggle = event.target.closest(
			'.book-cpt-purchase-links__toggle'
		);
		if ( ! toggle ) {
			if (
				! event.target.closest( '.book-cpt-purchase-links' )
			) {
				closeAll();
				document
					.querySelectorAll( '.book-cpt-purchase-links' )
					.forEach( ( block ) => setSddOverlayState( block, false ) );
			}
			return;
		}

		event.preventDefault();
		const block = toggle.closest( '.book-cpt-purchase-links' );
		const menu = block.querySelector( '.book-cpt-purchase-links__menu' );
		const isOpen = block.classList.contains( 'is-open' );

		closeAll();

		if ( ! isOpen ) {
			block.classList.add( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'true' );
			menu.hidden = false;
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' === event.key ) {
			closeAll();
			document
				.querySelectorAll( '.book-cpt-purchase-links' )
				.forEach( ( block ) => setSddOverlayState( block, false ) );
			return;
		}

		if ( 'Enter' !== event.key ) {
			return;
		}

		const emailInput = event.target.closest(
			'.book-cpt-purchase-links__sdd-email'
		);
		if ( ! emailInput ) {
			return;
		}

		event.preventDefault();
		handleFreeCheckout(
			emailInput.closest( '.book-cpt-purchase-links' )
		);
	} );
} )();
