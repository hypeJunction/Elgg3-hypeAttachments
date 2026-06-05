<?php

$guid = get_input('guid');
$entity = get_entity($guid);

$attachment_guids = (array) get_input('attachment_guid');

foreach ($attachment_guids as $attachment_guid) {
	$attachment = get_entity($attachment_guid);
	if ($entity && $entity->canEdit() && $attachment && hypeapps_attach($entity, $attachment)) {
		elgg_register_success_message(elgg_echo('attachments:attach:success', [
			$entity->getDisplayName(),
			$attachment->getDisplayName()
		]));
	} else {
		elgg_register_error_message(elgg_echo('attachments:attach:error'));
	}
}
