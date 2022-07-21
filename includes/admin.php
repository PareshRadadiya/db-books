<?php
/**
 * Adds a user interface for defining books.
 *
 * @package db-books
 */

namespace DB\Books\Admin_UI;

use DB\Books\Post_Type as Book_Post_Type;
use DB\Books\Utilities;
use WP_Post;

/**
 * Register hooks.
 */
function setup() {
	add_action( 'add_meta_boxes', __NAMESPACE__ . '\\add_meta_box' );
	add_action( 'save_post', __NAMESPACE__ . '\\handle_author_profile_saving' );
}

/**
 * Add the metabox for the redirects.
 */
function add_meta_box() {
	\add_meta_box(
		'db-books-meta',
		esc_html__( 'Book Detail', 'db-book' ),
		__NAMESPACE__ . '\\output_meta_box',
		Book_Post_Type\SLUG,
		'normal',
		'high'
	);
}

/**
 * Output the redirects metabox,
 *
 * @param WP_Post $post The currently edited post.
 */
function output_meta_box( $post ) {
	$book_detail = Utilities\get_book_author_profile( $post->ID );
	?>
	<p>
		<label for="db_books_author_name"><?php esc_html_e( 'Author Name', 'db-books' ); ?></label><br>
		<input type="text" name="db_books_author_name" id="db_books_author_name" value="<?php echo esc_attr( $book_detail['author_name'] ); ?>" class="code widefat"/>
	</p>
	<p>
		<label for="db_books_author_first_book"><?php esc_html_e( 'Author First Book', 'db-books' ); ?></label><br>
		<input type="text" name="db_books_author_first_book" id="db_books_author_first_book" value="<?php echo esc_attr( $book_detail['first_book'] ); ?>" class="code widefat"/>
	</p>
	<p>
		<label for="db_books_author_location"><?php esc_html_e( 'Author Location', 'db-books' ); ?></label><br>
		<input type="text" name="db_books_author_location" id="db_books_author_location" value="<?php echo esc_attr( $book_detail['location'] ); ?>" class="code widefat"/>
	</p>
	<p>
		<label for="db_books_author_writtern_books"><?php esc_html_e( 'Author Writtern Books', 'db-books' ); ?></label><br>
		<input type="text" name="db_books_author_writtern_books" id="db_books_author_writtern_books" value="<?php echo esc_attr( $book_detail['writtern_book'] ); ?>" class="code widefat"/>
	</p>
	<?php
	wp_nonce_field( 'db_books', 'db_books_nonce' );
}

/**
 * Save the book information.
 *
 * @param int $post_id Saved post id.
 *
 * @return bool Whether the redirect was saved successfully.
 */
function handle_author_profile_saving( $post_id ) {
	if ( ! isset( $_POST['db_books_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['db_books_nonce'] ) ), 'db_books' ) ) {
		return false;
	}

	if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
		return false;
	}

	// phpcs:disable WordPress.VIP.ValidatedSanitizedInput
	// We're using a custom sanitisation function.
	$data = sanitise_data(
		wp_unslash( $_POST['db_books_author_name'] ),
		wp_unslash( $_POST['db_books_author_first_book'] ),
		wp_unslash( $_POST['db_books_author_location'] ),
		wp_unslash( $_POST['db_books_author_writtern_books'] )
	);
	// phpcs:enable

	Utilities\insert_book_author_profile( $data, $post_id );
}

/**
 * Sanitise and normalise data for a redirect post.
 *
 * @param string $author_name    Book author name.
 * @param string $first_book     First published book by the author.
 * @param string $location       The author location.
 * @param int    $writtern_books Total number of books written by the author.
 *
 * @return array
 */
function sanitise_data( $author_name, $first_book, $location, $writtern_books ) {
	return [
		'author_name'    => sanitize_text_field( $author_name ),
		'first_book'     => sanitize_text_field( $first_book ),
		'location'       => sanitize_text_field( $location ),
		'writtern_books' => absint( $writtern_books ),
	];
}
