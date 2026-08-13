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
								'download_id' => [
									'type' => 'string',
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
			if ( ! is_array( $link ) ) {
				continue;
			}

			$retailer = sanitize_key( $link['retailer'] ?? 'custom' );
			if ( ! in_array( $retailer, $slugs, true ) ) {
				$retailer = 'custom';
			}

			if ( SimpleDigitalDownloads::RETAILER_SLUG === $retailer ) {
				if ( ! SimpleDigitalDownloads::is_available() ) {
					continue;
				}

				// Downloads without a file yet are kept, so an author can pair a
				// book with a download they are still building. The frontend
				// leaves them out until they have something to deliver.
				$download_id = absint( $link['download_id'] ?? 0 );
				if ( ! $download_id || ! SimpleDigitalDownloads::download_exists( $download_id ) ) {
					continue;
				}

				$label = sanitize_text_field( wp_unslash( $link['label'] ?? '' ) );
				if ( '' === $label ) {
					$label = SimpleDigitalDownloads::get_download_title( $download_id );
				}
				if ( '' === $label ) {
					$label = __( 'Buy Direct', 'book-cpt' );
				}

				$sanitized[] = [
					'retailer'    => $retailer,
					'label'       => $label,
					'url'         => '',
					'download_id' => (string) $download_id,
				];
				continue;
			}

			if ( empty( $link['url'] ) ) {
				continue;
			}

			$retailer = sanitize_key( $link['retailer'] ?? 'custom' );
			if ( ! in_array( $retailer, $slugs, true ) ) {
				$retailer = 'custom';
			}

			$url = esc_url_raw( wp_unslash( $link['url'] ) );
			if ( empty( $url ) ) {
				continue;
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

		return self::disambiguate_labels( self::sort_links( self::sanitize_links( $links ) ) );
	}

	/**
	 * Renders the purchase dropdown markup.
	 *
	 * @param int    $post_id     Book post ID.
	 * @param string $button_label Primary CTA label.
	 * @return string
	 */
	public static function render( $post_id, $button_label = '' ) {
		$links = self::remove_pending_checkout_links( self::get_links( $post_id ) );

		if ( empty( $links ) ) {
			return '';
		}

		if ( '' === $button_label ) {
			$button_label = __( 'Buy this book', 'book-cpt' );
		}

		$button_label = sanitize_text_field( $button_label );
		$menu_label   = __( 'Choose your retailer', 'book-cpt' );
		$has_free_sdd = false;

		foreach ( $links as $link ) {
			if (
				SimpleDigitalDownloads::RETAILER_SLUG === ( $link['retailer'] ?? '' )
				&& ! empty( $link['download_id'] )
				&& SimpleDigitalDownloads::is_free_download( (int) $link['download_id'] )
			) {
				$has_free_sdd = true;
				break;
			}
		}

		$wrapper_attrs = [
			'class' => 'book-cpt-purchase-links',
		];

		if ( self::has_checkout_links( $links ) ) {
			$wrapper_attrs['data-sdd-checkout-url'] = esc_url( SimpleDigitalDownloads::get_checkout_url() );
		}

		ob_start();
		?>
		<div <?php echo self::render_attributes( $wrapper_attrs ); ?>>
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
							<?php if ( self::is_checkout_link( $link ) ) : ?>
								<?php
								$download_id = absint( $link['download_id'] );
								$is_free     = SimpleDigitalDownloads::is_free_download( $download_id );
								?>
								<button
									type="button"
									role="menuitem"
									class="book-cpt-purchase-links__link book-cpt-purchase-links__link--checkout"
									data-download-id="<?php echo esc_attr( (string) $download_id ); ?>"
									data-free="<?php echo $is_free ? '1' : '0'; ?>"
								>
									<span class="book-cpt-purchase-links__link-label"><?php echo esc_html( $link['label'] ); ?></span>
									<span class="book-cpt-purchase-links__link-arrow" aria-hidden="true">&rarr;</span>
								</button>
							<?php else : ?>
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
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php if ( $has_free_sdd ) : ?>
				<div class="book-cpt-purchase-links__sdd-overlay" hidden aria-hidden="true">
					<button type="button" class="book-cpt-purchase-links__sdd-backdrop" tabindex="-1" aria-label="<?php esc_attr_e( 'Close', 'book-cpt' ); ?>"></button>
					<div class="book-cpt-purchase-links__sdd-dialog" role="dialog" aria-modal="true" aria-labelledby="book-cpt-sdd-email-title">
						<button type="button" class="book-cpt-purchase-links__sdd-close" aria-label="<?php esc_attr_e( 'Close', 'book-cpt' ); ?>">
							<span aria-hidden="true">&times;</span>
						</button>
						<h2 class="book-cpt-purchase-links__sdd-title" id="book-cpt-sdd-email-title">
							<?php esc_html_e( 'Where should we send your free download?', 'book-cpt' ); ?>
						</h2>
						<p class="book-cpt-purchase-links__sdd-description">
							<?php esc_html_e( 'Enter your email and we\'ll send you a secure download link.', 'book-cpt' ); ?>
						</p>
						<label class="screen-reader-text" for="book-cpt-sdd-email-input">
							<?php esc_html_e( 'Email address', 'book-cpt' ); ?>
						</label>
						<input
							type="email"
							id="book-cpt-sdd-email-input"
							class="book-cpt-purchase-links__sdd-email"
							autocomplete="email"
							placeholder="<?php esc_attr_e( 'you@example.com', 'book-cpt' ); ?>"
						/>
						<button type="button" class="book-cpt-purchase-links__sdd-submit">
							<?php esc_html_e( 'Send download link', 'book-cpt' ); ?>
						</button>
						<p class="book-cpt-purchase-links__sdd-error" hidden></p>
					</div>
				</div>
			<?php endif; ?>
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
		$download_id   = absint( $link['download_id'] ?? 0 );
		$is_custom     = 'custom' === $retailer;
		$is_sdd        = SimpleDigitalDownloads::RETAILER_SLUG === $retailer;
		$row_classes   = 'book-cpt-purchase-links-meta__row';
		if ( $is_custom ) {
			$row_classes .= ' has-custom-label';
		}
		if ( $is_sdd ) {
			$row_classes .= ' has-sdd-download';
		}
		$label_classes = 'book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--label' . ( $is_custom || $is_sdd ? '' : ' is-hidden' );
		$url_classes   = 'book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--url' . ( $is_sdd ? ' is-hidden' : '' );
		$sdd_classes   = 'book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--download' . ( $is_sdd ? '' : ' is-hidden' );
		$downloads     = SimpleDigitalDownloads::get_download_choices();

		$label_placeholder = $is_sdd
			? __( 'Buy Direct', 'book-cpt' )
			: __( 'Custom label', 'book-cpt' );
		?>
		<div class="<?php echo esc_attr( $row_classes ); ?>">
			<div class="book-cpt-purchase-links-meta__field book-cpt-purchase-links-meta__field--retailer">
				<label class="screen-reader-text"><?php esc_html_e( 'Retailer', 'book-cpt' ); ?></label>
				<select
					class="book-cpt-purchase-links-meta__retailer"
					name="book_purchase_links[<?php echo esc_attr( (string) $index ); ?>][retailer]"
				>
					<?php foreach ( $retailers as $slug => $retailer_label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $retailer, $slug ); ?>>
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
					placeholder="<?php echo esc_attr( $label_placeholder ); ?>"
				/>
			</div>
			<div class="<?php echo esc_attr( $sdd_classes ); ?>">
				<label class="screen-reader-text"><?php esc_html_e( 'Download', 'book-cpt' ); ?></label>
				<select
					class="book-cpt-purchase-links-meta__download"
					name="book_purchase_links[<?php echo esc_attr( (string) $index ); ?>][download_id]"
				>
					<option value=""><?php esc_html_e( 'Select a download', 'book-cpt' ); ?></option>
					<?php foreach ( $downloads as $id => $download_label ) : ?>
						<option value="<?php echo esc_attr( (string) $id ); ?>" <?php selected( $download_id, $id ); ?>>
							<?php echo esc_html( $download_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="<?php echo esc_attr( $url_classes ); ?>">
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
					<?php esc_html_e( 'Remove', 'book-cpt' ); ?>
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
			BOOK_CPT_URL . 'admin/css/admin-purchase-links.css',
			[],
			BOOK_CPT_VERSION
		);

		wp_enqueue_script(
			'book-cpt-admin-purchase-links',
			BOOK_CPT_URL . 'admin/js/admin-purchase-links.js',
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
				'sddSlug'        => SimpleDigitalDownloads::RETAILER_SLUG,
				'sddDefaultLabel'=> __( 'Buy Direct', 'book-cpt' ),
				'downloads'      => SimpleDigitalDownloads::get_download_choices(),
				'downloadTitles' => SimpleDigitalDownloads::get_download_titles(),
				'downloadLabel'  => __( 'Download', 'book-cpt' ),
				'retailerLabel'  => __( 'Retailer', 'book-cpt' ),
				'labelLabel'     => __( 'Label', 'book-cpt' ),
				'urlLabel'       => __( 'URL', 'book-cpt' ),
				'removeLabel'    => __( 'Remove', 'book-cpt' ),
				'selectDownload' => __( 'Select a download', 'book-cpt' ),
			]
		);
	}

	/**
	 * Hides SDD rows with nothing to deliver from logged out visitors.
	 *
	 * Buying one of these fails at SDD's checkout endpoint, so readers never
	 * see it. Logged in users do, to preview a book while its download is
	 * still being built.
	 *
	 * @param array<int, array<string, string>> $links Link rows.
	 * @return array<int, array<string, string>>
	 */
	private static function remove_pending_checkout_links( $links ) {
		if ( is_user_logged_in() ) {
			return $links;
		}

		$visible = [];

		foreach ( $links as $link ) {
			if (
				self::is_checkout_link( $link )
				&& ! SimpleDigitalDownloads::has_deliverable( (int) $link['download_id'] )
			) {
				continue;
			}

			$visible[] = $link;
		}

		return $visible;
	}

	/**
	 * Whether a purchase link row triggers SDD checkout.
	 *
	 * Rows whose download has nothing to deliver yet still count, so they keep
	 * rendering as checkout buttons rather than as empty links. SDD's checkout
	 * endpoint is the authority on whether the sale can complete.
	 *
	 * @param array<string, string> $link Link data.
	 * @return bool
	 */
	private static function is_checkout_link( $link ) {
		return SimpleDigitalDownloads::RETAILER_SLUG === ( $link['retailer'] ?? '' )
			&& ! empty( $link['download_id'] )
			&& SimpleDigitalDownloads::download_exists( (int) $link['download_id'] );
	}

	/**
	 * Puts Buy Direct (SDD checkout) links first, preserving order within each group.
	 *
	 * @param array<int, array<string, string>> $links Link rows.
	 * @return array<int, array<string, string>>
	 */
	private static function sort_links( $links ) {
		$checkout = [];
		$others   = [];

		foreach ( $links as $link ) {
			if ( self::is_checkout_link( $link ) ) {
				$checkout[] = $link;
			} else {
				$others[] = $link;
			}
		}

		return array_merge( $checkout, $others );
	}

	/**
	 * Appends the download title to checkout links that share a label.
	 *
	 * Books saved before labels were editable can end up with several
	 * "Buy Direct" rows; this keeps them tellable apart on the frontend.
	 *
	 * @param array<int, array<string, string>> $links Link rows.
	 * @return array<int, array<string, string>>
	 */
	private static function disambiguate_labels( $links ) {
		$counts = [];

		foreach ( $links as $link ) {
			$label            = $link['label'] ?? '';
			$counts[ $label ] = ( $counts[ $label ] ?? 0 ) + 1;
		}

		foreach ( $links as $index => $link ) {
			$label = $link['label'] ?? '';

			if ( ( $counts[ $label ] ?? 0 ) < 2 || ! self::is_checkout_link( $link ) ) {
				continue;
			}

			$title = SimpleDigitalDownloads::get_download_title( (int) $link['download_id'] );
			if ( '' === $title || $title === $label ) {
				continue;
			}

			$links[ $index ]['label'] = sprintf(
				/* translators: 1: purchase link label, 2: download title */
				__( '%1$s (%2$s)', 'book-cpt' ),
				$label,
				$title
			);
		}

		return $links;
	}

	/**
	 * Whether any links use SDD checkout.
	 *
	 * @param array<int, array<string, string>> $links Link rows.
	 * @return bool
	 */
	private static function has_checkout_links( $links ) {
		foreach ( $links as $link ) {
			if ( self::is_checkout_link( $link ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Renders HTML attributes for the purchase links wrapper.
	 *
	 * @param array<string, string> $attributes Attribute pairs.
	 * @return string
	 */
	private static function render_attributes( $attributes ) {
		$rendered = [];

		foreach ( $attributes as $name => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$rendered[] = sprintf(
				'%s="%s"',
				esc_attr( $name ),
				esc_attr( $value )
			);
		}

		return implode( ' ', $rendered );
	}
}

new PurchaseLinks();
