<?php

namespace BookCPT;

/**
 * Registers plugin blocks.
 */
class Block {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_filter( 'block_categories_all', [ $this, 'register_block_category' ] );
	}

	/**
	 * Registers the Books block category.
	 *
	 * @param array<int, array<string, string>> $categories Block categories.
	 * @return array<int, array<string, string>>
	 */
	public function register_block_category( $categories ) {
		return array_merge(
			[
				[
					'slug'  => 'book-cpt',
					'title' => __( 'Books', 'book-cpt' ),
				],
			],
			$categories
		);
	}

	/**
	 * Registers block types.
	 */
	public function register_blocks() {
		register_block_type( BOOK_CPT_PATH . 'build/purchase-links' );
	}
}

new Block();
