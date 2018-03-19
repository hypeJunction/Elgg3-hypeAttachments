<?php

$guid = get_input('guid');
$entity = get_entity($guid);

$attachment_guids = (array) get_input('attachment_guid');

foreach ($attachment_guids as $attachment_guid) {
	$attachment = get_entity($attachment_guid);
	if ($entity && $entity->canEdit() && $attachment && hypeapps_attach($entity, $attachment)) {
		system_message(elgg_echo('attachments:attach:success', [
			$entity->getDisplayName(),
			$attachment->getDisplayName()
		]));
	} else {
		register_error(elgg_echo('attachments:attach:error'));
	}
}
