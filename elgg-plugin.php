<?php

use hypeJunction\Attachments\Events;
use hypeJunction\Attachments\Menus;
use hypeJunction\Attachments\Permissions;

return [
	'plugin' => [
		'name' => 'hypeAttachments',
		'description' => 'File attachments for Elgg',
		'version' => '4.0.0',
		'dependencies' => [
			'file' => [],
			'hypeDropzone' => [
				'must_be_active' => false,
			],
		],
	],
	'actions' => [
		'attachments/attach' => [],
		'attachments/detach' => [],
		'attachments/upload' => [],
	],
	'routes' => [
		'attachments:upload' => [
			'path' => '/attachments/upload/{guid}',
			'resource' => 'attachments/upload',
		],
		'attachments:view' => [
			'path' => '/attachments/view/{guid}',
			'resource' => 'attachments/view',
		],
	],
	'hooks' => [
		'register' => [
			'menu:entity' => [
				Menus::class . '::setupEntityMenu' => [],
			],
			'menu:social' => [
				Menus::class . '::setupEntitySocialMenu' => [],
			],
		],
		'allow_attachments' => [
			'all' => [
				Permissions::class . '::allowsAttachments' => [],
			],
		],
		'permissions_check' => [
			'object' => [
				Permissions::class . '::protectMessageAttachments' => ['priority' => 999],
			],
		],
		'fields' => [
			'object' => [
				\hypeJunction\Attachments\AddFormField::class => [],
			],
		],
		'modules' => [
			'object' => [
				\hypeJunction\Attachments\AddAttachmentsModule::class => [],
			],
		],
	],
	'events' => [
		'create' => [
			'object' => [
				Events::class . '::saveCommentAttachments' => [],
				Events::class . '::saveMessageAttachments' => [],
			],
		],
		'update' => [
			'object' => [
				Events::class . '::saveCommentAttachments' => [],
				Events::class . '::saveMessageAttachments' => [],
				Events::class . '::syncAttachmentAccess' => [],
			],
		],
	],
	'view_extensions' => [
		'css/elgg' => [
			'css/input/attachments.css' => [],
		],
	],
];
