( function () {
	const config = window.bookCptPurchaseLinks;
	if ( ! config ) {
		return;
	}

	const container = document.querySelector( '.book-cpt-purchase-links-meta' );
	if ( ! container ) {
		return;
	}

	const tableHead = container.querySelector(
		'.book-cpt-purchase-links-meta__table-head'
	);
	const rowsContainer = container.querySelector(
		'.book-cpt-purchase-links-meta__rows'
	);
	const addButton = container.querySelector(
		'.book-cpt-purchase-links-meta__add'
	);

	const getNextIndex = () =>
		rowsContainer.querySelectorAll(
			'.book-cpt-purchase-links-meta__row'
		).length;

	const getRetailerOptions = () =>
		Object.entries( config.retailers )
			.map(
				( [ slug, label ] ) =>
					`<option value="${ slug }">${ label }</option>`
			)
			.join( '' );

	const syncCustomLabelState = () => {
		const hasCustom = !! container.querySelector(
			'.book-cpt-purchase-links-meta__row.has-custom-label'
		);
		container.classList.toggle( 'has-custom-label', hasCustom );
		if ( tableHead ) {
			tableHead.classList.toggle( 'has-custom-label', hasCustom );
		}
	};

	const createRow = ( index ) => {
		const row = document.createElement( 'div' );
		row.className = 'book-cpt-purchase-links-meta__row';
		row.innerHTML = `
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--retailer">
				<label class="screen-reader-text">${ config.retailerLabel }</label>
				<select class="book-cpt-purchase-links-meta__retailer" name="book_purchase_links[${ index }][retailer]">
					${ getRetailerOptions() }
				</select>
			</div>
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--label is-hidden">
				<label class="screen-reader-text">${ config.labelLabel }</label>
				<input type="text" class="book-cpt-purchase-links-meta__label-input" name="book_purchase_links[${ index }][label]" value="" placeholder="${ config.labelLabel }" />
			</div>
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--url">
				<label class="screen-reader-text">${ config.urlLabel }</label>
				<input type="url" class="large-text book-cpt-purchase-links-meta__url" name="book_purchase_links[${ index }][url]" value="" placeholder="https://" />
			</div>
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--actions">
				<button type="button" class="button-link-delete book-cpt-purchase-links-meta__remove">${ config.removeLabel }</button>
			</div>
		`;
		return row;
	};

	const syncLabelField = ( row ) => {
		const retailer = row.querySelector(
			'.book-cpt-purchase-links-meta__retailer'
		);
		const labelWrap = row.querySelector(
			'.book-cpt-purchase-links-meta__field--label'
		);
		const labelInput = row.querySelector(
			'.book-cpt-purchase-links-meta__label-input'
		);

		if ( retailer.value === config.customSlug ) {
			labelWrap.classList.remove( 'is-hidden' );
			row.classList.add( 'has-custom-label' );
			syncCustomLabelState();
			return;
		}

		labelWrap.classList.add( 'is-hidden' );
		row.classList.remove( 'has-custom-label' );
		labelInput.value = config.retailers[ retailer.value ] || '';
		syncCustomLabelState();
	};

	rowsContainer.addEventListener( 'change', ( event ) => {
		const target = event.target;
		if ( ! target.classList.contains(
			'book-cpt-purchase-links-meta__retailer'
		) ) {
			return;
		}

		syncLabelField( target.closest( '.book-cpt-purchase-links-meta__row' ) );
	} );

	rowsContainer.addEventListener( 'click', ( event ) => {
		const target = event.target;
		if ( ! target.classList.contains(
			'book-cpt-purchase-links-meta__remove'
		) ) {
			return;
		}

		event.preventDefault();
		const rows = rowsContainer.querySelectorAll(
			'.book-cpt-purchase-links-meta__row'
		);
		if ( rows.length <= 1 ) {
			const row = target.closest( '.book-cpt-purchase-links-meta__row' );
			row.querySelector( 'input[type="url"]' ).value = '';
			return;
		}

		target.closest( '.book-cpt-purchase-links-meta__row' ).remove();
		syncCustomLabelState();
	} );

	addButton.addEventListener( 'click', () => {
		const row = createRow( getNextIndex() );
		rowsContainer.appendChild( row );
		syncLabelField( row );
	} );

	rowsContainer
		.querySelectorAll( '.book-cpt-purchase-links-meta__row' )
		.forEach( syncLabelField );

	syncCustomLabelState();
} )();
