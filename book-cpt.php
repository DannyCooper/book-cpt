<?php
/**
 * Plugin Name: Book CPT
 * Plugin URI: https://wordpress.org/plugins/book-cpt
 * Description: A WordPress Custom Post Type for books. Supports Genre and Series.
 * Author: Danny Cooper
 * Author URI: https://dannycooper.com
 * Version: 1.0.0
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * @package book-cpt
 */

namespace BookCPT;

require_once plugin_dir_path( __FILE__ ) . 'CustomPostType.php';
require_once plugin_dir_path( __FILE__ ) . 'Taxonomies.php';
require_once plugin_dir_path( __FILE__ ) . 'Meta.php';


