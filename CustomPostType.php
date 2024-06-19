<?php

namespace BookCPT;

/**
 * CustomPostType class.
 *
 * @since 1.7
 */
class CustomPostType {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
	}

	/**
	 * Register Custom Post Type
	 */
	public function register_post_type() {
		$labels = array(
			'name'                  => _x( 'Books', 'Post Type General Name', 'book-cpt' ),
			'singular_name'         => _x( 'Book', 'Post Type Singular Name', 'book-cpt' ),
			'menu_name'             => __( 'Books', 'book-cpt' ),
			'name_admin_bar'        => __( 'Books', 'book-cpt' ),
			'archives'              => __( 'Book Archives', 'book-cpt' ),
			'attributes'            => __( 'Book Attributes', 'book-cpt' ),
			'parent_item_colon'     => __( 'Parent Book:', 'book-cpt' ),
			'all_items'             => __( 'All Books', 'book-cpt' ),
			'add_new_item'          => __( 'Add New Book', 'book-cpt' ),
			'add_new'               => __( 'Add New', 'book-cpt' ),
			'new_item'              => __( 'New Book', 'book-cpt' ),
			'edit_item'             => __( 'Edit Book', 'book-cpt' ),
			'update_item'           => __( 'Update Book', 'book-cpt' ),
			'view_item'             => __( 'View Book', 'book-cpt' ),
			'view_items'            => __( 'View Books', 'book-cpt' ),
			'search_items'          => __( 'Search Book', 'book-cpt' ),
			'not_found'             => __( 'Not found', 'book-cpt' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'book-cpt' ),
			'featured_image'        => __( 'Featured Image', 'book-cpt' ),
			'set_featured_image'    => __( 'Set featured image', 'book-cpt' ),
			'remove_featured_image' => __( 'Remove featured image', 'book-cpt' ),
			'use_featured_image'    => __( 'Use as featured image', 'book-cpt' ),
			'insert_into_item'      => __( 'Insert into book', 'book-cpt' ),
			'uploaded_to_this_item' => __( 'Uploaded to this book', 'book-cpt' ),
			'items_list'            => __( 'Books list', 'book-cpt' ),
			'items_list_navigation' => __( 'Books list navigation', 'book-cpt' ),
			'filter_items_list'     => __( 'Filter books list', 'book-cpt' ),
		);
		$args   = array(
			'label'               => __( 'Book', 'book-cpt' ),
			'description'         => __( 'Post Type Description', 'book-cpt' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'author', 'custom-fields', 'thumbnail' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-editor-bold',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
			'show_in_rest'        => true,
		);
		register_post_type( 'book', $args );

	}
}
new CustomPostType();

