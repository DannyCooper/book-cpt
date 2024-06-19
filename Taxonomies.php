<?php

namespace BookCPT;

/**
 * Taxonomies class.
 */
class Taxonomies {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register' ) );
	}

    /**
     * Register taxonomies for the books custom post type.
     */
    public function register() {
        /* Register the Book Genre taxonomy. */
        register_taxonomy(
            'book_genre',
            array( 'book' ),
            array(
                'public'            => true,
                'show_ui'           => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'show_tagcloud'     => true,
                'show_admin_column' => true,
                'hierarchical'      => true,
                'query_var'         => 'book_genre',

                /* The rewrite handles the URL structure. */
                'rewrite'           => array(
                    'slug'         => 'books/genre',
                    'with_front'   => false,
                    'hierarchical' => true,
                    'ep_mask'      => EP_NONE,
                ),

                /* Labels used when displaying taxonomy and terms. */
                'labels'            => array(
                    'name'                       => __( 'Book Genres', 'book-cpt' ),
                    'singular_name'              => __( 'Book Genre', 'book-cpt' ),
                    'menu_name'                  => __( 'Genres', 'book-cpt' ),
                    'name_admin_bar'             => __( 'Genre', 'book-cpt' ),
                    'search_items'               => __( 'Search Genres', 'book-cpt' ),
                    'popular_items'              => __( 'Popular Genres', 'book-cpt' ),
                    'all_items'                  => __( 'All Genres', 'book-cpt' ),
                    'edit_item'                  => __( 'Edit Genre', 'book-cpt' ),
                    'view_item'                  => __( 'View Genre', 'book-cpt' ),
                    'update_item'                => __( 'Update Genre', 'book-cpt' ),
                    'add_new_item'               => __( 'Add New Genre', 'book-cpt' ),
                    'new_item_name'              => __( 'New Genre Name', 'book-cpt' ),
                    'parent_item'                => __( 'Parent Genre', 'book-cpt' ),
                    'parent_item_colon'          => __( 'Parent Genre:', 'book-cpt' ),
                    'separate_items_with_commas' => null,
                    'add_or_remove_items'        => null,
                    'choose_from_most_used'      => null,
                    'not_found'                  => null,
                ),
            )
        );

        /* Register the Book Series taxonomy. */
        register_taxonomy(
            'book_series',
            array( 'book' ),
            array(
                'public'            => true,
                'show_ui'           => true,
                'show_in_nav_menus' => true,
                'show_in_rest'      => true,
                'show_tagcloud'     => true,
                'show_admin_column' => true,
                'hierarchical'      => true,
                'query_var'         => 'book_series',

                /* The rewrite handles the URL structure. */
                'rewrite'           => array(
                    'slug'         => 'books/series',
                    'with_front'   => false,
                    'hierarchical' => true,
                    'ep_mask'      => EP_NONE,
                ),

                /* Labels used when displaying taxonomy and terms. */
                'labels'            => array(
                    'name'                       => __( 'Book Series', 'book-cpt' ),
                    'singular_name'              => __( 'Book Series', 'book-cpt' ),
                    'menu_name'                  => __( 'Series', 'book-cpt' ),
                    'name_admin_bar'             => __( 'Series', 'book-cpt' ),
                    'search_items'               => __( 'Search Series', 'book-cpt' ),
                    'popular_items'              => __( 'Popular Series', 'book-cpt' ),
                    'all_items'                  => __( 'All Series', 'book-cpt' ),
                    'edit_item'                  => __( 'Edit Series', 'book-cpt' ),
                    'view_item'                  => __( 'View Series', 'book-cpt' ),
                    'update_item'                => __( 'Update Series', 'book-cpt' ),
                    'add_new_item'               => __( 'Add New Series', 'book-cpt' ),
                    'new_item_name'              => __( 'New Series Name', 'book-cpt' ),
                    'parent_item'                => __( 'Parent Series', 'book-cpt' ),
                    'parent_item_colon'          => __( 'Parent Series:', 'book-cpt' ),
                    'separate_items_with_commas' => null,
                    'add_or_remove_items'        => null,
                    'choose_from_most_used'      => null,
                    'not_found'                  => null,
                ),
            )
        );
    }
}

new Taxonomies();