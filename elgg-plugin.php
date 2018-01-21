<?php

return [
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
];