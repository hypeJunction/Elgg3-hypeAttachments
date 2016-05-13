<?php

/**
 * Displays an object as an attachment with an option to detach
 * @uses $vars['entity']  Attached entity
 * @uses $vars['subject'] Entity this attachment is attached to
 * @uses $vars['size']    Icon size
 */
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggObject) {
	return;
}

$subject = elgg_extract('subject', $vars);
if (!$subject instanceof \ElggObject) {
	return;
}

$size = elgg_extract('size', $vars, 'medium');
$output = elgg_view_entity_icon($entity, $size);

if ($subject->canEdit()) {
	if ($entity->canDelete() && $entity->container_guid == $subject->guid) {
		// File has been uploaded with the subject, so it can be deleted and detached
		$delete = true;
		$title = elgg_echo('delete');
	} else {
		$delete = false;
		$title = elgg_echo('interactions:detach');
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
	));
}

echo elgg_format_element('div', array(
	'class' => 'attachments-attached-item',
		), $output);
?>

<script>require(['object/format/attachment']);</script>