define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	require('jquery.form');
	var lightbox = require('elgg/lightbox');
	var spinner = require('elgg/spinner');
	var Ajax = require('elgg/Ajax');

	$(document).on('submit', '#colorbox .elgg-form-attachments-upload', function (e) {

		e.preventDefault();

		var $form = $(this);

		var ajax = new Ajax();

		ajax.action($form.attr('action'), {
			data: ajax.objectify($form),
			beforeSend: function() {
				$form.find('[type="submit"]').prop('disabled', true);
			}
		}).done(function() {
			$form.find('[type="submit"]').prop('disabled', false);
			lightbox.close();
		});

	});
});
