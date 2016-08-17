define(function (require) {

	var $ = require('jquery');
	require('jquery.form');

	$(document).on('submit', '#colorbox .elgg-form-attachments-upload', function (e) {
		e.preventDefault();
		var $form = $(this);

		$form.ajaxSubmit({
			dataType: 'json',
			iframe: true,
			data: {
				'X-Requested-With': 'XMLHttpRequest'
			},
			beforeSend: function () {
				$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
			},
			complete: function () {
				$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
			},
			success: function (data) {
				if (data.status >= 0) {
					$.colorbox.close();
				}
				if (data.system_messages) {
					elgg.register_error(data.system_messages.error);
					elgg.system_message(data.system_messages.success);
				}
			}
		});
	});
});