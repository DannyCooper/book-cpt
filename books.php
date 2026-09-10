<?php
/**
 * Plugin Name: Books Custom Post Type (Genre, Authors and Series)
 * Plugin URI: https://wordpress.org/plugins/books
 * Description: A WordPress Custom Post Type for books. Supports Authors, Genre and Series.
 * Author: Danny Cooper
 * Author URI: https://dannycooper.com
 * Version: 1.2.9
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * @package books
 */

namespace BookCPT;

define( 'BOOK_CPT_VERSION', '1.2.9' );
define( 'BOOK_CPT_PATH', plugin_dir_path( __FILE__ ) );
define( 'BOOK_CPT_URL', plugin_dir_url( __FILE__ ) );

require_once __DIR__ . '/includes/CustomPostType.php';
require_once __DIR__ . '/includes/Taxonomies.php';
require_once __DIR__ . '/includes/SimpleDigitalDownloads.php';
require_once __DIR__ . '/includes/Retailers.php';
require_once __DIR__ . '/includes/PurchaseLinks.php';
require_once __DIR__ . '/includes/Meta.php';
require_once __DIR__ . '/includes/Block.php';
