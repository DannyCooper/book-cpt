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

	document.addEventListener( 'click', ( event ) => {
		const toggle = event.target.closest(
			'.book-cpt-purchase-links__toggle'
		);
		if ( ! toggle ) {
			if (
				! event.target.closest( '.book-cpt-purchase-links' )
			) {
				closeAll();
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
		}
	} );
} )();
