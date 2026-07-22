<?php
/**
 * Server-side render callback for the purchase links block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package book-cpt
 */

$post_id = $block->context['postId'] ?? get_the_ID();
if ( ! $post_id || 'book' !== get_post_type( $post_id ) ) {
	return;
}

$button_label = $attributes['buttonLabel'] ?? __( 'Buy this book', 'book-cpt' );
echo \BookCPT\PurchaseLinks::render( (int) $post_id, $button_label );
