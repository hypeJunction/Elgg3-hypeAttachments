<?php

if (elgg_in_context('activity')) {
	return;
}

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof ElggEntity) {
	return;
}

if (!hypeapps_allow_attachments($entity->getType(), $entity->getSubtype())) {
	return;
}

$attachments = elgg_view('output/attachments', $vars);
if ($attachments) {
	echo elgg_view_module('aside', elgg_echo('attachments:title'), $attachments, [
		'class' => 'sbw-attachments-module',
	]);
}