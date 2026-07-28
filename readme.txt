=== Books Custom Post Type (Genre, Authors and Series) ===
Contributors: dannycooper
Tags: books, author, genre, series, purchase links, block
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 1.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress custom post type for books with genre, series, and author taxonomies, plus purchase links.

== Description ==

Books CPT registers a **Book** post type for managing books in WordPress.

**Taxonomies**

* **Genre** — hierarchical categories for book genres
* **Series** — hierarchical categories for book series
* **Authors** — assign one or more authors per book

**Book details**

Each book supports an excerpt, a series order field, and repeatable purchase links (retailer + URL). Add links in the **Book details** meta box below the editor.

**Book Purchase Links block**

Insert the **Book Purchase Links** block on a book page or template to show a single call-to-action button. Clicking it opens a dropdown of retailer links — so multiple stores never clutter the page with separate buttons.

Find the block by searching **book** or **buy**, or browse the **Books** block category in the inserter.

Customize the button text via **Button label** in the block settings sidebar when the block is selected.

Preset retailers: Amazon, Apple Books, Barnes & Noble, Kobo, Google Play, and Other (custom label).

== Installation ==

1. Upload the plugin zip via **Plugins → Add New → Upload Plugin**, or install from the WordPress plugin directory.
2. Activate **Book CPT**.
3. Add or edit a book under **Books** in the admin menu.
4. Add purchase links in the **Book details** meta box.
5. Insert the **Book Purchase Links** block on the book template or single book page.

== Frequently Asked Questions ==

= Where do I add purchase links? =

Edit a book and scroll below the editor to the **Book details** meta box. Use **Add purchase link** to add retailer URLs.

= Can I customize the button label? =

Yes. Select the Book Purchase Links block and change **Button label** under **Button settings** in the block sidebar.

= How do I find the block in the editor? =

Search for **book** or **buy** in the block inserter, or open the **Books** block category.

== Changelog ==

= 1.2.2 =
* Enable revision support for books

= 1.2.1 =
* Enable excerpt support for books

= 1.2.0 =
* Restore books.php as the main plugin file (matches WordPress.org slug)
* Keep book-cpt.php as a compatibility loader for recent installs

= 1.1.9 =
* Update plugin title to Books Custom Post Type (Genre, Authors and Series)

= 1.1.8 =
* Remove internal development notes from readme

= 1.1.7 =
* Add GitHub Action to deploy to WordPress.org on push

= 1.1.6 =
* Fix plugin URI to https://wordpress.org/plugins/books

= 1.1.5 =
* Rename block to Book Purchase Links for easier inserter discovery
* Add Books block category and expand block search keywords
* Update readme and plugin documentation

= 1.1.4 =
* Fix purchase links being cleared when meta box data is not submitted on save
* Tighten REST API permissions for book meta fields
* Widen purchase dropdown and raise z-index so it displays above surrounding content

= 1.1.3 =
* Move Book details and purchase links UI to the meta box below the editor
* Improve admin layout for purchase link rows

= 1.1.2 =
* Fix block editor compatibility for book details fields

= 1.1.1 =
* Add Book details fields to the block editor

= 1.1.0 =
* Add purchase link meta with retailer presets
* Add Purchase Links block with retailer dropdown UI

= 1.0.0 =
* Initial release with Book post type, Genre, and Series taxonomies
