<?php

/**
 * Attachments
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2016, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

use hypeJunction\Attachments\Events;
use hypeJunction\Attachments\Menus;
use hypeJunction\Attachments\Permissions;
use hypeJunction\Attachments\Views;

elgg_register_event_handler('init', 'system', function () {

	elgg_register_action('attachments/attach', __DIR__ . '/actions/attachments/attach.php');
	elgg_register_action('attachments/detach', __DIR__ . '/actions/attachments/detach.php');
	elgg_register_action('attachments/upload', __DIR__ . '/actions/attachments/upload.php');

	elgg_register_plugin_hook_handler('register', 'menu:entity', [Menus::class, 'setupEntityMenu']);
	elgg_register_plugin_hook_handler('allow_attachments', 'all', [Permissions::class, 'allowsAttachments']);

	elgg_register_plugin_hook_handler('view_vars', 'object/elements/summary', [Views::class, 'filterSummaryVars']);
	elgg_register_plugin_hook_handler('view_vars', 'object/elements/full', [Views::class, 'filterFullViewVars']);
	
	elgg_register_event_handler('create', 'object', [Events::class, 'saveCommentAttachments']);
	elgg_register_event_handler('update', 'object', [Events::class, 'saveCommentAttachments']);

	elgg_register_event_handler('create', 'object', [Events::class, 'saveMessageAttachments']);
	elgg_register_event_handler('update', 'object', [Events::class, 'saveMessageAttachments']);
	elgg_register_plugin_hook_handler('permissions_check', 'object', [Permissions::class, 'protectMessageAttachments'], 999);
	
	elgg_register_event_handler('update', 'object', [Events::class, 'syncAttachmentAccess']);

	elgg_extend_view('css/elgg', 'css/input/attachments.css');

	elgg_register_plugin_hook_handler('register', 'menu:social', [Menus::class, 'setupEntitySocialMenu']);

	// Arck CMS integration
	elgg_register_plugin_hook_handler('fields', 'all', [\hypeJunction\Attachments\CMS::class, 'addAttachmentField']);
	elgg_register_plugin_hook_handler('fields:profile', 'all', [\hypeJunction\Attachments\CMS::class, 'filterProfileFields']);

});
