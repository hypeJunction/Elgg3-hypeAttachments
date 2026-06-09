<?php

$guid = get_input('guid');
$entity = $guid ? get_entity((int) $guid) : null;

$attachment_guids = (array) get_input('attachment_guid');

foreach ($attachment_guids as $attachment_guid) {
	$attachment = $attachment_guid ? get_entity((int) $attachment_guid) : null;
	if ($entity && $entity->canEdit() && $attachment && hypeapps_attach($entity, $attachment)) {
		elgg_register_success_message(elgg_echo('attachments:attach:success', [
			$entity->getDisplayName(),
			$attachment->getDisplayName()
		]));
	} else {
		elgg_register_error_message(elgg_echo('attachments:attach:error'));
	}
}
