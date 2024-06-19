<?php

namespace BookCPT;

/**
 * Meta class.
 */
class Meta {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_meta' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
	}

    /**
     * Registers meta for the book custom post type.
     */
    public function register_meta() {
        register_post_meta(
            'book',
            'book_order',
            [
                'type'              => 'integer',
                'description'       => 'The order of the book in the series.',
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => 'absint',
                'auth_callback'     => '__return_true',
            ]
        );
    }

    /**
     * Register meta box
     */
    public function add_meta_box() {
        add_meta_box(
            'meta_box',
            __( 'Book details' ),
            array( $this, 'build_meta_box'),
            'book',
            'normal',
            'default'
        );
    }

    /**
     * Build meta box.
     */
    public function build_meta_box( $post ) {
        wp_nonce_field( 'save_meta_box_data', 'book_cpt' );
        $order = get_post_meta( $post->ID, 'book_order', true );
        ?>
        <div>
            <p><label for="book_order">Order</label></p>
            <p><input type="number" id="book_order" name="book_order" value="<?php echo esc_attr( $order ); ?>" /></p>	
        </div>
        <?php
    }

    /**
     * Save meta data.
     */
    public function save_meta_box_data( $post_id ) {
        if ( ! isset( $_POST['book_cpt'] ) )
            return;
        if ( ! wp_verify_nonce( $_POST['book_cpt'], 'save_meta_box_data' ) )
            return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;
        if ( ! current_user_can( 'edit_post', $post_id ) )
            return;

        if ( ! isset( $_POST['book_order'] ) )
            return;

        $order = absint( $_POST['book_order'] );

        update_post_meta( $post_id, 'book_order', $order );
    }
}

new Meta();