<?php

namespace BookCPT;

/**
 * Preset retailer definitions shared by the admin UI and purchase block.
 */
class Retailers {

	/**
	 * Returns all preset retailers keyed by slug.
	 *
	 * @return array<string, string>
	 */
	public static function get_all() {
		$retailers = [
			'amazon'       => __( 'Amazon', 'book-cpt' ),
			'apple-books'  => __( 'Apple Books', 'book-cpt' ),
			'barnes-noble' => __( 'Barnes & Noble', 'book-cpt' ),
			'kobo'         => __( 'Kobo', 'book-cpt' ),
			'google-play'  => __( 'Google Play', 'book-cpt' ),
		];

		if ( SimpleDigitalDownloads::is_available() ) {
			$retailers['simple-digital-download'] = __( 'Simple Digital Download', 'book-cpt' );
		}

		$retailers['custom'] = __( 'Other', 'book-cpt' );

		return $retailers;
	}

	/**
	 * Returns valid retailer slugs.
	 *
	 * @return string[]
	 */
	public static function get_slugs() {
		return array_keys( self::get_all() );
	}

	/**
	 * Returns the default label for a retailer slug.
	 *
	 * @param string $slug Retailer slug.
	 * @return string
	 */
	public static function get_label( $slug ) {
		$retailers = self::get_all();

		return $retailers[ $slug ] ?? $retailers['custom'];
	}
}
