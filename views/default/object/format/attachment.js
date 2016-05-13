/**
 * Ajaxify detach action
 *
 * @module object/format/attachment
 */
define(function (require) {
	var $ = require('jquery');

	var Ajax = require('elgg/Ajax');
	var ajax = new Ajax();

	$(document).on('click', '.attachments-detach-action', function (e) {
		e.preventDefault();
		var $elem = $(this);
		ajax.action($elem.prop('href')).then(function () {
			$elem.closest('li').remove();
		});
	});
});