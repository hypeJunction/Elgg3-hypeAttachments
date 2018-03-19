/**
 * Ajaxify detach action
 *
 * @module output/attachments
 */
define(function (require) {
	var $ = require('jquery');
	var elgg = require('elgg');

	$(document).on('click', '.attachments-detach-action', function (e) {
		e.preventDefault();
		var $elem = $(this);
		elgg.action($elem.prop('href'), {
			success: function () {
				$elem.closest('li').remove();
			}
		});
	});
});
