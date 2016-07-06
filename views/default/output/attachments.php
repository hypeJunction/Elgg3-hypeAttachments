<?php
/**
 * Displays a gallery of attachments
 * @uses $vars['entity']
 * @uses $vars['list_options']
 */
$entity = elgg_extract('entity', $vars);
/* @var \ElggEntity $entity */

if (!$entity instanceof ElggEntity) {
	return;
}

$count = hypeapps_has_attachments($entity);
if (!$count) {
	return;
}

$attachments = hypeapps_get_attachments($entity, ['limit' => 0]);

$options = [
	'limit' => 0,
	'pagination' => false,
	'size' => elgg_extract('size', $vars, 'medium'),
	'no_results' => elgg_echo('attachments:no_results'),
	'full_view' => false,
];

$list_options = (array) elgg_extract('list_options', $vars, []);

echo elgg_view_entity_list($attachments, array_merge($options, $list_options));
?>

<script>require(['output/attachments']);</script>