<?php
/**
 * Plugin Name: Book CPT
 * Plugin URI: https://wordpress.org/plugins/books
 * Description: A WordPress Custom Post Type for books. Supports Authors, Genre and Series.
 * Author: Danny Cooper
 * Author URI: https://dannycooper.com
 * Version: 1.1.6
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * @package book-cpt
 */

namespace BookCPT;

define( 'BOOK_CPT_VERSION', '1.1.6' );
define( 'BOOK_CPT_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOOK_CPT_URL', plugin_dir_url( __FILE__ ) );

require_once BOOK_CPT_PATH . 'CustomPostType.php';
require_once BOOK_CPT_PATH . 'Taxonomies.php';
require_once BOOK_CPT_PATH . 'Retailers.php';
require_once BOOK_CPT_PATH . 'PurchaseLinks.php';
require_once BOOK_CPT_PATH . 'Meta.php';
require_once BOOK_CPT_PATH . 'Block.php';
