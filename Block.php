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
	}

	/**
	 * Registers block types.
	 */
	public function register_blocks() {
		register_block_type( BOOK_CPT_PATH . 'build/purchase-links' );
	}
}

new Block();
