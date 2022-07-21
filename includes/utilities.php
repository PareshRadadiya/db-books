<?php
/**
 * Utility functions
 *
 * @package db-books
 */

namespace DB\Books\Utilities;

/**
 * Save book author detail.
 *
 * @param array $data    Autor profile detail.
 * @param int   $post_id Optional. The book post ID.
 *
 * @return void
 */
function insert_book_author_profile( $data, $post_id = 0 ) {
	$meta_mapping = [
		'author_name'   => $data['author_name'],
		'first_book'    => $data['first_book'],
		'location'      => $data['location'],
		'writtern_book' => $data['writtern_books'],
	];

	foreach ( $meta_mapping as $meta_key => $meta_value ) {
		if ( ! empty( $meta_value ) ) {
			update_post_meta( $post_id, $meta_key, $meta_value );
		}
	}
}

/**
 * Return author's profle detail by book post ID.
 *
 * @param int $post_id The book post id.
 * @return array List of author detail.
 */
function get_book_author_profile( $post_id ) {
	$meta_keys = [
		'author_name',
		'first_book',
		'location',
		'writtern_book',
	];

	$result = [];

	foreach ( $meta_keys as $meta_key ) {
		$result[ $meta_key ] = get_post_meta( $post_id, $meta_key, true );
	}

	return $result;
}

/**
 * Check whether the current page is create book form page.
 *
 * @return bool Whether the current page is create book form page.
 */
function is_book_form_page() {
	global $post;
	$page_id = absint( get_option( 'db_cpt_books_form' ) );

	return is_page() && $post->ID === $page_id;
}

/**
 * Check whether the current page is book list page.
 *
 * @return bool Whether the current page is book list page.
 */
function is_book_posts_page() {
	global $post;
	$page_id = absint( get_option( 'db_cpt_books_posts' ) );

	return is_page() && $post->ID === $page_id;
}


/**
 * Handle attaching photo to the book.
 *
 * @param string $file_handler Index of the ` $_FILES` array that the file was sent.
 * @param int    $post_id The book post id.
 *
 * @return void.
 */
function handle_book_attachment( $file_handler, $post_id ) {

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attachment_id = media_handle_upload( $file_handler, $post_id );

	if ( ! is_wp_error( $attachment_id ) ) {
		set_post_thumbnail( $post_id, $attachment_id );
	}
}
