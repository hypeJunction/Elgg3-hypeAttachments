<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggEntity) {
	return;
}

$output = elgg_view('output/attachments', [
	'entity' => $entity,
]);

echo elgg_view('post/module', [
	'title' => elgg_echo('attachments:title'),
	'body' => $output,
	'collapsed' => false,
	'class' => 'post-attachments',
]);