import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

export default function Edit( { attributes, setAttributes, context } ) {
	const { buttonLabel } = attributes;
	const postId = context.postId;
	const postType = context.postType;
	const blockProps = useBlockProps();

	const purchaseLinks = useSelect(
		( select ) => {
			if ( ! postId || 'book' !== postType ) {
				return [];
			}

			const record = select( coreStore ).getEditedEntityRecord(
				'postType',
				postType,
				postId
			);

			return record?.meta?.book_purchase_links || [];
		},
		[ postId, postType ]
	);

	const validLinks = Array.isArray( purchaseLinks )
		? purchaseLinks
				.filter(
					( link ) =>
						link?.url ||
						( link?.retailer === 'simple-digital-download' &&
							link?.download_id )
				)
				.sort( ( a, b ) => {
					const aIsBuyDirect =
						a?.retailer === 'simple-digital-download' &&
						a?.download_id;
					const bIsBuyDirect =
						b?.retailer === 'simple-digital-download' &&
						b?.download_id;

					if ( aIsBuyDirect === bIsBuyDirect ) {
						return 0;
					}

					return aIsBuyDirect ? -1 : 1;
				} )
		: [];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Button settings', 'book-cpt' ) }>
					<TextControl
						label={ __( 'Button label', 'book-cpt' ) }
						value={ buttonLabel }
						onChange={ ( value ) =>
							setAttributes( { buttonLabel: value } )
						}
						help={ __(
							'The main call-to-action shown before the retailer dropdown opens.',
							'book-cpt'
						) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ 'book' !== postType ? (
					<Placeholder
						icon="cart"
						label={ __( 'Book Purchase Links', 'book-cpt' ) }
						instructions={ __(
							'This block reads purchase links from the current book. Place it on a book template or single book page.',
							'book-cpt'
						) }
					/>
				) : validLinks.length ? (
					<div className="book-cpt-purchase-links is-editor-preview">
						<button
							type="button"
							className="book-cpt-purchase-links__toggle"
							disabled
						>
							{ buttonLabel }
						</button>
						<div className="book-cpt-purchase-links__menu">
							<p className="book-cpt-purchase-links__menu-label">
								{ __( 'Choose your retailer', 'book-cpt' ) }
							</p>
							<ul className="book-cpt-purchase-links__list">
								{ validLinks.map( ( link ) => (
									<li
										key={
											link.url ||
											`${ link.retailer }-${ link.download_id }`
										}
									>
										<span className="book-cpt-purchase-links__link">
											<span className="book-cpt-purchase-links__link-label">
												{ link.label }
											</span>
											<span
												className="book-cpt-purchase-links__link-arrow"
												aria-hidden="true"
											>
												&rarr;
											</span>
										</span>
									</li>
								) ) }
							</ul>
						</div>
					</div>
				) : (
					<Placeholder
						icon="cart"
						label={ __( 'Book Purchase Links', 'book-cpt' ) }
						instructions={ __(
							'Add purchase links in the Book details meta box below the editor, then they will appear here as a retailer dropdown.',
							'book-cpt'
						) }
					/>
				) }
			</div>
		</>
	);
}
