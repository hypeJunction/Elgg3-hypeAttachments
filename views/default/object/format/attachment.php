<?php

/**
 * Displays an object as an attachment with an option to detach
 * @uses $vars['entity']  Attached entity
 * @uses $vars['size']    Icon size
 */
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggObject) {
	return;
}

$size = elgg_extract('size', $vars, 'medium');
$output = elgg_view_entity_icon($entity, $size);

$subject_guid = $entity->getVolatileData('attachment_subject');
$subject = get_entity($subject_guid);

if ($subject && $subject->canEdit()) {
	if ($entity->container_guid == $subject->guid) {
		// File has been uploaded with the subject, so it can be deleted and detached
		$delete = true;
		$title = elgg_echo('delete');
	} else {
		$delete = false;
		$title = elgg_echo('attachments:detach');
	}
	
	$output .= elgg_view('output/url', array(
		'text' => elgg_view_icon('delete'),
		'href' => elgg_http_add_url_query_elements('action/attachments/detach', [
			'guid' => $subject->guid,
			'attachment_guid' => $entity->guid,
			'delete' => $delete,
		]),
		'is_action' => true,
		'class' => 'attachments-detach-action',
		'title' => $title,
	));
}

echo elgg_format_element('div', array(
	'class' => 'attachments-attached-item',
	'title' => $entity->getDisplayName(),
		), $output);

