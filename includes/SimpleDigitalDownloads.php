<?php

namespace BookCPT;

/**
 * Integration helpers for Simple Digital Downloads.
 */
class SimpleDigitalDownloads {

	const RETAILER_SLUG = 'simple-digital-download';

	/**
	 * Whether the SDD plugin is active and its post type is registered.
	 *
	 * @return bool
	 */
	public static function is_available() {
		return post_type_exists( 'sdd_download' );
	}

	/**
	 * Returns the REST checkout URL when SDD is available.
	 *
	 * @return string
	 */
	public static function get_checkout_url() {
		if ( ! self::is_available() ) {
			return '';
		}

		$namespace = defined( 'SDD_REST_NAMESPACE' ) ? SDD_REST_NAMESPACE : 'simple-digital-downloads/v1';

		return rest_url( $namespace . '/checkout' );
	}

	/**
	 * Whether a download post exists and can be offered to readers.
	 *
	 * A download that exists but has nothing to deliver yet still counts here,
	 * so authors can pick it while the book and the download are both in
	 * progress. Use is_valid_download() to gate an actual checkout.
	 *
	 * @param int $download_id Download post ID.
	 * @return bool
	 */
	public static function download_exists( $download_id ) {
		$download_id = absint( $download_id );
		if ( ! $download_id || ! self::is_available() ) {
			return false;
		}

		$download = get_post( $download_id );
		if ( ! $download || 'sdd_download' !== $download->post_type || 'publish' !== $download->post_status ) {
			return false;
		}

		return ! post_password_required( $download );
	}

	/**
	 * Whether a download has a file or delivery text attached.
	 *
	 * @param int $download_id Download post ID.
	 * @return bool
	 */
	public static function has_deliverable( $download_id ) {
		return function_exists( 'sdd_download_has_deliverable' )
			? (bool) sdd_download_has_deliverable( absint( $download_id ) )
			: true;
	}

	/**
	 * Whether a download post is valid for checkout.
	 *
	 * @param int $download_id Download post ID.
	 * @return bool
	 */
	public static function is_valid_download( $download_id ) {
		return self::download_exists( $download_id ) && self::has_deliverable( $download_id );
	}

	/**
	 * Whether a download is free.
	 *
	 * @param int $download_id Download post ID.
	 * @return bool
	 */
	public static function is_free_download( $download_id ) {
		return (int) get_post_meta( absint( $download_id ), '_sdd_price', true ) <= 0;
	}

	/**
	 * Returns a download's title.
	 *
	 * @param int $download_id Download post ID.
	 * @return string
	 */
	public static function get_download_title( $download_id ) {
		$download_id = absint( $download_id );
		if ( ! $download_id || ! self::is_available() ) {
			return '';
		}

		$download = get_post( $download_id );
		if ( ! $download || 'sdd_download' !== $download->post_type ) {
			return '';
		}

		return sanitize_text_field( $download->post_title );
	}

	/**
	 * Returns raw titles for published downloads, keyed by ID.
	 *
	 * @return array<int, string>
	 */
	public static function get_download_titles() {
		$titles = [];

		foreach ( array_keys( self::get_download_choices() ) as $id ) {
			$titles[ $id ] = self::get_download_title( $id );
		}

		return $titles;
	}

	/**
	 * Returns published downloads for the admin picker.
	 *
	 * @return array<int, string>
	 */
	public static function get_download_choices() {
		if ( ! self::is_available() ) {
			return [];
		}

		$downloads = get_posts(
			[
				'post_type'      => 'sdd_download',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		$choices = [];
		foreach ( $downloads as $download ) {
			if ( ! self::download_exists( $download->ID ) ) {
				continue;
			}

			$choices[ $download->ID ] = self::get_download_choice_label( $download );
		}

		return $choices;
	}

	/**
	 * Formats a download title for the admin picker.
	 *
	 * @param \WP_Post $download Download post.
	 * @return string
	 */
	private static function get_download_choice_label( $download ) {
		$label = $download->post_title;
		$price = (int) get_post_meta( $download->ID, '_sdd_price', true );

		if ( $price <= 0 ) {
			$label = sprintf(
				/* translators: %s: download title */
				__( '%s — Free', 'book-cpt' ),
				$label
			);
		} elseif ( function_exists( 'sdd_format_price' ) ) {
			$currency = get_option( 'sdd_currency', 'USD' );
			$label   .= ' — ' . sdd_format_price( $price, $currency );
		}

		if ( ! self::has_deliverable( $download->ID ) ) {
			$label = sprintf(
				/* translators: %s: download label */
				__( '%s (no deliverable yet)', 'book-cpt' ),
				$label
			);
		}

		return $label;
	}
}
