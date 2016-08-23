define(function (require) {

	var elgg = require('elgg');
	var $ = require('jquery');
	require('jquery.form');
	var lightbox = require('elgg/lightbox');
	var spinner = require('elgg/spinner');

	$(document).on('submit', '#colorbox .elgg-form-attachments-upload', function (e) {

		e.preventDefault();
		var $form = $(this);

		$form.ajaxSubmit({
			dataType: 'json',
			headers: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			beforeSend: function () {
				$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
				spinner.start();
			},
			complete: function () {
				$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
				spinner.stop();
			},
			success: function (data) {
				if (data.status >= 0) {
					lightbox.close();
				}
				if (data.system_messages) {
					elgg.register_error(data.system_messages.error);
					elgg.system_message(data.system_messages.success);
				}
			}
		});
	});
});