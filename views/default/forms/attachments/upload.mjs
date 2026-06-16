import $ from 'jquery';
import 'jquery.form';
import * as lightbox from 'elgg/lightbox';
import Ajax from 'elgg/Ajax';

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
