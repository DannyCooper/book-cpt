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
				'show_in_rest'      => false,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			]
		);
	}

	/**
	 * Register meta box
	 */
	public function add_meta_box() {
		add_meta_box(
			'book_cpt_details',
			__( 'Book details', 'book-cpt' ),
			array( $this, 'build_meta_box' ),
			'book',
			'normal',
			'high',
			array(
				'__block_editor_compatible_meta_box'                 => true,
				'__back_compat_meta_box_in_bottom_of_block_editor' => true,
			)
		);
	}

	/**
	 * Build meta box.
	 *
	 * @param \WP_Post $post Current post.
	 */
	public function build_meta_box( $post ) {
		wp_nonce_field( 'save_meta_box_data', 'book_cpt' );
		$order = get_post_meta( $post->ID, 'book_order', true );
		?>
		<div class="book-cpt-book-details">
			<div class="book-cpt-book-details__section">
				<label class="book-cpt-book-details__label" for="book_order">
					<? esc_html_e( 'Series order', 'book-cpt' ); ?>
				</label>
				<input
					type="number"
					class="book-cpt-book-details__order"
					id="book_order"
					name="book_order"
					value="<?php echo esc_attr( $order ); ?>"
					min="0"
					step="1"
				/>
				<p class="description">
					<? esc_html_e( 'The order of this book within its series.', 'book-cpt' ); ?>
				</p>
			</div>
			<?php PurchaseLinks::render_meta_box_fields( $post->ID ); ?>
		</div>
		<?php
	}

	/**
	 * Save meta data.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_meta_box_data( $post_id ) {
		if ( ! isset( $_POST['book_cpt'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['book_cpt'] ) ), 'save_meta_box_data' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( 'book' !== get_post_type( $post_id ) ) {
			return;
		}

		if ( isset( $_POST['book_order'] ) ) {
			update_post_meta( $post_id, 'book_order', absint( wp_unslash( $_POST['book_order'] ) ) );
		}

		PurchaseLinks::save_meta_box_data( $post_id );
	}
}

new Meta();
