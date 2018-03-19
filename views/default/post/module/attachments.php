<?php

$entity = elgg_extract('entity', $vars);

$output = elgg_view('output/attachments', [
	'entity' => $entity,
]);

if (!$output) {
	return;
}

echo elgg_view('post/module', [
	'title' => elgg_echo('attachments'),
	'body' => $output,
	'collapsed' => false,
	'class' => 'post-attachments',
]);
