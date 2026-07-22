<?php

namespace BookCPT;

/**
 * Purchase link meta, admin UI helpers, and frontend rendering.
 */
class PurchaseLinks {

	const META_KEY = 'book_purchase_links';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
	}

	/**
	 * Registers purchase link post meta.
	 */
	public function register_meta() {
		register_post_meta(
			'book',
			self::META_KEY,
			[
				'type'              => 'array',
				'description'       => __( 'Purchase links for the book.', 'book-cpt' ),
				'single'            => true,
				'show_in_rest'      => [
					'schema' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'retailer' => [
									'type' => 'string',
								],
								'label'    => [
									'type' => 'string',
								],
								'url'      => [
									'type'   => 'string',
									'format' => 'uri',
								],
							],
						],
					],
				],
				'sanitize_callback' => [ self::class, 'sanitize_links' ],
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			]
		);
	}

	/**
	 * Sanitizes purchase link rows before saving.
	 *
	 * @param mixed $value Raw meta value.
	 * @return array<int, array<string, string>>
	 */
	public static function sanitize_links( $value ) {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$sanitized = [];
		$slugs     = Retailers::get_slugs();

		foreach ( $value as $link ) {
			if ( ! is_array( $link ) || empty( $link['url'] ) ) {
				continue;
			}

			$url = esc_url_raw( wp_unslash( $link['url'] ) );
			if ( empty( $url ) ) {
				continue;
			}

			$retailer = sanitize_key( $link['retailer'] ?? 'custom' );
			if ( ! in_array( $retailer, $slugs, true ) ) {
				$retailer = 'custom';
			}

			$label = sanitize_text_field( wp_unslash( $link['label'] ?? '' ) );
			if ( '' === $label ) {
				$label = Retailers::get_label( $retailer );
			}

			$sanitized[] = [
				'retailer' => $retailer,
				'label'    => $label,
				'url'      => $url,
			];
		}

		return $sanitized;
	}

	/**
	 * Returns purchase links for a book.
	 *
	 * @param int $post_id Book post ID.
	 * @return array<int, array<string, string>>
	 */
	public static function get_links( $post_id ) {
		$links = get_post_meta( $post_id, self::META_KEY, true );

		if ( ! is_array( $links ) ) {
			return [];
		}

		return self::sanitize_links( $links );
	}

	/**
	 * Renders the purchase dropdown markup.
	 *
	 * @param int    $post_id     Book post ID.
	 * @param string $button_label Primary CTA label.
	 * @return string
	 */
	public static function render( $post_id, $button_label = '' ) {
		$links = self::get_links( $post_id );

		if ( empty( $links ) ) {
			return '';
		}

		if ( '' === $button_label ) {
			$button_label = __( 'Buy this book', 'book-cpt' );
		}

		$button_label = sanitize_text_field( $button_label );
		$menu_label   = __( 'Choose your retailer', 'book-cpt' );

		ob_start();
		?>
		<div class="book-cpt-purchase-links">
			<button
				type="button"
				class="book-cpt-purchase-links__toggle"
				aria-expanded="false"
				aria-haspopup="true"
			>
				<?php echo esc_html( $button_label ); ?>
			</button>
			<div class="book-cpt-purchase-links__menu" hidden>
				<p class="book-cpt-purchase-links__menu-label"><?php echo esc_html( $menu_label ); ?></p>
				<ul class="book-cpt-purchase-links__list" role="menu">
					<?php foreach ( $links as $link ) : ?>
						<li role="none">
							<a
								role="menuitem"
								class="book-cpt-purchase-links__link"
								href="<?php echo esc_url( $link['url'] ); ?>"
								target="_blank"
								rel="noopener noreferrer nofollow sponsored"
							>
								<span class="book-cpt-purchase-links__link-label"><?php echo esc_html( $link['label'] ); ?></span>
								<span class="book-cpt-purchase-links__link-arrow" aria-hidden="true">&rarr;</span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Outputs purchase link fields in the book meta box.
	 *
	 * @param int $post_id Book post ID.
	 */
	public static function render_meta_box_fields( $post_id ) {
		$links     = self::get_links( $post_id );
		$retailers = Retailers::get_all();

		if ( empty( $links ) ) {
			$links = [
				[
					'retailer' => 'amazon',
					'label'    => Retailers::get_label( 'amazon' ),
					'url'      => '',
				],
			];
		}
		?>
		<div class="book-cpt-purchase-links-meta">
			<div class="book-cpt-purchase-links-meta__header">
				<div>
					<h3 class="book-cpt-purchase-links-meta__title"><?php esc_html_e( 'Purchase links', 'book-cpt' ); ?></h3>
					<p class="description"><?php esc_html_e( 'Add where readers can buy this book. The Purchase Links block shows these as a single dropdown.', 'book-cpt' ); ?></p>
				</div>
				<button type="button" class="button book-cpt-purchase-links-meta__add">
					<?php esc_html_e( 'Add purchase link', 'book-cpt' ); ?>
				</button>
			</div>
			<div class="book-cpt-purchase-links-meta__table">
				<div class="book-cpt-purchase-links-meta__table-head">
					<span><?php esc_html_e( 'Retailer', 'book-cpt' ); ?></span>
					<span class="book-cpt-purchase-links-meta__label-head"><?php esc_html_e( 'Label', 'book-cpt' ); ?></span>
					<span><?php esc_html_e( 'URL', 'book-cpt' ); ?></span>
					<span></span>
				</div>
			</div>
			<div class="book-cpt-purchase-links-meta__rows">
				<?php foreach ( $links as $index => $link ) : ?>
					<?php self::render_meta_box_row( $index, $link, $retailers ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders one purchase link row in the meta box.
	 *
	 * @param int                  $index     Row index.
	 * @param array<string,string> $link      Link data.
	 * @param array<string,string> $retailers Retailer options.
	 */
	private static function render_meta_box_row( $index, $link, $retailers ) {
		$retailer      = $link['retailer'] ?? 'amazon';
		$label         = $link['label'] ?? Retailers::get_label( $retailer );
		$url           = $link['url'] ?? '';
		$is_custom     = 'custom' === $retailer;
		$row_classes   = 'book-cpt-purchase-links-meta__row' . ( $is_custom ? ' has-custom-label' : '' );
		$label_classes = 'book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--label' . ( $is_custom ? '' : ' is-hidden' );
		?>
		<div class="<?php echo esc_attr( $row_classes ); ?>">
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--retailer">
				<label class="screen-reader-text"><?php esc_html_e( 'Retailer', 'book-cpt' ); ?></label>
				<select
					class="book-cpt-purchase-links-meta__retailer"
					name="book_purchase_links[<?php echo esc_attr( (string) $index ); ?>][retailer]"
				>
					<?php foreach ( $retailers as $slug => $retailer_label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <? selected( $retailer, $slug ); ?>>
							<?php echo esc_html( $retailer_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="<?php echo esc_attr( $label_classes ); ?>">
				<label class="screen-reader-text"><?php esc_html_e( 'Label', 'book-cpt' ); ?></label>
				<input
					type="text"
					class="book-cpt-purchase-links-meta__label-input"
					name="book_purchase_links[<?php echo esc_attr( (string) $index ); ?>][label]"
					value="<?php echo esc_attr( $label ); ?>"
					placeholder="<?php esc_attr_e( 'Custom label', 'book-cpt' ); ?>"
				/>
			</div>
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--url">
				<label class="screen-reader-text"><?php esc_html_e( 'URL', 'book-cpt' ); ?></label>
				<input
					type="url"
					class="large-text book-cpt-purchase-links-meta__url"
					name="book_purchase_links[<?php echo esc_attr( (string) $index ); ?>][url]"
					value="<?php echo esc_attr( $url ); ?>"
					placeholder="https://"
				/>
			</div>
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--actions">
				<button type="button" class="button-link-delete book-cpt-purchase-links-meta__remove">
					<? esc_html_e( 'Remove', 'book-cpt' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Saves purchase link meta from the book meta box.
	 *
	 * @param int $post_id Book post ID.
	 */
	public static function save_meta_box_data( $post_id ) {
		if ( ! isset( $_POST['book_purchase_links'] ) || ! is_array( $_POST['book_purchase_links'] ) ) {
			return;
		}

		$links = self::sanitize_links( wp_unslash( $_POST['book_purchase_links'] ) );
		update_post_meta( $post_id, self::META_KEY, $links );
	}

	/**
	 * Enqueues admin assets for the purchase link repeater.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'book' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'book-cpt-admin-purchase-links',
			BOOK_CPT_URL . 'assets/admin-purchase-links.css',
			[],
			BOOK_CPT_VERSION
		);

		wp_enqueue_script(
			'book-cpt-admin-purchase-links',
			BOOK_CPT_URL . 'assets/admin-purchase-links.js',
			[],
			BOOK_CPT_VERSION,
			true
		);

		wp_localize_script(
			'book-cpt-admin-purchase-links',
			'bookCptPurchaseLinks',
			[
				'retailers'      => Retailers::get_all(),
				'customSlug'     => 'custom',
				'retailerLabel'  => __( 'Retailer', 'book-cpt' ),
				'labelLabel'     => __( 'Label', 'book-cpt' ),
				'urlLabel'       => __( 'URL', 'book-cpt' ),
				'removeLabel'    => __( 'Remove', 'book-cpt' ),
			]
		);
	}
}

new PurchaseLinks();
