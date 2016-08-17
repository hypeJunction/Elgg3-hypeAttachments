<?php

elgg_gatekeeper();

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid);

$entity = get_entity($guid);
if (!$entity->canEdit() || !hypeapps_allow_attachments($entity->getType(), $entity->getSubtype())) {
	register_error(elgg_echo('actionnotauthorized'));
	forward(REFERRER);
}

elgg_set_page_owner_guid($entity->container_guid);

$title = elgg_echo('attachments:upload');

elgg_push_breadcrumb($entity->getDisplayName(), $entity->getURL());
elgg_push_breadcrumb($title);

$content = elgg_view_form('attachments/upload', [
	'enctype' => 'multipart/form-data',
		], [
	'entity' => $entity,
		]);

if (elgg_is_xhr()) {
	echo elgg_view_module('aside', $title, $content);
} else {
	$layout = elgg_view_layout('content', [
		'title' => $title,
		'content' => $content,
		'filter' => '',
		'entity' => $entity,
	]);
	echo elgg_view_page($title, $layout, 'default', [
		'entity' => $entity,
	]);
}
