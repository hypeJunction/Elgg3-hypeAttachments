<?php

/**
 * Displays a gallery of attachments
 * @uses $vars['entity']
 */

$size = elgg_extract('size', $vars, 'medium');

$entity = elgg_extract('entity', $vars);
/* @var \ElggEntity $entity */

$count = hypeapps_has_attachments($entity);
if (!$count) {
	return true;
}

$attachments = hypeapps_get_attachments($entity, ['limit' => 0]);
echo elgg_view_entity_list($attachments, [
	'list_type' => 'gallery',
	'gallery_class' => 'attachments-list elgg-gallery-fluid',
	'item_class' => 'mas elgg-photo',
	'item_view' => 'object/format/attachment',
	'subject' => $entity,
	'limit' => 0,
	'pagination' => false,
	'size' => $size,
]);
