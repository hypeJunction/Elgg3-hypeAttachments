/**
 * @module input/attachments
 */
define(function(require) {
	var $ = require('jquery');
	$(document).on('click', '.attachments-toggler', function(e) {
		e.preventDefault();
		$(this).parent().addClass('attachments-show');
	});
	$(document).on('reset', 'form', function() {
		$(this).find('.attachments-show').removeClass('attachments-show');
	});
});
