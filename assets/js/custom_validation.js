(function ($) {

	jQuery.validator.setDefaults({
		debug: true,
		success: "valid"
	});

	$.validator.addMethod('filesize', function (value, element, param) {
		return this.optional(element) || (element.files[0].size <= param * 1000000)
	}, 'File size must be less than {0} MB');

	// validate signup form on keyup and submit
	$("#book-form").validate({
		rules: {
			db_books_book_title: "required",
			db_books_author_name: "required",
			db_books_number_of_copies: {
				required: true,
				number: true
			},
			db_books_book_excerpt: {
				required: true,
				minlength: 5,
				maxlength: 500
			},
			db_books_book_description: {
				required: true,
				minlength: 5,
				maxlength: 5000
			},
			db_books_book_publish_date: "required",
			db_books_book_image: {
				required: true,
				accept: "image/png,image/jpeg,image/jpg",
				filesize: 0.5
			},
		},
		messages: {
			db_books_book_title: wp.i18n.__('Please enter your Book title', 'db-books'),
			db_books_author_name: wp.i18n.__('Please enter your Book author name', 'db-books'),
			db_books_number_of_copies: {
				required: wp.i18n.__('Please enter your Books number of copies', 'db-books'),

			},
			db_books_book_excerpt: {
				required: wp.i18n.__('Please enter Book excerpt', 'db-books'),
				minlength: wp.i18n.__('Book excerpt must be at least 5 characters long', 'db-books'),
				maxlength: wp.i18n.__('Book excerpt maximun length 500 characters long', 'db-books'),
			},
			db_books_book_description: {
				required: wp.i18n.__('Please enter Book description', 'db-books'),
				minlength: wp.i18n.__('Book description must be at least 5 characters long', 'db-books'),
				maxlength: wp.i18n.__('Book description maximun length 5000 characters long', 'db-books'),
			},
			db_books_book_publish_date: wp.i18n.__('Please select publish date', 'db-books'),
			db_books_book_image: {
				required: wp.i18n.__('Please Upload Book image', 'db-books'),
				accept: wp.i18n.__('Please upload .png or .jpeg or .jpg image file of notice.', 'db-books'),
				filesize: wp.i18n.__('file size must be less than 500 KB.', 'db-books'),
			},
		},
		submitHandler: function (form) {
			if ($(form).valid())
				form.submit();
			return false; // prevent normal form posting
		}
	});


	//modal js

	$(".meta-info").click(function (e) {

		e.preventDefault();
		var myBookId = $(this).data('id');
		var modal = document.getElementById("myModal-" + myBookId);
		var span = document.getElementsByClassName("close-" + myBookId)[0];

		modal.style.display = "block";

		//Read More 
		$('.read-more-books-excerpt').readmore({
			collapsedHeight: 45,
			speed: 200,
			lessLink: '<a href="#">Read less</a>'
		});
		$('.read-more-books-description').readmore({
			collapsedHeight: 70,
			speed: 200,
			lessLink: '<a href="#">Read less</a>'
		});

		span.onclick = function () {
			modal.style.display = "none";
		}

		window.onclick = function (event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}


	});

	//Read more
	$('.artical-text').readmore({
		collapsedHeight: 50,
		speed: 200,
		lessLink: '<a href="#">Read less</a>'
	});

})(jQuery);