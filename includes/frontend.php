<?php
/**
 * Adds a user interface for frontend.
 *
 * @package db-books
 */

namespace DB\Books\Frontend;

use DB\Books\Utilities;
use DB\Books\Post_Type as Book_Post_Type;

/**
 * Register hooks.
 */
function setup() {
	add_filter( 'the_content', __NAMESPACE__ . '\\render_form', 1 );
	add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts', 20, 1 );
	add_action( 'init', __NAMESPACE__ . '\\handle_book_saving' );
	add_filter( 'the_content', __NAMESPACE__ . '\\render_books', 1 );
}

/**
 * Render book form.
 */
function render_form( $content ) {
	if ( ! Utilities\is_book_form_page() ) {
		return $content;
	}

	ob_start();
	?>
	<article class="db-books-form-wrapper">
		<div class="db-books-form-inner">
			<form method="POST" action="" class="db-books-form" enctype="multipart/form-data" id="book-form">

				<div class="db-books-field">
					<label for="db_books_book_title"><?php esc_html_e( 'Book Title', 'db-books' );?><span class="db_books_book_required">*</span></label><br>
					<input type="text" name="db_books_book_title" id="db_books_book_title" value="" class="db-books-input" />
				</div>
				<div class="db-books-field">
					<label for="db_books_author_name"><?php esc_html_e( 'Author Name', 'db-books' ); ?><span class="db_books_book_required">*</span></label><br>
					<input type="text" name="db_books_author_name" id="db_books_author_name" value="" class="db-books-input" />
				</div>
				<div class="db-books-field">
					<label for="db_books_number_of_copies"><?php esc_html_e( 'Number of copies', 'db-books' ); ?><span class="db_books_book_required">*</span></label><br>
					<input type="number" name="db_books_number_of_copies" id="db_books_number_of_copies" value="" class="db-books-input" />
				</div>
				<div class="db-books-field">
					<label for="db_books_book_excerpt"><?php esc_html_e( 'Book Excerpt', 'db-books' ); ?><span class="db_books_book_required">*</span></label><br>
					<textarea type="text" name="db_books_book_excerpt" id="db_books_book_excerpt" value="" class="db-books-input" rows="2"></textarea>
				</div>
				<div class="db-books-field">
					<label for="db_books_book_description"><?php esc_html_e( 'Book Description', 'db-books' ); ?><span class="db_books_book_required">*</span></label><br>
					<textarea type="text" name="db_books_book_description" id="db_books_book_description" value="" class="db-books-input" rows="3"></textarea>
				</div>
				<div class="db-books-field">
					<label for="db_books_book_publish_date"><?php esc_html_e( 'Book Publish Date', 'db-books' ); ?><span class="db_books_book_required">*</span></label><br>
					<input type="date" name="db_books_book_publish_date" id="db_books_book_publish_date" value="" class="db-books-input" />
				</div>
				<div class="db-books-field">
					<input type="file" name="db_books_book_image" />
				</div>
				<div class="db-books-field">
					<button class="submit db_book_btn_submit" type="submit"><?php esc_html_e( 'Submit', 'db-books' ); ?></button>
				</div>

				<?php wp_nonce_field( 'db_book_add', 'db_book_add_nonce' ); ?>
			</form>

		</div>
	</article>
	<?php
	return ob_get_clean();
}

/**
 * Enqueue scripts & styles.
 */
function enqueue_scripts() {
	$version = wp_get_theme()->version;
	wp_enqueue_style(
		'cpt-books-form-style',
		plugins_url( '/assets/css/frontend.css', __DIR__ ),
		[],
		$version
	);
	wp_enqueue_script(
		'validation-script',
		plugins_url( '/assets/js/jquery.validate.min.js', __DIR__ ),
		[ 'jquery', 'wp-i18n' ],
		$version,
		true
	);
	wp_enqueue_script(
		'additional-methods',
		plugins_url( '/assets/js/additional_methods.min.js', __DIR__ ),
		[],
		$version,
		true
	);
	wp_enqueue_script(
		'prettify-script',
		plugins_url( '/assets/js/prettify.min.js', __DIR__ ),
		[],
		$version,
		true
	);
	wp_enqueue_script(
		'readmore-script',
		plugins_url( '/assets/js/readmore.min.js', __DIR__ ),
		[],
		$version,
		true
	);
	wp_enqueue_script(
		'cpt-books-form-script',
		plugins_url( '/assets/js/custom_validation.js', __DIR__ ),
		[],
		$version,
		true
	);
}

/**
 * Handle book saving from the frontend form.
 */
function handle_book_saving() {
	if ( ! isset( $_POST['db_book_add_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['db_book_add_nonce'] ) ), 'db_book_add' ) ) {
		return false;
	}

	$post_data = [
		'post_title'   => sanitize_text_field( $_POST['db_books_book_title'] ),
		'post_content' => sanitize_text_field( $_POST['db_books_book_description'] ),
		'post_excerpt' => sanitize_text_field( $_POST['db_books_book_excerpt'] ),
		'post_status'  => 'publish',
		'post_type'    => Book_Post_Type\SLUG,
	];

	$post_id = wp_insert_post( $post_data );

	Utilities\handle_book_attachment( 'db_books_book_image', $post_id );

	update_post_meta( $post_id, 'author_name', sanitize_text_field( $_POST['db_books_author_name'] ) );
	update_post_meta( $post_id, 'writtern_books', absint( $_POST['db_books_number_of_copies'] ) );
	update_post_meta( $post_id, 'publish_date', sanitize_text_field( $_POST['db_books_book_publish_date'] ) );
}

/**
 * Render list of the books.
 *
 * @param string $content The post content.
 * @return string The page content.
 */
function render_books( $content ) {
	if ( ! Utilities\is_book_posts_page() ) {
		return $content;
	}

	$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

	// The Query.
	$query = new \WP_Query(
		[
			'post_type'      => Book_Post_Type\SLUG,
			'post_status'    => 'publish',
			'posts_per_page' => 10,
			'paged'          => $paged,
		]
	);

	$books = $query->get_posts();
	ob_start();
	?>
	<article class="db-books-table">

		<?php
		foreach ( $books as $book ) :
			$number_of_copies = get_post_meta( $book->ID, 'writtern_books', true );
			$author_name      = get_post_meta( $book->ID, 'author_name', true );
			$publish_date     = get_post_meta( $book->ID, 'publish_date', true );
			$image            = wp_get_attachment_image_src( get_post_thumbnail_id( $book->ID ), 'single-post-thumbnail' );
			?>
			<div class="db-books-data">
				<div class="db-books-image"><img src="<?php echo $image[0]; ?>" alt="book image"></div>
				<div class="db-books-details">
					<p class="book-title"><a href="<?php echo get_post_permalink( $book->ID ); ?>"><?php echo $book->post_title; ?></a></p>
					<p class="author-name">By <?php echo $author_name; ?></p>
					<p class="books-copies"><b>Available Books of Copies:</b> <?php echo $number_of_copies; ?></p>
					<p class="books-excerpt artical-text"><b>Book Excerpt:</b> <?php echo $book->post_excerpt; ?></p>
				</div>
				<div class="db-books-btn-modal">
					<!-- Trigger/Open The Modal -->
					<button class="meta-info" data-id="<?php echo $book->ID; ?>">Display Meta Info</button>

					<!-- The Modal -->
					<div id="myModal-<?php echo $book->ID; ?>" class="modal">

						<!-- Modal content -->
						<div class="modal-content">
							<div class="modal-header">
								<span class="close close-<?php echo $book->ID; ?>">&times;</span>
								<h4>Book Info</h4>
							</div>
							<div class="modal-body">
								<div class="db-books-image-modal">
									<img src="<?php echo $image[0]; ?>" alt="book image">
								</div>
								<div class="db-books-details-modal">
									<p class="book-title"><a href="<?php echo get_post_permalink( $book->ID ); ?>"><?php echo $book->post_title; ?></a></p>
									<p class="author-name"><span>By</span> <?php echo $author_name; ?></p>
									<p class="publish_date"><b>Publish on: </b><?php echo $publish_date; ?></p>
									<p class="books-copies"><b>Numbers of Copies: </b><?php echo $number_of_copies; ?></p>
									<!-- <p class="books-excerpt read-more-books-excerpt"><b>Book Excerpt:</b> <?php //echo $book->post_excerpt; ?></p> -->
									<p class="books-description read-more-books-description"><b>Book Description:</b> <?php echo $book->post_content; ?></p>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
			<?php
		endforeach;
		?>

	</article>
	<div class="db-books-pagination">
		<?php

		wp_reset_postdata();

		$big = 999999999;

		echo paginate_links(
			array(
				'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format'  => '?paged=%#%',
				'current' => max( 1, get_query_var( 'paged' ) ),
				'total'   => $query->max_num_pages,
			)
		);
		?>
	</div>
	<?php

	return ob_get_clean();
}
