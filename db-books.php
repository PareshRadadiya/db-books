<?php
/**
 * Plugin Name: DB Books
 *
 * @package db-books
 *
 * Description: WordPress task.
 * Version: 0.0.1
 * Author: Paresh Radadiya
 * Author URI: https://pareshradadiya.github.io/
 * Text Domain: db-books
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/post-type.php';
DB\Books\Post_Type\setup();

require_once __DIR__ . '/includes/admin.php';
DB\Books\Admin_UI\setup();

require_once __DIR__ . '/includes/frontend.php';
DB\Books\Frontend\setup();

require_once __DIR__ . '/includes/utilities.php';

/**
 * Insert required pages on plugin activation.
 */
function db_book_activation() {
	// Create posts page.
	foreach ( [ 'CPT Books Posts', 'CPT Books Form' ] as $title ) {
		$page = get_posts(
			[
				'post_type' => 'page',
				'title'     => $title,
			]
		);

		if ( empty( $page ) ) {
			$post = array(
				'post_title'  => $title,
				'post_status' => 'publish',
				'post_type'   => 'page',
			);

			  // Insert the post into the database.
			  $page_id = wp_insert_post( $post );
		} else {
			$page_id = $page[0]->ID;
		}

		$option_name = 'db_' . strtolower( str_replace( ' ', '_', $title ) );

		update_option( $option_name, $page_id );
	}
}

register_activation_hook( __FILE__, 'db_book_activation' );
